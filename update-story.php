<?php
// Add error handling at the very top
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "includes/database.php";

header('Content-Type: application/json');

// Function to send JSON error response
function sendError($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendError('Method not allowed');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendError('Invalid input data');
}

// Validate required fields
if (empty($input['title']) || empty($input['author']) || empty($input['chapters']) || empty($input['id'])) {
    sendError('Missing required fields');
}

try {
    $db = new Database();
    
    // Debug: Log the incoming data
    error_log("UPDATE STORY - Received data for story ID: " . $input['id']);
    error_log("UPDATE STORY - Title: " . $input['title']);
    error_log("UPDATE STORY - Author: " . $input['author']);
    error_log("UPDATE STORY - Description length: " . strlen($input['description'] ?? ''));
    error_log("UPDATE STORY - Chapters count: " . count($input['chapters']));
    error_log("UPDATE STORY - User ID: " . $_SESSION['user']['id']);
    
    // Prepare story data for Supabase
    $storyData = [
        'title' => $input['title'],
        'author' => $input['author'],
        'description' => $input['description'] ?? '',
        'genre' => json_encode($input['genre']),
        'cover_image' => $input['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'chapters' => json_encode($input['chapters']),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Debug the data being sent to update
    error_log("UPDATE STORY - Data to update: " . print_r($storyData, true));
    
    // Update the story - ensure it belongs to the current user using multiple conditions
    $conditions = [
        'id' => $input['id'],
        'user_id' => $_SESSION['user']['id']
    ];
    
    error_log("UPDATE STORY - Update conditions: " . print_r($conditions, true));
    
    // Use the new updateWithConditions method
    $result = $db->updateWithConditions('stories', $storyData, $conditions);
    
    error_log("UPDATE STORY - Update result: " . print_r($result, true));
    
    if ($result) {
        error_log("UPDATE STORY - Success: Story updated successfully - " . $input['title']);
        echo json_encode([
            'success' => true, 
            'message' => 'Story updated successfully', 
            'data' => $result
        ]);
    } else {
        error_log("UPDATE STORY - Failed: No rows affected or update failed - " . $input['title']);
        sendError('Failed to update story in database - no rows affected');
    }
    
} catch (Exception $e) {
    error_log("UPDATE STORY - Exception: " . $e->getMessage());
    error_log("UPDATE STORY - Stack trace: " . $e->getTraceAsString());
    sendError('Database error: ' . $e->getMessage());
}
?>