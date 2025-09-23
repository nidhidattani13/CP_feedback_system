<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

$event_filter_sem = isset($_GET['event_filter_sem']) ? intval($_GET['event_filter_sem']) : '';
if ($event_filter_sem && $event_filter_sem > 0) {
  $events = $conn->query("SELECT * FROM events WHERE semester_applicability=$event_filter_sem ORDER BY date DESC");
} else {
  $events = $conn->query("SELECT * FROM events ORDER BY date DESC");
}
?>
<div class="card card-lean p-3 mt-4">
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
          <th>Verified By</th>
        </tr>
      </thead>
      <tbody>
        <?php if($events && $events->num_rows>0): while($e = $events->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($e['title']) ?></td>
          <td><?= htmlspecialchars($e['date']) ?></td>
          <td><?= htmlspecialchars($e['description']) ?></td>
          <td><?= $e['semester_applicability']==0 ? 'All' : htmlspecialchars($e['semester_applicability']) ?></td>
          <td>
            <span id="evcount-<?= $e['id'] ?>">Loading...</span> student(s)
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" class="text-center text-muted">No events found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include("../includes/footer.php"); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  fetch('<?= APP_BASE ?>/backend/faculty/event_verification_stats.php')
    .then(response => response.json())
    .then(data => {
      for (const [eventId, count] of Object.entries(data)) {
        const el = document.getElementById('evcount-' + eventId);
        if (el) el.textContent = count;
      }
      // Set 0 for events not in data
      document.querySelectorAll('[id^="evcount-"]').forEach(function(el) {
        if (el.textContent === 'Loading...') el.textContent = '0';
      });
    });
});
</script>
