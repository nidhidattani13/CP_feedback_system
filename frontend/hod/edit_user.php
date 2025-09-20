<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'hod') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");

$id = intval($_GET['id'] ?? 0);
$res = $conn->query("SELECT * FROM users WHERE id=$id");
if(!$res || $res->num_rows==0) die("User not found.");
$user = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $enrollment_no = trim($_POST['enrollment_no'] ?? '');
  $semester = isset($_POST['semester']) ? intval($_POST['semester']) : null;
  $role = $_POST['role'] ?? $user['role'];
  if ($name && $enrollment_no) {
    if ($role === 'student') {
      $stmt = $conn->prepare("UPDATE users SET name=?, enrollment_no=?, semester=? WHERE id=?");
      $stmt->bind_param("ssii", $name, $enrollment_no, $semester, $id);
    } else {
      $stmt = $conn->prepare("UPDATE users SET name=?, enrollment_no=? WHERE id=?");
      $stmt->bind_param("ssi", $name, $enrollment_no, $id);
    }
    $stmt->execute();
    header("Location: manage_users.php?success=1"); exit;
  }
}
?>
<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card card-lean p-4">
        <h5>Edit User</h5>
        <form method="POST">
          <div class="mb-2">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Enrollment No</label>
            <input type="text" name="enrollment_no" class="form-control" value="<?= htmlspecialchars($user['enrollment_no']) ?>" required>
          </div>
          <?php if($user['role']==='student'): ?>
          <div class="mb-2">
            <label class="form-label">Semester</label>
            <input type="number" name="semester" class="form-control" min="1" max="8" value="<?= htmlspecialchars($user['semester']) ?>" required>
          </div>
          <?php endif; ?>
          <div class="mb-2">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['role']) ?>" readonly>
          </div>
          <button class="btn btn-primary">Save Changes</button>
          <a href="manage_users.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
