<?php
session_start(); // Must be at the very top, before any output
require_once "config.php"; // Your MySQL connection

header("Content-Type: application/json");

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method Not Allowed"]);
    exit;
}

// Get POST data safely
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

$errors = [];

// Validation
if (!$firstName) $errors[] = "First name is required.";
if (!$lastName) $errors[] = "Last name is required.";
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";

if (!empty($errors)) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

// Check if email already exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "errors" => ["Email is already registered."]]);
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Registration successful! Redirecting to login..."]);
} else {
    echo json_encode(["success" => false, "errors" => ["Database error: " . $stmt->error]]);
}

$stmt->close();
$mysqli->close();
