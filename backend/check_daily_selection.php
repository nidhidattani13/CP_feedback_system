<?php
include("../config.php");
// Call the procedure to select 5 students for today
if (!$conn->query("CALL generate_daily_selection(5)")) {
    echo "Error: " . $conn->error;
}
// Check if students were selected
$res = $conn->query("SELECT ds.*, u.name FROM daily_selected_students ds JOIN users u ON ds.student_id = u.id WHERE ds.selection_date = CURDATE()");
if ($res && $res->num_rows > 0) {
    echo "Selected students for today:<br>";
    while ($row = $res->fetch_assoc()) {
        echo htmlspecialchars($row['name']) . " (ID: " . $row['student_id'] . ")<br>";
    }
} else {
    echo "No students selected for today.";
}
?>
