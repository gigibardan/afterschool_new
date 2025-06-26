<?php
// Definire constantă pentru acces securizat
define('SECURE_ACCESS', true);

// Include fișierele necesare
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

// Verifică autentificarea
requireAdminAuth();

// Curăță sesiunile expirate
cleanExpiredSessions();

// Obține statisticile
$stats = getPreregistrationStats();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TechMinds Academy Afterschool</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="dashboard">
    <header class="dashboard-header">
        <div class="dashboard-logo">
            <i class="fas fa-code"></i>
            <span>TechMinds Academy - Admin</span>
        </div>
        
        <nav class="dashboard-nav">
            <div class="user-info">
                <i class="fas fa-user"></i>
                <span>Bună, <?php echo htmlspecialchars($_SESSION['admin_nume'] ?? $_SESSION['admin_username']); ?>!</span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Deconectare
            </a>
        </nav>
    </header>

    <main class="dashboard-main">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Privire generală asupra preînregistrărilor afterschool</p>
        </div>

        <?php if ($stats): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total Preînregistrări</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($stats['noi']); ?></div>
                <div class="stat-label">Noi (ultimele 7 zile)</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($stats['confirmat']); ?></div>
                <div class="stat-label">Confirmate</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($stats['nou']); ?></div>
                <div class="stat-label">În așteptare</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="content-header">
                <h2 class="content-title">Acțiuni rapide</h2>
            </div>
            <div class="content-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="preinscrieri.php" style="background: var(--gradient-secondary); color: white; padding: 1.5rem; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease;">
                        <i class="fas fa-list" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        <strong>Vezi toate preînregistrările</strong>
                        <br><small>Gestionează și editează</small>
                    </a>
                    
                    <a href="preinscrieri.php?action=add" style="background: var(--gradient-primary); color: white; padding: 1.5rem; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease;">
                        <i class="fas fa-plus" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        <strong>Adaugă preînregistrare</strong>
                        <br><small>Înregistrare manuală</small>
                    </a>
                    
                    <a href="preinscrieri.php?action=export" style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1.5rem; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease;">
                        <i class="fas fa-download" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        <strong>Export CSV</strong>
                        <br><small>Descarcă datele</small>
                    </a>
                    
                    <a href="../" style="background: var(--color-gray); color: white; padding: 1.5rem; border-radius: 15px; text-decoration: none; text-align: center; transition: transform 0.3s ease;">
                        <i class="fas fa-home" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                        <strong>Vezi site-ul</strong>
                        <br><small>Formularul public</small>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>