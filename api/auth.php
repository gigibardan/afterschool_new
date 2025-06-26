<?php
// Definire constantă pentru acces securizat
define('SECURE_ACCESS', true);

// Include fișierele necesare
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Headers pentru JSON
header('Content-Type: application/json; charset=utf-8');

// Verifică că este request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Metoda nu este permisă'], 405);
}

try {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        jsonResponse([
            'success' => false,
            'message' => 'Username și parola sunt obligatorii'
        ], 400);
    }
    
    if (authenticateAdmin($username, $password)) {
        jsonResponse([
            'success' => true,
            'message' => 'Autentificare reușită',
            'admin' => [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'nume' => $_SESSION['admin_nume']
            ]
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Date de autentificare incorecte'
        ], 401);
    }
    
} catch (Exception $e) {
    debug_log("Excepție în auth.php: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'Eroare la autentificare'
    ], 500);
}
?>