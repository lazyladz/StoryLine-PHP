<?php
session_start();
require_once "includes/database.php";

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

// Validate required fields
if (empty($input['title']) || empty($input['author']) || empty($input['chapters'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $db = new Database();
    
    // Prepare story data for Supabase
    $storyData = [
        'title' => $input['title'],
        'author' => $input['author'],
        'description' => $input['description'] ?? '', // ADD THIS LINE for the description
        'genre' => json_encode($input['genre'], JSON_UNESCAPED_UNICODE), // Store as JSON array
'chapters' => json_encode($input['chapters'], JSON_UNESCAPED_UNICODE), // Store chapters as JSON
        'cover_image' => $input['cover_image'] ?? 'https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80',
        'user_id' => $_SESSION['user']['id'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Debug: Log the data being sent
    error_log("Attempting to save story: " . $input['title']);
    error_log("User ID: " . $_SESSION['user']['id']);
    error_log("Description length: " . strlen($input['description'] ?? ''));
    
    // Insert into stories table
    $result = $db->insert('stories', $storyData);
    
    if ($result) {
        error_log("Story saved successfully: " . $input['title']);
        echo json_encode(['success' => true, 'message' => 'Story saved successfully', 'data' => $result]);
    } else {
        error_log("Failed to save story: " . $input['title']);
        echo json_encode(['success' => false, 'error' => 'Failed to save story to database']);
    }
    
} catch (Exception $e) {
    error_log("Error saving story: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>