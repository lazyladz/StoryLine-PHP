<?php
session_start();
require_once "includes/database.php";

header('Content-Type: application/json');

// Get story_id from GET parameter
$story_id = isset($_GET['story_id']) ? intval($_GET['story_id']) : null;

if (!$story_id || $story_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Valid Story ID required']);
    exit;
}

try {
    $db = new Database();
    
    // Use the select() method with filters
    $comments = $db->select('comments', '*', ['story_id' => $story_id]);
    
    // If we need to order by created_at DESC, we might need to handle it differently
    // Since SupabaseManual might not support ORDER BY in filters, we'll sort in PHP
    if (is_array($comments) && !empty($comments)) {
        // Sort comments by created_at descending
        usort($comments, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
    
    // Ensure we always return an array, even if empty
    $comments = $comments ?: [];
    
    echo json_encode([
        'success' => true, 
        'comments' => $comments
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error fetching comments: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to load comments: ' . $e->getMessage()
    ]);
}
?>