<?php
session_start();
include("../../config.php");
include("../helpers/validation.php");
if(empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') die("Access denied");
$user_id = $_SESSION['user_id'];
$sgpas = [];
for ($i=1; $i<=8; $i++) {
    $sgpas[$i] = isset($_POST['sgpa'.$i]) && $_POST['sgpa'.$i] !== '' ? floatval($_POST['sgpa'.$i]) : null;
    if (!valid_sgpa($sgpas[$i])) die('SGPA values must be between 0 and 10.');
}
$stmt = $conn->prepare("UPDATE users SET sgpa1=?, sgpa2=?, sgpa3=?, sgpa4=?, sgpa5=?, sgpa6=?, sgpa7=?, sgpa8=? WHERE id=?");
$stmt->bind_param("ddddddddi",
    $sgpas[1], $sgpas[2], $sgpas[3], $sgpas[4], $sgpas[5], $sgpas[6], $sgpas[7], $sgpas[8], $user_id
);
if ($stmt->execute()) {
    header("Location: ../../frontend/student/profile.php?success=1");
    exit;
} else {
    header("Location: ../../frontend/student/profile.php?error=1");
    exit;
}
