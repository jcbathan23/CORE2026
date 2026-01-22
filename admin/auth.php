<?php
// auth.php - simple access guard for admin pages
// Starts a session (if not already started) and ensures only logged-in admin accounts can proceed.

// Allow long-running operations (e.g., geocoding) without timing out
@set_time_limit(0);
@ini_set('max_execution_time', '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// If not logged in OR account type indicates non-admin (e.g., service provider: 3), redirect to login
$acct = isset($_SESSION['account_type']) ? (int)$_SESSION['account_type'] : null;
if (!isset($_SESSION['email']) || $acct === null || $acct === 3) {
    // Optionally capture the page the user tried to access
    $target = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';
    header('Location: loginpage.php?denied=1&next=' . $target);
    exit;
}

// If needed later, you can enforce stricter admin-only values here, e.g.:
// if (!in_array($acct, [1, 2], true)) { header('Location: loginpage.php?denied=1'); exit; }
