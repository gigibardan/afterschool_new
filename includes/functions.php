<?php
// Protejare acces direct
if (!defined('SECURE_ACCESS')) {
    die('Acces direct interzis!');
}

/**
 * Sanitizează input-ul utilizatorului
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Validează adresa de email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validează numărul de telefon
 */
function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^[+]?[0-9]{8,15}$/', $phone);
}

/**
 * Generează token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time']) || 
        time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verifică token CSRF
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Returnează răspuns JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validează datele formularului de preînregistrare
 */
function validatePreregistrationData($data) {
    $errors = [];
    
    // Date părinte - obligatorii
    if (empty($data['nume_parinte'])) {
        $errors[] = 'Numele părintelui este obligatoriu';
    }
    
    if (empty($data['prenume_parinte'])) {
        $errors[] = 'Prenumele părintelui este obligatoriu';
    }
    
    if (empty($data['telefon_parinte'])) {
        $errors[] = 'Telefonul părintelui este obligatoriu';
    } elseif (!isValidPhone($data['telefon_parinte'])) {
        $errors[] = 'Numărul de telefon nu este valid';
    }
    
    if (empty($data['email_parinte'])) {
        $errors[] = 'Email-ul părintelui este obligatoriu';
    } elseif (!isValidEmail($data['email_parinte'])) {
        $errors[] = 'Adresa de email nu este validă';
    }
    
    // Date copil - obligatorii
    if (empty($data['nume_copil'])) {
        $errors[] = 'Numele copilului este obligatoriu';
    }
    
    if (empty($data['varsta_copil']) || !is_numeric($data['varsta_copil'])) {
        $errors[] = 'Vârsta copilului este obligatorie';
    } elseif ($data['varsta_copil'] < 6 || $data['varsta_copil'] > 18) {
        $errors[] = 'Vârsta copilului trebuie să fie între 6 și 18 ani';
    }
    
    if (empty($data['clasa_copil'])) {
        $errors[] = 'Clasa copilului este obligatorie';
    }
    
    if (empty($data['scoala_copil'])) {
        $errors[] = 'Școala copilului este obligatorie';
    }
    
    // GDPR obligatoriu
    if (empty($data['acord_gdpr']) || $data['acord_gdpr'] != '1') {
        $errors[] = 'Este obligatoriu să acceptați prelucrarea datelor personale conform GDPR';
    }
    
    return $errors;
}

/**
 * Inserează preînregistrare în baza de date
 */
function insertPreregistration($data) {
    try {
        $db = getDB();
        
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
        
        $stmt = $db->prepare($sql);
        
        $result = $stmt->execute([
            ':nume_parinte' => $data['nume_parinte'],
            ':prenume_parinte' => $data['prenume_parinte'],
            ':telefon_parinte' => $data['telefon_parinte'],
            ':email_parinte' => $data['email_parinte'],
            ':nume_copil' => $data['nume_copil'],
            ':varsta_copil' => (int)$data['varsta_copil'],
            ':clasa_copil' => $data['clasa_copil'],
            ':scoala_copil' => $data['scoala_copil'],
            ':experienta_copil' => $data['experienta_copil'] ?? null,
            ':interese_copil' => $data['interese_copil'] ?? null,
            ':observatii' => $data['observatii'] ?? null,
            ':acord_gdpr' => ($data['acord_gdpr'] == '1') ? 1 : 0,
            ':acord_marketing' => ($data['acord_marketing'] == '1') ? 1 : 0,
            ':acord_foto' => ($data['acord_foto'] == '1') ? 1 : 0
        ]);
        
        if ($result) {
            return $db->lastInsertId();
        }
        
        return false;
        
    } catch (PDOException $e) {
        debug_log("Eroare inserare preînregistrare: " . $e->getMessage(), $data);
        return false;
    }
}

/**
 * Verifică dacă email-ul există deja
 */
function emailExists($email) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM preinscrieri WHERE email_parinte = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() !== false;
    } catch (PDOException $e) {
        debug_log("Eroare verificare email: " . $e->getMessage());
        return false;
    }
}

/**
 * Obține statistici preînregistrări
 */
function getPreregistrationStats() {
    try {
        $db = getDB();
        
        // Total preînregistrări
        $stmt = $db->query("SELECT COUNT(*) FROM preinscrieri");
        $total = $stmt->fetchColumn();
        
        // Noi (ultimele 7 zile)
        $stmt = $db->query("SELECT COUNT(*) FROM preinscrieri WHERE data_inscriere >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $noi = $stmt->fetchColumn();
        
        // Pe status
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM preinscrieri GROUP BY status");
        $byStatus = [];
        while ($row = $stmt->fetch()) {
            $byStatus[$row['status']] = $row['count'];
        }
        
        return [
            'total' => $total,
            'noi' => $noi,
            'nou' => $byStatus['nou'] ?? 0,
            'contactat' => $byStatus['contactat'] ?? 0,
            'confirmat' => $byStatus['confirmat'] ?? 0,
            'respins' => $byStatus['respins'] ?? 0
        ];
        
    } catch (PDOException $e) {
        debug_log("Eroare obținere statistici: " . $e->getMessage());
        return null;
    }
}

/**
 * Formatează data pentru afișare
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Formatează status pentru afișare
 */
function formatStatus($status) {
    $statuses = [
        'nou' => '<span class="status-badge status-nou">Nou</span>',
        'contactat' => '<span class="status-badge status-contactat">Contactat</span>',
        'confirmat' => '<span class="status-badge status-confirmat">Confirmat</span>',
        'respins' => '<span class="status-badge status-respins">Respins</span>'
    ];
    
    return $statuses[$status] ?? $status;
}
?>