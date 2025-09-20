<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') die("Access denied");

$title = trim($_POST['title'] ?? '');
$date = $_POST['date'] ?? null;
$description = trim($_POST['description'] ?? '');
$semester_applicability = intval($_POST['semester_applicability'] ?? 0);

if(!$title || !$date) die("Missing fields.");

$stmt = $conn->prepare("INSERT INTO events (title, description, date, semester_applicability) VALUES (?,?,?,?)");
$stmt->bind_param("sssi", $title, $description, $date, $semester_applicability);
if($stmt->execute()){
    header("Location: " . APP_BASE . "/frontend/faculty/dashboard.php");
    exit;
}else die("Insert failed.");
