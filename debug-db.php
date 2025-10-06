<?php
session_start();
require_once "includes/database.php";

echo "<h2>Database Debug Info</h2>";

// Check session
echo "<h3>Session Info:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check database connection
try {
    $db = new Database();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if stories table exists and has data
    $result = $db->select('stories', '*', []);
    echo "<h3>All Stories in Database:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    // Check stories for current user
    if (isset($_SESSION['user']['id'])) {
        $user_stories = $db->select('stories', '*', ['user_id' => $_SESSION['user']['id']]);
        echo "<h3>Stories for Current User (ID: " . $_SESSION['user']['id'] . "):</h3>";
        echo "<pre>";
        print_r($user_stories);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}
?>