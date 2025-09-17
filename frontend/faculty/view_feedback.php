<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

$fid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT f.*, u.name AS student_name FROM feedback f JOIN users u ON u.id = f.student_id WHERE f.faculty_id = ? ORDER BY f.created_at DESC");
$stmt->bind_param("i",$fid);
$stmt->execute(); $res = $stmt->get_result();
?>
<div class="card card-lean p-3">
  <h5>Feedback Received</h5>
  <?php while($r = $res->fetch_assoc()): ?>
    <div class="mb-2 border rounded p-2">
      <strong><?=htmlspecialchars($r['subject'])?></strong> — <small class="text-muted"><?=htmlspecialchars($r['student_name'])?> • <?= $r['created_at'] ?></small>
      <div>Rating: <?=intval($r['rating'])?> / 5</div>
      <div><?=nl2br(htmlspecialchars($r['comments']))?></div>
    </div>
  <?php endwhile; ?>
</div>
<?php include("../includes/footer.php"); ?>
