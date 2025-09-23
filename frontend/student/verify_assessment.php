<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

// Get student's semester
$user_id = $_SESSION['user_id'];
$userRes = $conn->query("SELECT semester FROM users WHERE id=$user_id");
$userRow = $userRes ? $userRes->fetch_assoc() : null;
$student_semester = intval($userRow['semester'] ?? 0);

// Get subject ids selected by student
$subject_ids = [];
$subRes = $conn->query("SELECT subject_id FROM student_subjects WHERE student_id=$user_id");
if ($subRes && $subRes->num_rows > 0) {
  while($row = $subRes->fetch_assoc()) {
    $subject_ids[] = intval($row['subject_id']);
  }
}
$subject_ids_str = implode(',', $subject_ids);

// Only show assessments for student's semester or for all semesters AND for selected subjects
$res = null;
if (!empty($subject_ids_str)) {
  $res = $conn->query(
    "SELECT a.*, u.name AS faculty_name, s.subject_name
     FROM assessments a
     JOIN users u ON u.id=a.faculty_id
     JOIN subjects s ON s.id=a.subject_id
     WHERE a.status='planned'
       AND (a.semester_applicability=0 OR a.semester_applicability=$student_semester)
       AND a.subject_id IN ($subject_ids_str)
     ORDER BY a.week_no"
  );
}
?>
<div class="card card-lean p-3">
  <h5>Verify Assessments</h5>
  <?php if(!$res || $res->num_rows==0) echo "<p class='text-muted'>No planned assessments.</p>"; ?>
  <?php if($res) while($r = $res->fetch_assoc()): ?>
    <div class="d-flex justify-content-between align-items-start border rounded p-2 mb-2">
      <div>
        <strong><?=htmlspecialchars($r['title'])?></strong><br>
        <span class="badge bg-info text-dark mb-1">Subject: <?=htmlspecialchars($r['subject_name'])?></span><br>
        <small class="text-muted">By <?=htmlspecialchars($r['faculty_name'])?> • Week <?=intval($r['week_no'])?></small>
      </div>
      <form method="POST" action="<?= APP_BASE ?>/backend/student/verify_assessment_process.php">
        <input type="hidden" name="assessment_id" value="<?=intval($r['id'])?>">
        <button class="btn btn-success btn-sm" name="verify" value="1">Confirm Done</button>
      </form>
    </div>
  <?php endwhile; ?>
</div>
<?php include("../includes/footer.php"); ?>
