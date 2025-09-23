<?php
// header.php (include at top of frontend pages)
// Ensure base constant is available everywhere header is included
if (!defined('APP_BASE')) {
  require_once __DIR__ . '/../../config.php';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Academic Feedback System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= APP_BASE ?>/frontend/">Feedback System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php
        if(session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        if(!empty($_SESSION['user_id'])) {
          $role = $_SESSION['role'] ?? 'student';
          if ($role === 'student') {
            // Student dashboard links
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/student/dashboard.php">Dashboard</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/student/profile.php">My Profile</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/student/verify_assessment.php">Assessment Check</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/student/verify_event.php">Event Check</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/student/history.php">My Feedback History</a></li>';
          } elseif ($role === 'faculty') {
            // Faculty dashboard links
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/faculty/dashboard.php">Dashboard</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/faculty/add_assessment.php">Add Assessment</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/faculty/view_feedback.php">View Feedback</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/faculty/subjects.php">Manage Subjects</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/faculty/events.php">Add Events</a></li>';
          } elseif ($role === 'hod') {
            // HOD dashboard links
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/hod/dashboard.php">Dashboard</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/hod/manage_users.php">Manage Users</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/hod/reports.php">View Feedback</a></li>';
            echo '<li class="nav-item"><a class="nav-link text-light" href="'.APP_BASE.'/frontend/hod/selected_students.php">Student List</a></li>';
          }
          echo '<li class="nav-item"><a class="btn btn-outline-light btn-sm ms-2" href="'.APP_BASE.'/backend/auth/logout_process.php">Logout</a></li>';
        } else {
          echo '<li class="nav-item me-2"><a class="btn btn-outline-light btn-sm" href="'.APP_BASE.'/frontend/auth/login.php">Login</a></li>';
          echo '<li class="nav-item"><a class="btn btn-light btn-sm" href="'.APP_BASE.'/frontend/auth/register.php">Register</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">
