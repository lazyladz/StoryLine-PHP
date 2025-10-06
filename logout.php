<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
if (session_destroy()) {
    // Redirect to login page
    header("Location: login.html");
    exit;
}
?>