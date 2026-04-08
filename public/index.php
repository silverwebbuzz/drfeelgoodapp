<?php
/**
 * Main Entry Point
 * Routes all requests to appropriate controllers
 */

// Start session
session_start();

// Load configuration
require_once dirname(dirname(__FILE__)) . '/config/constants.php';
require_once dirname(dirname(__FILE__)) . '/config/database.php';

// Autoloader for classes
spl_autoload_register(function($class) {
    $prefix = 'App\\';
    $base_dir = dirname(dirname(__FILE__)) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize database connection
$database = new Database();
$db = $database->connect();

if (!$db) {
    http_response_code(500);
    die('Database connection failed');
}

// Import controllers
use App\Controllers\AuthController;
use App\Controllers\PatientController;

// Check session timeout
AuthController::checkSessionTimeout();

// Get request path
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = '/app';
$route = str_replace($base_path, '', $request_uri);
$route = trim($route, '/');

// Set default route
if (empty($route)) {
    $route = 'dashboard';
}

// Route handler
switch ($route) {
    // Authentication routes
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController = new AuthController($db);
            $response = $authController->login(
                $_POST['username'] ?? '',
                $_POST['password'] ?? ''
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        require dirname(__FILE__) . '/../views/auth/login.php';
        break;

    case 'logout':
        $authController = new AuthController($db);
        $authController->logout();
        break;

    // Patient routes
    case 'patients':
        AuthController::requireLogin();
        $page = $_GET['page'] ?? 1;
        $patientController = new PatientController($db);
        $response = $patientController->getList($page, 10);
        require dirname(__FILE__) . '/../views/patient/list.php';
        break;

    case (preg_match('/^patient\/(\d+)$/', $route, $matches) ? true : false):
        AuthController::requireLogin();
        $patientId = $matches[1];
        $patientController = new PatientController($db);
        $response = $patientController->getDetail($patientId);
        require dirname(__FILE__) . '/../views/patient/detail.php';
        break;

    case 'patient/create':
        AuthController::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientController = new PatientController($db);
            $response = $patientController->create($_POST);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        require dirname(__FILE__) . '/../views/patient/create.php';
        break;

    case 'api/patient/search':
        AuthController::requireLogin();
        $query = $_GET['q'] ?? '';
        $patientController = new PatientController($db);
        $response = $patientController->search($query);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;

    case 'dashboard':
        AuthController::requireLogin();
        $patientController = new PatientController($db);
        $recentPatients = $patientController->getRecent(5);
        require dirname(__FILE__) . '/../views/dashboard.php';
        break;

    default:
        http_response_code(404);
        require dirname(__FILE__) . '/../views/error/404.php';
        break;
}
?>
