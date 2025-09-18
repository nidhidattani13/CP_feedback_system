<?php
// backend/feedback/submit_response.php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/validation.php';
session_start();
// Role check: only students should submit feedback
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    http_response_code(403);
    echo "Access denied. Only students can submit feedback.";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    http_response_code(400);
    echo "Invalid CSRF token";
    exit;
}
$student_id = intval($_SESSION['user_id']);
$faculty_id = intval($_POST['faculty_id'] ?? 0);
$subject_id = intval($_POST['subject_id'] ?? 0);
// Check subject mapping
$stmtS = $conn->prepare("SELECT 1 FROM student_subjects WHERE student_id=? AND subject_id=?");
$stmtS->bind_param("ii", $student_id, $subject_id);
$stmtS->execute();
$stmtS->store_result();
if ($stmtS->num_rows == 0) {
    http_response_code(403);
    echo "Subject not mapped to student.";
    exit;
}
// Prevent multiple submissions same day for same subject
$today = date('Y-m-d');
$stmtCheck = $conn->prepare("SELECT COUNT(*) FROM feedback_responses WHERE student_id = ? AND subject_id = ? AND DATE(created_at) = ?");
$stmtCheck->bind_param("iis", $student_id, $subject_id, $today);
$stmtCheck->execute();
$stmtCheck->bind_result($count);
$stmtCheck->fetch();
$stmtCheck->close();
if ($count > 0) {
    http_response_code(409);
    echo "You have already submitted feedback for this subject today.";
    exit;
}
// Expect responses as POST arrays: question_id[] and response[]
$question_ids = $_POST['question_id'] ?? [];
$responses = $_POST['response'] ?? [];
if (!is_array($question_ids) || !is_array($responses) || count($question_ids) !== count($responses)) {
    http_response_code(400);
    echo "Invalid payload.";
    exit;
}
$stmtIns = $conn->prepare("INSERT INTO feedback_responses (student_id, faculty_id, subject_id, question_id, response) VALUES (?, ?, ?, ?, ?)");
for ($i=0;$i<count($question_ids);$i++){
    $qid = intval($question_ids[$i]);
    $ans = sanitize_text($responses[$i]);
    $stmtIns->bind_param("iiiis", $student_id, $faculty_id, $subject_id, $qid, $ans);
    $stmtIns->execute();
}
$stmtIns->close();
header('Location: ../../frontend/student/dashboard.php?success=1');
exit;
