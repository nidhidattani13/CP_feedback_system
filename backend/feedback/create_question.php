<?php
// backend/feedback/create_question.php
require_once __DIR__ . '../../database/db.sql';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/validation.php';
session_start();

// ROLE CHECK: only faculty or hod can create questions
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || !in_array($_SESSION['role'], ['faculty','hod'])) {
    http_response_code(403);
    echo "Access denied";
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

$question_text = sanitize_text($_POST['question_text'] ?? '');
$question_type = $_POST['question_type'] ?? 'text';
$allowed_types = ['text','yesno','mcq','rating'];

if (!$question_text || !in_array($question_type, $allowed_types)) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO questions (question_text, question_type) VALUES (?, ?)");
    $stmt->execute([$question_text, $question_type]);
    $question_id = $pdo->lastInsertId();

    // If MCQ, expect options[] array
    if ($question_type === 'mcq') {
        $options = $_POST['options'] ?? [];
        if (!is_array($options) || count($options) < 2) {
            $pdo->rollBack();
            http_response_code(400);
            echo "MCQ must include at least 2 options.";
            exit;
        }
        $stmtOpt = $pdo->prepare("INSERT INTO question_options (question_id, option_text) VALUES (?, ?)");
        foreach ($options as $opt) {
            $opt = sanitize_text($opt);
            if ($opt === '') continue;
            $stmtOpt->execute([$question_id, $opt]);
        }
    } elseif ($question_type === 'yesno') {
        // add Yes/No options automatically (optional)
        $stmtOpt = $pdo->prepare("INSERT INTO question_options (question_id, option_text) VALUES (?, ?)");
        $stmtOpt->execute([$question_id, 'Yes']);
        $stmtOpt->execute([$question_id, 'No']);
    }

    $pdo->commit();
    echo "Question created successfully";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
