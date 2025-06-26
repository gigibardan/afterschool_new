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
    jsonResponse(['success' => false, 'message' => 'Metoda nu este permisă'], 405);
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
        jsonResponse([
            'success' => false, 
            'message' => 'Date invalide: ' . implode(', ', $errors)
        ], 400);
    }
    
    // Verifică dacă email-ul există deja (opțional - warning doar)
    if (emailExists($data['email_parinte'])) {
        debug_log("Email duplicat detectat", ['email' => $data['email_parinte']]);
        // Pentru moment nu blocăm, doar logăm
    }
    
    // Inserează în baza de date
    $insertId = insertPreregistration($data);
    
    if ($insertId) {
        debug_log("Preînregistrare inserată cu succes", ['id' => $insertId, 'email' => $data['email_parinte']]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Preînregistrarea a fost trimisă cu succes!',
            'id' => $insertId
        ]);
    } else {
        debug_log("Eroare la inserarea preînregistrării", $data);
        
        jsonResponse([
            'success' => false,
            'message' => 'A apărut o eroare la salvarea datelor. Vă rugăm să încercați din nou.'
        ], 500);
    }
    
} catch (Exception $e) {
    debug_log("Excepție în submit.php: " . $e->getMessage());
    
    jsonResponse([
        'success' => false,
        'message' => 'A apărut o eroare neașteptată. Vă rugăm să încercați din nou.'
    ], 500);
}
?>