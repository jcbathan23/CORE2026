<?php
require_once '../connect.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$email = isset($_SESSION['email']) ? $_SESSION['email'] : null;
if (!$email) {
    header('Location: loginpage.php');
    exit;
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS admin_profiles (
    email VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    avatar VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Handle file upload if present
$avatarFileName = null;
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

if (isset($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
    $file = $_FILES['avatar'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (isset($allowed[$mime])) {
            if ($file['size'] <= 5 * 1024 * 1024) { // 5MB
                $ext = $allowed[$mime];
                $avatarFileName = time() . '_avatar.' . $ext;
                $dest = $uploadDir . $avatarFileName;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $avatarFileName = null; // fallback
                }
            }
        }
    }
}

// If no new file, keep existing avatar
if ($avatarFileName === null) {
    $stmt = $conn->prepare('SELECT avatar FROM admin_profiles WHERE email = ?');
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $avatarFileName = $row['avatar'];
        }
    }
    $stmt->close();
}

// Upsert profile
$stmt = $conn->prepare('INSERT INTO admin_profiles (email, name, phone, avatar) VALUES (?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE name = VALUES(name), phone = VALUES(phone), avatar = VALUES(avatar)');
$stmt->bind_param('ssss', $email, $name, $phone, $avatarFileName);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    header('Location: profile.php?profile=updated');
} else {
    header('Location: profile.php?profile=error');
}
exit;
