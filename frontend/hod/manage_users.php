<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

// Handle filters
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$filter_semester = isset($_GET['filter_semester']) ? trim($_GET['filter_semester']) : '';

// Fetch students grouped by semester
$students = [];
$res = $conn->query("SELECT id, name, enrollment_no, semester FROM users WHERE role='student' ORDER BY semester, name");
while($row = $res->fetch_assoc()) {
  // Apply filters
  if ($filter_semester !== '' && $row['semester'] != $filter_semester) continue;
  if ($search_name !== '' && stripos($row['name'], $search_name) === false) continue;
  $students[$row['semester']][] = $row;
}
// Fetch all faculty
$faculty = [];
$res2 = $conn->query("SELECT id, name, enrollment_no FROM users WHERE role='faculty' ORDER BY name");
while($row = $res2->fetch_assoc()) {
  $faculty[] = $row;
}
// Handle delete
if (isset($_GET['delete_user'])) {
  $del_id = intval($_GET['delete_user']);
  $conn->query("DELETE FROM users WHERE id=$del_id");
  header("Location: manage_users.php?deleted=1"); exit;
}
?>
<div class="container mt-4">
  <h4>Manage Students & Faculty</h4>
  <!-- Filter Form -->
  <form method="get" class="row g-2 mb-3 align-items-end">
    <div class="col-3">
      <label class="form-label">Filter by Semester</label>
      <select name="filter_semester" class="form-select">
        <option value="">All</option>
        <?php for($i=1;$i<=8;$i++): ?>
          <option value="<?= $i ?>" <?= $filter_semester==$i?'selected':'' ?>>Semester <?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-4">
      <label class="form-label">Search by Name</label>
      <input type="text" name="search_name" class="form-control" value="<?= htmlspecialchars($search_name) ?>" placeholder="Student name">
    </div>
    <div class="col-2">
      <button class="btn btn-primary w-100" type="submit">Filter</button>
    </div>
  </form>
  <!-- End Filter Form -->
  <div class="row">
    <div class="col-md-6">
      <h5>Students (Semester-wise)</h5>
      <?php if(empty($students)): ?>
        <div class="text-muted">No students found for selected filter.</div>
      <?php else: ?>
        <div class="accordion" id="studentsAccordion">
          <?php $semIndex = 0; foreach($students as $sem => $list): $semIndex++; ?>
            <div class="accordion-item mb-2">
              <h2 class="accordion-header" id="headingSem<?= $sem ?>">
                <button class="accordion-button <?= $semIndex > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSem<?= $sem ?>" aria-expanded="<?= $semIndex == 1 ? 'true' : 'false' ?>" aria-controls="collapseSem<?= $sem ?>">
                  Semester <?= htmlspecialchars($sem) ?>
                </button>
              </h2>
              <div id="collapseSem<?= $sem ?>" class="accordion-collapse collapse <?= $semIndex == 1 ? 'show' : '' ?>" aria-labelledby="headingSem<?= $sem ?>" data-bs-parent="#studentsAccordion">
                <div class="accordion-body p-0">
                  <ul class="list-group">
                    <?php foreach($list as $stu): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($stu['name']) ?> (<?= htmlspecialchars($stu['enrollment_no']) ?>)
                        <span>
                          <a href="edit_user.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                          <a href="manage_users.php?delete_user=<?= $stu['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this student?')">Delete</a>
                        </span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-md-6">
      <h5>Faculty List</h5>
      <ul class="list-group">
        <?php foreach($faculty as $fac): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($fac['name']) ?> (<?= htmlspecialchars($fac['enrollment_no']) ?>)
            <span>
              <a href="edit_user.php?id=<?= $fac['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
              <a href="manage_users.php?delete_user=<?= $fac['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this faculty?')">Delete</a>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
