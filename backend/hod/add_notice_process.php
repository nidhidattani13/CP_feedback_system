<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') die("Access denied");

$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
if ($title === '') {
    header('Location: ' . APP_BASE . '/frontend/hod/dashboard.php?err=missing');
    exit;
}

$stmt = $conn->prepare("INSERT INTO notices (title, body, created_by) VALUES (?,?,?)");
$stmt->bind_param('ssi', $title, $body, $_SESSION['user_id']);
if ($stmt->execute()) {
    header('Location: ' . APP_BASE . '/frontend/hod/dashboard.php?ok=1');
    exit;
}

die('Failed to add notice: ' . $conn->error);
?>

