<?php
// backend/feedback/view_responses.php
require_once __DIR__ . '/../db.php';
session_start();

// Only faculty or hod (or admin) should view results
$role = $_SESSION['role'] ?? null;
if (empty($_SESSION['user_id']) || !in_array($role, ['faculty','hod'])) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// Optional: faculty sees only responses for themselves, HOD sees all
$forFacultyId = null;
if ($role === 'faculty') {
    $forFacultyId = intval($_SESSION['user_id']);
}

// fetch questions
$questions = $pdo->query("SELECT id, question_text, question_type FROM questions ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$out = [];
foreach ($questions as $q) {
    $qid = $q['id'];
    $qtype = $q['question_type'];

    // build base SQL depending on role
    $params = [];
    $where = '';
    if ($forFacultyId) {
        $where = " WHERE faculty_id = ? ";
        $params[] = $forFacultyId;
    }

    // Different aggregation per type
    if ($qtype === 'rating' || $qtype === 'yesno' || $qtype === 'mcq') {
        // count occurrences grouped by response
        $sql = "SELECT response, COUNT(*) as cnt FROM feedback_responses
                WHERE question_id = ?" . ($forFacultyId ? " AND faculty_id = ? " : "") . " GROUP BY response ORDER BY cnt DESC";
        if ($forFacultyId) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$qid, $forFacultyId]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$qid]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out[] = ['question' => $q, 'type' => $qtype, 'distribution' => $rows];
    } else { // text
        $sql = "SELECT fr.response, u.name AS student_name, fr.created_at
                FROM feedback_responses fr
                JOIN users u ON u.id = fr.student_id
                WHERE fr.question_id = ?" . ($forFacultyId ? " AND fr.faculty_id = ? " : "") . " ORDER BY fr.created_at DESC";
        if ($forFacultyId) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$qid, $forFacultyId]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$qid]);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out[] = ['question' => $q, 'type' => $qtype, 'responses' => $rows];
    }
}

// simple JSON output (frontend can render)
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'data' => $out], JSON_UNESCAPED_UNICODE);
