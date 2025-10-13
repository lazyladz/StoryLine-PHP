<?php
require_once "includes/database.php";

try {
    $db = new Database();
    
    echo "<h3>Testing Supabase Comments Connection</h3>";
    
    // Test connection
    $test = $db->testConnection();
    echo "<p>Connection test: " . ($test ? 'SUCCESS' : 'FAILED') . "</p>";
    
    // Test selecting comments
    $comments = $db->select('comments', '*', ['story_id' => 1]);
    echo "<p>Comments found: " . (is_array($comments) ? count($comments) : '0') . "</p>";
    
    if (is_array($comments) && !empty($comments)) {
        echo "<h4>Sample Comment:</h4>";
        echo "<pre>";
        print_r($comments[0]);
        echo "</pre>";
    }
    
    // Test inserting a comment
    $test_comment = [
        'story_id' => 1,
        'user_id' => 1,
        'author' => 'Test User',
        'comment_text' => 'This is a test comment'
    ];
    
    $insert_result = $db->insert('comments', $test_comment);
    echo "<p>Insert test: " . ($insert_result ? 'SUCCESS' : 'FAILED') . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>