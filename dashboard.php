<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit;
}
// If logged in, show the HTML content
include 'dashboard.html';
?>