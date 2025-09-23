<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$verification_counts = [];
$res = $conn->query("SELECT event_id, COUNT(*) as cnt FROM event_verifications GROUP BY event_id");
if ($res && $res->num_rows > 0) {
  while($row = $res->fetch_assoc()) {
    $verification_counts[$row['event_id']] = intval($row['cnt']);
  }
}
echo json_encode($verification_counts);
