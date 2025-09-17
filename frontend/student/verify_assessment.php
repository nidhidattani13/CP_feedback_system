<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

$res = $conn->query("SELECT a.*, u.name AS faculty_name FROM assessments a JOIN users u ON u.id=a.faculty_id WHERE a.status='planned' ORDER BY a.week_no");
?>
<div class="card card-lean p-3">
  <h5>Verify Assessments</h5>
  <?php if($res->num_rows==0) echo "<p class='text-muted'>No planned assessments.</p>"; ?>
  <?php while($r = $res->fetch_assoc()): ?>
    <div class="d-flex justify-content-between align-items-start border rounded p-2 mb-2">
      <div>
        <strong><?=htmlspecialchars($r['title'])?></strong><br>
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
