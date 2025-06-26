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

$message = '';
$messageType = '';
$action = $_GET['action'] ?? 'list';

// Procesează acțiunile
switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $data['acord_gdpr'] = isset($_POST['acord_gdpr']) ? '1' : '0';
            $data['acord_marketing'] = isset($_POST['acord_marketing']) ? '1' : '0';
            $data['acord_foto'] = isset($_POST['acord_foto']) ? '1' : '0';
            
            // Validează datele
            $errors = validatePreregistrationData($data);
            
            if (empty($errors)) {
                $insertId = insertPreregistration($data);
                if ($insertId) {
                    $message = 'Preînregistrarea a fost adăugată cu succes!';
                    $messageType = 'success';
                    $action = 'list'; // Redirecționează la listă
                } else {
                    $message = 'Eroare la adăugarea preînregistrării!';
                    $messageType = 'error';
                }
            } else {
                $message = 'Date invalide: ' . implode(', ', $errors);
                $messageType = 'error';
            }
        }
        break;
        
    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $db = getDB();
                
                $sql = "UPDATE preinscrieri SET 
                    nume_parinte = :nume_parinte,
                    prenume_parinte = :prenume_parinte,
                    telefon_parinte = :telefon_parinte,
                    email_parinte = :email_parinte,
                    nume_copil = :nume_copil,
                    varsta_copil = :varsta_copil,
                    clasa_copil = :clasa_copil,
                    scoala_copil = :scoala_copil,
                    experienta_copil = :experienta_copil,
                    interese_copil = :interese_copil,
                    observatii = :observatii,
                    status = :status
                    WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([
                    ':nume_parinte' => sanitizeInput($_POST['nume_parinte']),
                    ':prenume_parinte' => sanitizeInput($_POST['prenume_parinte']),
                    ':telefon_parinte' => sanitizeInput($_POST['telefon_parinte']),
                    ':email_parinte' => sanitizeInput($_POST['email_parinte']),
                    ':nume_copil' => sanitizeInput($_POST['nume_copil']),
                    ':varsta_copil' => (int)$_POST['varsta_copil'],
                    ':clasa_copil' => sanitizeInput($_POST['clasa_copil']),
                    ':scoala_copil' => sanitizeInput($_POST['scoala_copil']),
                    ':experienta_copil' => sanitizeInput($_POST['experienta_copil']),
                    ':interese_copil' => sanitizeInput($_POST['interese_copil']),
                    ':observatii' => sanitizeInput($_POST['observatii']),
                    ':status' => sanitizeInput($_POST['status']),
                    ':id' => $id
                ]);
                
                if ($result) {
                    $message = 'Preînregistrarea a fost actualizată cu succes!';
                    $messageType = 'success';
                    $action = 'list';
                } else {
                    $message = 'Eroare la actualizarea preînregistrării!';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Eroare baza de date!';
                $messageType = 'error';
                debug_log("Eroare update preînregistrare: " . $e->getMessage());
            }
        }
        break;
        
    case 'delete':
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            try {
                $db = getDB();
                $stmt = $db->prepare("DELETE FROM preinscrieri WHERE id = :id");
                $result = $stmt->execute([':id' => $id]);
                
                if ($result) {
                    $message = 'Preînregistrarea a fost ștearsă cu succes!';
                    $messageType = 'success';
                } else {
                    $message = 'Eroare la ștergerea preînregistrării!';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Eroare baza de date!';
                $messageType = 'error';
                debug_log("Eroare delete preînregistrare: " . $e->getMessage());
            }
        }
        $action = 'list';
        break;
        
    case 'export':
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM preinscrieri ORDER BY data_inscriere DESC");
            $preinscrieri = $stmt->fetchAll();
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="preinscrieri_afterschool_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($output, [
                'ID', 'Nume Părinte', 'Prenume Părinte', 'Telefon', 'Email',
                'Nume Copil', 'Vârsta', 'Clasa', 'Școala', 'Experiență',
                'Interese', 'Observații', 'GDPR', 'Marketing', 'Foto',
                'Status', 'Data Înregistrare'
            ]);
            
            // Date
            foreach ($preinscrieri as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['nume_parinte'],
                    $row['prenume_parinte'],
                    $row['telefon_parinte'],
                    $row['email_parinte'],
                    $row['nume_copil'],
                    $row['varsta_copil'],
                    $row['clasa_copil'],
                    $row['scoala_copil'],
                    $row['experienta_copil'],
                    $row['interese_copil'],
                    $row['observatii'],
                    $row['acord_gdpr'] ? 'Da' : 'Nu',
                    $row['acord_marketing'] ? 'Da' : 'Nu',
                    $row['acord_foto'] ? 'Da' : 'Nu',
                    $row['status'],
                    $row['data_inscriere']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (PDOException $e) {
            $message = 'Eroare la exportul datelor!';
            $messageType = 'error';
            debug_log("Eroare export CSV: " . $e->getMessage());
        }
        $action = 'list';
        break;
}

// Pentru editare - obține datele existente
$editData = null;
if ($action === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM preinscrieri WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $editData = $stmt->fetch();
            
            if (!$editData) {
                $message = 'Preînregistrarea nu a fost găsită!';
                $messageType = 'error';
                $action = 'list';
            }
        } catch (PDOException $e) {
            $message = 'Eroare la încărcarea datelor!';
            $messageType = 'error';
            $action = 'list';
        }
    }
}

// Pentru listă - obține toate preînregistrările
$preinscrieri = [];
if ($action === 'list') {
    try {
        $db = getDB();
        
        // Filtrare și sortare
        $where = '';
        $params = [];
        
        if (!empty($_GET['status'])) {
            $where = 'WHERE status = :status';
            $params[':status'] = $_GET['status'];
        }
        
        $orderBy = 'ORDER BY data_inscriere DESC';
        if (!empty($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'name':
                    $orderBy = 'ORDER BY nume_copil ASC';
                    break;
                case 'date_asc':
                    $orderBy = 'ORDER BY data_inscriere ASC';
                    break;
                case 'status':
                    $orderBy = 'ORDER BY status ASC, data_inscriere DESC';
                    break;
            }
        }
        
        $sql = "SELECT * FROM preinscrieri $where $orderBy";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $preinscrieri = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        $message = 'Eroare la încărcarea preînregistrărilor!';
        $messageType = 'error';
        debug_log("Eroare obținere preînregistrări: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preînregistrări - TechMinds Academy Afterschool</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin.css">
    <link rel="stylesheet" href="assets/preinscrieri.css">
</head>
<body class="dashboard">
    <header class="dashboard-header">
        <div class="dashboard-logo">
            <i class="fas fa-code"></i>
            <a href="dashboard.php" style="color: inherit; text-decoration: none;">TechMinds Academy - Admin</a>
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
            <h1 class="page-title">
                <?php 
                switch($action) {
                    case 'add': echo 'Adaugă Preînregistrare'; break;
                    case 'edit': echo 'Editează Preînregistrare'; break;
                    default: echo 'Preînregistrări';
                }
                ?>
            </h1>
            <p class="page-subtitle">
                <?php 
                switch($action) {
                    case 'add': echo 'Adaugă o nouă preînregistrare manual'; break;
                    case 'edit': echo 'Modifică datele preînregistrării'; break;
                    default: echo 'Gestionează toate preînregistrările pentru afterschool';
                }
                ?>
            </p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- LISTĂ PREÎNREGISTRĂRI -->
            <div class="toolbar">
                <div class="toolbar-left">
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Adaugă Preînregistrare
                    </a>
                    <a href="?action=export" class="btn btn-success">
                        <i class="fas fa-download"></i>
                        Export CSV
                    </a>
                </div>
                
                <div class="toolbar-right">
                    <select onchange="window.location.href='?status=' + this.value + '&sort=<?php echo $_GET['sort'] ?? ''; ?>'" class="filter-select">
                        <option value="">Toate statusurile</option>
                        <option value="nou" <?php echo ($_GET['status'] ?? '') === 'nou' ? 'selected' : ''; ?>>Nou</option>
                        <option value="contactat" <?php echo ($_GET['status'] ?? '') === 'contactat' ? 'selected' : ''; ?>>Contactat</option>
                        <option value="confirmat" <?php echo ($_GET['status'] ?? '') === 'confirmat' ? 'selected' : ''; ?>>Confirmat</option>
                        <option value="respins" <?php echo ($_GET['status'] ?? '') === 'respins' ? 'selected' : ''; ?>>Respins</option>
                    </select>
                    
                    <select onchange="window.location.href='?sort=' + this.value + '&status=<?php echo $_GET['status'] ?? ''; ?>'" class="filter-select">
                        <option value="">Sortare implicită</option>
                        <option value="name" <?php echo ($_GET['sort'] ?? '') === 'name' ? 'selected' : ''; ?>>Nume copil</option>
                        <option value="date_asc" <?php echo ($_GET['sort'] ?? '') === 'date_asc' ? 'selected' : ''; ?>>Data (vechi)</option>
                        <option value="status" <?php echo ($_GET['sort'] ?? '') === 'status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                </div>
            </div>

            <div class="content-card">
                <div class="content-header">
                    <h2 class="content-title">
                        Preînregistrări (<?php echo count($preinscrieri); ?>)
                    </h2>
                </div>
                
                <div class="content-body">
                    <?php if (empty($preinscrieri)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Nu există preînregistrări</h3>
                            <p>Nu au fost găsite preînregistrări<?php echo !empty($_GET['status']) ? ' cu statusul selectat' : ''; ?>.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Copil</th>
                                        <th>Părinte</th>
                                        <th>Contact</th>
                                        <th>Detalii</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preinscrieri as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="cell-content">
                                                    <strong><?php echo htmlspecialchars($item['nume_copil']); ?></strong>
                                                    <small><?php echo $item['varsta_copil']; ?> ani, <?php echo htmlspecialchars($item['clasa_copil']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="cell-content">
                                                    <strong><?php echo htmlspecialchars($item['nume_parinte'] . ' ' . $item['prenume_parinte']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="cell-content">
                                                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($item['telefon_parinte']); ?></div>
                                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($item['email_parinte']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="cell-content">
                                                    <div><strong>Școala:</strong> <?php echo htmlspecialchars($item['scoala_copil']); ?></div>
                                                    <?php if ($item['experienta_copil']): ?>
                                                        <div><strong>Exp:</strong> <?php echo htmlspecialchars($item['experienta_copil']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo formatStatus($item['status']); ?></td>
                                            <td>
                                                <div class="cell-content">
                                                    <?php echo formatDate($item['data_inscriere'], 'd.m.Y'); ?>
                                                    <small><?php echo formatDate($item['data_inscriere'], 'H:i'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn-small btn-primary" title="Editează">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $item['id']; ?>" 
                                                       class="btn-small btn-danger" 
                                                       title="Șterge"
                                                       onclick="return confirm('Sigur doriți să ștergeți această preînregistrare?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- FORMULAR ADĂUGARE/EDITARE -->
            <div class="content-card">
                <form method="POST" class="registration-form">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Date Părinte/Tutore</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nume_parinte">Nume *</label>
                                <input type="text" id="nume_parinte" name="nume_parinte" 
                                       value="<?php echo htmlspecialchars($editData['nume_parinte'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="prenume_parinte">Prenume *</label>
                                <input type="text" id="prenume_parinte" name="prenume_parinte" 
                                       value="<?php echo htmlspecialchars($editData['prenume_parinte'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefon_parinte">Telefon *</label>
                                <input type="tel" id="telefon_parinte" name="telefon_parinte" 
                                       value="<?php echo htmlspecialchars($editData['telefon_parinte'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email_parinte">Email *</label>
                                <input type="email" id="email_parinte" name="email_parinte" 
                                       value="<?php echo htmlspecialchars($editData['email_parinte'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-child"></i> Date Copil</h3>
                        
                        <div class="form-group">
                            <label for="nume_copil">Nume complet copil *</label>
                            <input type="text" id="nume_copil" name="nume_copil" 
                                   value="<?php echo htmlspecialchars($editData['nume_copil'] ?? ''); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="varsta_copil">Vârsta *</label>
                                <select id="varsta_copil" name="varsta_copil" required>
                                    <option value="">Selectează vârsta</option>
                                    <?php for ($i = 6; $i <= 18; $i++): ?>
                                        <option value="<?php echo $i; ?>" 
                                                <?php echo ($editData['varsta_copil'] ?? '') == $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> ani
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="clasa_copil">Clasa *</label>
                                <select id="clasa_copil" name="clasa_copil" required>
                                    <option value="">Selectează clasa</option>
                                    <?php 
                                    $clase = ['Pregătitoare', 'Clasa I', 'Clasa II', 'Clasa III', 'Clasa IV', 'Clasa V', 'Clasa VI', 'Clasa VII', 'Clasa VIII', 'Clasa IX', 'Clasa X', 'Clasa XI', 'Clasa XII'];
                                    foreach ($clase as $clasa): 
                                    ?>
                                        <option value="<?php echo $clasa; ?>" 
                                                <?php echo ($editData['clasa_copil'] ?? '') === $clasa ? 'selected' : ''; ?>>
                                            <?php echo $clasa; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="scoala_copil">Școala *</label>
                            <input type="text" id="scoala_copil" name="scoala_copil" 
                                   value="<?php echo htmlspecialchars($editData['scoala_copil'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Informații Suplimentare</h3>
                        
                        <div class="form-group">
                            <label for="experienta_copil">Experiența în programare</label>
                            <select id="experienta_copil" name="experienta_copil">
                                <option value="">Selectează...</option>
                                <?php 
                                $experiente = ['Fără experiență', 'Începător', 'Intermediar', 'Avansat'];
                                foreach ($experiente as $exp): 
                                ?>
                                    <option value="<?php echo $exp; ?>" 
                                            <?php echo ($editData['experienta_copil'] ?? '') === $exp ? 'selected' : ''; ?>>
                                        <?php echo $exp; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="interese_copil">Interese în tehnologie</label>
                            <textarea id="interese_copil" name="interese_copil" rows="3"><?php echo htmlspecialchars($editData['interese_copil'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="observatii">Observații speciale</label>
                            <textarea id="observatii" name="observatii" rows="3"><?php echo htmlspecialchars($editData['observatii'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <?php if ($action === 'add'): ?>
                    <div class="form-section">
                        <h3><i class="fas fa-shield-alt"></i> Acorduri</h3>
                        
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="acord_gdpr" checked>
                                <span class="checkmark"></span>
                                Acord GDPR (obligatoriu)
                            </label>
                        </div>

                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="acord_marketing">
                                <span class="checkmark"></span>
                                Acord marketing
                            </label>
                        </div>

                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="acord_foto">
                                <span class="checkmark"></span>
                                Acord fotografii
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($action === 'edit'): ?>
                    <div class="form-section">
                        <h3><i class="fas fa-flag"></i> Status</h3>
                        
                        <div class="form-group">
                            <label for="status">Status preînregistrare</label>
                            <select id="status" name="status" required>
                                <option value="nou" <?php echo ($editData['status'] ?? '') === 'nou' ? 'selected' : ''; ?>>Nou</option>
                                <option value="contactat" <?php echo ($editData['status'] ?? '') === 'contactat' ? 'selected' : ''; ?>>Contactat</option>
                                <option value="confirmat" <?php echo ($editData['status'] ?? '') === 'confirmat' ? 'selected' : ''; ?>>Confirmat</option>
                                <option value="respins" <?php echo ($editData['status'] ?? '') === 'respins' ? 'selected' : ''; ?>>Respins</option>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $action === 'add' ? 'Adaugă Preînregistrarea' : 'Actualizează'; ?>
                        </button>
                        <a href="preinscrieri.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Anulează
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>