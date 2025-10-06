<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$story_id = $input['story_id'] ?? null;
$user_id = $_SESSION['user']['id'];

if (!$story_id) {
    echo json_encode(['success' => false, 'error' => 'No story ID provided']);
    exit;
}

try {
    // Include your custom database class
    require_once __DIR__ . '/../includes/Database.php';
    
    // Create database instance
    $db = new Database();
    
    // First verify the story belongs to the user
    $verifyResult = $db->select('stories', 'id', [
        'id' => $story_id,
        'user_id' => $user_id
    ]);
    
    if (isset($verifyResult['data']) && !empty($verifyResult['data'])) {
        // Delete the story
        $deleteResult = $db->delete('stories', 'id', $story_id);
        
        if ($deleteResult['success']) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to delete story from database');
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Story not found or access denied']);
    }
    
} catch (Exception $e) {
    error_log("Error in delete-story.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>