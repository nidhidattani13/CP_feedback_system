<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') die("Access denied");

$title = trim($_POST['title'] ?? '');
$date = $_POST['date'] ?? null;
$description = trim($_POST['description'] ?? '');

if(!$title || !$date) die("Missing fields.");

$stmt = $conn->prepare("INSERT INTO events (title, description, date) VALUES (?,?,?)");
$stmt->bind_param("sss", $title, $description, $date);
if($stmt->execute()){
    header("Location: " . APP_BASE . "/frontend/faculty/dashboard.php");
    exit;
}else die("Insert failed.");
