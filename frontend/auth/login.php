<?php
session_start();
include("../includes/header.php");
$role = $_GET['role'] ?? 'student';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card card-lean p-4">
      <h4 class="mb-3">Login</h4>
      <form method="POST" action="<?= APP_BASE ?>/backend/auth/login_process.php">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <input type="hidden" name="preferred_role" value="<?= htmlspecialchars($role) ?>">
        <button class="btn btn-primary w-100">Login</button>
      </form>
      <div class="mt-2 text-center">
        <small>New? <a href="register.php">Register here</a></small>
      </div>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
