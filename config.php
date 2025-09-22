<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$dbname = getenv('MYSQLDATABASE');
$port = intval(getenv('MYSQLPORT'));

// Use $conn as the variable name (or stick with $mysqli but be consistent)
$conn = new mysqli($host, $user, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// REMOVE any echo statements — do not output anything here
// echo "Connected successfully!";
?>
