<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
$faculty_id = $_SESSION['user_id'];
// Handle delete
if (isset($_GET['delete'])) {
  $sid = intval($_GET['delete']);
  $conn->query("DELETE FROM subjects WHERE id=$sid AND faculty_id=$faculty_id");
  header("Location: subjects.php"); exit;
}
// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_name'], $_POST['semester']) && !isset($_POST['edit_id'])) {
  $subject_name = trim($_POST['subject_name']);
  $semester = intval($_POST['semester']);
  if ($subject_name && $semester >= 1 && $semester <= 8) {
    $stmt = $conn->prepare("INSERT INTO subjects (faculty_id, subject_name, semester) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $faculty_id, $subject_name, $semester);
    $stmt->execute();
  }
  header("Location: subjects.php"); exit;
}
// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $edit_id = intval($_POST['edit_id']);
  $subject_name = trim($_POST['subject_name']);
  $semester = intval($_POST['semester']);
  if ($subject_name && $semester >= 1 && $semester <= 8) {
    $stmt = $conn->prepare("UPDATE subjects SET subject_name=?, semester=? WHERE id=? AND faculty_id=?");
    $stmt->bind_param("siii", $subject_name, $semester, $edit_id, $faculty_id);
    $stmt->execute();
  }
  header("Location: subjects.php"); exit;
}
// Fetch subjects
$res = $conn->query("SELECT id, subject_name, semester FROM subjects WHERE faculty_id=$faculty_id ORDER BY semester, subject_name");
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
// Check if edit_id is valid for this faculty
$edit_mode = false;
if ($edit_id) {
  $check = $conn->query("SELECT id FROM subjects WHERE id=$edit_id AND faculty_id=$faculty_id");
  if ($check && $check->num_rows > 0) {
    $edit_mode = true;
  } else {
    echo '<div class="alert alert-warning">Subject not found or not yours.</div>';
    $edit_id = 0;
  }
}
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card card-lean p-4">
      <h5>My Subjects</h5>
      <form method="POST" class="mb-3 row g-2 align-items-end">
        <div class="col-7">
          <input type="text" name="subject_name" class="form-control" placeholder="Add new subject" required>
        </div>
        <div class="col-3">
          <select name="semester" class="form-select" required>
            <option value="">Semester</option>
            <?php for($i=1;$i<=8;$i++): ?>
              <option value="<?= $i ?>">Sem <?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-2">
          <button class="btn btn-primary w-100" type="submit">Add</button>
        </div>
      </form>
      <ul class="list-group">
        <?php while($row = $res->fetch_assoc()): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php if($edit_mode && $edit_id === $row['id']): ?>
              <!-- Edit mode for subject -->
              <form method="POST" class="d-flex w-100 align-items-end">
                <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                <input type="text" name="subject_name" id="editSubjectInput" class="form-control me-2" value="<?= htmlspecialchars($row['subject_name']) ?>" required style="max-width: 40%">
                <select name="semester" class="form-select me-2" required style="max-width: 30%">
                  <?php for($i=1;$i<=8;$i++): ?>
                    <option value="<?= $i ?>" <?= $row['semester']==$i?'selected':'' ?>>Sem <?= $i ?></option>
                  <?php endfor; ?>
                </select>
                <button class="btn btn-success btn-sm me-1" type="submit">Save</button>
                <a href="subjects.php" class="btn btn-secondary btn-sm">Cancel</a>
              </form>
              <script>setTimeout(function(){document.getElementById('editSubjectInput').focus();},100);</script>
              <span class="text-info ms-2">Editing...</span>
            <?php else: ?>
              <span><?= htmlspecialchars($row['subject_name']) ?> <span class="badge bg-secondary ms-2">Sem <?= $row['semester'] ?></span></span>
              <span>
                <a href="subjects.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                <a href="subjects.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?')">Delete</a>
              </span>
            <?php endif; ?>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
