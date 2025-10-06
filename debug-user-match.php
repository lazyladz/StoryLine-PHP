<?php
session_start();
require_once "includes/database.php";

echo "<h2>User & Stories Match Debug</h2>";

// Check session
echo "<h3>Current Session User:</h3>";
echo "<pre>";
print_r($_SESSION['user']);
echo "</pre>";

$user_id = $_SESSION['user']['id'];

try {
    $db = new Database();
    
    // Check ALL stories in database
    $all_stories = $db->select('stories', '*', []);
    echo "<h3>ALL Stories in Database (showing user_id for each):</h3>";
    if (isset($all_stories['data']) && is_array($all_stories['data'])) {
        foreach ($all_stories['data'] as $story) {
            echo "Story ID: " . ($story['id'] ?? 'N/A') . " | ";
            echo "Title: " . ($story['title'] ?? 'N/A') . " | ";
            echo "User ID: " . ($story['user_id'] ?? 'NO USER_ID') . " | ";
            echo "Matches current user? " . (($story['user_id'] ?? '') == $user_id ? 'YES' : 'NO');
            echo "<br>";
        }
    } else {
        echo "No stories found in database";
    }
    
    echo "<h3>Querying stories for user_id = $user_id:</h3>";
    $user_stories = $db->select('stories', '*', ['user_id' => $user_id]);
    echo "<pre>";
    print_r($user_stories);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>