<?php
session_start();
// session_check.php
// Middleware to ensure user is logged in
// Include this at the TOP of every protected page

if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Optional: Security, regenerate session ID periodically to prevent session fixation attacks
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>
