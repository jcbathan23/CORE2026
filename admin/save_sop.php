<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($title === '' || $category === '' || $content === '' || $status === '') {
        header('Location: view_sop.php?error=' . urlencode('Please fill in all required fields.'));
        exit;
    }

    $file_path = NULL;
    if (!empty($_FILES['sop_file']['name'])) {
        if (!isset($_FILES['sop_file']['error']) || $_FILES['sop_file']['error'] !== UPLOAD_ERR_OK) {
            header('Location: view_sop.php?error=' . urlencode('File upload failed.'));
            exit;
        }
        if (!isset($_FILES['sop_file']['size']) || (int)$_FILES['sop_file']['size'] > 3 * 1024 * 1024) {
            header('Location: view_sop.php?error=' . urlencode('File is too large. Maximum size is 3MB.'));
            exit;
        }

        $originalName = (string)($_FILES['sop_file']['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            header('Location: view_sop.php?error=' . urlencode('Invalid file type. Please upload a PDF or image (JPG/PNG/WEBP).'));
            exit;
        }

        $tmpName = (string)($_FILES['sop_file']['tmp_name'] ?? '');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $tmpName !== '' ? (string)@$finfo->file($tmpName) : '';
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        if ($mime === '' || !in_array($mime, $allowedMimes, true)) {
            header('Location: view_sop.php?error=' . urlencode('Invalid file content. Please upload a valid PDF or image.'));
            exit;
        }

        $targetDir = __DIR__ . "/../uploads/sop/";
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . preg_replace('/[^A-Za-z0-9_\.-]/', '_', basename($_FILES["sop_file"]["name"]));
        $targetFilePath = $targetDir . $fileName;

        if (is_uploaded_file($_FILES["sop_file"]["tmp_name"])) {
            if (@move_uploaded_file($_FILES["sop_file"]["tmp_name"], $targetFilePath)) {
                // Store path relative to project root like 'uploads/sop/xxx'
                $file_path = "uploads/sop/" . $fileName;
            } else {
                $err = 'File upload failed.';
                header('Location: view_sop.php?error=' . urlencode($err));
                exit;
            }
        }
    }

    $sql = "INSERT INTO sop_documents (title, category, content, file_path, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $err = 'Prepare failed: ' . $conn->error;
        header('Location: view_sop.php?error=' . urlencode($err));
        exit;
    }
    $stmt->bind_param("sssss", $title, $category, $content, $file_path, $status);

    if ($stmt->execute()) {
        addNotification($conn, "New SOP document created: '$title' ($category)", 'success', 'view_sop.php');
        header('Location: view_sop.php?success=1');
        exit;
    } else {
        $err = 'DB Error: ' . $stmt->error;
        header('Location: view_sop.php?error=' . urlencode($err));
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
