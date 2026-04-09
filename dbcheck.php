<?php
require_once __DIR__ . '/config/database.php';
$database = new Database();
$db = $database->connect();

echo "=== PATIENT TABLE COLUMNS ===\n";
$stmt = $db->query("SHOW COLUMNS FROM patient");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== SAMPLE PATIENT DATA (5 rows) ===\n";
$stmt = $db->query("SELECT * FROM patient LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
    echo "---\n";
}

echo "\n=== PROGRESS_REPORT TABLE COLUMNS ===\n";
$stmt = $db->query("SHOW COLUMNS FROM progress_report");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n=== SAMPLE PROGRESS_REPORT DATA (3 rows) ===\n";
$stmt = $db->query("SELECT * FROM progress_report LIMIT 3");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
    echo "---\n";
}
