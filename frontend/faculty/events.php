<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
?>
<div class="card card-lean p-3">
  <h5>Publish Event</h5>
  <form action="<?= APP_BASE ?>/backend/faculty/events_process.php" method="POST">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Date</label>
      <input name="date" type="date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <button class="btn btn-primary">Publish Event</button>
  </form>
</div>
<?php include("../includes/footer.php"); ?>
