<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$password = trim((string)($_POST['password'] ?? ''));
if ($password === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Password is required']);
    exit;
}

$email = isset($_SESSION['email']) ? (string)$_SESSION['email'] : '';
$acct = isset($_SESSION['account_type']) ? (int)$_SESSION['account_type'] : 0;
if ($email === '' || $acct === 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

$ok = false;

if ($acct === 1) {
    $stmt = $conn->prepare('SELECT password FROM admin_list WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = ($res ? $res->fetch_assoc() : null)) {
        $ok = hash_equals((string)$row['password'], $password);
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare('SELECT password FROM newaccounts WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = ($res ? $res->fetch_assoc() : null)) {
        $ok = hash_equals((string)$row['password'], $password);
    }
    $stmt->close();
}

if (!$ok) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Incorrect password']);
    exit;
}

$_SESSION['sop_reauth_until'] = time() + (5 * 60);
$_SESSION['sop_last_activity'] = time();

echo json_encode(['ok' => true]);
exit;
