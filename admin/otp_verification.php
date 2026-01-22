<?php
session_start();
require_once __DIR__ . '/../includes/password_utils.php';

// Check if user came from login page with valid email/password verification
if (!isset($_SESSION['pending_otp_email']) || !isset($_SESSION['pending_otp_user_type'])) {
    header('Location: loginpage.php');
    exit();
}

// Initialize variables
$email = $_SESSION['pending_otp_email'];
$user_type = $_SESSION['pending_otp_user_type'];
$otp_error = '';
$otp_sent = false;

// Lockout config for OTP attempts
$max_otp_attempts = 3;
$otp_lockout_duration = 300; // 5 minutes

if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}
if (isset($_SESSION['otp_lockout_until']) && $_SESSION['otp_lockout_until'] <= time()) {
    unset($_SESSION['otp_lockout_until']);
    $_SESSION['otp_attempts'] = 0;
}

$otp_locked = false;
$otp_lock_remaining = 0;
if (isset($_SESSION['otp_lockout_until']) && $_SESSION['otp_lockout_until'] > time()) {
    $otp_locked = true;
    $otp_lock_remaining = max(0, $_SESSION['otp_lockout_until'] - time());
}

// Handle OTP form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$otp_locked) {
    $otp_code = trim($_POST['otp_code'] ?? '');
    
    if ($otp_code === '') {
        $otp_error = 'Please enter the OTP code.';
    } else {
        include('../connect.php');
        
        // Verify OTP
        $email_lower = strtolower($email);
        $otpStmt = $conn->prepare("SELECT id, otp, expires_at FROM login_otps WHERE email = ? AND otp = ? AND used = 0 ORDER BY id DESC LIMIT 1");
        $otpStmt->bind_param("ss", $email_lower, $otp_code);
        $otpStmt->execute();
        $otpRes = $otpStmt->get_result();
        $otpRow = $otpRes ? $otpRes->fetch_assoc() : null;
        $otpStmt->close();
        
        $notExpired = true;
        if ($otpRow && isset($otpRow['expires_at'])) {
            $notExpired = (strtotime($otpRow['expires_at']) > time());
        }
        
        if (!$otpRow || !$notExpired) {
            $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
            if ($_SESSION['otp_attempts'] >= $max_otp_attempts) {
                $_SESSION['otp_lockout_until'] = time() + $otp_lockout_duration;
                $otp_locked = true;
                $otp_lock_remaining = $otp_lockout_duration;
            } else {
                $attempts_left = $max_otp_attempts - $_SESSION['otp_attempts'];
                $otp_error = (!$otpRow) ? 'Invalid OTP. Attempts left: ' . $attempts_left . '.' : 'Expired OTP. Please request a new one.';
            }
        } else {
            // OTP is valid - mark as used and complete login
            $mark = $conn->prepare("UPDATE login_otps SET used = 1 WHERE id = ?");
            $mark->bind_param("i", $otpRow['id']);
            $mark->execute();
            $mark->close();
            
            // Get user details and complete login
            if ($user_type === 'admin') {
                $stmt = $conn->prepare("SELECT email, account_type FROM admin_list WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['account_type'] = $row['account_type'];
                    $_SESSION['just_logged_in'] = 'admin';
                    $stmt->close();
                    $conn->close();
                    
                    // Clear OTP session data
                    unset($_SESSION['pending_otp_email']);
                    unset($_SESSION['pending_otp_user_type']);
                    unset($_SESSION['otp_attempts']);
                    unset($_SESSION['otp_lockout_until']);
                    
                    header('Location: dashboard.php');
                    exit();
                }
                $stmt->close();
            }
        }
        $conn->close();
    }
}

// Handle resend OTP request
if (isset($_POST['resend_otp']) && !$otp_locked) {
    // Clear previous OTP attempts for fresh attempt
    $_SESSION['otp_attempts'] = 0;
    unset($_SESSION['otp_lockout_until']);
    
    // Call send_otp.php logic
    try {
        require_once __DIR__ . '/../connect.php';
        require_once __DIR__ . '/otp_mailer.php';
        
        $email_lower = strtolower($email);
        
        // Verify admin email exists
        $stmt = $conn->prepare('SELECT email FROM admin_list WHERE email = ?');
        $stmt->bind_param('s', $email_lower);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $stmt->close();
            
            // Generate new OTP
            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiryMins = 5;
            $expiresAt = date('Y-m-d H:i:s', time() + $expiryMins * 60);
            
            $ins = $conn->prepare('INSERT INTO login_otps (email, otp, expires_at) VALUES (?, ?, ?)');
            $ins->bind_param('sss', $email_lower, $otp, $expiresAt);
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
                if ($ok) {
                    $otp_sent = true;
                }
            }
        } else {
            $stmt->close();
        }
        $conn->close();
    } catch (Exception $e) {
        // Silent fail for security
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OTP Verification | SLATE</title>
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
.otp-card {
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

.otp-card h2 {
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  margin-bottom: 1rem;
  color: #f8fafc;
  position: relative;
}

.otp-card h2::after {
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

.otp-card .subtitle {
  color: #94a3b8;
  font-size: 0.95rem;
  margin-bottom: 2rem;
  text-align: center;
}

/* Modern form labels */
.otp-card label {
  font-weight: 500;
  font-size: 0.875rem;
  color: #cbd5e1;
  margin-bottom: 8px;
  display: block;
  letter-spacing: 0.025em;
}

/* Enhanced form inputs */
.otp-card .form-control {
  background: rgba(31, 41, 55, 0.8);
  border: 1.5px solid rgba(39, 52, 73, 0.6);
  color: #e5e7eb;
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 0.95rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(10px);
  text-align: center;
  font-size: 1.2rem;
  letter-spacing: 4px;
  font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
}

.otp-card .form-control:focus {
  background: rgba(31, 41, 55, 0.95);
  border-color: #0ea5ea;
  box-shadow: 
    0 0 0 3px rgba(14, 165, 234, 0.1),
    0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-1px);
}

.otp-card .form-control::placeholder { 
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

.alert-success {
  background: rgba(34, 197, 94, 0.1);
  color: #86efac;
  border-left-color: #22c55e;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
}

/* Email display */
.email-display {
  background: rgba(31, 41, 55, 0.8);
  border: 1px solid rgba(39, 52, 73, 0.6);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 1.5rem;
  text-align: center;
  color: #94a3b8;
}

.email-display strong {
  color: #e2e8f0;
  font-weight: 600;
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

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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
  
  .otp-card {
    padding: 40px 30px;
  }
  
  .otp-card h2 {
    font-size: 1.75rem;
  }
}
</style>
</head>
<body>

<div class="wrapper">
    <!-- Left Container -->
    <div class="left-container">
        <div>
            <img src="logo.png" alt="SLATE Logo">
            <div class="system-title">FREIGHT MANAGEMENT SYSTEM</div>
        </div>
    </div>

    <!-- Right OTP Card -->
    <div class="otp-card">
        <h2 class="text-center">TWO-FACTOR AUTHENTICATION</h2>
        <p class="subtitle">Enter the 6-digit code sent to your email</p>
        
        <div class="email-display">
            <i class="bi bi-envelope-fill me-2"></i>
            Code sent to: <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>

        <?php if (!empty($otp_error)) : ?>
            <div class="alert alert-danger"><?php echo $otp_error; ?></div>
        <?php endif; ?>
        
        <?php if ($otp_sent) : ?>
            <div class="alert alert-success">New OTP has been sent to your email.</div>
        <?php endif; ?>
        
        <?php if ($otp_locked): ?>
            <div class="alert alert-danger">
                Too many failed attempts. Please try again in <span id="otpLockCountdown"></span>.
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-4">
                <label>One-Time Password (OTP)</label>
                <input type="text" class="form-control" name="otp_code" placeholder="000000" maxlength="6" autocomplete="one-time-code" required <?php echo $otp_locked ? 'disabled' : ''; ?>>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3" id="verifyBtn" <?php echo $otp_locked ? 'disabled' : ''; ?>>
                Verify & Continue
            </button>
        </form>
        
        <div class="text-center">
            <form action="" method="POST" style="display:inline;">
                <button type="submit" name="resend_otp" class="btn btn-secondary" id="resendBtn" <?php echo $otp_locked ? 'disabled' : ''; ?>>
                    <i class="bi bi-arrow-clockwise me-2"></i>Resend OTP
                </button>
            </form>
        </div>
        
        <div class="text-center mt-3">
            <a href="loginpage.php" style="color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.querySelector('input[name="otp_code"]');
    const verifyBtn = document.getElementById('verifyBtn');
    const resendBtn = document.getElementById('resendBtn');
    const lockedState = <?php echo $otp_locked ? 'true' : 'false'; ?>;
    const initialLockRemaining = <?php echo (int)$otp_lock_remaining; ?>;
    
    // Auto-focus OTP input
    if (otpInput && !lockedState) {
        otpInput.focus();
    }
    
    // Only allow numbers in OTP input
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-submit when 6 digits are entered
            if (this.value.length === 6 && !lockedState) {
                verifyBtn.click();
            }
        });
        
        // Prevent paste of non-numeric content
        otpInput.addEventListener('paste', function(e) {
            const pasteData = (e.clipboardData || window.clipboardData).getData('text');
            const numericData = pasteData.replace(/[^0-9]/g, '');
            if (numericData !== pasteData) {
                e.preventDefault();
                // Only paste numeric characters
                document.execCommand('insertText', false, numericData);
            }
        });
    }
    
    // OTP lockout countdown
    if (lockedState) {
        const countdownEl = document.getElementById('otpLockCountdown');
        let remaining = initialLockRemaining;
        
        function renderCountdown() {
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            if (countdownEl) countdownEl.textContent = `${m}m ${String(s).padStart(2,'0')}s`;
        }
        
        function tick() {
            if (remaining <= 0) {
                // Lock expired: reload page to enable controls
                window.location.reload();
                return;
            }
            renderCountdown();
            remaining -= 1;
            setTimeout(tick, 1000);
        }
        
        tick();
    }
    
    // Add loading state to verify button
    const verifyForm = document.querySelector('form[action=""][method="POST"]:not([name])');
    if (verifyForm && verifyBtn) {
        verifyForm.addEventListener('submit', function() {
            if (!lockedState) {
                verifyBtn.classList.add('btn-loading');
                verifyBtn.disabled = true;
            }
        });
    }
    
    // Add loading state to resend button
    const resendForm = document.querySelector('form[name="resend_otp"]');
    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function() {
            if (!lockedState) {
                resendBtn.classList.add('btn-loading');
                resendBtn.disabled = true;
            }
        });
    }
});
</script>

</body>
</html>
