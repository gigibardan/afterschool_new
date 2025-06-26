<?php
define('SECURE_ACCESS', true);

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Functions</h1>";

try {
    require_once 'includes/config.php';
    echo "✅ Config loaded<br>";
    
    require_once 'includes/database.php';
    echo "✅ Database loaded<br>";
    
    require_once 'includes/functions.php';
    echo "✅ Functions loaded<br>";
    
    // Test sanitizeInput
    $test = sanitizeInput("Test <script>alert('test')</script>");
    echo "✅ sanitizeInput works: " . $test . "<br>";
    
    // Test validare
    $testData = [
        'nume_parinte' => 'Test',
        'prenume_parinte' => 'Părinte',
        'telefon_parinte' => '0721181188',
        'email_parinte' => 'test@test.com',
        'nume_copil' => 'Test Copil',
        'varsta_copil' => '10',
        'clasa_copil' => 'Clasa IV',
        'scoala_copil' => 'Școala Test',
        'acord_gdpr' => '1'
    ];
    
    echo "<h2>Test Validare</h2>";
    $errors = validatePreregistrationData($testData);
    echo "Errors count: " . count($errors) . "<br>";
    if (!empty($errors)) {
        echo "Errors: " . implode(', ', $errors) . "<br>";
    } else {
        echo "✅ Validation passed<br>";
    }
    
    echo "<h2>Test Insert</h2>";
    $insertId = insertPreregistration($testData);
    if ($insertId) {
        echo "✅ Insert successful, ID: " . $insertId . "<br>";
        
        // Verifică în baza de date
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM preinscrieri WHERE id = ?");
        $stmt->execute([$insertId]);
        $result = $stmt->fetch();
        
        if ($result) {
            echo "✅ Data found in database<br>";
            echo "Nume copil: " . $result['nume_copil'] . "<br>";
        }
    } else {
        echo "❌ Insert failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>