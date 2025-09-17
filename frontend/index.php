<?php
session_start();
include("includes/header.php");
?>
<div class="py-5 text-center">
  <h1 class="display-5 fw-bold">Welcome to the Academic Feedback System</h1>
  <p class="lead text-muted">Please select your portal to continue.</p>
</div>

<div class="row justify-content-center g-3">
  <div class="col-md-3">
    <a href="auth/login.php" class="btn btn-outline-primary w-100 py-3">Student Login</a>
  </div>
  <div class="col-md-3">
    <a href="auth/login.php?role=faculty" class="btn btn-outline-success w-100 py-3">Faculty Login</a>
  </div>
  <div class="col-md-3">
    <a href="auth/login.php?role=hod" class="btn btn-outline-danger w-100 py-3">HOD Login</a>
  </div>
</div>
<?php include("includes/footer.php"); ?>
