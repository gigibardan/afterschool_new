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

// Verifică autentificarea admin pentru API
if (!isAdminLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Neautorizat'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                $stats = getPreregistrationStats();
                jsonResponse(['success' => true, 'data' => $stats]);
            } else {
                // Lista preînregistrărilor
                $db = getDB();
                $stmt = $db->query("SELECT * FROM preinscrieri ORDER BY data_inscriere DESC");
                $preinscrieri = $stmt->fetchAll();
                
                jsonResponse(['success' => true, 'data' => $preinscrieri]);
            }
            break;
            
        case 'PUT':
            // Update status rapid
            $id = (int)($_GET['id'] ?? 0);
            $newStatus = sanitizeInput($_POST['status'] ?? '');
            
            if ($id && in_array($newStatus, ['nou', 'contactat', 'confirmat', 'respins'])) {
                $db = getDB();
                $stmt = $db->prepare("UPDATE preinscrieri SET status = :status WHERE id = :id");
                $result = $stmt->execute([':status' => $newStatus, ':id' => $id]);
                
                if ($result) {
                    jsonResponse(['success' => true, 'message' => 'Status actualizat']);
                } else {
                    jsonResponse(['success' => false, 'message' => 'Eroare la actualizare'], 500);
                }
            } else {
                jsonResponse(['success' => false, 'message' => 'Date invalide'], 400);
            }
            break;
            
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id) {
                $db = getDB();
                $stmt = $db->prepare("DELETE FROM preinscrieri WHERE id = :id");
                $result = $stmt->execute([':id' => $id]);
                
                if ($result) {
                    jsonResponse(['success' => true, 'message' => 'Preînregistrare ștearsă']);
                } else {
                    jsonResponse(['success' => false, 'message' => 'Eroare la ștergere'], 500);
                }
            } else {
                jsonResponse(['success' => false, 'message' => 'ID invalid'], 400);
            }
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Metoda nu este suportată'], 405);
    }
    
} catch (Exception $e) {
    debug_log("Excepție în preinscrieri.php API: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Eroare server'], 500);
}
?>