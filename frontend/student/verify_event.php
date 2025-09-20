<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

$user_id = $_SESSION['user_id'];
$userRes = $conn->query("SELECT semester FROM users WHERE id=$user_id");
$userRow = $userRes ? $userRes->fetch_assoc() : null;
$student_semester = intval($userRow['semester'] ?? 0);
$res = $conn->query("SELECT * FROM events WHERE status='planned' AND (semester_applicability=0 OR semester_applicability=$student_semester) ORDER BY date DESC");
?>
<div class="card card-lean p-3">
  <h5>Verify Events</h5>
  <?php while($r = $res->fetch_assoc()): ?>
    <div class="d-flex justify-content-between align-items-start border rounded p-2 mb-2">
      <div>
        <strong><?=htmlspecialchars($r['title'])?></strong><br>
        <small class="text-muted"><?=htmlspecialchars($r['date'])?></small>
        <div><?=nl2br(htmlspecialchars($r['description']))?></div>
      </div>
      <form method="POST" action="<?= APP_BASE ?>/backend/student/verify_event_process.php">
        <input type="hidden" name="event_id" value="<?=intval($r['id'])?>">
        <button class="btn btn-success btn-sm">Confirm Done</button>
      </form>
    </div>
  <?php endwhile; ?>
</div>
<?php include("../includes/footer.php"); ?>
