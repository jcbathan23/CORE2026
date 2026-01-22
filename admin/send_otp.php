<?php
session_start();

// Force JSON and suppress HTML error display
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('html_errors', '0');
ini_set('log_errors', '1');

// Buffer cleanup helper to prevent stray output
function json_out($payload, $code = 200) {
    while (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code($code);
    echo json_encode($payload);
    exit;
}
function json_fail($msg, $code = 400) { json_out(['success' => false, 'message' => $msg], $code); }
function json_ok($data = []) { json_out(array_merge(['success' => true], $data), 200); }

ob_start();

try {
    require_once __DIR__ . '/../connect.php';
    require_once __DIR__ . '/otp_mailer.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_fail('Method not allowed', 405); }

    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    if ($email === '') { json_fail('Email is required'); }

    // Check if email exists in admin_list only (OTP is for admin dashboard)
    $stmt = $conn->prepare('SELECT email FROM admin_list WHERE email = ?');
    if (!$stmt) { json_fail('DB error: prepare failed'); }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) { json_fail('Admin email not found'); }
    $stmt->close();

    // Ensure table exists
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

    // Generate OTP
    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiryMins = 5;
    $expiresAt = date('Y-m-d H:i:s', time() + $expiryMins * 60);

    $ins = $conn->prepare('INSERT INTO login_otps (email, otp, expires_at) VALUES (?, ?, ?)');
    if (!$ins) { json_fail('DB error: prepare insert failed'); }
    $ins->bind_param('sss', $email, $otp, $expiresAt);
    if (!$ins->execute()) { json_fail('Failed to save OTP'); }
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

    list($ok, $msg) = send_smtp_mail($email, '', $subject, $body);
    if (!$ok) { json_fail('SMTP send failed: ' . $msg, 500); }

    json_ok(['message' => 'OTP sent']);
} catch (Throwable $e) {
    json_fail('Server error: ' . $e->getMessage(), 500);
}
