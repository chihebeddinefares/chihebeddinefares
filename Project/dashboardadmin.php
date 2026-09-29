<?php
session_start();
require_once 'dbconnexion.php';

// Vérification de sécurité : Connecté ET rôle ADMIN
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'ADMIN') {
    header('Location: dashboarduser.php');
    exit;
}

$message = '';
$error = '';

// --- GESTION DES ACTIONS ADMINISTRATEUR ---

// 1. SUPPRESSION D'UN UTILISATEUR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $target_id = intval($_POST['user_id'] ?? 0);

    // Empêcher l'admin de supprimer son propre compte
    if ($target_id === $_SESSION['user_id']) {
        $error = "Vous ne pouvez pas supprimer votre propre compte !";
    } else {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
        if ($stmt->execute([$target_id])) {
            $message = "Utilisateur supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression.";
        }
    }
}

// 2. CHANGEMENT DE RÔLE (ADMIN <-> USER)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_role') {
    $target_id = intval($_POST['user_id'] ?? 0);
    $new_role = $_POST['new_role'] ?? 'USER';

    // Empêcher l'admin de modifier son propre rôle
    if ($target_id === $_SESSION['user_id']) {
        $error = "Vous ne pouvez pas modifier votre propre rôle !";
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
        if ($stmt->execute([$new_role, $target_id])) {
            $message = "Rôle mis à jour avec succès.";
        } else {
            $error = "Erreur lors de la mise à jour du rôle.";
        }
    }
}

// --- RÉCUPÉRATION DES DONNÉES ET STATISTIQUES ---
$totalUsers = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$totalAdmins = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'ADMIN'")->fetchColumn();

$stmt = $pdo->query("SELECT id, nom, prenom, email, role FROM utilisateurs ORDER BY id DESC");
$utilisateurs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administration</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-wrapper {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .stats-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 4px;
        }
        .stat-card h4 { margin: 0 0 5px 0; color: #555; }
        .stat-card p { margin: 0; font-size: 22px; font-weight: bold; color: #2c3e50; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e0e0e0; padding: 12px; text-align: left; }
        th { background-color: #f4f6f7; color: #333; }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-admin { background: #e74c3c; color: #fff; }
        .badge-user { background: #2ecc71; color: #fff; }

        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            color: #fff;
            margin-right: 4px;
        }
        .btn-toggle { background-color: #f39c12; }
        .btn-delete { background-color: #e74c3c; }
        .btn-logout {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #7f8c8d;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <div class="admin-wrapper">
        <h2>Panneau d'Administration</h2>
        <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']) ?></strong> (ADMIN)</p>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Utilisateurs</h4>
                <p><?= $totalUsers ?></p>
            </div>
            <div class="stat-card" style="border-left-color: #e74c3c;">
                <h4>Administrateurs</h4>
                <p><?= $totalAdmins ?></p>
            </div>
        </div>

        <!-- Messages de confirmation ou d'erreur -->
        <?php if ($message): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;"><?= $error ?></div>
        <?php endif; ?>

        <h3>Gestion des Comptes</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="badge <?= $user['role'] === 'ADMIN' ? 'badge-admin' : 'badge-user' ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <!-- Changer de Rôle -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_role">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="new_role" value="<?= $user['role'] === 'ADMIN' ? 'USER' : 'ADMIN' ?>">
                                    <button type="submit" class="btn-action btn-toggle">
                                        Passer en <?= $user['role'] === 'ADMIN' ? 'USER' : 'ADMIN' ?>
                                    </button>
                                </form>

                                <!-- Supprimer un compte -->
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <em>(Vous)</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="deconnexion.php" class="btn-logout">Se déconnecter</a>
    </div>

</body>
</html>