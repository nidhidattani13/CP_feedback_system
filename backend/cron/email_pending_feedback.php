<?php
// backend/cron/email_pending_feedback.php
include_once("../../config.php");
$now = intval(date('H'));
if ($now < 17) exit; // Only run after 5pm
$today = date('Y-m-d');
// Get all students
$students = $conn->query("SELECT id, name, email FROM users WHERE role='student'");
while($stu = $students->fetch_assoc()) {
  $uid = $stu['id'];
  $email = $stu['email'];
  // Get subjects mapped to student
  $subs = $conn->query("SELECT subject_id FROM student_subjects WHERE student_id=$uid");
  $subject_ids = [];
  while($row = $subs->fetch_assoc()) $subject_ids[] = $row['subject_id'];
  if (empty($subject_ids)) continue;
  // Get feedbacks submitted today
  $done = [];
  $res = $conn->query("SELECT subject_id FROM feedback_responses WHERE student_id=$uid AND DATE(created_at)='$today'");
  while($row = $res->fetch_assoc()) $done[] = $row['subject_id'];
  $pending = array_diff($subject_ids, $done);
  if (count($pending) > 0) {
    $msg = "Dear {$stu['name']},\n\nYou have pending feedback for today. Please log in and submit feedback for your subjects before midnight.\n\nThank you.";
    mail($email, "Pending Feedback Reminder", $msg, "From: noreply@college.edu");
  }
}
echo "Email reminders sent.";
