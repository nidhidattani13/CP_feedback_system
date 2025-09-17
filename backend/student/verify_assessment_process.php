<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') die("Access denied");

$aid = intval($_POST['assessment_id'] ?? 0);
if(!$aid) die("Invalid id");

$stmt = $conn->prepare("UPDATE assessments SET status='verified' WHERE id = ?");
$stmt->bind_param("i",$aid);
if($stmt->execute()){
    header("Location: " . APP_BASE . "/frontend/student/verify_assessment.php");
    exit;
}else die("Update failed.");
