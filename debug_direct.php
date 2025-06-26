<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Direct</h1>";

echo "<h2>1. Test Basic</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current directory: " . __DIR__ . "<br>";

echo "<h2>2. Test Files</h2>";
$files_to_check = [
    'includes/config.php',
    'includes/database.php', 
    'includes/functions.php',
    'api/submit.php'
];

foreach ($files_to_check as $file) {
    echo $file . ": " . (file_exists($file) ? "✅ EXISTS" : "❌ MISSING") . "<br>";
}

echo "<h2>3. Test Config Load</h2>";
define('SECURE_ACCESS', true);

try {
    require_once 'includes/config.php';
    echo "✅ Config loaded<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
} catch (Throwable $e) {
    echo "❌ ERROR loading config: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    exit;
}

echo "<h2>4. Test Database</h2>";
try {
    require_once 'includes/database.php';
    echo "✅ Database class loaded<br>";
    
    $db = getDB();
    echo "✅ Database connection OK<br>";
    
    $stmt = $db->query("SELECT COUNT(*) FROM preinscrieri");
    $count = $stmt->fetchColumn();
    echo "✅ Preinscrieri count: " . $count . "<br>";
    
} catch (Throwable $e) {
    echo "❌ ERROR database: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<h2>5. Test POST Simulation</h2>";
try {
    require_once 'includes/functions.php';
    echo "✅ Functions loaded<br>";
    
    // Simulează un POST
    $_POST = [
        'nume_parinte' => 'Test',
        'prenume_parinte' => 'Părinte',
        'telefon_parinte' => '0123456789',
        'email_parinte' => 'test@test.com',
        'nume_copil' => 'Test Copil',
        'varsta_copil' => '10',
        'clasa_copil' => 'Clasa IV',
        'scoala_copil' => 'Școala Test',
        'acord_gdpr' => '1'
    ];
    
    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = sanitizeInput($value);
    }
    
    echo "✅ Data sanitized<br>";
    
    $errors = validatePreregistrationData($data);
    echo "Validation errors: " . count($errors) . "<br>";
    
    if (empty($errors)) {
        echo "✅ Validation passed<br>";
    } else {
        echo "❌ Validation failed: " . implode(', ', $errors) . "<br>";
    }
    
} catch (Throwable $e) {
    echo "❌ ERROR functions: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<h2>✅ Debug complet!</h2>";
?>