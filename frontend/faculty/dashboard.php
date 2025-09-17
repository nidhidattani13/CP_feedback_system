<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
?>
<div class="row">
  <div class="col-md-4">
    <div class="card card-lean p-3">
      <h5>Welcome, <?=htmlspecialchars($_SESSION['name'] ?? 'Faculty')?> </h5>
      <div class="list-group">
        <a class="list-group-item" href="add_assessment.php">Add Assessment Plan</a>
        <a class="list-group-item" href="view_feedback.php">View Feedback</a>
        <a class="list-group-item" href="events.php">Publish Events</a>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <?php include("../../config.php"); $nres = $conn->query("SELECT title, body, created_at FROM notices ORDER BY created_at DESC LIMIT 5"); ?>
    <div class="card p-3 card-lean">
      <h5>Recent Notices</h5>
      <?php if($nres && $nres->num_rows>0): while($n=$nres->fetch_assoc()): ?>
        <div class="border rounded p-2 mb-2">
          <strong><?= htmlspecialchars($n['title']) ?></strong>
          <div class="small text-muted"><?= $n['created_at'] ?></div>
          <?php if(!empty($n['body'])): ?><div><?= nl2br(htmlspecialchars($n['body'])) ?></div><?php endif; ?>
        </div>
      <?php endwhile; else: ?>
        <p class="text-muted">No notices yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
