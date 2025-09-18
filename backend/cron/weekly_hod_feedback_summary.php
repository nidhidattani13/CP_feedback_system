<?php
// backend/cron/weekly_hod_feedback_summary.php
include_once("../../config.php");
$start = date('Y-m-d', strtotime('-7 days'));
$end = date('Y-m-d');
// Get all HODs
$hods = $conn->query("SELECT email, name FROM users WHERE role='hod'");
if ($hods->num_rows == 0) exit;
// Build summary
$summary = "Weekly Feedback Summary (" . $start . " to " . $end . ")\n\n";
$subjects = $conn->query("SELECT s.id, s.subject_name, u.name as faculty_name FROM subjects s JOIN users u ON s.faculty_id = u.id ORDER BY s.subject_name");
while($sub = $subjects->fetch_assoc()) {
  $sid = $sub['id'];
  $summary .= "Subject: {$sub['subject_name']} (Faculty: {$sub['faculty_name']})\n";
  // Count feedbacks
  $count = $conn->query("SELECT COUNT(DISTINCT student_id, DATE(created_at)) as cnt FROM feedback_responses WHERE subject_id=$sid AND created_at BETWEEN '$start' AND '$end'")->fetch_assoc()['cnt'];
  $summary .= "  Feedbacks submitted: $count\n";
  // Per-question breakdown
  $qres = $conn->query("SELECT id, question_text FROM questions ORDER BY id");
  while($q = $qres->fetch_assoc()) {
    $qid = $q['id'];
    $qt = $q['question_text'];
    $summary .= "    $qt\n";
    $rres = $conn->query("SELECT response, COUNT(*) as cnt FROM feedback_responses WHERE subject_id=$sid AND question_id=$qid AND created_at BETWEEN '$start' AND '$end' GROUP BY response");
    while($r = $rres->fetch_assoc()) {
      $summary .= "      - {$r['response']}: {$r['cnt']}\n";
    }
  }
  $summary .= "\n";
}
// Email to all HODs
while($hod = $hods->fetch_assoc()) {
  mail($hod['email'], "Weekly Student Feedback Summary", $summary, "From: noreply@college.edu");
}
echo "Weekly summary sent to HODs.";
?>
