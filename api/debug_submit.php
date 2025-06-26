<?php
// Debug simplu pentru API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== API DEBUG ===\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none') . "\n";
echo "POST data: " . print_r($_POST, true) . "\n";
echo "Raw input: " . file_get_contents('php://input') . "\n";

// Test basic includes
define('SECURE_ACCESS', true);

try {
    require_once '../includes/config.php';
    echo "✅ Config loaded\n";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "\n";
    exit;
}

try {
    require_once '../includes/database.php';
    echo "✅ Database loaded\n";
    $db = getDB();
    echo "✅ DB connection OK\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit;
}

try {
    require_once '../includes/functions.php';
    echo "✅ Functions loaded\n";
} catch (Exception $e) {
    echo "❌ Functions error: " . $e->getMessage() . "\n";
    exit;
}

echo "=== API DEBUG COMPLETE ===\n";
?>