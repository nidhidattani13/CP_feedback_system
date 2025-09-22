<?php
session_start();
include("includes/header.php");
?>
<div class="container py-5">
  <div class="row mb-4">
    <div class="col text-center">
      <h1 class="display-4 fw-bold text-primary mb-2">Feedback System</h1>
      <p class="lead text-muted">A modern platform for academic feedback, assessment, and event management. Empowering students, faculty, and HODs to collaborate and improve the learning experience.</p>
    </div>
  </div>
  <div class="row justify-content-center g-4">
    <div class="col-md-4">
      <div class="card shadow h-100">
        <div class="card-body text-center">
          <h5 class="card-title">Student Portal</h5>
          <p class="card-text">Submit feedback, view history, verify assessments and events, and manage your profile.</p>
          <a href="auth/login.php" class="btn btn-primary w-100">Student Login</a>
          <a href="auth/register.php" class="btn btn-link w-100">Register</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow h-100">
        <div class="card-body text-center">
          <h5 class="card-title">Faculty Portal</h5>
          <p class="card-text">Add subjects, view feedback, manage assessments, and events for your classes.</p><br>
          <a href="auth/login.php?role=faculty" class="btn btn-success w-100">Faculty Login</a>
          <a href="auth/register.php" class="btn btn-link w-100">Register</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow h-100">
        <div class="card-body text-center">
          <h5 class="card-title">HOD Portal</h5>
          <p class="card-text">View analytics, manage reports, notices, students, and faculty lists.</p><br>
          <a href="auth/login.php?role=hod" class="btn btn-danger w-100">HOD Login</a>
          <a href="auth/register.php" class="btn btn-link w-100">Register</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include("includes/footer.php"); ?>
