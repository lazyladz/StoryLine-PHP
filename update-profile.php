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

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

// Validate required fields
if (empty($input['first_name']) || empty($input['last_name']) || empty($input['email'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit;
}

try {
    $db = new Database();
    
    // Prepare update data
    $updateData = [
        'first_name' => trim($input['first_name']),
        'last_name' => trim($input['last_name']),
        'email' => trim($input['email'])
    ];
    
    // Handle profile image if provided
    if (isset($input['profile_image']) && !empty($input['profile_image'])) {
        $updateData['profile_image'] = $input['profile_image'];
    }
    
    // Update user in database
    $result = $db->update('users', $updateData, 'id', $_SESSION['user']['id']);
    
    if ($result) {
        // Update session data
        $_SESSION['user']['first_name'] = $updateData['first_name'];
        $_SESSION['user']['last_name'] = $updateData['last_name'];
        $_SESSION['user']['email'] = $updateData['email'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'user' => $_SESSION['user']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile in database']);
    }
    
} catch (Exception $e) {
    error_log("Error updating profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
