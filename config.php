<?php
// Read environment variables
$host = getenv('MYSQLHOST') ?: 'containers-us-west-123.railway.app';
$user = getenv('MYSQLUSER') ?: 'your_username';
$password = getenv('MYSQLPASSWORD') ?: 'your_password';
$dbname = getenv('MYSQLDATABASE') ?: 'your_dbname';
$port = intval(getenv('MYSQLPORT')) ?: 3306;

// Debug: make sure values are set
var_dump($host, $user, $password, $dbname, $port);

// Create connection
$mysqli = new mysqli($host, $user, $password, $dbname, $port);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected successfully!";
?>
