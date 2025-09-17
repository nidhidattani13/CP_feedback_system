<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
?>
<div class="card card-lean p-3">
  <h5>Add Assessment Plan</h5>
  <form action="<?= APP_BASE ?>/backend/faculty/add_assessment_process.php" method="POST">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Week No</label>
      <input name="week_no" type="number" class="form-control" required>
    </div>
    <button class="btn btn-primary">Add Plan</button>
  </form>
</div>
<?php include("../includes/footer.php"); ?>
