<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT f.*, u.name AS faculty_name FROM feedback f LEFT JOIN users u ON u.id = f.faculty_id WHERE f.student_id = ? ORDER BY f.created_at DESC");
$stmt->bind_param("i",$uid);
$stmt->execute();
$res = $stmt->get_result();
?>
<div class="card card-lean p-3">
  <h5>My Feedback History</h5>
  <?php while($row = $res->fetch_assoc()): ?>
    <div class="border rounded p-2 mb-2">
      <strong><?=htmlspecialchars($row['subject'])?></strong>
      <div class="small text-muted">Faculty: <?=htmlspecialchars($row['faculty_name'] ?? 'N/A')?> • <?= $row['created_at'] ?></div>
      <div>Rating: <?= intval($row['rating']) ?> / 5</div>
      <div><?= nl2br(htmlspecialchars($row['comments'])) ?></div>
    </div>
  <?php endwhile; ?>
</div>
<?php include("../includes/footer.php"); ?>
