<?php
/**
 * Logout Handler
 * 
 * Destroys admin session and redirects to login page
 */

session_start();

// Clear all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Destroy session
session_destroy();

// Redirect to login page
header('Location: admin-login.html');
exit;

?>
