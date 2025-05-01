<?php
include_once 'session.php';

// Use the `redirect` query parameter to determine where to redirect after logout
$redirect_url = $_GET['redirect'] ?? 'index.php';
session_destroy();
header("Location: $redirect_url");
exit;
?>
