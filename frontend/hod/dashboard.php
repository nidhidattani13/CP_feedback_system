<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
?>
<div class="row">
  <div class="col-md-4">
    <div class="card p-3 card-lean">
      <h5>HOD Panel</h5>
      <div class="list-group">
        <a class="list-group-item" href="reports.php">View Reports</a>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card p-3 card-lean mb-3">
      <h5>Post a Notice</h5>
      <form method="POST" action="<?= APP_BASE ?>/backend/hod/add_notice_process.php">
        <div class="mb-2">
          <label class="form-label">Title</label>
          <input name="title" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Body</label>
          <textarea name="body" class="form-control" rows="3"></textarea>
        </div>
        <button class="btn btn-primary">Publish</button>
      </form>
    </div>
    <?php
    include("../../config.php");
    $notices = $conn->query("SELECT n.*, u.name AS author FROM notices n JOIN users u ON u.id=n.created_by ORDER BY n.created_at DESC LIMIT 10");
    ?>
    <div class="card p-3 card-lean">
      <h5>Recent Notices</h5>
      <?php while($n = $notices->fetch_assoc()): ?>
        <div class="border rounded p-2 mb-2">
          <strong><?= htmlspecialchars($n['title']) ?></strong>
          <div class="small text-muted">By <?= htmlspecialchars($n['author']) ?> • <?= $n['created_at'] ?></div>
          <?php if(!empty($n['body'])): ?><div><?= nl2br(htmlspecialchars($n['body'])) ?></div><?php endif; ?>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
