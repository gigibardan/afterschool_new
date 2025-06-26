<?php
// Protejare acces direct
if (!defined('SECURE_ACCESS')) {
    die('Acces direct interzis!');
}

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch(PDOException $e) {
            debug_log("Eroare conexiune baza de date: " . $e->getMessage());
            die("Eroare conexiune baza de date!");
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Functie pentru prevenirea clonarii
    private function __clone() {}
    
    // Functie pentru prevenirea deserializarii
    public function __wakeup() {}
}

// Functie globala pentru obtinerea conexiunii
function getDB() {
    return Database::getInstance()->getConnection();
}
?>