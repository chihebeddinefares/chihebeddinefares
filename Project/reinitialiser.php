<?php
require_once 'dbconnexion.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    die("Jeton de réinitialisation manquant.");
}

// Vérification de l'existence du token en BDD
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE token_recuperation = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Jeton invalide ou expiré.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Mise à jour du mot de passe et réinitialisation du token à NULL
        $update = $pdo->prepare("UPDATE utilisateurs SET password = ?, token_recuperation = NULL WHERE id = ?");
        if ($update->execute([$hashedPassword, $user['id']])) {
            $success = "Mot de passe réinitialisé avec succès ! <a href='login.php'>Se connecter</a>";
        } else {
            $error = "Une erreur est survenue.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Nouveau mot de passe</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
            <form action="reinitialiser.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit">Enregistrer</button>
            </form>
        <?php endif; ?>

        <div class="links">
            <a href="login.php">Se connecter</a>
        </div>
    </div>
</body>
</html>