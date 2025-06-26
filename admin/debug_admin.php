<?php
// FIȘIER TEMPORAR PENTRU DEBUG - ȘTERGE DUPĂ FOLOSIRE!
define('SECURE_ACCESS', true);

require_once '../includes/config.php';
require_once '../includes/database.php';

try {
    $db = getDB();
    
    echo "<h2>Debug Admin Login</h2>";
    
    // Verifică dacă tabelul există
    $stmt = $db->query("SHOW TABLES LIKE 'administratori'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabelul 'administratori' nu există!</p>";
        echo "<p>Rulează SQL-ul pentru crearea tabelelor:</p>";
        echo "<pre>
CREATE TABLE administratori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nume_complet VARCHAR(100),
    data_creare TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sesiuni_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    data_expirare DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES administratori(id) ON DELETE CASCADE
);
</pre>";
    } else {
        echo "<p style='color: green;'>✅ Tabelul 'administratori' există</p>";
        
        // Verifică utilizatorii
        $stmt = $db->query("SELECT id, username, email, nume_complet FROM administratori");
        $admins = $stmt->fetchAll();
        
        if (empty($admins)) {
            echo "<p style='color: orange;'>⚠️ Nu există administratori în baza de date!</p>";
            echo "<p>Adaugă admin:</p>";
            
            // Creează admin automat
            $username = 'admin';
            $password = 'admin123';
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("INSERT INTO administratori (username, password_hash, email, nume_complet) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$username, $password_hash, 'admin@techminds-academy.ro', 'Administrator TechMinds']);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Admin creat cu succes!</p>";
                echo "<p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>";
            } else {
                echo "<p style='color: red;'>❌ Eroare la crearea admin-ului!</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Administratori găsiți:</p>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Nume</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . $admin['id'] . "</td>";
                echo "<td>" . $admin['username'] . "</td>";
                echo "<td>" . $admin['email'] . "</td>";
                echo "<td>" . $admin['nume_complet'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Testează parola
            $stmt = $db->prepare("SELECT password_hash FROM administratori WHERE username = 'admin'");
            $stmt->execute();
            $hash = $stmt->fetchColumn();
            
            if ($hash) {
                $test_password = 'admin123';
                $verify = password_verify($test_password, $hash);
                
                echo "<p><strong>Test parolă 'admin123':</strong> " . 
                     ($verify ? "<span style='color: green;'>✅ CORECTĂ</span>" : "<span style='color: red;'>❌ INCORECTĂ</span>") . "</p>";
                
                if (!$verify) {
                    echo "<p>Hash-ul actual: <code>" . $hash . "</code></p>";
                    echo "<p>Resetez parola...</p>";
                    
                    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE administratori SET password_hash = ? WHERE username = 'admin'");
                    $result = $stmt->execute([$new_hash]);
                    
                    if ($result) {
                        echo "<p style='color: green;'>✅ Parolă resetată cu succes!</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Eroare la resetarea parolei!</p>";
                    }
                }
            }
        }
    }
    
    // Verifică conexiunea
    echo "<br><p><strong>Conexiune DB:</strong> <span style='color: green;'>✅ OK</span></p>";
    echo "<p><strong>Database:</strong> " . DB_NAME . "</p>";
    echo "<p><strong>Host:</strong> " . DB_HOST . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Eroare: " . $e->getMessage() . "</p>";
}
?>

<br><br>
<p><strong>Șterge acest fișier după debugging!</strong></p>
<a href="index.php">← Înapoi la login</a>