<?php
// backend/cron/email_pending_feedback.php
include_once("../../config.php");
$now = intval(date('H'));
if ($now < 13) exit; // Only run after 5pm
$today = date('Y-m-d');

// Get all semesters and categories
$semesters = range(1,8);
$categories = ['Excellent','Very Good','Good','Average','Below Average'];

foreach($semesters as $sem) {
  foreach($categories as $cat) {
    // Select 10 random students from this semester and category
    $students = $conn->query("SELECT id, name, email FROM users WHERE role='student' AND semester='$sem' AND category='$cat' ORDER BY RAND() LIMIT 10");
    while($stu = $students->fetch_assoc()) {
      $uid = $stu['id'];
      $email = $stu['email'];
      // Here you could insert into a daily assignment table if needed
      // For now, send targeted email
      $msg = "Dear {$stu['name']},\n\nYou have been selected for today's feedback. Please log in and submit feedback for your subjects before midnight.\n\nThank you.";
      mail($email, "Daily Feedback Assignment", $msg, "From: noreply@college.edu");
    }
  }
}
echo "Daily feedback assignments sent.";
