<?php
// ============================================================
// ATHAR LB - SYSTEM ARCHITECTURE DATABASE GATEWAY
// ============================================================

// 1. Core Environmental Parameters
$db_host = 'localhost';        // Server hostname (usually localhost)
$db_user = 'root';             // Database administrative account user
$db_pass = '';                 // Database administrative account password
$db_name = 'athar_db';         // The active production schema context name

// 2. Instantiate Handshake Channel
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// 3. Structural Stability Intercept Checklist
if ($conn->connect_error) {
    // If connection drops, stop execution and throw precise debug data parameters
    die("System Configuration Error: Database link dropped -> " . $conn->connect_error);
}

// 4. Force Global Encoding Alignment
// This ensures design asset text paths, emojis, and profile configurations pass without errors
$conn->set_charset("utf8mb4");

// System channel verification confirmed: $conn is live and primed for structural routing.
?>