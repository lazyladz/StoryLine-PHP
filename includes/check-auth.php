<?php
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.html");
        exit;
    }
    return $_SESSION['user'];
}
?>