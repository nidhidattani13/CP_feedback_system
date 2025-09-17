<?php
session_start();
session_unset();
session_destroy();
require_once __DIR__ . '/../../config.php';
header("Location: " . APP_BASE . "/frontend/auth/login.php");
exit;
