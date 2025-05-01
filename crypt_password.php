<<<<<<< HEAD
<?php
$hashed = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $password = $_POST['password'];
    // Utiliser le même algo que dans conn.log (MD5)
    $hashed = md5($password);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiffrer un mot de passe</title>
</head>
<body>
    <h2>Chiffrer un mot de passe (MD5)</h2>
    <form method="post">
        <label>Mot de passe :
            <input type="text" name="password" required>
        </label>
        <button type="submit">Chiffrer</button>
    </form>
    <?php if ($hashed): ?>
        <div>
            <strong>Mot de passe chiffré :</strong><br>
            <code><?php echo htmlspecialchars($hashed); ?></code>
        </div>
    <?php endif; ?>
</body>
</html>
=======
<?php
$hashed = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $password = $_POST['password'];
    // Utiliser le même algo que dans conn.log (MD5)
    $hashed = md5($password);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiffrer un mot de passe</title>
</head>
<body>
    <h2>Chiffrer un mot de passe (MD5)</h2>
    <form method="post">
        <label>Mot de passe :
            <input type="text" name="password" required>
        </label>
        <button type="submit">Chiffrer</button>
    </form>
    <?php if ($hashed): ?>
        <div>
            <strong>Mot de passe chiffré :</strong><br>
            <code><?php echo htmlspecialchars($hashed); ?></code>
        </div>
    <?php endif; ?>
</body>
</html>
>>>>>>> d4e83f785fc5091700cc1c0e3758e855ef045bd2
