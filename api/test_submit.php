<?php
echo "=== API TEST ===<br>";

// Test basic PHP
echo "PHP works: OK<br>";

// Test path
echo "Current dir: " . __DIR__ . "<br>";
echo "Parent dir: " . dirname(__DIR__) . "<br>";
echo "Config path: " . dirname(__DIR__) . '/includes/config.php<br>';
echo "Config exists: " . (file_exists(dirname(__DIR__) . '/includes/config.php') ? 'YES' : 'NO') . "<br>";

// Test define
define('SECURE_ACCESS', true);
echo "SECURE_ACCESS defined: OK<br>";

// Test require
try {
    require_once '../includes/config.php';
    echo "Config loaded: OK<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
} catch (Exception $e) {
    echo "ERROR loading config: " . $e->getMessage() . "<br>";
    die();
}

try {
    require_once '../includes/database.php';
    echo "Database class loaded: OK<br>";
    
    $db = getDB();
    echo "DB connection: OK<br>";
} catch (Exception $e) {
    echo "ERROR database: " . $e->getMessage() . "<br>";
}
?>