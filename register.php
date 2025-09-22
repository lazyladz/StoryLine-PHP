<?php
session_start(); // Must be at the very top
require_once "config.php"; // $mysqli comes from here

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

// Check if email exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['errors'] = ["Email is already registered."];
    $stmt->close();
    $mysqli->close();
    header("Location: register.html");
    exit;
}
$stmt->close();

// Hash password and insert
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

if ($stmt->execute()) {
    $stmt->close();
    $mysqli->close();
    header("Location: login.html"); // Redirect to login page
    exit;
} else {
    $_SESSION['errors'] = ["Database error: " . $stmt->error];
    $stmt->close();
    $mysqli->close();
    header("Location: register.html");
    exit;
}
?>
