<?php
include("../../config.php");
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../../frontend/auth/login.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];
$subject_id = intval($_POST['subject_id']);
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$date = $_POST['date'];
$week_no = intval($_POST['week_no']);
$semester_applicability = intval($_POST['semester_applicability']);
$status = 'planned';

$sql = "INSERT INTO assessments (faculty_id, subject_id, title, description, date, week_no, semester_applicability, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("iisssiss", $faculty_id, $subject_id, $title, $description, $date, $week_no, $semester_applicability, $status);

if ($stmt->execute()) {
    header("Location: ../../frontend/faculty/add_assessment.php?success=1");
    exit;
} else {
    die("Failed to add assessment: " . $stmt->error);
}
?>