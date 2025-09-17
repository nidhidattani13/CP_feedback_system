<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') die("Access denied");

$faculty_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$week_no = intval($_POST['week_no'] ?? 0);

if(!$title || !$week_no) die("Missing fields.");

$stmt = $conn->prepare("INSERT INTO assessments (faculty_id, title, week_no) VALUES (?,?,?)");
$stmt->bind_param("isi", $faculty_id, $title, $week_no);
if($stmt->execute()){
    header("Location: " . APP_BASE . "/frontend/faculty/dashboard.php");
    exit;
}else die("Insert failed.");
