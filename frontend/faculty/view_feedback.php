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
        <div class="accordion mb-3" id="accordion_<?= $sub['id'] ?>">
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading_<?= $sub['id'] ?>">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $sub['id'] ?>" aria-expanded="false" aria-controls="collapse_<?= $sub['id'] ?>">
                Subject: <?= htmlspecialchars($sub['subject_name']) ?>
              </button>
            </h2>
            <div id="collapse_<?= $sub['id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading_<?= $sub['id'] ?>" data-bs-parent="#accordion_<?= $sub['id'] ?>">
              <div class="accordion-body">
                <a href="?export_csv=1&subject_id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline-secondary mb-2">Export CSV</a>
                <?php
                $sid = $sub['id'];
                // Summary card
                $total_feedbacks = $conn->query("SELECT COUNT(*) as cnt FROM feedback_responses WHERE subject_id=$sid AND faculty_id=$faculty_id")->fetch_assoc()['cnt'];
                $avg_rating = $conn->query("SELECT AVG(CASE WHEN question_id=3 THEN response END) as avg_rating FROM feedback_responses WHERE subject_id=$sid AND faculty_id=$faculty_id")->fetch_assoc()['avg_rating'];
                echo '<div class="row mb-3">';
                echo '<div class="col-md-4"><div class="card bg-light p-2"><strong>Total Feedbacks:</strong> ' . $total_feedbacks . '</div></div>';
                echo '<div class="col-md-4"><div class="card bg-light p-2"><strong>Avg. Delivery Rating:</strong> ' . ($avg_rating ? round($avg_rating,2) : 'N/A') . '</div></div>';
                echo '</div>';
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
                    echo "<script>new Chart(document.getElementById('$chartId').getContext('2d'), {type: 'bar', data: {labels: " . json_encode($labels) . ", datasets: [{label: 'Responses', data: " . json_encode($data) . ", backgroundColor: ['#36a2eb','#4bc0c0','#ffcd56','#ff6384','#9966ff','#c9cbcf']} ]}, options: {plugins: {legend: {display: false}}}});</script>";
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
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
