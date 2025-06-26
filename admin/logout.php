<?php
// Definire constantă pentru acces securizat
define('SECURE_ACCESS', true);

// Include fișierele necesare
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Efectuează logout
logoutAdmin();

// Redirectează la login cu mesaj
header('Location: index.php?logged_out=1');
exit;
?>