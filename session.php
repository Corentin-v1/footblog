<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}

// Store the current page and query string in the session for redirection after login/logout
if (!isset($_SESSION['redirect_after_login'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
}
?>