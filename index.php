<?php
/**
 * Dr. Feelgood - Root Entry Point
 *
 * This file serves as the entry point for the application.
 * It forwards all requests to the public/index.php file.
 *
 * VPS Path: /home/silverwebbuzz_in/public_html/drfeelgoods.in/app/
 * Domain: https://app.drfeelgoods.in/
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

// Autoloader for classes
spl_autoload_register(function($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';

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

// Get request path from URL
// Handle both direct requests and .htaccess rewrites
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /app if it exists (for compatibility)
if (strpos($request_uri, '/app/') === 0) {
    $route = substr($request_uri, 5); // Remove '/app/'
} else {
    $route = $request_uri;
}

$route = trim($route, '/');

// Also check if path parameter was passed from .htaccess
if (empty($route) && isset($_GET['path'])) {
    $route = trim($_GET['path'], '/');
}

// Set default route
if (empty($route)) {
    $route = 'login';
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
        require __DIR__ . '/views/auth/login.php';
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
        require __DIR__ . '/views/patient/list.php';
        break;

    case (preg_match('/^patient\/(\d+)$/', $route, $matches) ? true : false):
        AuthController::requireLogin();
        $patientId = $matches[1];
        $patientController = new PatientController($db);
        $response = $patientController->getDetail($patientId);
        require __DIR__ . '/views/patient/detail.php';
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
        require __DIR__ . '/views/patient/create.php';
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
        require __DIR__ . '/views/dashboard.php';
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/views/error/404.php';
        break;
}
?>
