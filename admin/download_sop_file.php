<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../connect.php';

$SOP_IDLE_LOCK_SECONDS = 2 * 60;

$sop_id = isset($_GET['sop_id']) ? (int)$_GET['sop_id'] : 0;
if ($sop_id <= 0) {
    http_response_code(400);
    echo 'Invalid SOP.';
    exit;
}

$last = isset($_SESSION['sop_last_activity']) ? (int)$_SESSION['sop_last_activity'] : 0;
$reauthUntil = isset($_SESSION['sop_reauth_until']) ? (int)$_SESSION['sop_reauth_until'] : 0;
$idleTooLong = ($last > 0) ? ((time() - $last) > $SOP_IDLE_LOCK_SECONDS) : false;
$hasRecentReauth = ($reauthUntil > time());

// Require re-auth only after inactivity window has elapsed (or if we have no last-activity record yet)
if ($idleTooLong && !$hasRecentReauth) {
    http_response_code(403);
    echo 'Re-auth required.';
    exit;
}

$stmt = $conn->prepare('SELECT file_path FROM sop_documents WHERE sop_id = ? LIMIT 1');
$stmt->bind_param('i', $sop_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

$file_path = $row['file_path'] ?? '';
if ($file_path === '') {
    http_response_code(404);
    echo 'No file uploaded.';
    exit;
}

if (strpos($file_path, 'uploads/sop/') !== 0) {
    http_response_code(400);
    echo 'Invalid file path.';
    exit;
}

$baseDir = realpath(__DIR__ . '/../uploads/sop');
$fullPath = realpath(__DIR__ . '/../' . $file_path);
if ($baseDir === false || $fullPath === false || strpos($fullPath, $baseDir) !== 0) {
    http_response_code(400);
    echo 'Invalid file path.';
    exit;
}

if (!is_file($fullPath) || !is_readable($fullPath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

// Successful access counts as activity
$_SESSION['sop_last_activity'] = time();

$downloadName = basename($fullPath);

$inline = isset($_GET['inline']) && (string)$_GET['inline'] === '1';
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = (string)@$finfo->file($fullPath);
if ($mime === '') {
    $mime = 'application/octet-stream';
}

header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $downloadName . '"');
header('Content-Length: ' . (string)filesize($fullPath));
readfile($fullPath);
exit;
