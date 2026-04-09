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

// Indian Standard Time for all date() calls
date_default_timezone_set('Asia/Kolkata');

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
use App\Controllers\MedicineController;
use App\Controllers\AppointmentController;

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

    case 'api/medicines':
        AuthController::requireLogin();
        header('Content-Type: application/json');
        $medicineController = new MedicineController($db);
        $q = trim($_GET['q'] ?? '');
        $response = $q !== '' ? $medicineController->search($q) : $medicineController->getTop();
        echo json_encode($response);
        exit;

    case 'api/patient/search':
        AuthController::requireLogin();
        $query = $_GET['q'] ?? '';
        $patientController = new PatientController($db);
        $response = $patientController->search($query);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;

    case (preg_match('/^api\/patient\/(\d+)\/report$/', $route, $matches) ? true : false):
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }
        $patientId = $matches[1];
        $patientController = new PatientController($db);
        $response = $patientController->addReport($patientId, $_POST);
        echo json_encode($response);
        exit;

    case (preg_match('/^api\/patient\/(\d+)\/update$/', $route, $matches) ? true : false):
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }
        $patientId = $matches[1];
        $patientController = new PatientController($db);
        $response = $patientController->update($patientId, $_POST);
        echo json_encode($response);
        exit;

    case (preg_match('/^api\/report\/(\d+)\/update$/', $route, $matches) ? true : false):
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }
        $reportId = $matches[1];
        $patientController = new PatientController($db);
        $response = $patientController->updateReport($reportId, $_POST);
        echo json_encode($response);
        exit;

    case 'dashboard':
        AuthController::requireLogin();
        $patientController = new PatientController($db);
        $recentPatients = $patientController->getRecent(5);
        require __DIR__ . '/views/dashboard.php';
        break;

    // ── Appointment / Queue routes ──────────────────────────────────────────

    case 'queue':
        AuthController::requireLogin();
        $apptController = new AppointmentController($db);
        $date = $_GET['date'] ?? null;
        $queueData = $apptController->getQueue($date);
        require __DIR__ . '/views/appointment/queue.php';
        break;

    case 'walkin':
        AuthController::requireLogin();
        require __DIR__ . '/views/appointment/walkin.php';
        break;

    case 'clinic-settings':
        AuthController::requireLogin();
        $apptController = new AppointmentController($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = $apptController->saveSettings($_POST);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        // Load current settings for the form
        $settingModel = new App\Models\Setting($db);
        $clinicSettings = $settingModel->getAllSettings();
        require __DIR__ . '/views/appointment/settings.php';
        break;

    case 'api/appointment/walkin':
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }
        $apptController = new AppointmentController($db);
        $userId = $_SESSION['user_id'] ?? null;
        $response = $apptController->createWalkin($_POST, $userId);
        echo json_encode($response);
        exit;

    case (preg_match('/^api\/appointment\/(\d+)\/status$/', $route, $matches) ? true : false):
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }
        $apptId = $matches[1];
        $apptController = new AppointmentController($db);
        $response = $apptController->updateStatus($apptId, $_POST['status'] ?? '');
        echo json_encode($response);
        exit;

    case 'api/slots':
        // Public — no auth required
        header('Content-Type: application/json');
        $date = $_GET['date'] ?? date('Y-m-d');
        $apptController = new AppointmentController($db);
        $response = $apptController->getAvailableSlots($date);
        echo json_encode($response);
        exit;

    case 'api/booking':
        // Public — no auth required
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            exit;
        }
        $apptController = new AppointmentController($db);
        $response = $apptController->createPrebooked($_POST);
        echo json_encode($response);
        exit;

    case 'api/patient/lookup':
        // Public — no auth required
        header('Content-Type: application/json');
        $phone = trim($_GET['phone'] ?? '');
        if ($phone === '') {
            echo json_encode(['success' => false, 'message' => 'Phone required']);
            exit;
        }
        $apptController = new AppointmentController($db);
        $response = $apptController->lookupByPhone($phone);
        echo json_encode($response);
        exit;

    case 'booking':
        // Public booking page — no auth required
        $settingModel     = new App\Models\Setting($db);
        $bookingDaysAhead = (int)$settingModel->get('booking_days_ahead', 15);
        $closedDatesRaw   = (new App\Models\Appointment($db))->getClosedDates();
        $closedDatesArr   = array_column($closedDatesRaw, 'date');

        // Build list of unavailable dates (closed dates + days with no sessions configured)
        $unavailableDates = $closedDatesArr;
        for ($i = 0; $i < $bookingDaysAhead; $i++) {
            $d   = date('Y-m-d', strtotime("+{$i} days"));
            $dow = (int)date('N', strtotime($d)); // 1=Mon, 7=Sun
            $noSlots = false;
            if ($dow === 7) {
                // Sunday
                $noSlots = $settingModel->get('sunday_on', '1') !== '1';
            } else {
                // Mon-Sat: no slots if both sessions are off
                $morningOff = $settingModel->get('mon_sat_morning_on', '1') !== '1';
                $eveningOff = $settingModel->get('mon_sat_evening_on', '1') !== '1';
                $noSlots = $morningOff && $eveningOff;
            }
            if ($noSlots && !in_array($d, $unavailableDates)) {
                $unavailableDates[] = $d;
            }
        }
        require __DIR__ . '/views/booking/index.php';
        break;

    case 'api/closed-dates':
        AuthController::requireLogin();
        header('Content-Type: application/json');
        $apptController = new AppointmentController($db);
        $dates = (new App\Models\Appointment($db))->getClosedDates();
        echo json_encode(['success' => true, 'dates' => $dates]);
        exit;

    case 'api/closed-dates/add':
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }
        $date   = $_POST['date']   ?? '';
        $reason = $_POST['reason'] ?? '';
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['success'=>false,'message'=>'Invalid date']);
            exit;
        }
        (new App\Models\Appointment($db))->addClosedDate($date, $reason);
        echo json_encode(['success' => true]);
        exit;

    case 'api/closed-dates/remove':
        AuthController::requireLogin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }
        (new App\Models\Appointment($db))->removeClosedDate($id);
        echo json_encode(['success' => true]);
        exit;

    default:
        error_log("404 - Route not found: '{$route}'");
        http_response_code(404);
        require __DIR__ . '/views/error/404.php';
        break;
}
?>
