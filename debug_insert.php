<?php
define('SECURE_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Insert Function</h1>";

try {
    $db = getDB();
    
    echo "<h2>1. Structura actuală tabel:</h2>";
    $stmt = $db->query("DESCRIBE preinscrieri");
    $columns = $stmt->fetchAll();
    
    $existing_columns = [];
    foreach ($columns as $col) {
        $existing_columns[] = $col['Field'];
        echo $col['Field'] . " (" . $col['Type'] . ")<br>";
    }
    
    echo "<h2>2. Coloane necesare pentru INSERT:</h2>";
    $needed = [
        'nume_parinte', 'prenume_parinte', 'telefon_parinte', 'email_parinte',
        'nume_copil', 'varsta_copil', 'clasa_copil', 'scoala_copil',
        'experienta_copil', 'interese_copil', 'observatii',
        'acord_gdpr', 'acord_marketing', 'acord_foto'
    ];
    
    $missing = [];
    foreach ($needed as $col) {
        if (in_array($col, $existing_columns)) {
            echo "✅ $col<br>";
        } else {
            echo "❌ $col - LIPSEȘTE!<br>";
            $missing[] = $col;
        }
    }
    
    if (!empty($missing)) {
        echo "<h2>3. Adaug coloanele lipsă:</h2>";
        
        $column_definitions = [
            'experienta_copil' => 'VARCHAR(50)',
            'interese_copil' => 'TEXT',
            'observatii' => 'TEXT',
            'acord_gdpr' => 'BOOLEAN DEFAULT FALSE',
            'acord_marketing' => 'BOOLEAN DEFAULT FALSE',
            'acord_foto' => 'BOOLEAN DEFAULT FALSE'
        ];
        
        foreach ($missing as $col) {
            if (isset($column_definitions[$col])) {
                $sql = "ALTER TABLE preinscrieri ADD COLUMN $col " . $column_definitions[$col];
                echo "Executez: $sql<br>";
                
                if ($db->exec($sql)) {
                    echo "✅ $col adăugată cu succes<br>";
                } else {
                    echo "❌ Eroare la adăugarea $col<br>";
                }
            }
        }
    }
    
    echo "<h2>4. Test INSERT manual:</h2>";
    
    $testData = [
        'nume_parinte' => 'Test',
        'prenume_parinte' => 'Părinte',
        'telefon_parinte' => '0721181188',
        'email_parinte' => 'test@test.com',
        'nume_copil' => 'Test Copil',
        'varsta_copil' => 10,
        'clasa_copil' => 'Clasa IV',
        'scoala_copil' => 'Școala Test',
        'experienta_copil' => 'Fără experiență',
        'interese_copil' => 'Test interese',
        'observatii' => 'Test observații',
        'acord_gdpr' => 1,
        'acord_marketing' => 0,
        'acord_foto' => 0
    ];
    
    $sql = "INSERT INTO preinscrieri (
        nume_parinte, prenume_parinte, telefon_parinte, email_parinte,
        nume_copil, varsta_copil, clasa_copil, scoala_copil,
        experienta_copil, interese_copil, observatii,
        acord_gdpr, acord_marketing, acord_foto,
        status, data_inscriere
    ) VALUES (
        :nume_parinte, :prenume_parinte, :telefon_parinte, :email_parinte,
        :nume_copil, :varsta_copil, :clasa_copil, :scoala_copil,
        :experienta_copil, :interese_copil, :observatii,
        :acord_gdpr, :acord_marketing, :acord_foto,
        'nou', NOW()
    )";
    
    echo "SQL: <pre>$sql</pre>";
    
    $stmt = $db->prepare($sql);
    
    $params = [
        ':nume_parinte' => $testData['nume_parinte'],
        ':prenume_parinte' => $testData['prenume_parinte'],
        ':telefon_parinte' => $testData['telefon_parinte'],
        ':email_parinte' => $testData['email_parinte'],
        ':nume_copil' => $testData['nume_copil'],
        ':varsta_copil' => $testData['varsta_copil'],
        ':clasa_copil' => $testData['clasa_copil'],
        ':scoala_copil' => $testData['scoala_copil'],
        ':experienta_copil' => $testData['experienta_copil'],
        ':interese_copil' => $testData['interese_copil'],
        ':observatii' => $testData['observatii'],
        ':acord_gdpr' => $testData['acord_gdpr'],
        ':acord_marketing' => $testData['acord_marketing'],
        ':acord_foto' => $testData['acord_foto']
    ];
    
    echo "Parametri: <pre>" . print_r($params, true) . "</pre>";
    
    if ($stmt->execute($params)) {
        $insertId = $db->lastInsertId();
        echo "✅ INSERT successful! ID: $insertId<br>";
        
        // Verifică datele
        $stmt = $db->prepare("SELECT * FROM preinscrieri WHERE id = ?");
        $stmt->execute([$insertId]);
        $result = $stmt->fetch();
        
        echo "Date salvate: <pre>" . print_r($result, true) . "</pre>";
        
    } else {
        echo "❌ INSERT failed!<br>";
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . print_r($errorInfo, true) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>