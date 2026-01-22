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

function send_json($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit();
}

function get_enum_values($table, $column) {
    global $conn;

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
    if ($table === '' || $column === '') {
        return null;
    }

    $sql = "SHOW COLUMNS FROM `$table` LIKE ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row || empty($row['Type'])) {
        return null;
    }

    $type = strtolower((string)$row['Type']);
    if (strpos($type, 'enum(') !== 0) {
        return null;
    }

    if (!preg_match('/^enum\((.*)\)$/i', $row['Type'], $m)) {
        return null;
    }

    $inner = $m[1];
    preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $inner, $matches);
    $values = [];
    foreach ($matches[1] as $v) {
        $values[] = stripcslashes($v);
    }
    return $values;
}

function normalize_status_for_column($table, $column, $status) {
    $status = trim((string)$status);
    if ($status === '') {
        return '';
    }

    $allowed = get_enum_values($table, $column);
    if ($allowed === null) {
        return $status;
    }

    foreach ($allowed as $v) {
        if (strcasecmp($v, $status) === 0) {
            return $v;
        }
    }

    send_json([
        'success' => false,
        'message' => 'Invalid status for ' . $table . '.' . $column,
        'allowed_statuses' => $allowed
    ], 400);
}

function try_normalize_status_for_column($table, $column, $status) {
    $status = trim((string)$status);
    if ($status === '') {
        return '';
    }

    $allowed = get_enum_values($table, $column);
    if ($allowed === null) {
        return $status;
    }

    foreach ($allowed as $v) {
        if (strcasecmp($v, $status) === 0) {
            return $v;
        }
    }

    return null;
}

function get_json_body() {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function get_provider_id_for_session_email($email) {
    global $conn;
    $stmt = $conn->prepare('SELECT provider_id FROM active_service_provider WHERE email = ?');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($providerId);
    $providerId = null;
    $stmt->fetch();
    $stmt->close();
    return $providerId ? (int)$providerId : null;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = $path === '' ? [] : explode('/', $path);
$scriptIndex = array_search('schedules_api.php', $parts, true);
$sub = $scriptIndex === false ? [] : array_slice($parts, $scriptIndex + 1);

$resource = $sub[0] ?? 'schedules';
$id = $sub[1] ?? ($_GET['id'] ?? null);
$action = $sub[2] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($resource === 'schedules') {
                if ($id !== null) {
                    get_schedule((int)$id);
                }
                list_schedules();
            }
            if ($resource === 'schedule') {
                if ($id === null) {
                    send_json(['success' => false, 'message' => 'Missing schedule id'], 400);
                }
                get_schedule((int)$id);
            }
            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'POST':
            if ($resource === 'schedules' && $id === null) {
                create_schedule();
            }

            if (($resource === 'schedules' || $resource === 'schedule') && $id !== null && $action !== null) {
                $normalized = strtolower((string)$action);
                if ($normalized === 'status') {
                    update_schedule_status((int)$id);
                }
            }

            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'PUT':
            if (($resource === 'schedules' || $resource === 'schedule') && $id !== null) {
                update_schedule((int)$id);
            }
            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'DELETE':
            if (($resource === 'schedules' || $resource === 'schedule') && $id !== null) {
                delete_schedule((int)$id);
            }
            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        default:
            send_json(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function list_schedules() {
    global $conn;

    $status = $_GET['status'] ?? 'all';
    $providerId = $_GET['provider_id'] ?? 'all';
    $routeId = $_GET['route_id'] ?? 'all';
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 200;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $where = [];
    $params = [];
    $types = '';

    if ($status !== 'all') {
        $where[] = 's.status = ?';
        $params[] = $status;
        $types .= 's';
    }

    if ($providerId !== 'all') {
        $where[] = 's.provider_id = ?';
        $params[] = (int)$providerId;
        $types .= 'i';
    }

    if ($routeId !== 'all') {
        $where[] = 's.route_id = ?';
        $params[] = (int)$routeId;
        $types .= 'i';
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
        SELECT
            s.schedule_id,
            s.rate_id,
            s.route_id,
            s.provider_id,
            s.sop_id,
            s.schedule_date,
            s.schedule_time,
            s.total_rate,
            s.status,
            s.created_at,
            sp.company_name AS provider_name,
            r.carrier_type,
            r.distance_km,
            r.eta_min,
            np1.point_name AS origin_name,
            np2.point_name AS destination_name
        FROM schedules s
        LEFT JOIN active_service_provider sp ON s.provider_id = sp.provider_id
        LEFT JOIN routes r ON s.route_id = r.route_id
        LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
        LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
        $whereSql
        ORDER BY s.schedule_date DESC, s.schedule_time DESC
        LIMIT ? OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query'], 500);
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $items = [];
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }

    send_json(['success' => true, 'schedules' => $items, 'limit' => $limit, 'offset' => $offset]);
}

function get_schedule($scheduleId) {
    global $conn;

    $sql = "
        SELECT
            s.schedule_id,
            s.rate_id,
            s.route_id,
            s.provider_id,
            s.sop_id,
            s.schedule_date,
            s.schedule_time,
            s.total_rate,
            s.status,
            s.created_at,
            sp.company_name AS provider_name,
            r.carrier_type,
            r.distance_km,
            r.eta_min,
            np1.point_name AS origin_name,
            np2.point_name AS destination_name
        FROM schedules s
        LEFT JOIN active_service_provider sp ON s.provider_id = sp.provider_id
        LEFT JOIN routes r ON s.route_id = r.route_id
        LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
        LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
        WHERE s.schedule_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query'], 500);
    }

    $stmt->bind_param('i', $scheduleId);
    $stmt->execute();
    $res = $stmt->get_result();

    $row = $res->fetch_assoc();
    if (!$row) {
        send_json(['success' => false, 'message' => 'Schedule not found'], 404);
    }

    send_json(['success' => true, 'schedule' => $row]);
}

function create_schedule() {
    global $conn;

    $data = get_json_body();

    $rateId = isset($data['rate_id']) ? (int)$data['rate_id'] : 0;
    $routeId = isset($data['route_id']) ? (int)$data['route_id'] : 0;
    $sopId = isset($data['sop_id']) ? (int)$data['sop_id'] : 0;
    $scheduleDate = isset($data['schedule_date']) ? (string)$data['schedule_date'] : '';
    $scheduleTime = isset($data['schedule_time']) ? (string)$data['schedule_time'] : '';
    $providerId = isset($data['provider_id']) ? (int)$data['provider_id'] : 0;
    $totalRate = isset($data['total_rate']) ? (float)$data['total_rate'] : 0;

    if ($rateId <= 0 || $routeId <= 0 || $sopId <= 0 || $providerId <= 0 || $scheduleDate === '' || $scheduleTime === '' || $totalRate <= 0) {
        send_json(['success' => false, 'message' => 'Missing or invalid fields'], 400);
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare('INSERT INTO schedules (rate_id, route_id, provider_id, sop_id, schedule_date, schedule_time, total_rate) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new Exception('Failed to prepare insert');
        }

        $stmt->bind_param('iiiissd', $rateId, $routeId, $providerId, $sopId, $scheduleDate, $scheduleTime, $totalRate);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create schedule');
        }

        $scheduleId = (int)$conn->insert_id;
        $stmt->close();

        $normalizedRateStatus = try_normalize_status_for_column('calculated_rates', 'status', 'scheduled');
        if ($normalizedRateStatus !== null && $normalizedRateStatus !== '') {
            $stmt = $conn->prepare('UPDATE calculated_rates SET status = ? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('si', $normalizedRateStatus, $rateId);
                $stmt->execute();
                $stmt->close();
            }
        }
        $conn->commit();

        send_json(['success' => true, 'schedule_id' => $scheduleId], 201);

    } catch (Throwable $e) {
        $conn->rollback();
        send_json(['success' => false, 'message' => 'Failed to create schedule: ' . $e->getMessage()], 500);
    }
}

function update_schedule($scheduleId) {
    global $conn;

    $data = get_json_body();

    $allowed = ['schedule_date', 'schedule_time', 'provider_id', 'sop_id', 'total_rate', 'status'];
    $set = [];
    $params = [];
    $types = '';

    foreach ($allowed as $field) {
        if (!array_key_exists($field, $data)) {
            continue;
        }

        $value = $data[$field];

        if ($field === 'provider_id' || $field === 'sop_id') {
            $value = (int)$value;
            $types .= 'i';
        } elseif ($field === 'total_rate') {
            $value = (float)$value;
            $types .= 'd';
        } elseif ($field === 'status') {
            $value = normalize_status_for_column('schedules', 'status', $value);
            $types .= 's';
        } else {
            $value = (string)$value;
            $types .= 's';
        }

        $set[] = "$field = ?";
        $params[] = $value;
    }

    if (!$set) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }

    $types .= 'i';
    $params[] = $scheduleId;

    $sql = 'UPDATE schedules SET ' . implode(', ', $set) . ' WHERE schedule_id = ?';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare update'], 500);
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to update schedule: ' . $conn->error], 500);
    }

    send_json(['success' => true]);
}

function update_schedule_status($scheduleId) {
    global $conn;

    $email = $_SESSION['email'] ?? null;
    $acct = isset($_SESSION['account_type']) ? (int)$_SESSION['account_type'] : null;

    $data = get_json_body();
    $status = isset($data['status']) ? (string)$data['status'] : '';
    $status = normalize_status_for_column('schedules', 'status', $status);

    $allowed = ['pending', 'scheduled', 'in progress', 'delayed', 'completed', 'cancelled'];
    if ($status === '' || !in_array(strtolower($status), $allowed, true)) {
        send_json(['success' => false, 'message' => 'Invalid status'], 400);
    }

    $conn->begin_transaction();

    try {
        if ($acct === 3) {
            if (!$email) {
                throw new Exception('Not authenticated');
            }

            $providerId = get_provider_id_for_session_email($email);
            if (!$providerId) {
                throw new Exception('Provider not found');
            }

            $stmt = $conn->prepare('SELECT schedule_id, route_id, rate_id, status FROM schedules WHERE schedule_id = ? AND provider_id = ?');
            if (!$stmt) {
                throw new Exception('Failed to prepare schedule lookup');
            }

            $stmt->bind_param('ii', $scheduleId, $providerId);
            $stmt->execute();
            $stmt->bind_result($schedId, $routeId, $rateId, $currentStatus);

            if (!$stmt->fetch()) {
                $stmt->close();
                throw new Exception('Schedule not found or does not belong to you');
            }

            $stmt->close();

            if ($currentStatus === 'completed' && in_array(strtolower($status), ['in progress', 'delayed'], true)) {
                throw new Exception('Cannot change a completed schedule back to In Progress or Delayed');
            }

        } else {
            $stmt = $conn->prepare('SELECT schedule_id, route_id, rate_id, status FROM schedules WHERE schedule_id = ?');
            if (!$stmt) {
                throw new Exception('Failed to prepare schedule lookup');
            }

            $stmt->bind_param('i', $scheduleId);
            $stmt->execute();
            $stmt->bind_result($schedId, $routeId, $rateId, $currentStatus);

            if (!$stmt->fetch()) {
                $stmt->close();
                throw new Exception('Schedule not found');
            }

            $stmt->close();
        }

        $stmt = $conn->prepare('UPDATE schedules SET status = ? WHERE schedule_id = ?');
        if (!$stmt) {
            throw new Exception('Failed to prepare schedule update');
        }
        $stmt->bind_param('si', $status, $scheduleId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update schedule status');
        }
        $stmt->close();

        $routeStatusMap = [
            'pending' => 'pending',
            'scheduled' => 'approved',
            'in progress' => 'approved',
            'delayed' => 'approved',
            'completed' => 'completed',
            'cancelled' => 'rejected'
        ];
        $mappedRouteStatus = $routeStatusMap[strtolower($status)] ?? $status;
        $normalizedRouteStatus = try_normalize_status_for_column('routes', 'status', $mappedRouteStatus);
        if ($normalizedRouteStatus !== null && $normalizedRouteStatus !== '') {
            $stmt = $conn->prepare('UPDATE routes SET status = ? WHERE route_id = ?');
            if ($stmt) {
                $stmt->bind_param('si', $normalizedRouteStatus, $routeId);
                $stmt->execute();
                $stmt->close();
            }
        }

        if (!empty($rateId)) {
            $rateStatusMap = [
                'pending' => 'pending',
                'scheduled' => 'approved',
                'in progress' => 'approved',
                'delayed' => 'approved',
                'completed' => 'completed',
                'cancelled' => 'rejected'
            ];
            $mappedRateStatus = $rateStatusMap[strtolower($status)] ?? $status;
            $normalizedRateStatus = try_normalize_status_for_column('calculated_rates', 'status', $mappedRateStatus);
            if ($normalizedRateStatus !== null && $normalizedRateStatus !== '') {
                $stmt = $conn->prepare('UPDATE calculated_rates SET status = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $normalizedRateStatus, $rateId);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $conn->commit();
        send_json(['success' => true, 'schedule_id' => $scheduleId, 'status' => $status]);

    } catch (Throwable $e) {
        $conn->rollback();
        send_json(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()], 400);
    }
}

function delete_schedule($scheduleId) {
    global $conn;

    $stmt = $conn->prepare('DELETE FROM schedules WHERE schedule_id = ?');
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare delete'], 500);
    }

    $stmt->bind_param('i', $scheduleId);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to delete schedule: ' . $conn->error], 500);
    }

    send_json(['success' => true]);
}
