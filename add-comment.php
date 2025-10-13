<?php
session_start();
require_once "includes/database.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please login to comment']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

$story_id = isset($input['story_id']) ? intval($input['story_id']) : null;
$comment = isset($input['comment']) ? trim($input['comment']) : null;

if (!$story_id || $story_id <= 0 || !$comment) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $db = new Database();
    
    // Insert comment into database
    $comment_data = [
        'story_id' => $story_id,
        'user_id' => (int)$_SESSION['user']['id'],
        'author' => $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'],
        'comment_text' => $comment
        // created_at and updated_at will be set automatically by Supabase
    ];
    
    $result = $db->insert('comments', $comment_data);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save comment']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error adding comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>