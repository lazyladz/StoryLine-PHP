<?php
session_start(); // Must be at the very top
require_once "includes/database.php"; // Now using Supabase

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// Get POST data
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validation
$errors = [];
if (!$firstName) $errors[] = "First name is required.";
if (!$lastName) $errors[] = "Last name is required.";
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: register.html");
    exit;
}

try {
    $db = new Database();
    
    // Check if email exists using Supabase
    $existingUser = $db->select('users', '*', ['email' => $email]);
    
    if (!empty($existingUser)) {
        $_SESSION['errors'] = ["Email is already registered."];
        header("Location: register.html");
        exit;
    }

    // Hash password and insert using Supabase
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $userData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => $hashedPassword
    ];
    
    $result = $db->insert('users', $userData);
    
    if ($result) {
        // Registration successful
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.html");
        exit;
    } else {
        throw new Exception("Failed to create user account.");
    }
    
} catch (Exception $e) {
    $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
    header("Location: register.html");
    exit;
}
?>