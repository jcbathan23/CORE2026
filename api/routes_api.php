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
    if (!str_starts_with($type, 'enum(')) {
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

function get_json_body() {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = $path === '' ? [] : explode('/', $path);
$scriptIndex = array_search('routes_api.php', $parts, true);
$sub = $scriptIndex === false ? [] : array_slice($parts, $scriptIndex + 1);

$resource = $sub[0] ?? 'routes';
$id = $sub[1] ?? ($_GET['id'] ?? null);
$action = $sub[2] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($resource === 'routes') {
                if ($id !== null) {
                    get_route((int)$id);
                }
                list_routes();
            }
            if ($resource === 'route') {
                if ($id === null) {
                    send_json(['success' => false, 'message' => 'Missing route id'], 400);
                }
                get_route((int)$id);
            }
            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'POST':
            if ($resource === 'routes' && $id === null) {
                create_route();
            }

            if (($resource === 'routes' || $resource === 'route') && $id !== null && $action !== null) {
                $normalized = strtolower((string)$action);
                if (in_array($normalized, ['approve', 'reject', 'complete', 'lock', 'unlock'], true)) {
                    set_route_status((int)$id, $normalized);
                }
            }

            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'PUT':
            if (($resource === 'routes' || $resource === 'route') && $id !== null) {
                update_route((int)$id);
            }
            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        case 'DELETE':
            if (($resource === 'routes' || $resource === 'route') && $id !== null) {
                delete_route((int)$id);
            }
            send_json(['success' => false, 'message' => 'Endpoint not found'], 404);

        default:
            send_json(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Throwable $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function list_routes() {
    global $conn;

    $status = $_GET['status'] ?? 'all';
    $carrierType = $_GET['carrier_type'] ?? 'all';
    $providerId = $_GET['provider_id'] ?? 'all';
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $where = [];
    $params = [];
    $types = '';

    if ($status !== 'all') {
        $where[] = 'r.status = ?';
        $params[] = $status;
        $types .= 's';
    }

    if ($carrierType !== 'all') {
        $where[] = 'r.carrier_type = ?';
        $params[] = $carrierType;
        $types .= 's';
    }

    if ($providerId !== 'all') {
        $where[] = 'r.provider_id = ?';
        $params[] = (int)$providerId;
        $types .= 'i';
    }

    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $sql = "
        SELECT
            r.route_id,
            r.origin_id,
            r.destination_id,
            r.carrier_type,
            r.provider_id,
            r.distance_km,
            r.eta_min,
            r.status,
            sp.company_name AS provider_name,
            np1.point_name AS origin_name,
            np2.point_name AS destination_name
        FROM routes r
        LEFT JOIN active_service_provider sp ON r.provider_id = sp.provider_id
        LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
        LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
        $whereSql
        ORDER BY r.route_id DESC
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

    send_json(['success' => true, 'routes' => $items, 'limit' => $limit, 'offset' => $offset]);
}

function get_route($routeId) {
    global $conn;

    $sql = "
        SELECT
            r.route_id,
            r.origin_id,
            r.destination_id,
            r.carrier_type,
            r.provider_id,
            r.distance_km,
            r.eta_min,
            r.status,
            sp.company_name AS provider_name,
            np1.point_name AS origin_name,
            np2.point_name AS destination_name
        FROM routes r
        LEFT JOIN active_service_provider sp ON r.provider_id = sp.provider_id
        LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
        LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
        WHERE r.route_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query'], 500);
    }

    $stmt->bind_param('i', $routeId);
    $stmt->execute();
    $res = $stmt->get_result();

    $row = $res->fetch_assoc();
    if (!$row) {
        send_json(['success' => false, 'message' => 'Route not found'], 404);
    }

    send_json(['success' => true, 'route' => $row]);
}

function create_route() {
    global $conn;

    $data = get_json_body();

    $originId = isset($data['origin_id']) ? (int)$data['origin_id'] : 0;
    $destinationId = isset($data['destination_id']) ? (int)$data['destination_id'] : 0;
    $carrierType = isset($data['carrier_type']) ? trim((string)$data['carrier_type']) : '';
    $providerId = isset($data['provider_id']) ? (int)$data['provider_id'] : 0;
    $distanceKm = isset($data['distance_km']) ? (float)$data['distance_km'] : 0;
    $etaMin = isset($data['eta_min']) ? (int)$data['eta_min'] : 0;
    $status = isset($data['status']) ? trim((string)$data['status']) : 'pending';
    $status = normalize_status_for_column('routes', 'status', $status);

    if ($originId <= 0 || $destinationId <= 0 || $carrierType === '' || $providerId <= 0 || $distanceKm <= 0 || $etaMin <= 0) {
        send_json(['success' => false, 'message' => 'Missing or invalid fields'], 400);
    }

    $sql = "INSERT INTO routes (origin_id, destination_id, carrier_type, provider_id, distance_km, eta_min, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare insert'], 500);
    }

    $stmt->bind_param('iisidis', $originId, $destinationId, $carrierType, $providerId, $distanceKm, $etaMin, $status);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to create route: ' . $conn->error], 500);
    }

    send_json(['success' => true, 'route_id' => (int)$conn->insert_id], 201);
}

function update_route($routeId) {
    global $conn;

    $data = get_json_body();

    $allowed = ['origin_id', 'destination_id', 'carrier_type', 'provider_id', 'distance_km', 'eta_min', 'status'];
    $set = [];
    $params = [];
    $types = '';

    foreach ($allowed as $field) {
        if (!array_key_exists($field, $data)) {
            continue;
        }

        $value = $data[$field];

        if ($field === 'origin_id' || $field === 'destination_id' || $field === 'provider_id' || $field === 'eta_min') {
            $value = (int)$value;
            $types .= 'i';
        } elseif ($field === 'distance_km') {
            $value = (float)$value;
            $types .= 'd';
        } elseif ($field === 'status') {
            $value = normalize_status_for_column('routes', 'status', $value);
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
    $params[] = $routeId;

    $sql = 'UPDATE routes SET ' . implode(', ', $set) . ' WHERE route_id = ?';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare update'], 500);
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to update route: ' . $conn->error], 500);
    }

    send_json(['success' => true]);
}

function set_route_status($routeId, $action) {
    global $conn;

    $map = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'complete' => 'completed',
        'lock' => 'locked',
        'unlock' => 'pending'
    ];

    if (!isset($map[$action])) {
        send_json(['success' => false, 'message' => 'Invalid action'], 400);
    }

    $status = normalize_status_for_column('routes', 'status', $map[$action]);
    $stmt = $conn->prepare('UPDATE routes SET status = ? WHERE route_id = ?');
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare update'], 500);
    }

    $stmt->bind_param('si', $status, $routeId);
    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to update status: ' . $conn->error], 500);
    }

    send_json(['success' => true, 'route_id' => $routeId, 'status' => $status]);
}

function delete_route($routeId) {
    global $conn;

    $stmt = $conn->prepare('DELETE FROM routes WHERE route_id = ?');
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare delete'], 500);
    }

    $stmt->bind_param('i', $routeId);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to delete route: ' . $conn->error], 500);
    }

    send_json(['success' => true]);
}
