<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
?>
<?php
include("../../config.php");
$faculty_id = $_SESSION['user_id'];
$subjects = $conn->query("SELECT id, subject_name, semester FROM subjects WHERE faculty_id=$faculty_id ORDER BY semester, subject_name");
// Fetch filter values
$filter_sem = isset($_GET['filter_sem']) ? intval($_GET['filter_sem']) : 0;
$filter_sub = isset($_GET['filter_sub']) ? intval($_GET['filter_sub']) : 0;
// Build query for assessments
$assess_query = "SELECT a.*, s.subject_name, s.semester FROM assessments a JOIN subjects s ON a.subject_id = s.id WHERE a.faculty_id=$faculty_id";
if($filter_sem) $assess_query .= " AND a.semester_applicability=$filter_sem";
if($filter_sub) $assess_query .= " AND a.subject_id=$filter_sub";
$assess_query .= " ORDER BY a.semester_applicability, a.subject_id, a.week_no";
$assessments = $conn->query($assess_query);

// Event form and history
$event_filter_sem = isset($_GET['event_filter_sem']) ? intval($_GET['event_filter_sem']) : 0;
$events_query = "SELECT * FROM events WHERE 1";
if($event_filter_sem) $events_query .= " AND (semester_applicability=0 OR semester_applicability=$event_filter_sem)";
$events_query .= " ORDER BY date DESC";
$events = $conn->query($events_query);
?>
<div class="card card-lean p-3">
  <h5>Add Assessment Plan</h5>
  <form action="<?= APP_BASE ?>/backend/faculty/add_assessment_process.php" method="POST">
    <div class="mb-3">
      <label class="form-label">Subject</label>
      <select name="subject_id" id="subjectSelect" class="form-select" required onchange="updateSemester()">
        <option value="">Select Subject</option>
        <?php $subjects->data_seek(0); while($sub = $subjects->fetch_assoc()): ?>
          <option value="<?= $sub['id'] ?>" data-semester="<?= $sub['semester'] ?>">
            <?= htmlspecialchars($sub['subject_name']) ?> (Sem <?= $sub['semester'] ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Semester</label>
      <input name="semester" id="semesterField" class="form-control" readonly required>
    </div>
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Week No</label>
      <input name="week_no" type="number" class="form-control" required>
    </div>
    <button class="btn btn-primary">Add Plan</button>
  </form>
</div>
<script>
function updateSemester() {
  var select = document.getElementById('subjectSelect');
  var sem = select.options[select.selectedIndex].getAttribute('data-semester');
  document.getElementById('semesterField').value = sem || '';
}
</script>
<div class="card card-lean p-3 mt-4">
  <h5>Assessment Plan History</h5>
  <form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-3">
      <label class="form-label">Filter by Semester</label>
      <select name="filter_sem" class="form-select">
        <option value="">All</option>
        <?php for($i=1;$i<=8;$i++): ?>
          <option value="<?= $i ?>" <?= $filter_sem==$i?'selected':'' ?>>Sem <?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-4">
      <label class="form-label">Filter by Subject</label>
      <select name="filter_sub" class="form-select">
        <option value="">All</option>
        <?php $subjects->data_seek(0); while($sub = $subjects->fetch_assoc()): ?>
          <option value="<?= $sub['id'] ?>" <?= $filter_sub==$sub['id']?'selected':'' ?>><?= htmlspecialchars($sub['subject_name']) ?> (Sem <?= $sub['semester'] ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-2">
      <button class="btn btn-secondary w-100" type="submit">Filter</button>
    </div>
  </form>
  <div class="table-responsive">
    <table class="table table-bordered table-sm">
      <thead>
        <tr>
          <th>Subject</th>
          <th>Semester</th>
          <th>Title</th>
          <th>Week No</th>
        </tr>
      </thead>
      <tbody>
        <?php if($assessments && $assessments->num_rows>0): while($a = $assessments->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($a['subject_name']) ?></td>
          <td><?= htmlspecialchars($a['semester']) ?></td>
          <td><?= htmlspecialchars($a['title']) ?></td>
          <td><?= htmlspecialchars($a['week_no']) ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="4" class="text-center text-muted">No assessment plans found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<!-- <div class="card card-lean p-3 mt-4">
  <h5>Publish Event</h5>
  <form action="<?= APP_BASE ?>/backend/faculty/events_process.php" method="POST">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Date</label>
      <input name="date" type="date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Applicable Semester</label>
      <select name="semester_applicability" class="form-select" required>
        <option value="0">All Semesters</option>
        <?php for($i=1;$i<=8;$i++): ?>
          <option value="<?= $i ?>">Semester <?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button class="btn btn-primary">Publish Event</button>
  </form>
</div>
<div class="card card-lean p-3 mt-4">
  <h5>Event History</h5>
  <form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-3">
      <label class="form-label">Filter by Semester</label>
      <select name="event_filter_sem" class="form-select">
        <option value="">All</option>
        <?php for($i=1;$i<=8;$i++): ?>
          <option value="<?= $i ?>" <?= $event_filter_sem==$i?'selected':'' ?>>Sem <?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-2">
      <button class="btn btn-secondary w-100" type="submit">Filter</button>
    </div>
  </form>
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
</div> -->
<?php include("../includes/footer.php"); ?>
