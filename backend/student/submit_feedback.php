<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access denied");
}

$student_id = $_SESSION['user_id'];
$faculty_id = intval($_POST['faculty_id'] ?? 0);
$subject = trim($_POST['subject'] ?? '');
$rating = intval($_POST['rating'] ?? 0);
$comments = trim($_POST['comments'] ?? '');

if (!$faculty_id || !$subject || !$rating) {
    die("Missing required fields.");
}

$stmt = $conn->prepare("INSERT INTO feedback (student_id, faculty_id, subject, rating, comments) VALUES (?,?,?,?,?)");
$stmt->bind_param("iiiss", $student_id, $faculty_id, $subject, $rating, $comments);
if($stmt->execute()){
    header("Location: " . APP_BASE . "/frontend/student/history.php");
    exit;
} else {
    die("Insert failed: " . $conn->error);
}
