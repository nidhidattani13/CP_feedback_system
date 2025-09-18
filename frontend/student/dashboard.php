<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
$user_id = $_SESSION['user_id'];
$now = intval(date('H'));
// Fetch selected subjects with faculty
$q = "SELECT s.id as subject_id, s.subject_name, s.semester, u.name as faculty_name, u.id as faculty_id
      FROM student_subjects ss
      JOIN subjects s ON ss.subject_id = s.id
      JOIN users u ON s.faculty_id = u.id
      WHERE ss.student_id = $user_id
      ORDER BY s.semester, s.subject_name";
$subjects = $conn->query($q);
// Fetch today's feedbacks
$today = date('Y-m-d');
$done = [];
$res = $conn->query("SELECT subject_id FROM feedback_responses WHERE student_id=$user_id AND DATE(created_at)='$today'");
while($row = $res->fetch_assoc()) $done[] = $row['subject_id'];

// Calculate pending feedback count
$pending = 0;
if ($now >= 17 && $subjects) {
  $subjects->data_seek(0); // reset pointer
  while($sub = $subjects->fetch_assoc()) {
    if (!in_array($sub['subject_id'], $done)) $pending++;
  }
  $subjects->data_seek(0); // reset again for main loop
}
?>
<div class="row">
  <div class="col-md-4">
    <div class="card p-3 card-lean">
      <h5>Welcome, <?=htmlspecialchars($_SESSION['name'] ?? 'Student')?> </h5>
      <p class="small text-muted">Student dashboard</p>
      <div class="mb-2">
        <strong>Enrollment No:</strong> <?= htmlspecialchars($_SESSION['enrollment_no'] ?? '') ?><br>
        <?php
$semRes = $conn->query("SELECT semester FROM users WHERE id=$user_id");
$semRow = $semRes ? $semRes->fetch_assoc() : null;
?>
<strong>Semester:</strong> <?= htmlspecialchars($semRow['semester'] ?? 'N/A') ?><br>
        <strong>CGPA:</strong> <?= htmlspecialchars($_SESSION['cgpa'] ?? 'N/A') ?><br>
        <strong>Category:</strong> <?= htmlspecialchars($_SESSION['category'] ?? 'N/A') ?>
      </div>
      <div class="list-group">
        <a class="list-group-item" href="profile.php">My Profile</a>
        <a class="list-group-item" href="history.php">My Feedback History</a>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <?php if(isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        Feedback submitted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if($now >= 17 && $pending > 0): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Reminder:</strong> You have <?= $pending ?> pending feedback<?= $pending > 1 ? 's' : '' ?> to submit today!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <div class="card p-3 card-lean mb-3">
      <h5>Daily Feedback (after 5pm)</h5>
      <?php if($now < 17): ?>
        <div class="alert alert-info">Feedback will be available after 5pm.</div>
      <?php else: ?>
        <?php if($subjects->num_rows == 0): ?>
          <div class="alert alert-warning">No subjects selected for your semester. Please update your profile.</div>
        <?php else: ?>
          <ul class="list-group">
          <?php while($sub = $subjects->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong><?= htmlspecialchars($sub['subject_name']) ?></strong>
                <span class="text-muted small ms-2">Faculty: <?= htmlspecialchars($sub['faculty_name']) ?></span>
              </div>
              <?php if(in_array($sub['subject_id'], $done)): ?>
                <span class="badge bg-success">Already submitted</span>
              <?php else: ?>
                <a href="feedback_form.php?subject_id=<?= $sub['subject_id'] ?>&faculty_id=<?= $sub['faculty_id'] ?>" class="btn btn-sm btn-primary">Fill Feedback</a>
              <?php endif; ?>
            </li>
          <?php endwhile; ?>
          </ul>
        <?php endif; ?>
      <?php endif; ?>
    </div>
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
