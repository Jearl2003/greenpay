<?php
// Logout handler - clears session and redirects to login
session_start();
session_destroy();
header('Location: index.php');
exit;
?>

