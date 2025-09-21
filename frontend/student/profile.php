<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT name, enrollment_no, semester, sgpa1, sgpa2, sgpa3, sgpa4, sgpa5, sgpa6, sgpa7, sgpa8, cgpa, category FROM users WHERE id=$user_id");
$u = $res->fetch_assoc();
$semester = intval($u['semester']);
// Fetch all subjects for this semester
$all_subjects = $conn->query("SELECT id, subject_name FROM subjects WHERE semester=$semester ORDER BY subject_name");
// Fetch student's selected subjects
$selected_subjects = [];
$res2 = $conn->query("SELECT subject_id FROM student_subjects WHERE student_id=$user_id");
while($row = $res2->fetch_assoc()) $selected_subjects[] = $row['subject_id'];
?>
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card card-lean p-4">
      <h5>My Profile</h5>
        <!-- <div class="mb-3">
          <a href="verify_assessment.php" class="btn btn-outline-primary btn-sm me-2">Verify Assessments</a>
          <a href="verify_event.php" class="btn btn-outline-secondary btn-sm">Verify Events</a>
        </div> -->
      <form method="POST" action="../../backend/student/update_profile.php">
        <div class="mb-2">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($u['name']) ?>" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Enrollment No</label>
          <input type="text" name="enrollment_no" class="form-control" value="<?= htmlspecialchars($u['enrollment_no']) ?>" required maxlength="11">
        </div>
        <div class="mb-2">
          <label class="form-label">Semester</label>
          <input type="number" name="semester" class="form-control" min="1" max="8" value="<?= htmlspecialchars($u['semester']) ?>" required>
        </div>
        <div class="mb-2">
          <label class="form-label">New Password (leave blank to keep current)</label>
          <input type="password" name="password" class="form-control" autocomplete="new-password">
        </div>
        <div class="mb-2"><strong>CGPA:</strong> <?= htmlspecialchars($u['cgpa'] ?? 'N/A') ?></div>
        <div class="mb-2"><strong>Category:</strong> <?= htmlspecialchars($u['category'] ?? 'N/A') ?></div>
        <hr>
        
        <label class="form-label">Update SGPA (Sem 1-8)</label>
        <div class="row">
          <?php for($i=1;$i<=8;$i++): ?>
            <div class="col-3 mb-2">
              <input type="number" step="0.01" min="0" max="10" name="sgpa<?= $i ?>" class="form-control" placeholder="SGPA<?= $i ?>" value="<?= htmlspecialchars($u['sgpa'.$i]) ?>">
            </div>
          <?php endfor; ?>
        </div>
        <div class="mb-2">
          <label class="form-label">Subjects for Semester <?= $semester ?></label>
          <div class="row">
            <?php while($sub = $all_subjects->fetch_assoc()): ?>
              <div class="col-6 mb-1">
                <label class="form-check-label">
                  <input type="checkbox" name="subject_ids[]" value="<?= $sub['id'] ?>" class="form-check-input" <?= in_array($sub['id'], $selected_subjects) ? 'checked' : '' ?>>
                  <?= htmlspecialchars($sub['subject_name']) ?>
                </label>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
        <button class="btn btn-primary mt-2">Update Profile</button>
      </form>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
