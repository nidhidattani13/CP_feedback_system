<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
// Fetch all subjects with faculty
$subjects = $conn->query("SELECT s.id, s.subject_name, u.name as faculty_name FROM subjects s JOIN users u ON s.faculty_id = u.id ORDER BY s.subject_name");
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
      <?php while($sub = $subjects->fetch_assoc()): ?>
        <?php $sid = intval($sub['id']); ?>
        <div class="card kpi-card p-3 mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0">Subject: <?= htmlspecialchars($sub['subject_name']) ?> <span class="text-muted small">(Faculty: <?= htmlspecialchars($sub['faculty_name']) ?>)</span></h6>
            <?php
            // Overall badge based on weighted averages across questions (simple heuristic)
            $totalScore = 0; $maxScore = 0;
            // Delivery effectiveness (question containing 'effectiveness') map 5..1
            $qEff = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id IN (SELECT id FROM questions WHERE question_text LIKE '%effectiveness%') GROUP BY response");
            $map5 = ['Excellent'=>5,'Very Good'=>4,'Good'=>3,'Bad'=>1,'Very Bad'=>1];
            $effScore = 0; $effCount = 0;
            while($r = $qEff->fetch_assoc()){ $effScore += ($map5[$r['response']] ?? 0) * intval($r['cnt']); $effCount += intval($r['cnt']); }
            if($effCount>0){ $avgEff = $effScore/$effCount; $totalScore += $avgEff; $maxScore += 5; } else { $avgEff = 0; }
            // Content quality (question containing 'content quality')
            $qQual = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id IN (SELECT id FROM questions WHERE question_text LIKE '%content quality%') GROUP BY response");
            $qualScore = 0; $qualCount = 0; $mapQual = ['Excellent'=>5,'Very Good'=>4,'Good'=>3,'Bad'=>1,'Very Bad'=>1,'Good'=>3];
            while($r = $qQual->fetch_assoc()){ $qualScore += ($mapQual[$r['response']] ?? 0) * intval($r['cnt']); $qualCount += intval($r['cnt']); }
            if($qualCount>0){ $avgQual = $qualScore/$qualCount; $totalScore += $avgQual; $maxScore += 5; } else { $avgQual = 0; }
            // Punctuality (question containing 'on time') lower is worse. Map: On time 100, 5 min late 75, 10 min late 50, 15+ min late 25
            $qTime = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id IN (SELECT id FROM questions WHERE question_text LIKE '%on time%') GROUP BY response");
            $timeMap = ['On time'=>100,'5 min late'=>75,'10 min late'=>50,'15+ min late'=>25];
            $timeScore=0; $timeCount=0;
            while($r = $qTime->fetch_assoc()){ $timeScore += ($timeMap[$r['response']] ?? 0) * intval($r['cnt']); $timeCount += intval($r['cnt']); }
            $punctuality = $timeCount>0 ? round($timeScore/$timeCount) : 0;
            // Presence yes-rate (question containing 'present')
            $qPres = $conn->query("SELECT response, COUNT(*) cnt FROM feedback_responses WHERE subject_id=$sid AND question_id IN (SELECT id FROM questions WHERE question_text LIKE '%present%') GROUP BY response");
            $yes=0;$total=0; while($r=$qPres->fetch_assoc()){ $total+=intval($r['cnt']); if(strtolower($r['response'])==='yes') $yes+=intval($r['cnt']); }
            $presence = $total>0 ? round(($yes/$total)*100) : 0;
            // Determine badge
            $overallPct = $maxScore>0 ? round(($totalScore/$maxScore)*100) : 0;
            $tier = $overallPct>=85?'🏆 Gold':($overallPct>=70?'🥈 Silver':($overallPct>=55?'🥉 Bronze':'🔧 Improve'));
            ?>
            <span class="badge bg-primary badge-tier">Overall: <?= $overallPct ?>% <?= $tier ?></span>
          </div>
          <div class="mt-2">
            <div class="mb-2">Presence: <?= $presence ?>%
              <div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: <?= $presence ?>%"></div></div>
            </div>
            <div class="mb-2">Punctuality: <?= $punctuality ?>%
              <div class="progress"><div class="progress-bar bg-info" role="progressbar" style="width: <?= $punctuality ?>%"></div></div>
            </div>
            <div class="mb-2">Content Quality Avg: <?= number_format($avgQual,2) ?>/5
              <div class="progress"><div class="progress-bar bg-warning" role="progressbar" style="width: <?= intval(($avgQual/5)*100) ?>%"></div></div>
            </div>
            <div class="mb-2">Delivery Effectiveness Avg: <?= number_format($avgEff,2) ?>/5
              <div class="progress"><div class="progress-bar bg-danger" role="progressbar" style="width: <?= intval(($avgEff/5)*100) ?>%"></div></div>
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
      <?php endwhile; ?>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
