<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

// Fetch filter values
$filter_semester = isset($_GET['filter_semester']) ? intval($_GET['filter_semester']) : 0;
$filter_faculty = isset($_GET['filter_faculty']) ? intval($_GET['filter_faculty']) : 0;
$filter_subject = isset($_GET['filter_subject']) ? intval($_GET['filter_subject']) : 0;

// Fetch faculty list for filter
$facultyList = $conn->query("SELECT id, name FROM users WHERE role='faculty' ORDER BY name");

// Build subjects query with filters
$subjects_query = "SELECT s.id, s.subject_name, s.semester, u.id as faculty_id, u.name as faculty_name FROM subjects s JOIN users u ON s.faculty_id = u.id WHERE 1";
if ($filter_semester) $subjects_query .= " AND s.semester = $filter_semester";
if ($filter_faculty) $subjects_query .= " AND u.id = $filter_faculty";
if ($filter_subject) $subjects_query .= " AND s.id = $filter_subject";
$subjects_query .= " ORDER BY s.subject_name";
$subjects = $conn->query($subjects_query);

// Fetch subject list for filter
$subjectList = $conn->query("SELECT id, subject_name FROM subjects ORDER BY subject_name");
?>
<style>
.kpi-card { background: linear-gradient(135deg,#f8fafc,#eef2ff); border: 1px solid #e5e7eb; }
.badge-tier { font-size: 12px; }
.progress { height: 10px; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="row justify-content-center">
  <div class="col-md-11">
    <div class="card card-lean p-4">
      <h5>Feedback Analytics (Averages & Live Summary)</h5>

      <!-- Filter Form -->
      <form method="get" class="row g-2 mb-3 align-items-end">
        <div class="col-3">
          <label class="form-label">Filter by Semester</label>
          <select name="filter_semester" class="form-select">
            <option value="">All</option>
            <?php for($i=1;$i<=8;$i++): ?>
              <option value="<?= $i ?>" <?= $filter_semester==$i?'selected':'' ?>>Sem <?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-3">
          <label class="form-label">Filter by Faculty</label>
          <select name="filter_faculty" class="form-select">
            <option value="">All</option>
            <?php if($facultyList) while($f = $facultyList->fetch_assoc()): ?>
              <option value="<?= $f['id'] ?>" <?= $filter_faculty==$f['id']?'selected':'' ?>><?= htmlspecialchars($f['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-3">
          <label class="form-label">Filter by Subject</label>
          <select name="filter_subject" class="form-select">
            <option value="">All</option>
            <?php if($subjectList) while($s = $subjectList->fetch_assoc()): ?>
              <option value="<?= $s['id'] ?>" <?= $filter_subject==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['subject_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-2">
          <button class="btn btn-secondary w-100" type="submit">Filter</button>
        </div>
      </form>
      <!-- End Filter Form -->

      <div class="accordion" id="hodReportsAccordion">
        <?php while($sub = $subjects->fetch_assoc()): ?>
          <?php
          $sid = intval($sub['id']);
          // Overall badge based on weighted averages across questions (simple heuristic)
          $totalScore = 0; $maxScore = 0;
          // Delivery effectiveness (question_id = 3)
          $avgQual = 0; // No content quality question, so set to 0
          $qEff = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id=3 GROUP BY response");
          $map5 = ['Excellent'=>5,'Very Good'=>4,'Good'=>3,'Bad'=>1,'Very Bad'=>1];
          $effScore = 0; $effCount = 0;
          while($r = $qEff->fetch_assoc()){ $effScore += ($map5[$r['response']] ?? 0) * intval($r['cnt']); $effCount += intval($r['cnt']); }
          if($effCount>0){ $avgEff = $effScore/$effCount; $totalScore += $avgEff; $maxScore += 5; } else { $avgEff = 0; }
          // Punctuality (question_id = 2)
          $qTime = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id=2 GROUP BY response");
          // Update this map to match your actual response values
          $timeMap = [
            'On time' => 100,
            '5 min late' => 75,
            '10 min late' => 50,
            '15 min late' => 25,
            'No' => 0
          ];
          $timeScore=0; $timeCount=0;
          while($r = $qTime->fetch_assoc()){ $timeScore += ($timeMap[$r['response']] ?? 0) * intval($r['cnt']); $timeCount += intval($r['cnt']); }
          $punctuality = $timeCount>0 ? round($timeScore/$timeCount) : 0;
          // Presence yes-rate (question_id = 1)
          $qPres = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id=1 GROUP BY response");
          $yes=0;$total=0; while($r=$qPres->fetch_assoc()){ $total+=intval($r['cnt']); if(strtolower($r['response'])==='yes') $yes+=intval($r['cnt']); }
          $presence = $total>0 ? round(($yes/$total)*100) : 0;
          // Content quality (question_id = 4)
          $qQual = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id=4 GROUP BY response");
          $qualMap = [
            'Yes' => 5,
            'No' => 1
          ];
          $qualScore = 0; $qualCount = 0;
          while($r = $qQual->fetch_assoc()){
            $score = $qualMap[$r['response']] ?? 0;
            $qualScore += $score * intval($r['cnt']);
            if($score > 0) $qualCount += intval($r['cnt']);
          }
          $avgQual = $qualCount>0 ? $qualScore/$qualCount : 0;
          if($qualCount>0){ $totalScore += $avgQual; $maxScore += 5; }
          // Determine badge
          $overallPct = $maxScore>0 ? round(($totalScore/$maxScore)*100) : 0;
          $tier = $overallPct>=85?'🏆 Gold':($overallPct>=70?'🥈 Silver':($overallPct>=55?'🥉 Bronze':'🔧 Improve'));
          ?>
          <div class="accordion-item mb-2">
            <h2 class="accordion-header" id="heading<?= $sid ?>">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $sid ?>" aria-expanded="false" aria-controls="collapse<?= $sid ?>">
                Subject: <?= htmlspecialchars($sub['subject_name']) ?> <span class="text-muted small">(Faculty: <?= htmlspecialchars($sub['faculty_name']) ?>)</span>
              </button>
            </h2>
            <div id="collapse<?= $sid ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $sid ?>" data-bs-parent="#hodReportsAccordion">
              <div class="accordion-body">
                <div class="card kpi-card p-3 mb-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0">Subject: <?= htmlspecialchars($sub['subject_name']) ?> <span class="text-muted small">(Faculty: <?= htmlspecialchars($sub['faculty_name']) ?>)</span></h6>
                    <span class="badge bg-primary badge-tier">Overall: <?= $overallPct ?>% <?= $tier ?></span>
                  </div>
                  <div class="mt-2">
                    <div class="mb-2">Presence: <?= $total > 0 ? $presence . '%' : 'N/A' ?>
                      <div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: <?= $total > 0 ? $presence : 0 ?>%"></div></div>
                    </div>
                    <div class="mb-2">Punctuality: <?= $timeCount > 0 ? $punctuality . '%' : 'N/A' ?>
                      <div class="progress"><div class="progress-bar bg-info" role="progressbar" style="width: <?= $timeCount > 0 ? $punctuality : 0 ?>%"></div></div>
                    </div>
                    <div class="mb-2">Content Quality Avg: <?= $qualCount > 0 ? number_format($avgQual,2) . '/5' : 'N/A' ?>
                      <div class="progress"><div class="progress-bar bg-warning" role="progressbar" style="width: <?= $qualCount > 0 ? intval(($avgQual/5)*100) : 0 ?>%"></div></div>
                    </div>
                    <div class="mb-2">Delivery Effectiveness Avg: <?= $effCount > 0 ? number_format($avgEff,2) . '/5' : 'N/A' ?>
                      <div class="progress"><div class="progress-bar bg-danger" role="progressbar" style="width: <?= $effCount > 0 ? intval(($avgEff/5)*100) : 0 ?>%"></div></div>
                    </div>
                  </div>
                </div>
                <?php
                // Detailed charts per question
                $qres = $conn->query("SELECT id, question_text, question_type FROM questions ORDER BY id");
                while($q = $qres->fetch_assoc()):
                  $qid = intval($q['id']);
                  $qtype = $q['question_type'];
                  $qtext = $q['question_text'];
                  echo '<div class="mb-3"><strong>' . htmlspecialchars($qtext) . '</strong><br>';
                  if(in_array($qtype, ['yesno','mcq','rating'])) {
                    $rres = $conn->query("SELECT response, COUNT(*) as cnt FROM feedback_responses WHERE subject_id=$sid AND question_id=$qid GROUP BY response ORDER BY cnt DESC");
                    $labels = []; $data = [];
                    while($r = $rres->fetch_assoc()) { $labels[] = $r['response']; $data[] = intval($r['cnt']); }
                    $chartId = 'chart_' . $sid . '_' . $qid;
                    echo '<canvas id="' . $chartId . '" height="80"></canvas>';
                    echo "<script>new Chart(document.getElementById('$chartId').getContext('2d'), {type: 'bar', data: {labels: " . json_encode($labels) . ", datasets: [{label: 'Responses', data: " . json_encode($data) . ", backgroundColor: 'rgba(99,102,241,0.6)'}]}, options: {plugins: {legend: {display: false}}}});</script>";
                  } else {
                    $rres = $conn->query("SELECT response FROM feedback_responses WHERE subject_id=$sid AND question_id=$qid ORDER BY created_at DESC LIMIT 10");
                    echo '<ul class="mb-0">';
                    while($r = $rres->fetch_assoc()) { echo '<li>' . htmlspecialchars($r['response']) . '</li>'; }
                    echo '</ul>';
                  }
                  echo '</div><hr/>';
                endwhile;
                ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
