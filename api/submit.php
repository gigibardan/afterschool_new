<?php
// Definire constantă pentru acces securizat
define('SECURE_ACCESS', true);

// Include fișierele necesare
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Headers pentru CORS și JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verifică că este request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda nu este permisă']);
    exit;
}

try {
    // Obține și sanitizează datele
    $data = [];
    
    // Date părinte
    $data['nume_parinte'] = sanitizeInput($_POST['nume_parinte'] ?? '');
    $data['prenume_parinte'] = sanitizeInput($_POST['prenume_parinte'] ?? '');
    $data['telefon_parinte'] = sanitizeInput($_POST['telefon_parinte'] ?? '');
    $data['email_parinte'] = sanitizeInput($_POST['email_parinte'] ?? '');
    
    // Date copil
    $data['nume_copil'] = sanitizeInput($_POST['nume_copil'] ?? '');
    $data['varsta_copil'] = sanitizeInput($_POST['varsta_copil'] ?? '');
    $data['clasa_copil'] = sanitizeInput($_POST['clasa_copil'] ?? '');
    $data['scoala_copil'] = sanitizeInput($_POST['scoala_copil'] ?? '');
    
    // Date opționale
    $data['experienta_copil'] = sanitizeInput($_POST['experienta_copil'] ?? '');
    $data['interese_copil'] = sanitizeInput($_POST['interese_copil'] ?? '');
    $data['observatii'] = sanitizeInput($_POST['observatii'] ?? '');
    
    // Acorduri
    $data['acord_gdpr'] = $_POST['acord_gdpr'] ?? '0';
    $data['acord_marketing'] = $_POST['acord_marketing'] ?? '0';
    $data['acord_foto'] = $_POST['acord_foto'] ?? '0';
    
    // Validează datele
    $errors = validatePreregistrationData($data);
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Date invalide: ' . implode(', ', $errors)
        ]);
        exit;
    }
    
    // Inserează în baza de date
    $insertId = insertPreregistration($data);
    
    if ($insertId) {
        echo json_encode([
            'success' => true,
            'message' => 'Preînregistrarea a fost trimisă cu succes!',
            'id' => $insertId
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'A apărut o eroare la salvarea datelor. Vă rugăm să încercați din nou.'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A apărut o eroare neașteptată. Vă rugăm să încercați din nou.'
    ]);
}
?>