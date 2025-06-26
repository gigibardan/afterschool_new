<?php
echo "<h2>Test Configurație</h2>";

// Test 1 - Verifică dacă config.php există și se încarcă
if (file_exists('includes/config.php')) {
    echo "✅ config.php există<br>";
    
    define('SECURE_ACCESS', true);
    
    try {
        require_once 'includes/config.php';
        echo "✅ config.php se încarcă<br>";
        echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NEDEFINIT') . "<br>";
        echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NEDEFINIT') . "<br>";
    } catch (Exception $e) {
        echo "❌ Eroare config.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ config.php nu există!<br>";
}

// Test 2 - Verifică path-urile
echo "<br><h3>Paths:</h3>";
echo "__DIR__: " . __DIR__ . "<br>";
echo "includes/ exists: " . (is_dir('includes') ? 'DA' : 'NU') . "<br>";
echo "api/ exists: " . (is_dir('api') ? 'DA' : 'NU') . "<br>";

// Test 3 - Verifică permisiuni
echo "<br><h3>Permisiuni:</h3>";
echo "config.php readable: " . (is_readable('includes/config.php') ? 'DA' : 'NU') . "<br>";
echo "api/submit.php exists: " . (file_exists('api/submit.php') ? 'DA' : 'NU') . "<br>";

// Test 4 - PHP info
echo "<br><h3>PHP Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO available: " . (extension_loaded('pdo') ? 'DA' : 'NU') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'DA' : 'NU') . "<br>";
?>