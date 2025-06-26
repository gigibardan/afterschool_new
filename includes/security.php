<?php
// Protejare acces direct
if (!defined('SECURE_ACCESS')) {
    die('Acces direct interzis!');
}

/**
 * Verifică autentificarea admin
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_username']) &&
           isset($_SESSION['admin_login_time']) &&
           (time() - $_SESSION['admin_login_time'] < SESSION_LIFETIME);
}

/**
 * Autentifică admin
 */
function authenticateAdmin($username, $password) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT id, username, password_hash, email, nume_complet FROM administratori WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Regenerare ID sesiune pentru securitate
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_nume'] = $admin['nume_complet'];
            $_SESSION['admin_login_time'] = time();
            
            // Salvare sesiune în baza de date
            saveAdminSession($admin['id']);
            
            debug_log("Admin login success", ['username' => $username]);
            return true;
        }
        
        debug_log("Admin login failed", ['username' => $username]);
        return false;
        
    } catch (PDOException $e) {
        debug_log("Eroare autentificare admin: " . $e->getMessage());
        return false;
    }
}

/**
 * Salvează sesiunea admin în baza de date
 */
function saveAdminSession($adminId) {
    try {
        $db = getDB();
        
        // Șterge sesiunile vechi
        $stmt = $db->prepare("DELETE FROM sesiuni_admin WHERE admin_id = :admin_id OR data_expirare < NOW()");
        $stmt->execute([':admin_id' => $adminId]);
        
        // Inserează sesiunea nouă
        $stmt = $db->prepare("INSERT INTO sesiuni_admin (admin_id, session_id, data_expirare) VALUES (:admin_id, :session_id, :data_expirare)");
        $stmt->execute([
            ':admin_id' => $adminId,
            ':session_id' => session_id(),
            ':data_expirare' => date('Y-m-d H:i:s', time() + SESSION_LIFETIME)
        ]);
        
    } catch (PDOException $e) {
        debug_log("Eroare salvare sesiune admin: " . $e->getMessage());
    }
}

/**
 * Deconectează admin
 */
function logoutAdmin() {
    if (isset($_SESSION['admin_id'])) {
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM sesiuni_admin WHERE admin_id = :admin_id");
            $stmt->execute([':admin_id' => $_SESSION['admin_id']]);
        } catch (PDOException $e) {
            debug_log("Eroare ștergere sesiune admin: " . $e->getMessage());
        }
    }
    
    // Curăță toate datele sesiunii
    $_SESSION = array();
    
    // Șterge cookie-ul sesiunii
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Distruge sesiunea
    session_destroy();
}

/**
 * Redirectează la login dacă nu este autentificat
 */
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Verifică și curăță sesiunile expirate
 */
function cleanExpiredSessions() {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM sesiuni_admin WHERE data_expirare < NOW()");
        $stmt->execute();
    } catch (PDOException $e) {
        debug_log("Eroare curățare sesiuni expirate: " . $e->getMessage());
    }
}

/**
 * Protecție împotriva atacurilor brute force
 */
function checkLoginAttempts($ip) {
    // Simplu: doar log pentru implementare viitoare
    debug_log("Login attempt from IP: " . $ip);
    return true; // Pentru moment permitem toate încercările
}
?>