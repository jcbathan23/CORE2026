<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../includes/password_utils.php';

// Only allow this script to run from command line or localhost for security
$allowed = false;
if (php_sapi_name() === 'cli') {
    $allowed = true;
} else {
    $whitelist = array('127.0.0.1', '::1');
    if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
        $allowed = true;
    }
}

if (!$allowed) {
    die('Access denied. This script can only be run from localhost or command line.');
}

echo "Admin Password Update Tool\n";
echo "========================\n\n";

// Get admin email and new password from command line or form
$email = '';
$new_password = '';

if (php_sapi_name() === 'cli') {
    // Command line mode
    if ($argc < 3) {
        die("Usage: php update_admin_password.php <email> <new_password>\n");
    }
    $email = $argv[1];
    $new_password = $argv[2];
} else {
    // Web mode
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $new_password = $_POST['password'] ?? '';
    } else {
        // Show form
        echo '<form method="post">';
        echo 'Email: <input type="email" name="email" required><br>';
        echo 'New Password: <input type="password" name="password" required><br>';
        echo '<input type="submit" value="Update Password">';
        echo '</form>';
        exit;
    }
}

// Validate input
if (empty($email) || empty($new_password)) {
    die("Error: Email and password are required\n");
}

// Hash the new password
$hashed_password = hashPassword($new_password);

// Update the database
try {
    $stmt = $conn->prepare("UPDATE admin_list SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    $result = $stmt->execute();
    
    if ($result && $stmt->affected_rows > 0) {
        echo "Password updated successfully for admin: " . htmlspecialchars($email) . "\n";
        echo "You can now log in with the new password.\n";
        
        // Security recommendation
        echo "\nSecurity Note: " . __FILE__ . " should be deleted after use.\n";
    } else {
        echo "Error: No admin found with email: " . htmlspecialchars($email) . "\n";
    }
    
    $stmt->close();
} catch (Exception $e) {
    die("Error updating password: " . $e->getMessage() . "\n");
}

$conn->close();

// Self-destruct if running from command line
if (php_sapi_name() === 'cli') {
    echo "\nWould you like to delete this script for security? (y/n): ";
    $handle = fopen('php://stdin', 'r');
    $response = trim(fgets($handle));
    if (strtolower($response) === 'y') {
        if (unlink(__FILE__)) {
            echo "Script deleted successfully.\n";
        } else {
            echo "Failed to delete script. Please delete " . __FILE__ . " manually.\n";
        }
    }
}
?>
