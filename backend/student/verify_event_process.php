<?php
session_start();
include("../../config.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') die("Access denied");

$eid = intval($_POST['event_id'] ?? 0);
if(!$eid) die("Invalid id");

$stmt = $conn->prepare("UPDATE events SET status='verified' WHERE id = ?");
$stmt->bind_param("i",$eid);
if($stmt->execute()){
    header("Location: " . APP_BASE . "/frontend/student/verify_event.php");
    exit;
}else die("Update failed.");
