<?php
$host = 'containers-us-west-123.railway.app'; // Railway MySQL host
$user = 'root';                       // Railway MySQL user
$password = 'JdnuFjCVaPQpxmDEfYhiuRMewYgxSxmM';                   // Railway MySQL password
$dbname = 'railway';                       // Railway MySQL database
$port = 3306;                                  // Railway MySQL port

$mysqli = new mysqli($host, $user, $password, $dbname, $port);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected successfully!";
?>
