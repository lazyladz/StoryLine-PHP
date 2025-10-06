<?php
session_start();
require_once "includes/database.php";

$db = new Database();
$user_id = $_SESSION['user']['id'] ?? 'no-user';

echo "<h2>Debug: Stories in Database</h2>";
echo "<p>User ID: " . $user_id . "</p>";

try {
    $result = $db->select('stories', '*', ['user_id' => $user_id]);
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>