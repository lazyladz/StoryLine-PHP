<?php
session_start();
require_once "config.php"; // Make sure $mysqli is defined here

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

// Check credentials
$stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(["success" => false, "errors" => ["Invalid email or password."]]);
    exit;
}

// ✅ Login successful: save session
$_SESSION['user'] = [
    "id" => $user['id'],
    "first_name" => $user['first_name'],
    "last_name" => $user['last_name'],
    "email" => $user['email']
];

// Respond with redirect
echo json_encode(["success" => true, "redirect" => "dashboard.html"]);

$mysqli->close();
?>
