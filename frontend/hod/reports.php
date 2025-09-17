<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

// total feedbacks
$totalFeedback = $conn->query("SELECT COUNT(*) AS cnt FROM feedback")->fetch_assoc()['cnt'] ?? 0;
// average rating per faculty
$facultyRatings = $conn->query("SELECT u.id, u.name, AVG(f.rating) AS avg_rating, COUNT(f.id) AS cnt
  FROM users u LEFT JOIN feedback f ON u.id=f.faculty_id
  WHERE u.role='faculty'
  GROUP BY u.id ORDER BY avg_rating DESC");

// assessments planned vs verified
$assessCounts = $conn->query("SELECT status, COUNT(*) AS cnt FROM assessments GROUP BY status");
$assessMap = [];
while($r = $assessCounts->fetch_assoc()) $assessMap[$r['status']] = $r['cnt'];

// events planned vs verified
$evCounts = $conn->query("SELECT status, COUNT(*) AS cnt FROM events GROUP BY status");
$evMap = [];
while($r = $evCounts->fetch_assoc()) $evMap[$r['status']] = $r['cnt'];
?>
<div class="card card-lean p-3">
  <h5>Reports</h5>
  <p>Total feedback entries: <strong><?=intval($totalFeedback)?></strong></p>
  <h6>Average rating per faculty</h6>
  <?php while($f=$facultyRatings->fetch_assoc()): ?>
    <div class="border p-2 mb-2">
      <strong><?=htmlspecialchars($f['name'])?></strong> — Avg: <?=($f['avg_rating']===null? "N/A": round($f['avg_rating'],2))?> (<?=intval($f['cnt'])?> feedbacks)
    </div>
  <?php endwhile; ?>

  <div class="row mt-3">
    <div class="col-md-6">
      <h6>Assessments</h6>
      <p>Planned: <?=intval($assessMap['planned'] ?? 0)?> • Verified: <?=intval($assessMap['verified'] ?? 0)?></p>
    </div>
    <div class="col-md-6">
      <h6>Events</h6>
      <p>Planned: <?=intval($evMap['planned'] ?? 0)?> • Verified: <?=intval($evMap['verified'] ?? 0)?></p>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
