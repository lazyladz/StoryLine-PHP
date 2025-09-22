<?php
session_start(); // Must be at the very top, before any output
require_once "config.php"; // Your MySQL connection ($conn)

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
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
    // Store errors in session to display in form if needed
    $_SESSION['errors'] = $errors;
    header("Location: register.html"); // Redirect back to registration page
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $_SESSION['errors'] = ["Email is already registered."];
    $stmt->close();
    $conn->close();
    header("Location: register.html"); // Redirect back to registration page
    exit;
}
$stmt->close();

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // Redirect to login after successful registration
    header("Location: login.html");
    exit;
} else {
    $_SESSION['errors'] = ["Database error: " . $stmt->error];
    $stmt->close();
    $conn->close();
    header("Location: register.html");
    exit;
}
?>
