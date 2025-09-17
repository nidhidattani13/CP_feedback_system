<?php
// config.php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // set if you have a password
$DB_NAME = 'feedback_system';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
// Base URL path of the application (as served by the web server)
// Adjust if the project is hosted under a different directory
if (!defined('APP_BASE')) {
    define('APP_BASE', '/CP_feedback_system');
}
?>
