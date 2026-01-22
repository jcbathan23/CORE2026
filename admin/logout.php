<?php
session_start();

// Clear all session variables
$_SESSION = [];

// If the session uses cookies, clear the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Optionally, do NOT clear remember me cookies here to keep UX consistent
// If you want to force clearing remembered credentials on logout, uncomment below:
// setcookie('remember_email', '', time() - 3600, '/');
// setcookie('remember_password', '', time() - 3600, '/');

// Finally, destroy the session
session_destroy();

// Redirect to login page; a new session will start there
header('Location: loginpage.php?logged_out=1');
exit;
