<?php
session_start();
include("../../config.php");

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (!$email || !$password) die("Missing fields.");

$stmt = $conn->prepare("SELECT id, password, role, name, enrollment_no, cgpa, category FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s",$email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows !== 1) {
    die("User not found. Please register.");
}
$user = $res->fetch_assoc();
if (!password_verify($password, $user['password'])) {
    die("Invalid credentials.");
}
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['name'] = $user['name'];
$_SESSION['enrollment_no'] = $user['enrollment_no'];
if ($user['role'] === 'student') {
    $_SESSION['cgpa'] = $user['cgpa'];
    $_SESSION['category'] = $user['category'];
}

if(!$user) {
  header("Location: " . APP_BASE . "/frontend/auth/login.php?error=invalid");
  exit;
}

switch($user['role']) {
    case 'student': header("Location: ".APP_BASE."/frontend/student/dashboard.php"); break;
    case 'faculty': header("Location: ".APP_BASE."/frontend/faculty/dashboard.php"); break;
    case 'hod': header("Location: ".APP_BASE."/frontend/hod/dashboard.php"); break;
    default: header("Location: ".APP_BASE."/frontend/auth/login.php"); break;
}
exit;
