<?php
require_once 'dbconnexion.php';

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Veuillez saisir votre adresse email.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Génération d'un jeton aléatoire
            $token = bin2hex(random_bytes(16));

            // Enregistrement dans le champ token_recuperation
            $updateStmt = $pdo->prepare("UPDATE utilisateurs SET token_recuperation = ? WHERE email = ?");
            $updateStmt->execute([$token, $email]);

            // Affichage du lien de simulation conforme à l'énoncé
            $resetLink = "reinitialiser.php?token=" . $token;
            $message = "[Simulation Mail] Cliquez sur ce lien pour réinitialiser : <br><a href='$resetLink'><strong>$resetLink</strong></a>";
        } else {
            $error = "Aucun compte n'est associé à cet email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Mot de passe oublié</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <form action="forgot.php" method="POST">
            <div class="form-group">
                <label for="email">Saisissez votre email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <button type="submit">Valider</button>
        </form>

        <div class="links">
            <a href="login.php">Retour à la connexion</a>
        </div>
    </div>
</body>
</html>