<?php
define('SECURE_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/database.php';

echo "<h1>Verificare Tabele Baza de Date</h1>";

try {
    $db = getDB();
    
    echo "<h2>1. Verificare Tabele Existente</h2>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabele găsite: " . count($tables) . "<br><br>";
    
    $required_tables = ['preinscrieri', 'administratori', 'sesiuni_admin'];
    
    foreach ($required_tables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ $table - există<br>";
            
            // Verifică structura
            $stmt = $db->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            echo "&nbsp;&nbsp;Coloane: " . count($columns) . "<br>";
            
            // Verifică datele
            $stmt = $db->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "&nbsp;&nbsp;Înregistrări: $count<br><br>";
            
        } else {
            echo "❌ $table - LIPSEȘTE!<br><br>";
        }
    }
    
    echo "<h2>2. Structura Tabelului 'preinscrieri'</h2>";
    if (in_array('preinscrieri', $tables)) {
        $stmt = $db->query("DESCRIBE preinscrieri");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Coloană</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>3. Creează Tabelele Lipsă</h2>";
    
    // Creează tabela preinscrieri dacă nu există
    if (!in_array('preinscrieri', $tables)) {
        echo "Creez tabela 'preinscrieri'...<br>";
        $sql = "CREATE TABLE preinscrieri (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nume_parinte VARCHAR(100) NOT NULL,
            prenume_parinte VARCHAR(100) NOT NULL,
            telefon_parinte VARCHAR(20) NOT NULL,
            email_parinte VARCHAR(150) NOT NULL,
            nume_copil VARCHAR(100) NOT NULL,
            varsta_copil INT NOT NULL,
            clasa_copil VARCHAR(20) NOT NULL,
            scoala_copil VARCHAR(200) NOT NULL,
            experienta_copil VARCHAR(50),
            interese_copil TEXT,
            observatii TEXT,
            acord_gdpr BOOLEAN DEFAULT FALSE,
            acord_marketing BOOLEAN DEFAULT FALSE,
            acord_foto BOOLEAN DEFAULT FALSE,
            status ENUM('nou', 'contactat', 'confirmat', 'respins') DEFAULT 'nou',
            data_inscriere TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($db->exec($sql)) {
            echo "✅ Tabela 'preinscrieri' creată cu succes!<br>";
        } else {
            echo "❌ Eroare la crearea tabelei 'preinscrieri'<br>";
        }
    }
    
    // Creează tabela administratori dacă nu există
    if (!in_array('administratori', $tables)) {
        echo "Creez tabela 'administratori'...<br>";
        $sql = "CREATE TABLE administratori (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            nume_complet VARCHAR(100),
            data_creare TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if ($db->exec($sql)) {
            echo "✅ Tabela 'administratori' creată cu succes!<br>";
            
            // Adaugă admin
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO administratori (username, password_hash, email, nume_complet) VALUES (?, ?, ?, ?)");
            if ($stmt->execute(['admin', $password_hash, 'admin@techminds-academy.ro', 'Administrator TechMinds'])) {
                echo "✅ Admin creat cu succes!<br>";
            }
        } else {
            echo "❌ Eroare la crearea tabelei 'administratori'<br>";
        }
    }
    
    // Creează tabela sesiuni_admin dacă nu există
    if (!in_array('sesiuni_admin', $tables)) {
        echo "Creez tabela 'sesiuni_admin'...<br>";
        $sql = "CREATE TABLE sesiuni_admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            data_expirare DATETIME NOT NULL,
            FOREIGN KEY (admin_id) REFERENCES administratori(id) ON DELETE CASCADE
        )";
        
        if ($db->exec($sql)) {
            echo "✅ Tabela 'sesiuni_admin' creată cu succes!<br>";
        } else {
            echo "❌ Eroare la crearea tabelei 'sesiuni_admin'<br>";
        }
    }
    
    echo "<h2>✅ Verificare completă!</h2>";
    
} catch (Exception $e) {
    echo "❌ EROARE: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>