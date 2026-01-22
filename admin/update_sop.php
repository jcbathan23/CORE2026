<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: view_sop.php');
  exit;
}

$sop_id  = intval($_POST['sop_id'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$status  = trim($_POST['status'] ?? '');
$content = trim($_POST['content'] ?? '');

if ($sop_id <= 0 || $title === '' || $category === '' || $status === '' || $content === '') {
  header('Location: view_sop.php?error=' . urlencode('Please fill in all required fields.'));
  exit;
}

// Handle file upload if a new file is selected
$file_path = null;
if (isset($_FILES['file']) && is_array($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
  if (!isset($_FILES['file']['size']) || (int)$_FILES['file']['size'] > 3 * 1024 * 1024) {
    header('Location: view_sop.php?error=' . urlencode('File is too large. Maximum size is 3MB.'));
    exit;
  }

  $originalName = (string)($_FILES['file']['name'] ?? '');
  $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
  $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
  if (!in_array($ext, $allowedExts, true)) {
    header('Location: view_sop.php?error=' . urlencode('Invalid file type. Please upload a PDF or image (JPG/PNG/WEBP).'));
    exit;
  }

  $tmpName = (string)($_FILES['file']['tmp_name'] ?? '');
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $tmpName !== '' ? (string)@$finfo->file($tmpName) : '';
  $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
  if ($mime === '' || !in_array($mime, $allowedMimes, true)) {
    header('Location: view_sop.php?error=' . urlencode('Invalid file content. Please upload a valid PDF or image.'));
    exit;
  }

  $uploadDir = __DIR__ . '/../uploads/sop/';
  if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
  $base = preg_replace('/[^A-Za-z0-9_\.-]/','_', basename($_FILES['file']['name']));
  $finalName = time() . '_' . $base;
  $targetFile = $uploadDir . $finalName;
  if (!@move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
    header('Location: view_sop.php?error=' . urlencode('Error uploading file.'));
    exit;
  }
  // Store as relative path used elsewhere
  $file_path = 'uploads/sop/' . $finalName;
}

// Prepare SQL
if ($file_path) {
  $sql = "UPDATE sop_documents SET title=?, category=?, status=?, content=?, file_path=? WHERE sop_id=?";
  $stmt = $conn->prepare($sql);
  if (!$stmt) { header('Location: view_sop.php?error=' . urlencode('Prepare failed.')); exit; }
  $stmt->bind_param("sssssi", $title, $category, $status, $content, $file_path, $sop_id);
} else {
  $sql = "UPDATE sop_documents SET title=?, category=?, status=?, content=? WHERE sop_id=?";
  $stmt = $conn->prepare($sql);
  if (!$stmt) { header('Location: view_sop.php?error=' . urlencode('Prepare failed.')); exit; }
  $stmt->bind_param("ssssi", $title, $category, $status, $content, $sop_id);
}

if ($stmt->execute()) {
  header('Location: view_sop.php?updated=1');
  exit;
} else {
  header('Location: view_sop.php?error=' . urlencode('Error updating SOP.'));
  exit;
}

?>
