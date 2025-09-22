<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$password = getenv('MYSQLPASSWORD');
$dbname = getenv('MYSQLDATABASE');
$port = intval(getenv('MYSQLPORT'));

$mysqli = new mysqli($host, $user, $password, $dbname, $port);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected successfully!";
?>
