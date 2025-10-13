<?php
session_start();
require_once "includes/database.php";

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$story_id = $input['story_id'] ?? null;
$current_chapter_index = $input['current_chapter_index'] ?? 0;
$progress_percentage = $input['progress_percentage'] ?? 0;

if (!$story_id) {
    echo json_encode(['success' => false, 'error' => 'Story ID required']);
    exit;
}

try {
    $db = new Database();
    
    // Check if progress already exists
    $existing = $db->select('reading_progress', '*', [
        'user_id' => $_SESSION['user']['id'],
        'story_id' => $story_id
    ]);
    
    if ($existing && count($existing) > 0) {
        // Update existing progress
        $db->update('reading_progress', [
            'current_chapter_index' => $current_chapter_index,
            'progress_percentage' => $progress_percentage,
            'last_read_at' => date('Y-m-d H:i:s')
        ], [
            'user_id' => $_SESSION['user']['id'],
            'story_id' => $story_id
        ]);
    } else {
        // Create new progress
        $db->insert('reading_progress', [
            'user_id' => $_SESSION['user']['id'],
            'story_id' => $story_id,
            'current_chapter_index' => $current_chapter_index,
            'progress_percentage' => $progress_percentage,
            'last_read_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Error updating reading progress: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>