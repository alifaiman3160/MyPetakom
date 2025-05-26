<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Not logged in, redirect to failed login page
    header("Location: ./loginfailed.html");
    exit();
}

// If session exists and user is logged in, continue to the protected page
// No action needed here as the script continues to the protected page
?>