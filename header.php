<?php
include_once 'session.php';
?>
<header style="width:100%;display:flex;justify-content:flex-end;align-items:center;padding:10px 20px;box-sizing:border-box;">
    <?php if (is_logged_in()): ?>
        <span style="margin-right:10px;">Connecté : <?php echo htmlspecialchars($_SESSION['user']); ?></span>
        <a href="logout.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI'] . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>" style="padding:8px 16px;background:#007BFF;color:#fff;text-decoration:none;border-radius:4px;">Déconnexion</a>
    <?php else: ?>
        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING']); ?>" style="padding:8px 16px;background:#007BFF;color:#fff;text-decoration:none;border-radius:4px;">Connexion</a>
    <?php endif; ?>
    <button id="darkModeBtn" style="margin-left:14px;padding:8px 16px;background:#222;color:#fff;border:none;border-radius:4px;cursor:pointer;">🌙</button>
</header>
<script>
(function() {
    function setDarkMode(on) {
        if (on) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', '1');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', '0');
        }
    }
    document.getElementById('darkModeBtn').onclick = function() {
        setDarkMode(!document.body.classList.contains('dark-mode'));
    };
    // Initialisation au chargement
    if (localStorage.getItem('darkMode') === '1') {
        document.body.classList.add('dark-mode');
    }
})();
</script>