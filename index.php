<?php
/**
 * Dr. Feelgood - Main Entry Point
 *
 * This file is the entry point for the application.
 * Place this file in the root directory: /home/silverwebbuzz_in/public_html/drfeelgoods.in/app/index.php
 */

// Enable error reporting
error_reporting(E_ALL);

// Load configuration first to check DEBUG_MODE
require_once __DIR__ . '/config/constants.php';

// Configure error handling based on debug mode
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    ini_set('display_errors', 1); // Display errors to user in debug mode
} else {
    ini_set('display_errors', 0); // Don't display errors to user in production
}
ini_set('log_errors', 1); // Always log errors

// Start session
session_start();

// Load database configuration
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
    } else {
        error_log("Autoloader: File not found for class '{$class}' - Expected: '{$file}'");
    }
});

// Initialize database connection
try {
    $database = new Database();
    $db = $database->connect();

    if (!$db) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
} catch (\Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Import controllers
use App\Controllers\AuthController;
use App\Controllers\PatientController;

// Check session timeout
AuthController::checkSessionTimeout();

// Get request path from REQUEST_URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract route - remove /app if it exists, otherwise use as-is
if (strpos($request_uri, '/app/') === 0) {
    $route = substr($request_uri, 5); // Remove '/app/'
} else {
    $route = $request_uri;
}

$route = trim($route, '/');

// Log route for debugging
error_log("Route extracted: '{$route}' from REQUEST_URI: '{$request_uri}' Method: {$_SERVER['REQUEST_METHOD']}");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data received: " . json_encode($_POST));
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
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            error_log("Login attempt - Username: '{$username}', Password length: " . strlen($password));

            try {
                $authController = new AuthController($db);
                $response = $authController->login($username, $password);

                header('Content-Type: application/json');
                $json = json_encode($response);
                error_log("Login response JSON: " . $json);
                echo $json;
                exit;
            } catch (\Exception $e) {
                error_log("Login error: " . $e->getMessage());
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Login error: ' . $e->getMessage()]);
                exit;
            }
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
        $patientController = new PatientController($db);
        $response = $patientController->getAllPatients();
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
        error_log("404 - Route not found: '{$route}'");
        http_response_code(404);
        require __DIR__ . '/views/error/404.php';
        break;
}
?>
