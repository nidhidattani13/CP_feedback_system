<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

$today = date('Y-m-d');
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

// Get distinct semesters for filter
$semRes = $conn->query("SELECT DISTINCT semester FROM users WHERE role='student' ORDER BY semester");
$semesters = [];
while($row = $semRes->fetch_assoc()) {
  if (!empty($row['semester'])) $semesters[] = $row['semester'];
}

// Build query for selected students
$q = "SELECT u.id, u.name, u.enrollment_no, u.semester
      FROM daily_selected_students ds
      JOIN users u ON ds.student_id = u.id
      WHERE ds.selection_date = '$today'";
if ($semester > 0) {
  $q .= " AND u.semester = '$semester'";
}
$q .= " ORDER BY u.semester, u.name";
$selRes = $conn->query($q);
?>
<div class="container mt-4">
  <div class="card p-3 card-lean mb-3">
    <h5>Selected Students for Today</h5>
    <form method="get" class="mb-3">
      <label for="semester" class="form-label">Filter by Semester:</label>
      <select name="semester" id="semester" class="form-select" style="max-width:200px;display:inline-block;">
        <option value="0">All Semesters</option>
        <?php foreach($semesters as $sem): ?>
          <option value="<?= $sem ?>" <?= $semester==$sem ? 'selected' : '' ?>>Semester <?= htmlspecialchars($sem) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm" type="submit">Filter</button>
    </form>
    <?php if($selRes && $selRes->num_rows > 0): ?>
      <table class="table table-bordered table-sm">
        <thead>
          <tr>
            <th>Name</th>
            <th>Enrollment No</th>
            <th>Semester</th>
          </tr>
        </thead>
        <tbody>
          <?php while($stu = $selRes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($stu['name']) ?></td>
              <td><?= htmlspecialchars($stu['enrollment_no']) ?></td>
              <td><?= htmlspecialchars($stu['semester']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="text-muted small">No students selected today<?= $semester > 0 ? " for Semester $semester" : "" ?>.</div>
    <?php endif; ?>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
