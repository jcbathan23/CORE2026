<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../connect.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../admin/functions.php';

function send_json($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit();
}

function get_json_body() {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function require_admin() {
    $acct = isset($_SESSION['account_type']) ? (int)$_SESSION['account_type'] : null;
    if (!isset($_SESSION['email']) || $acct === null || $acct === 3) {
        send_json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = $path === '' ? [] : explode('/', $path);
$scriptIndex = array_search('providers_api.php', $parts, true);
$sub = $scriptIndex === false ? [] : array_slice($parts, $scriptIndex + 1);

$resource = $sub[0] ?? 'providers';
$id = $sub[1] ?? null;
$action = $sub[2] ?? null;

try {
    switch ($method) {
        case 'GET':
            require_admin();

            if ($resource === 'pending') {
                list_pending();
            }

            if ($resource === 'active') {
                list_active();
            }

            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'POST':
            require_admin();

            if ($resource === 'pending' && $id !== null && $action !== null) {
                $normalized = strtolower((string)$action);
                if ($normalized === 'approve') {
                    approve_pending((int)$id);
                }
                if ($normalized === 'reject') {
                    reject_pending((int)$id);
                }
            }

            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        default:
            send_json(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function list_pending() {
    global $conn;

    $sql = 'SELECT * FROM pending_service_provider ORDER BY registration_id DESC';
    $res = $conn->query($sql);

    if (!$res) {
        send_json(['success' => false, 'message' => 'Failed to fetch pending providers'], 500);
    }

    $items = [];
    while ($row = $res->fetch_assoc()) {
        unset($row['password']);
        $items[] = $row;
    }

    send_json(['success' => true, 'pending' => $items]);
}

function list_active() {
    global $conn;

    $sql = 'SELECT provider_id, company_name, email, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, status, created_at FROM active_service_provider ORDER BY provider_id DESC';
    $res = $conn->query($sql);

    if (!$res) {
        send_json(['success' => false, 'message' => 'Failed to fetch active providers'], 500);
    }

    $items = [];
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }

    send_json(['success' => true, 'active' => $items]);
}

function approve_pending($registrationId) {
    global $conn;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare('SELECT * FROM pending_service_provider WHERE registration_id = ?');
        if (!$stmt) {
            throw new Exception('Failed to prepare pending lookup');
        }

        $stmt->bind_param('i', $registrationId);
        $stmt->execute();
        $res = $stmt->get_result();
        $provider = $res->fetch_assoc();
        $stmt->close();

        if (!$provider) {
            throw new Exception('Provider not found');
        }

        $password = $provider['password'];

        $insert = $conn->prepare('INSERT INTO active_service_provider (company_name, email, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$insert) {
            throw new Exception('Failed to prepare active insert');
        }

        $insert->bind_param(
            'ssssssssss',
            $provider['company_name'],
            $provider['email'],
            $provider['contact_person'],
            $provider['contact_number'],
            $provider['address'],
            $provider['services'],
            $provider['iso_certified'],
            $provider['business_permit'],
            $provider['company_profile'],
            $password
        );

        if (!$insert->execute()) {
            throw new Exception('Failed to approve provider');
        }

        $insert->close();

        $delete = $conn->prepare('DELETE FROM pending_service_provider WHERE registration_id = ?');
        if (!$delete) {
            throw new Exception('Failed to prepare pending delete');
        }

        $delete->bind_param('i', $registrationId);
        if (!$delete->execute()) {
            throw new Exception('Failed to remove from pending');
        }

        $delete->close();

        addNotification(
            $conn,
            'Service Provider approved: ' . $provider['company_name'] . ' | Contact: ' . $provider['contact_person'],
            'service_provider',
            'active_providers.php'
        );

        if (function_exists('sendApprovalEmail') && !empty($provider['email'])) {
            try {
                sendApprovalEmail(
                    $provider['email'],
                    $provider['contact_person'],
                    $provider['company_name'],
                    $provider['email'],
                    $password
                );
            } catch (Throwable $e) {
                error_log('Failed to send approval email: ' . $e->getMessage());
            }
        }

        $conn->commit();
        send_json(['success' => true, 'message' => 'Provider approved']);

    } catch (Throwable $e) {
        $conn->rollback();
        send_json(['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()], 400);
    }
}

function reject_pending($registrationId) {
    global $conn;

    $data = get_json_body();
    $remarks = isset($data['remarks']) ? trim((string)$data['remarks']) : '';
    $rejectionReason = isset($data['rejection_reason']) ? trim((string)$data['rejection_reason']) : '';

    $stmtFetch = $conn->prepare('SELECT company_name, email, contact_person FROM pending_service_provider WHERE registration_id = ?');
    if (!$stmtFetch) {
        send_json(['success' => false, 'message' => 'Failed to prepare pending lookup'], 500);
    }

    $stmtFetch->bind_param('i', $registrationId);
    $stmtFetch->execute();
    $res = $stmtFetch->get_result();
    $provider = $res->fetch_assoc();
    $stmtFetch->close();

    if (!$provider) {
        send_json(['success' => false, 'message' => 'Provider not found'], 404);
    }

    $stmt = $conn->prepare('DELETE FROM pending_service_provider WHERE registration_id = ?');
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare delete'], 500);
    }

    $stmt->bind_param('i', $registrationId);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to reject provider'], 500);
    }

    $stmt->close();

    $reasonText = $rejectionReason !== '' ? $rejectionReason : 'Rejected';

    $notificationMessage = 'Service Provider rejected: ' . $provider['company_name'] . ' | Reason: ' . $reasonText . ($remarks !== '' ? ' | Remarks: ' . $remarks : '') . ' | Contact: ' . $provider['contact_person'];
    addNotification($conn, $notificationMessage, 'service_provider', 'pending_providers.php');

    if (function_exists('sendRejectionEmail') && !empty($provider['email'])) {
        try {
            sendRejectionEmail($provider['email'], $provider['contact_person'], $provider['company_name'], $reasonText, $remarks);
        } catch (Throwable $e) {
            error_log('Failed to send rejection email: ' . $e->getMessage());
        }
    }

    send_json(['success' => true, 'message' => 'Provider rejected']);
}
