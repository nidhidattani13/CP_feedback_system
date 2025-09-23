<?php
// backend/cron/auto_generate_daily_selection.php
include_once("../../config.php");

$categories = ['Excellent', 'Very Good', 'Good', 'Average', 'Below Average'];
$today = date('Y-m-d');

// Remove previous selections for today
$conn->query("DELETE FROM daily_selected_students WHERE selection_date = '$today'");

foreach ($categories as $cat) {
    $res = $conn->query("SELECT id FROM users WHERE role='student' AND category='$cat' ORDER BY RAND() LIMIT 10");
    while ($row = $res->fetch_assoc()) {
        $sid = $row['id'];
        $conn->query("INSERT INTO daily_selected_students (student_id, selection_date) VALUES ($sid, '$today')");
    }
}

echo "Daily selection completed for all categories.";
