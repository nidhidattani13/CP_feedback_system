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
        <button class="btn btn-primary w-100">Register</button>
        <div class="mt-2 text-center">
          <small>Already registered? <a href="login.php">Login</a></small>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include("../includes/footer.php"); ?>
