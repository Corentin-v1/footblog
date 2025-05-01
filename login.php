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
        $redirect_url = $_POST['redirect'] ?? $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        header("Location: $redirect_url");
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
<body style="display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:linear-gradient(135deg, #4158d0, rgb(204, 181, 202));">
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div style="width:100%;max-width:400px;padding:30px;background:rgba(255, 255, 255, 0.2);box-shadow:0 8px 32px rgba(0, 0, 0, 0.37);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-radius:16px;border:1px solid rgba(255, 255, 255, 0.18);">
        <h2 style="color:#fff;text-align:center;">Connexion</h2>
        <?php if ($err): ?>
            <div style="color:#ff6b6b;margin-bottom:10px;text-align:center;"><?php echo $err; ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? $_SESSION['redirect_after_login'] ?? ''); ?>">
            <label style="color:#fff;">Nom d'utilisateur<br>
                <input type="text" name="username" required style="width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:8px;background:rgba(255, 255, 255, 0.3);color:#fff;">
            </label><br>
            <label style="color:#fff;">Mot de passe<br>
                <input type="password" name="password" required style="width:100%;padding:8px;margin-bottom:10px;border:none;border-radius:8px;background:rgba(255, 255, 255, 0.3);color:#fff;">
            </label><br>
            <button type="submit" style="padding:10px 20px;background:rgba(255, 255, 255, 0.3);color:#fff;border:none;border-radius:8px;cursor:pointer;">Se connecter</button>
        </form>
    </div>
</body>
</html>