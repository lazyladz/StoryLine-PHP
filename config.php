<?php
// Read environment variables from Railway
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$dbname = getenv('MYSQLDATABASE');
$port = intval(getenv('MYSQLPORT'));

// Create connection
$mysqli = new mysqli($host, $user, $password, $dbname, $port);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected successfully!";
?>
