<?php
// backend/feedback/get_questions.php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Fetch questions
    $qres = $conn->query("SELECT id, question_text, question_type FROM questions ORDER BY id ASC");
    $questions = [];
    while($row = $qres->fetch_assoc()) $questions[] = $row;
    // Fetch options in batch
    $qIds = array_column($questions, 'id');
    $optionsMap = [];
    if (count($qIds) > 0) {
        $in = implode(',', array_fill(0, count($qIds), '?'));
        $types = str_repeat('i', count($qIds));
        $stmt2 = $conn->prepare("SELECT question_id, option_text FROM question_options WHERE question_id IN ($in) ORDER BY id ASC");
        $stmt2->bind_param($types, ...$qIds);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        while($row = $res2->fetch_assoc()) {
            $optionsMap[$row['question_id']][] = $row['option_text'];
        }
    }
    $out = [];
    foreach ($questions as $q) {
        $q['options'] = $optionsMap[$q['id']] ?? [];
        $out[] = $q;
    }
    echo json_encode(['success' => true, 'questions' => $out], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
