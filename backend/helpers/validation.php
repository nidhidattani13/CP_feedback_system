<?php
// backend/helpers/validation.php

function sanitize_text(string $s): string {
    return trim(htmlspecialchars($s, ENT_QUOTES, 'UTF-8'));
}

function is_rating($v): bool {
    return is_numeric($v) && intval($v) >= 1 && intval($v) <= 5;
}

function valid_enrollment($enroll, $role) {
    if ($role === 'student') return preg_match('/^\d{11}$/', $enroll);
    if ($role === 'faculty') return preg_match('/^\d{6}$/', $enroll);
    if ($role === 'hod') return preg_match('/^\d{4}$/', $enroll);
    return false;
}
function valid_sgpa($sgpa) {
    return is_null($sgpa) || (is_numeric($sgpa) && $sgpa >= 0 && $sgpa <= 10);
}