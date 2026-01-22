<?php
session_start();
require_once __DIR__ . '/../includes/password_utils.php';

// Helper: minimal redirect after login (no interim overlay/message)
if (!function_exists('emit_login_loading_and_redirect')) {
    function emit_login_loading_and_redirect($message, $redirectUrl, $delayMs = 0) {
        $redirectEsc = htmlspecialchars($redirectUrl, ENT_QUOTES);
        echo "<script>(function(){ setTimeout(function(){ window.location.replace('" . $redirectEsc . "'); }, 50); })();</script>";
    }
}

// Initialize variables
$email = $password = '';
$remember_me = false;
$login_error = '';


// Lockout config and state
// Max attempts and lockout duration (seconds)
$max_attempts = 3;
$lockout_duration = 60;
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] <= time()) {
    unset($_SESSION['lockout_until']);
    $_SESSION['login_attempts'] = 0;
}
$locked = false;
$lock_remaining_seconds = 0;
if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
    $locked = true;
    $lock_remaining_seconds = max(0, $_SESSION['lockout_until'] - time());
    $mins = floor($lock_remaining_seconds / 60);
    $secs = $lock_remaining_seconds % 60;
    $login_error =  $mins . 'm' . str_pad((string)$secs, 2, '0', STR_PAD_LEFT) . 's.';
}

// If redirected here due to access control
if (isset($_GET['denied'])) {
    $login_error = 'Access denied. Please log in first.';
}

// No captcha flow; replaced by OTP for admin emails


// ✅ Check for cookies
if(isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])){
    $email = $_COOKIE['remember_email'];
    $password = $_COOKIE['remember_password'];
    $remember_me = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$locked) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $remember_me = isset($_POST['remember_me']);
        $otp_code = trim($_POST['otp_code'] ?? '');

        include('../connect.php');

        if ($email && $password) {
            // Detect if admin email; if yes, require OTP
            $is_admin = false;
            $stmt = $conn->prepare("SELECT email, password, account_type FROM admin_list WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $adminRes = $stmt->get_result();
            if ($adminRes && $adminRes->fetch_assoc()) { $is_admin = true; }
            $stmt->close();

            if ($is_admin) {
                // Ensure OTP table exists
                $conn->query("CREATE TABLE IF NOT EXISTS login_otps (
                  id INT AUTO_INCREMENT PRIMARY KEY,
                  email VARCHAR(255) NOT NULL,
                  otp VARCHAR(16) NOT NULL,
                  expires_at DATETIME NOT NULL,
                  used TINYINT(1) NOT NULL DEFAULT 0,
                  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  INDEX (email),
                  INDEX (expires_at),
                  INDEX (used)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                
                // For admin users, verify password first, then send OTP and redirect
                $stmt = $conn->prepare("SELECT email, password, account_type FROM admin_list WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if ($password === $row['password']) {
                        // Password is correct - send OTP and redirect to OTP page
                        require_once __DIR__ . '/otp_mailer.php';
                        
                        // Generate OTP
                        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $expiryMins = 5;
                        $expiresAt = date('Y-m-d H:i:s', time() + $expiryMins * 60);
                        $email_lower = strtolower($email);
                        
                        $ins = $conn->prepare('INSERT INTO login_otps (email, otp, expires_at) VALUES (?, ?, ?)');
                        $ins->bind_param('sss', $email_lower, $otp, $expiresAt);
                        $otpSent = false;
                        if ($ins->execute()) {
                            $ins->close();
                            
                            $subject = 'Your SLATE Admin Login OTP';
                            $body = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#111">'
                              . '<p>Hello,</p>'
                              . '<p>Your one-time password (OTP) for SLATE Admin login is:</p>'
                              . '<p style="font-size:22px;font-weight:700;letter-spacing:3px;background:#f4f6f8;padding:10px 16px;display:inline-block;border-radius:6px;">' . htmlspecialchars($otp) . '</p>'
                              . '<p>This code will expire in ' . $expiryMins . ' minutes.</p>'
                              . '<p>If you did not request this, you can ignore this email.</p>'
                              . '<p>Thank you.</p>'
                              . '</div>';
                            
                            list($ok, $msg) = send_smtp_mail($email_lower, '', $subject, $body);
                            $otpSent = $ok;
                        }
                        
                        if ($otpSent) {
                            // Set session variables for OTP verification
                            $_SESSION['pending_otp_email'] = $email_lower;
                            $_SESSION['pending_otp_user_type'] = 'admin';
                            $_SESSION['login_attempts'] = 0;
                            unset($_SESSION['lockout_until']);
                            
                            // Redirect to OTP verification page
                            header('Location: otp_verification.php');
                            exit();
                        } else {
                            $login_error = 'Failed to send OTP. Please try again.';
                        }
                    } else {
                        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                        if ($_SESSION['login_attempts'] >= $max_attempts) {
                            $_SESSION['lockout_until'] = time() + $lockout_duration;
                            $locked = true;
                            $lock_remaining_seconds = max(0, $_SESSION['lockout_until'] - time());
                        } else {
                            $attempts_left = $max_attempts - $_SESSION['login_attempts'];
                            $login_error = 'Incorrect password. Attempts left: ' . $attempts_left . '.';
                        }
                    }
                }
                $stmt->close();
            } else {
                // Service Provider check
                $stmt = $conn->prepare("
                    SELECT email, password, 3 AS account_type 
                    FROM active_service_provider 
                    WHERE email = ? 
                    UNION 
                    SELECT email, password, 3 AS account_type 
                    FROM pending_service_provider 
                    WHERE email = ?
                ");
                $stmt->bind_param("ss", $email, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if ($password === $row['password']) {
                        $_SESSION['login_attempts'] = 0;
                        unset($_SESSION['lockout_until']);
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['account_type'] = 3;
                        $_SESSION['just_logged_in'] = 'provider';
                        if($remember_me){
                            setcookie('remember_email', $email, time() + (86400 * 30), "/");
                            setcookie('remember_password', $password, time() + (86400 * 30), "/");
                        } else {
                            setcookie('remember_email', '', time() - 3600, "/");
                            setcookie('remember_password', '', time() - 3600, "/");
                        }
                        emit_login_loading_and_redirect('Successful Login, Hello Service Provider!', 'provider_dashboard.php');
                        exit();
                    } else {
                        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                        if ($_SESSION['login_attempts'] >= $max_attempts) {
                            $_SESSION['lockout_until'] = time() + $lockout_duration;
                            $locked = true;
                            $lock_remaining_seconds = max(0, $_SESSION['lockout_until'] - time());
                        } else {
                            $attempts_left = $max_attempts - $_SESSION['login_attempts'];
                            $login_error = 'Incorrect email or password. Attempts left: ' . $attempts_left . '.';
                        }
                    }
                } else {
                    // Normal users check
                    $stmt = $conn->prepare("SELECT email, password, account_type FROM newaccounts WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        if ($password === $row['password']) {
                            $_SESSION['login_attempts'] = 0;
                            unset($_SESSION['lockout_until']);
                            $_SESSION['email'] = $row['email'];
                            $_SESSION['account_type'] = $row['account_type'];
                            $_SESSION['just_logged_in'] = 'user';
                            if($remember_me){
                                setcookie('remember_email', $email, time() + (86400 * 30), "/");
                                setcookie('remember_password', $password, time() + (86400 * 30), "/");
                            } else {
                                setcookie('remember_email', '', time() - 3600, "/");
                                setcookie('remember_password', '', time() - 3600, "/");
                            }
                            emit_login_loading_and_redirect('Successful Login, Hello User!', 'user_dashboard.php');
                            exit();
                        } else {
                            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                            if ($_SESSION['login_attempts'] >= $max_attempts) {
                                $_SESSION['lockout_until'] = time() + $lockout_duration;
                                $locked = true;
                                $lock_remaining_seconds = max(0, $_SESSION['lockout_until'] - time());
                            } else {
                                $attempts_left = $max_attempts - $_SESSION['login_attempts'];
                                $login_error = 'Incorrect email or password. Attempts left: ' . $attempts_left . '.';
                            }
                        }
                    } else {
                        // Generic error
                        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                        if ($_SESSION['login_attempts'] >= $max_attempts) {
                            $_SESSION['lockout_until'] = time() + $lockout_duration;
                            $locked = true;
                            $lock_remaining_seconds = max(0, $_SESSION['lockout_until'] - time());
                        } else {
                            $attempts_left = $max_attempts - $_SESSION['login_attempts'];
                            $login_error = 'Incorrect email or password. Attempts left: ' . $attempts_left . '.';
                        }
                    }
                    $stmt->close();
                }
            }
        } else {
            if (!$email) $login_error = "Email is required!";
            if (!$password) $login_error = "Password is required!";
        }
        $conn->close();
    } else {
        if (isset($_SESSION['lockout_until']) && $_SESSION['lockout_until'] > time()) {
            $remaining = $_SESSION['lockout_until'] - time();
            $mins = floor($remaining / 60);
            $secs = $remaining % 60;
            $login_error = $mins . 'm ' . str_pad((string)$secs, 2, '0', STR_PAD_LEFT) . 's.';
        }
    }
}

// No captcha generation; OTP is sent via email for admin logins
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | SLATE</title>
<link rel="icon" type="image/png" href="logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Modern font import */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Background - enhanced gradient with subtle pattern */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
  background-attachment: fixed;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  padding: 20px;
  position: relative;
  overflow-x: hidden;
}

/* Subtle background pattern */
body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 20% 50%, rgba(15, 91, 127, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(20, 75, 107, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 40% 80%, rgba(15, 91, 127, 0.05) 0%, transparent 50%);
  pointer-events: none;
  z-index: -1;
}

/* Enhanced wrapper with glassmorphism */
.wrapper {
  display: flex;
  max-width: 1100px;
  width: 100%;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.4),
    0 0 0 1px rgba(255, 255, 255, 0.05);
  margin: auto;
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.wrapper:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 32px 64px -12px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.1);
}

/* Enhanced left panel with modern styling */
.left-container {
  flex: 1.4;
  background: linear-gradient(135deg, #0f5b7f 0%, #144b6b 50%, #0f5b7f 100%);
  color: #e6f1f8;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  font-size: 2rem;
  font-weight: 600;
  padding: 80px 60px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

/* Animated background elements */
.left-container::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.03), transparent);
  animation: shimmer 8s ease-in-out infinite;
  pointer-events: none;
}

@keyframes shimmer {
  0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
  50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.left-container img { 
  width: 70%; 
  max-width: 280px; 
  height: auto; 
  margin-bottom: 30px;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
  transition: transform 0.3s ease;
}

.left-container img:hover {
  transform: scale(1.05);
}

.left-container > div {
  z-index: 2;
  position: relative;
}

.left-container .system-title {
  font-size: 1.8rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  line-height: 1.2;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  margin-top: 20px;
}

/* Enhanced right panel with modern form styling */
.login-card {
  flex: 1;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(20px);
  color: #e2e8f0;
  padding: 50px 40px;
  animation: slideInRight 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

@keyframes slideInRight { 
  from { opacity: 0; transform: translateX(30px); } 
  to { opacity: 1; transform: translateX(0); } 
}

.login-card h2 {
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin-bottom: 2rem;
  color: #f8fafc;
  position: relative;
}

.login-card h2::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #0ea5ea, #2563eb);
  border-radius: 2px;
}

/* Modern form labels */
.login-card label {
  font-weight: 500;
  font-size: 0.875rem;
  color: #cbd5e1;
  margin-bottom: 8px;
  display: block;
  letter-spacing: 0.025em;
}

/* Enhanced form inputs */
.login-card .form-control {
  background: rgba(31, 41, 55, 0.8);
  border: 1.5px solid rgba(39, 52, 73, 0.6);
  color: #e5e7eb;
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 0.95rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(10px);
}

.login-card .form-control:focus {
  background: rgba(31, 41, 55, 0.95);
  border-color: #0ea5ea;
  box-shadow: 
    0 0 0 3px rgba(14, 165, 234, 0.1),
    0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-1px);
}

.login-card .form-control::placeholder { 
  color: #94a3b8; 
  font-weight: 400;
}

/* Enhanced primary button */
.btn-primary {
  background: linear-gradient(135deg, #0ea5ea 0%, #2563eb 100%);
  border: none;
  border-radius: 12px;
  padding: 14px 24px;
  font-weight: 600;
  font-size: 0.95rem;
  letter-spacing: 0.025em;
  box-shadow: 
    0 8px 20px rgba(37, 99, 235, 0.3),
    0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.btn-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 12px 28px rgba(37, 99, 235, 0.4),
    0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-primary:hover::before {
  left: 100%;
}

.btn-primary:active {
  transform: translateY(0);
}

/* Enhanced secondary button */
.btn-secondary { 
  background: rgba(51, 65, 85, 0.8);
  border: 1px solid rgba(71, 85, 105, 0.6);
  border-radius: 10px;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: rgba(71, 85, 105, 0.9);
  border-color: rgba(100, 116, 139, 0.8);
  transform: translateY(-1px);
}

.loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(180deg, rgba(44, 62, 80, 0.95) 0%, rgba(52, 73, 94, 0.98) 100%);
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .loading-overlay.show {
      opacity: 1;
      visibility: visible;
    }

    .loading-container {
      text-align: center;
      position: relative;
    }

    .loading-logo {
      width: 80px;
      height: 80px;
      margin-bottom: 2rem;
      animation: logoFloat 3s ease-in-out infinite;
    }

    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 3px solid rgba(0, 198, 255, 0.2);
      border-top: 3px solid #00c6ff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1.5rem;
      position: relative;
    }

    .loading-spinner::before {
      content: '';
      position: absolute;
      top: -3px;
      left: -3px;
      right: -3px;
      bottom: -3px;
      border: 3px solid transparent;
      border-top: 3px solid rgba(0, 198, 255, 0.4);
      border-radius: 50%;
      animation: spin 1.5s linear infinite reverse;
    }

    .loading-text {
      font-size: 1.2rem;
      font-weight: 600;
      color: #00c6ff;
      margin-bottom: 0.5rem;
      opacity: 0;
      animation: textFadeIn 0.5s ease-out 0.3s forwards;
    }

    .loading-subtext {
      font-size: 0.9rem;
      color: #b0bec5;
      opacity: 0;
      animation: textFadeIn 0.5s ease-out 0.6s forwards;
    }

    .loading-progress {
      width: 200px;
      height: 4px;
      background: rgba(0, 198, 255, 0.2);
      border-radius: 2px;
      margin: 1rem auto 0;
      overflow: hidden;
      position: relative;
    }

    .loading-progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #00c6ff, #0072ff);
      border-radius: 2px;
      width: 0%;
      animation: progressFill 2s ease-in-out infinite;
    }

    .loading-dots {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .loading-dot {
      width: 8px;
      height: 8px;
      background: #00c6ff;
      border-radius: 50%;
      animation: dotPulse 1.4s ease-in-out infinite both;
    }

    .loading-dot:nth-child(2) {
      animation-delay: 0.2s;
    }

    .loading-dot:nth-child(3) {
      animation-delay: 0.4s;
    }

    /* Keyframes for loading overlay */
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes logoFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    @keyframes textFadeIn {
      0% { opacity: 0; transform: translateY(6px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes progressFill {
      0% { width: 0%; }
      50% { width: 70%; }
      100% { width: 100%; }
    }

    @keyframes dotPulse {
      0%, 80%, 100% { transform: scale(0.8); opacity: 0.6; }
      40% { transform: scale(1.1); opacity: 1; }
    }

footer {
      text-align: center;
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.875rem;
      backdrop-filter: blur(10px);
    }


    .footer-links {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
      margin-top: 10px;
    }

    .footer-link {
      color: rgba(255, 255, 255, 0.6);
      text-decoration: none;
      font-size: 0.8rem;
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
    }

    .footer-link:hover {
      color: rgba(255, 255, 255, 0.9);
      background: rgba(255, 255, 255, 0.1);
      border-color: rgba(255, 255, 255, 0.2);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .footer-divider {
      color: rgba(255, 255, 255, 0.3);
      margin: 0 0.5rem;
    }

/* Enhanced captcha styling */
.captcha-display {
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.9) 0%, rgba(39, 52, 73, 0.8) 100%) !important;
  border: 1.5px solid rgba(14, 165, 234, 0.3) !important;
  border-radius: 12px !important;
  padding: 16px 20px !important;
  font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace !important;
  font-size: 20px !important;
  font-weight: 700 !important;
  color: #00c6ff !important;
  letter-spacing: 4px !important;
  text-align: center !important;
  min-width: 120px !important;
  backdrop-filter: blur(10px);
  box-shadow: 
    0 4px 12px rgba(0, 198, 255, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.captcha-display::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(0, 198, 255, 0.1), transparent);
  animation: captchaScan 3s ease-in-out infinite;
}

@keyframes captchaScan {
  0%, 100% { left: -100%; }
  50% { left: 100%; }
}

/* Enhanced form check styling */
.form-check {
  margin: 1.5rem 0;
}

.form-check-input {
  width: 1.2em;
  height: 1.2em;
  background-color: rgba(31, 41, 55, 0.8);
  border: 1.5px solid rgba(39, 52, 73, 0.6);
  border-radius: 6px;
  transition: all 0.3s ease;
}

.form-check-input:checked {
  background-color: #0ea5ea;
  border-color: #0ea5ea;
  box-shadow: 0 0 0 3px rgba(14, 165, 234, 0.2);
}

.form-check-input:focus {
  border-color: #0ea5ea;
  box-shadow: 0 0 0 3px rgba(14, 165, 234, 0.1);
}

.form-check-label {
  font-weight: 500;
  color: #cbd5e1;
  margin-left: 0.5rem;
  cursor: pointer;
  transition: color 0.3s ease;
}

.form-check-label:hover {
  color: #e2e8f0;
}

/* Enhanced alert styling */
.alert {
  border: none;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 1.5rem;
  font-weight: 500;
  backdrop-filter: blur(10px);
  border-left: 4px solid;
}

.alert-danger {
  background: rgba(239, 68, 68, 0.1);
  color: #fca5a5;
  border-left-color: #ef4444;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.alert-warning {
  background: rgba(245, 158, 11, 0.1);
  color: #fcd34d;
  border-left-color: #f59e0b;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
}

/* Enhanced captcha section */
.captcha-section {
  margin-bottom: 1.5rem;
}

.captcha-container {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.captcha-refresh-btn {
  min-width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.captcha-refresh-btn:hover {
  background: rgba(71, 85, 105, 0.9);
  transform: rotate(180deg);
}

/* Responsive design improvements */
@media (max-width: 768px) {
  .wrapper {
    flex-direction: column;
    max-width: 500px;
    border-radius: 20px;
  }
  
  .left-container {
    padding: 40px 30px;
    font-size: 1.5rem;
  }
  
  .left-container .system-title {
    font-size: 1.4rem;
  }
  
  .login-card {
    padding: 40px 30px;
  }
  
  .login-card h2 {
    font-size: 1.75rem;
  }
  
  .captcha-container {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }
  
  .captcha-display {
    margin-bottom: 10px;
  }
}

/* Loading state for buttons */
.btn-loading {
  position: relative;
  color: transparent !important;
}

.btn-loading::after {
  content: '';
  position: absolute;
  width: 20px;
  height: 20px;
  top: 50%;
  left: 50%;
  margin-left: -10px;
  margin-top: -10px;
  border: 2px solid #ffffff;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 1s linear infinite;
}
</style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-container">
      <img src="logo.png" alt="SLATE Logo" class="loading-logo">
      <div class="loading-spinner"></div>
      <div class="loading-text" id="loadingText">Loading...</div>
      <div class="loading-subtext" id="loadingSubtext">Please wait while we prepare your login</div>
      <div class="loading-progress">
        <div class="loading-progress-bar"></div>
      </div>
      <div class="loading-dots">
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
      </div>
    </div>
  </div>

<div class="wrapper">
    <!-- Left Container -->
    <div class="left-container">
        <div>
            <img src="logo.png" alt="SLATE Logo">
            <div class="system-title">FREIGHT MANAGEMENT SYSTEM</div>
        </div>
    </div>

    <!-- Right Login Card -->
    <div class="login-card">
        <h2 class="text-center mb-4">LOG IN</h2>

        <?php if (!empty($login_error)) : ?>
            <div class="alert alert-danger"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        

        <form action="" method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="text" class="form-control" name="email" placeholder="Enter Email" value="<?php echo htmlspecialchars($email); ?>" required <?php echo $locked ? 'disabled' : ''; ?>>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter Password" value="<?php echo htmlspecialchars($password); ?>" required <?php echo $locked ? 'disabled' : ''; ?>>
            </div>



            <button type="submit" class="btn btn-primary w-100" id="loginBtn" <?php echo $locked ? 'disabled' : ''; ?>>Login</button>
            <?php if ($locked): ?>
                <div class="mt-2 text-center" id="lockoutNote" style="color:#fcd34d; font-weight:500;">
                    Too many failed attempts. Try again in <span id="lockCountdown"></span>.
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<footer>
    <div>&copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.</div>
    <div class="footer-links">
        <div class="footer-terms">
            <a href="terms.php" class="footer-link">
                <i class="bi bi-file-text"></i>
                Terms & Conditions
            </a>
            <span class="footer-divider">•</span>
            <a href="policy.php" class="footer-link">
                <i class="bi bi-shield-check"></i>
                Privacy Policy
            </a>
        </div>
    </div>
  </footer>


<script>


// Enhanced page interactions and loading states
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action=""][method="POST"]') || document.querySelector('form');
    const overlay = document.getElementById('loadingOverlay');
    const submitBtn = document.querySelector('button[type="submit"]');
    const inputs = document.querySelectorAll('.form-control');
    const wrapper = document.querySelector('.wrapper');
    let lockedState = <?php echo $locked ? 'true' : 'false'; ?>;
    const initialLockRemaining = <?php echo (int)$lock_remaining_seconds; ?>;
    const emailInput = document.querySelector('input[name="email"]');

    // Live countdown for lockout and auto-enable when time elapses
    if (lockedState) {
        const countdownEl = document.getElementById('lockCountdown');
        const loginBtn = document.getElementById('loginBtn');
        const toggles = document.querySelectorAll('input.form-control');
        let remaining = initialLockRemaining;

        function setDisabled(state) {
            toggles.forEach(el => { el.disabled = state; });
            if (loginBtn) loginBtn.disabled = state;
        }

        function renderCountdown() {
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            if (countdownEl) countdownEl.textContent = `${m}m ${String(s).padStart(2,'0')}s`;
        }

        function tick() {
            if (remaining <= 0) {
                // Lock expired: enable controls without refresh
                setDisabled(false);
                const note = document.getElementById('lockoutNote');
                if (note) note.remove();
                // flip locked state and allow submissions
                lockedState = false;
                if (form) {
                    try { form.removeEventListener('submit', preventWhileLocked); } catch(e) {}
                }
                // Ensure captcha is visible and focused (it's required)
                try {
                    revealCaptcha();
                    const captchaInput = document.querySelector('input[name="captcha"]');
                    if (captchaInput) captchaInput.focus();
                } catch(_) {}
                return;
            }
            renderCountdown();
            remaining -= 1;
            setTimeout(tick, 1000);
        }

        // Ensure disabled while counting down
        setDisabled(true);
        tick();
    }

    // Show overlay immediately on initial page render
    if (overlay) {
        overlay.classList.add('show');
        // Hide overlay after all resources are fully loaded
        window.addEventListener('load', function() {
            setTimeout(function(){
                overlay.classList.remove('show');
            }, 300);
        });
    }

    // Enhanced form submission with loading state
    if (form && overlay && submitBtn) {
        // Dedicated handler we can remove later
        function preventWhileLocked(e){ e.preventDefault(); }
        if (lockedState) {
            form.addEventListener('submit', preventWhileLocked);
        }
        form.addEventListener('submit', function(e) {
            if (lockedState) { e.preventDefault(); return; }
            // Add loading state to button
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            
            // Show overlay
            overlay.classList.add('show');
            
            // Update loading text
            const loadingText = document.getElementById('loadingText');
            const loadingSubtext = document.getElementById('loadingSubtext');
            if (loadingText) loadingText.textContent = 'Authenticating...';
            if (loadingSubtext) loadingSubtext.textContent = 'Verifying your credentials';
        });
    }

    // Add floating label effect and enhanced focus states
    inputs.forEach(input => {
        // Enhanced focus animations
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
        
        // Add typing animation effect
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                this.classList.add('has-content');
            } else {
                this.classList.remove('has-content');
            }
        });
        
        // Initialize state
        if (input.value.length > 0) {
            input.classList.add('has-content');
        }
    });

    // Add entrance animation to wrapper
    if (wrapper) {
        setTimeout(() => {
            wrapper.style.opacity = '1';
            wrapper.style.transform = 'translateY(0)';
        }, 100);
    }

    // Add current year to footer
    const yearElement = document.getElementById('currentYear');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (form && !submitBtn.disabled && !lockedState) {
                form.submit();
            }
        }
        
        
    });

    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Initialize wrapper with initial hidden state for animation
    if (wrapper) {
        wrapper.style.opacity = '0';
        wrapper.style.transform = 'translateY(20px)';
        wrapper.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
    }
});
</script>

<!-- Simple modal for notices -->
<div id="otpModal" style="position:fixed; inset:0; display:none; align-items:center; justify-content:center; background: rgba(0,0,0,0.5); z-index:10000;">
  <div style="background:#111827; color:#e5e7eb; padding:20px; border-radius:12px; width:90%; max-width:420px; box-shadow:0 10px 30px rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.08);">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
      <h6 style="margin:0; font-weight:700; letter-spacing:0.3px;">Notification</h6>
      <button id="otpModalClose" type="button" class="btn btn-secondary btn-sm" style="padding:6px 10px;">Close</button>
    </div>
    <div id="otpModalMessage" style="font-size:0.95rem; line-height:1.5;"></div>
  </div>
  <script>
    function showNotice(msg, isError){
      const modal = document.getElementById('otpModal');
      const msgEl = document.getElementById('otpModalMessage');
      if (!modal || !msgEl) return alert(msg);
      msgEl.textContent = msg;
      msgEl.style.color = isError ? '#fca5a5' : '#e5e7eb';
      modal.style.display = 'flex';
    }
    (function(){
      const modal = document.getElementById('otpModal');
      const btn = document.getElementById('otpModalClose');
      if (btn) btn.addEventListener('click', ()=>{ modal.style.display='none'; });
      if (modal) modal.addEventListener('click', (e)=>{ if(e.target===modal){ modal.style.display='none'; } });
    })();
  </script>
</div>

</body>
</html>
