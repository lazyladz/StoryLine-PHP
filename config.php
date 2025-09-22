<?php
// Get database connection details from Railway's environment variables
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $password = getenv('MYSQLPASSWORD');
    $dbname = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT');

// Create connection
$mysqli = new mysqli($host, $user, $password, $dbname, $port);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "Connected successfully to Railway's MySQL!";
?>