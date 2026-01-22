<?php
// index.php
// This is the main entry point for the Fleet Management System (NEWFMSCORE2).
// It redirects users to either the dashboard or the login page based on their session status.

session_start(); // Start the PHP session to manage user login state

// Check if the user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // If logged in, redirect to the dashboard page
    header("Location: admin/dashboard.php");
    exit(); // Always exit after a header redirect
} else {
    // If not logged in, redirect to the login page
    header("Location: admin/loginpage.php");
    exit(); // Always exit after a header redirect
}
?>
