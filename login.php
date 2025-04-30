<?php
include_once 'session.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    // Lire les identifiants et les hash de mots de passe depuis conn.log
    $lines = file('conn.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logins = [];
    $hashes = [];

    foreach ($lines as $line) {
        if (strpos($line, 'log=') === 0) {
            $logins = array_map('trim', explode(',', substr($line, 4)));
        }
        if (strpos($line, 'mdp=') === 0) {
            $hashes = array_map('trim', explode(',', substr($line, 4)));
        }
    }

    $valid = false;
    foreach ($logins as $i => $login) {
        if ($user === $login && isset($hashes[$i])) {
            // On hash le mot de passe saisi avec MD5
            $pass_hash = md5($pass);
            if ($pass_hash === $hashes[$i]) {
                $valid = true;
                break;
            }
        }
    }
    if ($valid) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $err = "Identifiants invalides.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.php">
</head>
<body>
    <?php include 'header.php'; ?>
    <div style="max-width:400px;margin:60px auto;padding:30px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);border-radius:8px;">
        <h2>Connexion</h2>
        <?php if ($err): ?>
            <div style="color:red;margin-bottom:10px;"><?php echo $err; ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Nom d'utilisateur<br>
                <input type="text" name="username" required style="width:100%;padding:8px;margin-bottom:10px;">
            </label><br>
            <label>Mot de passe<br>
                <input type="password" name="password" required style="width:100%;padding:8px;margin-bottom:10px;">
            </label><br>
            <button type="submit" style="padding:8px 16px;background:#007BFF;color:#fff;border:none;border-radius:4px;">Se connecter</button>
        </form>
    </div>
</body>
</html>
