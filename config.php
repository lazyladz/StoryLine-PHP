<?php
$host = "localhost";
$user = "root";    // default in XAMPP
$pass = "";        // default is blank
$db   = "myapp";   // make sure this database exists in phpMyAdmin

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
