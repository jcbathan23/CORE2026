<?php
// Simple test script to verify OTP flow setup
session_start();

echo "<h1>OTP Flow Test</h1>";

// Test database connection
try {
    require_once __DIR__ . '/../connect.php';
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit();
}

// Test OTP table structure
$result = $conn->query("DESCRIBE login_otps");
if ($result) {
    echo "<p style='color: green;'>✓ OTP table exists</p>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ OTP table check failed</p>";
}

// Test SMTP configuration
try {
    $cfg = require __DIR__ . '/smtp_config.php';
    $required = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'FROM_EMAIL'];
    $missing = [];
    foreach ($required as $key) {
        if (empty($cfg[$key])) $missing[] = $key;
    }
    
    if (empty($missing)) {
        echo "<p style='color: green;'>✓ SMTP configuration complete</p>";
    } else {
        echo "<p style='color: orange;'>⚠ SMTP configuration missing: " . implode(', ', $missing) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ SMTP configuration error: " . $e->getMessage() . "</p>";
}

// Test OTP mailer function
try {
    require_once __DIR__ . '/otp_mailer.php';
    echo "<p style='color: green;'>✓ OTP mailer loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ OTP mailer failed: " . $e->getMessage() . "</p>";
}

// Test admin user existence
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM admin_list");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$adminCount = $row['count'];
echo "<p style='color: green;'>✓ Found {$adminCount} admin users in database</p>";

echo "<h2>Test Flow Summary</h2>";
echo "<p><strong>New Login Flow:</strong></p>";
echo "<ol>";
echo "<li>User enters email and password on loginpage.php</li>";
echo "<li>System detects admin email and verifies password</li>";
echo "<li>If password correct, generates OTP and sends via email</li>";
echo "<li>Redirects to otp_verification.php</li>";
echo "<li>User enters OTP on verification page</li>";
echo "<li>If OTP valid, completes login and redirects to dashboard</li>";
echo "</ol>";

echo "<p><a href='loginpage.php'>Go to Login Page</a></p>";
?>
