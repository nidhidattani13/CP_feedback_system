<?php
session_start();
require_once __DIR__ . '/../../config.php';

if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}

$user_id = intval($_SESSION['user_id']);
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($event_id > 0) {
  // Prevent duplicate verification
  $check = $conn->query("SELECT id FROM event_verifications WHERE event_id=$event_id AND student_id=$user_id");
  if ($check && $check->num_rows == 0) {
    $conn->query("INSERT INTO event_verifications (event_id, student_id) VALUES ($event_id, $user_id)");
  }
}

header("Location: " . APP_BASE . "/frontend/student/verify_event.php?success=1");
exit;
