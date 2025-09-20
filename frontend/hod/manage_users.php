<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

// Fetch students grouped by semester
$students = [];
$res = $conn->query("SELECT id, name, enrollment_no, semester FROM users WHERE role='student' ORDER BY semester, name");
while($row = $res->fetch_assoc()) {
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
  <div class="row">
    <div class="col-md-6">
      <h5>Students (Semester-wise)</h5>
      <?php foreach($students as $sem => $list): ?>
        <div class="mb-3">
          <strong>Semester <?= htmlspecialchars($sem) ?></strong>
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
      <?php endforeach; ?>
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
