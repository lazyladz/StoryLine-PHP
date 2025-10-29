<?php
session_start();
require_once "includes/database.php"; // Now using Supabase

header("Content-Type: application/json");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit;
}

// Get POST data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (!$password) $errors[] = "Password is required.";

if (!empty($errors)) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

try {
    $db = new Database();
    
    // Check if user exists using Supabase
    $users = $db->select('users', '*', ['email' => $email]);
    
    if (empty($users)) {
        echo json_encode(["success" => false, "errors" => ["Invalid email or password."]]);
        exit;
    }
    
    $user = $users[0]; // Get first user (should be only one due to unique email)
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(["success" => false, "errors" => ["Invalid email or password."]]);
        exit;
    }
    
    // ✅ Login successful: save session with role
    $_SESSION['user'] = [
        "id" => $user['id'],
        "first_name" => $user['first_name'],
        "last_name" => $user['last_name'],
        "email" => $user['email'],
        "role" => $user['role'] // Add role to session
    ];
    
    // ✅ Redirect based on role
    $redirect = ($user['role'] == 'admin') ? 'indexAdmin.php' : 'dashboard.php';
    
    // Respond with redirect
    echo json_encode(["success" => true, "redirect" => $redirect]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "errors" => ["Database error: " . $e->getMessage()]]);
    exit;
}
?>