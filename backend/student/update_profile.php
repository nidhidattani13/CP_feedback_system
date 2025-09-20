<?php
session_start();
include("../../config.php");
include("../helpers/validation.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') die("Access denied");
$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$enrollment_no = trim($_POST['enrollment_no'] ?? '');
$semester = intval($_POST['semester'] ?? 0);
$password = $_POST['password'] ?? '';
$sgpas = [];
for ($i=1; $i<=8; $i++) {
    $sgpas[$i] = isset($_POST['sgpa'.$i]) && $_POST['sgpa'.$i] !== '' ? floatval($_POST['sgpa'.$i]) : null;
    if (!valid_sgpa($sgpas[$i])) die('SGPA values must be between 0 and 10.');
}
if (!$name) die('Name required.');
if (!valid_enrollment($enrollment_no, 'student')) die('Invalid enrollment number.');
if ($semester < 1 || $semester > 8) die('Semester must be between 1 and 8.');
if ($password) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET name=?, enrollment_no=?, semester=?, password=?, sgpa1=?, sgpa2=?, sgpa3=?, sgpa4=?, sgpa5=?, sgpa6=?, sgpa7=?, sgpa8=? WHERE id=?");
    $stmt->bind_param("ssissdddddddi",
        $name, $enrollment_no, $semester, $hashed,
        $sgpas[1], $sgpas[2], $sgpas[3], $sgpas[4], $sgpas[5], $sgpas[6], $sgpas[7], $sgpas[8], $user_id
    );
} else {
    $stmt = $conn->prepare("UPDATE users SET name=?, enrollment_no=?, semester=?, sgpa1=?, sgpa2=?, sgpa3=?, sgpa4=?, sgpa5=?, sgpa6=?, sgpa7=?, sgpa8=? WHERE id=?");
    $stmt->bind_param("ssiddddddddi",
        $name, $enrollment_no, $semester,
        $sgpas[1], $sgpas[2], $sgpas[3], $sgpas[4], $sgpas[5], $sgpas[6], $sgpas[7], $sgpas[8], $user_id
    );
}
if ($stmt->execute()) {
    $_SESSION['name'] = $name;
    $_SESSION['enrollment_no'] = $enrollment_no;
    // Update subject selections
    $conn->query("DELETE FROM student_subjects WHERE student_id=$user_id");
    if (!empty($_POST['subject_ids']) && is_array($_POST['subject_ids'])) {
        $ins = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
        foreach ($_POST['subject_ids'] as $sid) {
            $sid = intval($sid);
            $ins->bind_param("ii", $user_id, $sid);
            $ins->execute();
        }
    }
        header("Location: ../../frontend/student/dashboard.php?profile_updated=1");
    exit;
} else {
    header("Location: ../../frontend/student/profile.php?error=1");
    exit;
}
