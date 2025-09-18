<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  require_once __DIR__ . '/../../config.php';
  header("Location: " . APP_BASE . "/frontend/auth/login.php"); exit;
}
include("../includes/header.php");
include("../../config.php");
$user_id = $_SESSION['user_id'];
$subject_id = intval($_GET['subject_id'] ?? 0);
$faculty_id = intval($_GET['faculty_id'] ?? 0);
// Check if this subject is mapped to the student
$chk = $conn->query("SELECT 1 FROM student_subjects WHERE student_id=$user_id AND subject_id=$subject_id");
if($chk->num_rows == 0) { echo '<div class="alert alert-danger">Access denied.</div>'; include("../includes/footer.php"); exit; }
// Check if already submitted today
$today = date('Y-m-d');
$res = $conn->query("SELECT 1 FROM feedback_responses WHERE student_id=$user_id AND subject_id=$subject_id AND DATE(created_at)='$today'");
if($res->num_rows > 0) { echo '<div class="alert alert-success">Feedback already submitted for today.</div>'; include("../includes/footer.php"); exit; }
if(isset($_GET['success'])) {
  echo '<div class="alert alert-success">Feedback submitted successfully!</div>';
}
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card card-lean p-4">
      <h5>Submit Feedback</h5>
      <form id="feedbackForm" action="<?= APP_BASE ?>/backend/feedback/submit_response.php" method="POST">
        <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
        <input type="hidden" name="faculty_id" value="<?= $faculty_id ?>">
        <div id="questionsArea"></div>
        <input type="hidden" name="csrf_token" value="<?php include('../../backend/helpers/csrf.php'); echo generate_csrf_token(); ?>">
        <button class="btn btn-primary">Submit Feedback</button>
      </form>
    </div>
  </div>
</div>
<script>
fetch('<?= APP_BASE ?>/backend/feedback/get_questions.php')
  .then(r => r.json())
  .then(data => {
    if (!data.success) return;
    const area = document.getElementById('questionsArea');
    let hasQuestions = false;
    data.questions.forEach(q => {
      hasQuestions = true;
      let html = `<div class='mb-3'><label class='form-label'>${q.question_text}</label>`;
      if (q.question_type === 'text') {
        html += `<input type='hidden' name='question_id[]' value='${q.id}'>`;
        html += `<textarea name='response[]' class='form-control' required></textarea>`;
      } else if (q.question_type === 'yesno') {
        html += `<input type='hidden' name='question_id[]' value='${q.id}'>`;
        html += `<select name='response[]' class='form-select' required><option value=''>--select--</option><option>Yes</option><option>No</option></select>`;
      } else if (q.question_type === 'mcq') {
        html += `<input type='hidden' name='question_id[]' value='${q.id}'>`;
        html += `<select name='response[]' class='form-select' required><option value=''>--select--</option>`;
        q.options.forEach(opt => { html += `<option>${opt}</option>`; });
        html += `</select>`;
      } else if (q.question_type === 'rating') {
        html += `<input type='hidden' name='question_id[]' value='${q.id}'>`;
        html += `<input name='response[]' type='number' min='1' max='5' class='form-control' required placeholder='1-5'>`;
      }
      html += '</div>';
      area.insertAdjacentHTML('beforeend', html);
    });
    if (!hasQuestions) {
      area.innerHTML = '<div class="alert alert-warning">No feedback questions are currently set.</div>';
      document.querySelector('#feedbackForm button[type="submit"]').style.display = 'none';
    }
  });
</script>
<?php include("../includes/footer.php"); ?>
