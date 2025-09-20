<?php
session_start();
include("../includes/header.php");
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card card-lean p-4">
      <h4 class="mb-3">Register</h4>
      <form action="<?= APP_BASE ?>/backend/auth/register_process.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="hod">HOD</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Enrollment No.</label>
          <input name="enrollment_no" id="enrollment_no" class="form-control" required maxlength="16">
          <div class="invalid-feedback" id="enrollHelp"></div>
        </div>
          <div class="mb-3" id="semesterField" style="display:none;">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select">
              <option value="">Select Semester</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
              <option value="6">6</option>
              <option value="7">7</option>
              <option value="8">8</option>
            </select>
          </div>
        <div id="sgpaFields" style="display:none;">
          <label class="form-label">SGPA (Sem 1-8)</label>
          <div class="row">
            <?php for($i=1;$i<=8;$i++): ?>
              <div class="col-3 mb-2">
                <input type="number" step="0.01" min="0" max="10" name="sgpa<?= $i ?>" id="sgpa<?= $i ?>" class="form-control sgpa-input" placeholder="SGPA<?= $i ?>">
              </div>
            <?php endfor; ?>
          </div>
          <div class="invalid-feedback" id="sgpaHelp"></div>
        </div>
        <button class="btn btn-primary w-100">Register</button>
        <div class="mt-2 text-center">
          <small>Already registered? <a href="login.php">Login</a></small>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
<script>
const roleSelect = document.querySelector('select[name="role"]');
const enrollInput = document.getElementById('enrollment_no');
const enrollHelp = document.getElementById('enrollHelp');
const sgpaFields = document.getElementById('sgpaFields');
const sgpaInputs = document.querySelectorAll('.sgpa-input');
const semesterField = document.getElementById('semesterField');
const semesterSelect = semesterField.querySelector('select');
const sgpaHelp = document.getElementById('sgpaHelp');

function updateFields() {
  const role = roleSelect.value;
  enrollInput.value = '';
  enrollInput.maxLength = role === 'student' ? 11 : (role === 'faculty' ? 6 : 4);
  enrollInput.placeholder = role === 'student' ? '11-digit' : (role === 'faculty' ? '6-digit' : '4-digit');
  sgpaFields.style.display = (role === 'student') ? '' : 'none';
  sgpaInputs.forEach(inp => { inp.required = false; inp.value = ''; });
    semesterField.style.display = (role === 'student') ? '' : 'none';
    semesterSelect.required = (role === 'student');
    if (role !== 'student') semesterSelect.value = '';
}
roleSelect.addEventListener('change', updateFields);
document.addEventListener('DOMContentLoaded', updateFields);

document.querySelector('form').addEventListener('submit', function(e) {
  let valid = true;
  enrollHelp.textContent = '';
  sgpaHelp.textContent = '';
  const role = roleSelect.value;
  const enrollVal = enrollInput.value.trim();
    if (role === 'student') {
      if (!semesterSelect.value) {
        semesterSelect.classList.add('is-invalid');
        valid = false;
      } else {
        semesterSelect.classList.remove('is-invalid');
      }
    }
  if (role === 'student' && enrollVal.length !== 11) {
    enrollHelp.textContent = 'Enrollment number must be 11 digits for students.';
    enrollInput.classList.add('is-invalid');
    valid = false;
  } else if (role === 'faculty' && enrollVal.length !== 6) {
    enrollHelp.textContent = 'Enrollment number must be 6 digits for faculty.';
    enrollInput.classList.add('is-invalid');
    valid = false;
  } else if (role === 'hod' && enrollVal.length !== 4) {
    enrollHelp.textContent = 'Enrollment number must be 4 digits for HOD.';
    enrollInput.classList.add('is-invalid');
    valid = false;
  } else {
    enrollInput.classList.remove('is-invalid');
  }
  if (role === 'student') {
    for (let inp of sgpaInputs) {
      if (inp.value && (parseFloat(inp.value) < 0 || parseFloat(inp.value) > 10)) {
        sgpaHelp.textContent = 'SGPA values must be between 0 and 10.';
        inp.classList.add('is-invalid');
        valid = false;
      } else {
        inp.classList.remove('is-invalid');
      }
    }
  }
  if (!valid) e.preventDefault();
});
</script>
