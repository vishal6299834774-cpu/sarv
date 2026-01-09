<?php
require_once 'includes/functions.php';

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    // Clear token from database
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("UPDATE users SET remember_token = NULL, token_expiry = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    
    // Clear cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destroy session
session_destroy();

// Redirect to login page
redirect('login.php');
?>
