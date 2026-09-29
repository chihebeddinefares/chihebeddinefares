<?php
session_start();

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$nom = $_SESSION['nom'] ?? 'Utilisateur';
$prenom = $_SESSION['prenom'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Utilisateur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Bienvenue sur votre Espace utilisateur</h2>
        <p>Bonjour, <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong> !</p>
        <p>Vous êtes connecté en tant qu'utilisateur simple.</p>
        
        <br>
        <a href="deconnexion.php" class="btn-danger" style="display:inline-block; padding:10px 15px; background:#e74c3c; color:#fff; text-decoration:none; border-radius:4px;">Se déconnecter</a>
    </div>
</body>
</html>