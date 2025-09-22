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
if ($now >= 13 && $subjects) {
  $subjects->data_seek(0); // reset pointer
  while($sub = $subjects->fetch_assoc()) {
    if (!in_array($sub['subject_id'], $done)) $pending++;
  }
  $subjects->data_seek(0); // reset again for main loop
}

// Fetch student semester
$userRes = $conn->query("SELECT semester, cgpa, category FROM users WHERE id=$user_id");
$userRow = $userRes ? $userRes->fetch_assoc() : null;
$student_semester = intval($userRow['semester'] ?? 0);
// Fetch relevant events
$events = $conn->query("SELECT * FROM events WHERE semester_applicability=0 OR semester_applicability=$student_semester ORDER BY date DESC");
// Fetch relevant assessment plans
$subject_ids = [];
if ($subjects && $subjects->num_rows > 0) {
  $subjects->data_seek(0);
  while($sub = $subjects->fetch_assoc()) {
    $subject_ids[] = intval($sub['subject_id']);
  }
  $subjects->data_seek(0);
}
$subject_ids_str = implode(',', $subject_ids);
$assessments = null;
if (!empty($subject_ids_str)) {
  $assessments = $conn->query(
    "SELECT a.*, s.subject_name 
     FROM assessments a 
     JOIN subjects s ON a.subject_id = s.id 
     WHERE a.subject_id IN ($subject_ids_str) 
       AND (a.semester_applicability = 0 OR a.semester_applicability = $student_semester)
     ORDER BY a.week_no"
  );
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
        $userRes = $conn->query("SELECT semester, cgpa, category FROM users WHERE id=$user_id");
        $userRow = $userRes ? $userRes->fetch_assoc() : null;
        ?>
        <strong>Semester:</strong> <?= htmlspecialchars($userRow['semester'] ?? 'N/A') ?><br>
        <strong>CGPA:</strong> <?= htmlspecialchars($userRow['cgpa'] ?? 'N/A') ?><br>
        <strong>Category:</strong> <?= htmlspecialchars($userRow['category'] ?? 'N/A') ?>
      </div>
      <div class="list-group">
  <a class="list-group-item" href="profile.php">My Profile</a>
  <a class="list-group-item" href="history.php">My Feedback History</a>
  <a class="list-group-item" href="verify_assessment.php">Assessment Check</a>
  <a class="list-group-item" href="verify_event.php">Event Check</a>
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
    <?php if($now >= 13 && $pending > 0): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Reminder:</strong> You have <?= $pending ?> pending feedback<?= $pending > 1 ? 's' : '' ?> to submit today!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <div class="card p-3 card-lean mb-3">
      <h5>Daily Feedback (after 1pm)</h5>
      <?php if($now < 13): ?>
        <div class="alert alert-info">Feedback will be available after 1pm.</div>
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
      </br>
    <div class="card p-3 card-lean mb-3">
      <h5>Upcoming Events</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Title</th>
              <th>Date</th>
              <th>Description</th>
              <th>Semester</th>
            </tr>
          </thead>
          <tbody>
            <?php if($events && $events->num_rows>0): while($e = $events->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($e['title']) ?></td>
              <td><?= htmlspecialchars($e['date']) ?></td>
              <td><?= htmlspecialchars($e['description']) ?></td>
              <td><?= $e['semester_applicability']==0 ? 'All' : htmlspecialchars($e['semester_applicability']) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4" class="text-center text-muted">No events found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card p-3 card-lean mb-3">
      <h5>Assessment Plans</h5>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Subject</th>
              <th>Title</th>
              <th>Week No</th>
            </tr>
          </thead>
          <tbody>
            <?php if($assessments && $assessments->num_rows>0): while($a = $assessments->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($a['subject_name']) ?></td>
              <td><?= htmlspecialchars($a['title']) ?></td>
              <td><?= htmlspecialchars($a['week_no']) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="3" class="text-center text-muted">No assessment plans found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
