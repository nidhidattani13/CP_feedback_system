<?php
session_start();
include("../../config.php");
include("../../backend/helpers/validation.php");

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';
$enrollment_no = trim($_POST['enrollment_no'] ?? '');
$sgpas = [];
for ($i=1; $i<=8; $i++) {
    $sgpas[$i] = isset($_POST['sgpa'.$i]) ? floatval($_POST['sgpa'.$i]) : null;
}

// Role-based validation
if (!valid_enrollment($enrollment_no, $role)) {
    die('Invalid enrollment number for role.');
}
if ($role === 'student') {
    foreach ($sgpas as $sgpa) {
        if (!valid_sgpa($sgpa)) die('SGPA values must be between 0 and 10.');
    }
} elseif ($role === 'faculty') {
    if (strlen($enrollment_no) !== 6) die('Enrollment number must be 6 digits for faculty.');
} elseif ($role === 'hod') {
    if (strlen($enrollment_no) !== 4) die('Enrollment number must be 4 digits for HOD.');
}

if (!$name || !$email || !$password) {
    die("Missing fields.");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name,email,password,role,enrollment_no,sgpa1,sgpa2,sgpa3,sgpa4,sgpa5,sgpa6,sgpa7,sgpa8) VALUES (?,?,?,?,?,?,?,?,?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssssdddddddd",
    $name, $email, $hashed, $role, $enrollment_no,
    $sgpas[1], $sgpas[2], $sgpas[3], $sgpas[4], $sgpas[5], $sgpas[6], $sgpas[7], $sgpas[8]
);
if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $name;
    header("Location: " . APP_BASE . "/frontend/{$role}/dashboard.php");
    exit;
} else {
    die("Register failed: " . $conn->error);
}
