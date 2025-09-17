<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card card-lean p-4">
      <h5>Submit Feedback</h5>
      <form action="<?= APP_BASE ?>/backend/student/submit_feedback.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Faculty (select)</label>
          <select name="faculty_id" class="form-select" required>
            <option value="">-- choose faculty --</option>
            <?php
            include("../../config.php");
            $res = $conn->query("SELECT id, name FROM users WHERE role='faculty' ORDER BY name");
            while($row = $res->fetch_assoc()){
              echo "<option value=\"{$row['id']}\">".htmlspecialchars($row['name'])."</option>";
            }
            ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subject / Topic</label>
          <input name="subject" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Rating (1-5)</label>
          <input name="rating" type="number" min="1" max="5" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Comments</label>
          <textarea name="comments" class="form-control" rows="4"></textarea>
        </div>
        <button class="btn btn-primary">Submit Feedback</button>
      </form>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
