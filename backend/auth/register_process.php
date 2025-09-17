<?php
session_start();
include("../../config.php");

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';

if (!$name || !$email || !$password) {
    die("Missing fields.");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
$stmt->bind_param("ssss", $name, $email, $hashed, $role);
if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $name;
    header("Location: " . APP_BASE . "/frontend/{$role}/dashboard.php");
    exit;
} else {
    die("Register failed: " . $conn->error);
}
