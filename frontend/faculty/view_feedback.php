<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
$faculty_id = $_SESSION['user_id'];
// Fetch subjects
$subjects = $conn->query("SELECT id, subject_name FROM subjects WHERE faculty_id=$faculty_id ORDER BY subject_name");

if(isset($_GET['export_csv']) && isset($_GET['subject_id'])) {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="feedback_analytics_subject_' . intval($_GET['subject_id']) . '.csv"');
  $sid = intval($_GET['subject_id']);
  $faculty_id = $_SESSION['user_id'];
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Question', 'Response', 'Count']);
  $qres = $conn->query("SELECT id, question_text FROM questions ORDER BY id");
  while($q = $qres->fetch_assoc()) {
    $qid = $q['id'];
    $qt = $q['question_text'];
    $rres = $conn->query("SELECT response, COUNT(*) as cnt FROM feedback_responses WHERE subject_id=$sid AND faculty_id=$faculty_id AND question_id=$qid GROUP BY response");
    while($r = $rres->fetch_assoc()) {
      fputcsv($out, [$qt, $r['response'], $r['cnt']]);
    }
  }
  fclose($out);
  exit;
}
?>
<div class="row justify-content-center">
  <div class="col-md-10">
    <div class="card card-lean p-4">
      <h5>Feedback Analytics</h5>
      <?php while($sub = $subjects->fetch_assoc()): ?>
        <h6 class="mt-3 mb-2">Subject: <?= htmlspecialchars($sub['subject_name']) ?>
          <a href="?export_csv=1&subject_id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline-secondary ms-2">Export CSV</a>
        </h6>
        <?php
        $sid = $sub['id'];
        // Fetch questions
        $qres = $conn->query("SELECT id, question_text, question_type FROM questions ORDER BY id");
        while($q = $qres->fetch_assoc()):
          $qid = $q['id'];
          $qtype = $q['question_type'];
          echo '<div class="mb-2"><strong>' . htmlspecialchars($q['question_text']) . '</strong><br>';
          if(in_array($qtype, ['yesno','mcq','rating'])) {
            $rres = $conn->query("SELECT response, COUNT(*) as cnt FROM feedback_responses WHERE subject_id=$sid AND faculty_id=$faculty_id AND question_id=$qid GROUP BY response ORDER BY cnt DESC");
            $labels = [];
            $data = [];
            while($r = $rres->fetch_assoc()) {
              $labels[] = $r['response'];
              $data[] = $r['cnt'];
            }
            $chartId = 'chart_' . $sid . '_' . $qid;
            echo '<canvas id="' . $chartId . '" height="80"></canvas>';
            echo "<script>new Chart(document.getElementById('$chartId').getContext('2d'), {type: 'bar', data: {labels: " . json_encode($labels) . ", datasets: [{label: 'Responses', data: " . json_encode($data) . ", backgroundColor: 'rgba(54, 162, 235, 0.6)'}]}, options: {plugins: {legend: {display: false}}}});</script>";
            echo '<ul class="list-inline">';
            foreach($labels as $i => $lbl) {
              echo '<li class="list-inline-item">' . htmlspecialchars($lbl) . ': <span class="badge bg-primary">' . $data[$i] . '</span></li>';
            }
            echo '</ul>';
          } else {
            $rres = $conn->query("SELECT response FROM feedback_responses WHERE subject_id=$sid AND faculty_id=$faculty_id AND question_id=$qid ORDER BY created_at DESC LIMIT 10");
            echo '<ul>';
            while($r = $rres->fetch_assoc()) {
              echo '<li>' . htmlspecialchars($r['response']) . '</li>';
            }
            echo '</ul>';
          }
          echo '</div>';
        endwhile;
        ?>
        <hr>
      <?php endwhile; ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
