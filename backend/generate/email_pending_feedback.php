<?php
// backend/cron/email_pending_feedback.php
include_once("../../config.php");
$today = date('Y-m-d');

// Get all students who have at least one pending feedback today
$q = "
  SELECT u.id, u.name, u.email
  FROM users u
  WHERE u.role='student'
    AND EXISTS (
      SELECT 1
      FROM student_subjects ss
      JOIN subjects s ON ss.subject_id = s.id
      LEFT JOIN feedback_responses fr
        ON fr.student_id = u.id
        AND fr.subject_id = ss.subject_id
        AND DATE(fr.created_at) = '$today'
      WHERE ss.student_id = u.id
        AND fr.id IS NULL
    )
";
$res = $conn->query($q);

while($stu = $res->fetch_assoc()) {
  $email = $stu['email'];
  $msg = "Dear {$stu['name']},\n\nYou have pending feedback to submit for today. Please log in and complete your feedback before midnight.\n\nThank you.";
  $sent = mail($email, "Pending Feedback Reminder", $msg, "From: noreply@college.edu");
  if (!$sent) {
    error_log("Failed to send email to $email");
  }
}

echo "Pending feedback reminders sent.";
