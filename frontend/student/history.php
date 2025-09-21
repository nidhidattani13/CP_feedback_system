<?php
session_start();
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php");
  exit;
}
include("../includes/header.php");
include("../../config.php");
$user_id = intval($_SESSION['user_id']);

// Debug: Show SQL error if query fails
$query = "
  SELECT fr.created_at, s.subject_name, q.question_text, fr.response
  FROM feedback_responses fr
  JOIN subjects s ON fr.subject_id = s.id
  JOIN questions q ON fr.question_id = q.id
  WHERE fr.student_id = $user_id
  ORDER BY fr.created_at DESC
";
$res = $conn->query($query);

if (!$res) {
  echo "<div class='alert alert-danger'>Error fetching feedback history: " . htmlspecialchars($conn->error) . "</div>";
  // Optionally: echo "<pre>$query</pre>"; // Uncomment for SQL debug
} else {
?>
<div class="container py-4">
  <h4>My Feedback History</h4>
  <table class="table table-bordered mt-3">
    <thead>
      <tr>
        <th>Date</th>
        <th>Subject</th>
        <th>Question</th>
        <th>Response</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td><?= htmlspecialchars($row['subject_name']) ?></td>
          <td><?= htmlspecialchars($row['question_text']) ?></td>
          <td><?= htmlspecialchars($row['response']) ?></td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="4" class="text-center">No feedback history found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php } ?>
<?php include("../includes/footer.php"); ?>