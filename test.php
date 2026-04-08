<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: PHP is working\n";

// Test database connection
require_once __DIR__ . '/config/database.php';
echo "Test 2: Database config loaded\n";

try {
    $database = new Database();
    $db = $database->connect();
    if ($db) {
        echo "Test 3: Database connected\n";
    } else {
        echo "Test 3: Database connection returned null\n";
    }
} catch (Exception $e) {
    echo "Test 3 Error: " . $e->getMessage() . "\n";
}

// Test autoloader
echo "Test 4: About to test autoloader\n";
try {
    $user = new \App\Models\User($db);
    echo "Test 5: User model loaded\n";
} catch (Exception $e) {
    echo "Test 5 Error: " . $e->getMessage() . "\n";
}

echo "All tests completed\n";
