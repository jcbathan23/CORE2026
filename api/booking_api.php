<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function createBookingRoute($booking_id) {
    global $conn;

    require_tables_or_fail(['bookings']);

    $q = $conn->prepare('SELECT * FROM bookings WHERE booking_id = ? LIMIT 1');
    if (!$q) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare booking lookup: ' . $conn->error]);
        return;
    }
    $q->bind_param('i', $booking_id);
    $q->execute();
    $res = $q->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $q->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        return;
    }

    // If already has a route, just return it
    if (!empty($row['route_id'])) {
        echo json_encode(['success' => true, 'route_id' => (int)$row['route_id']]);
        return;
    }

    // Need provider and coordinates
    $provider_id = $row['provider_id'] ?? null;
    $hasProvider = $provider_id !== null && $provider_id !== '';
    $hasCoords = isset($row['origin_lat'], $row['origin_lng'], $row['destination_lat'], $row['destination_lng'])
        && $row['origin_lat'] !== null && $row['origin_lng'] !== null && $row['destination_lat'] !== null && $row['destination_lng'] !== null
        && $row['origin_lat'] !== '' && $row['origin_lng'] !== '' && $row['destination_lat'] !== '' && $row['destination_lng'] !== '';

    if (!$hasProvider || !$hasCoords) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot create route: provider and coordinates are required']);
        return;
    }

    // Reuse existing helper to create the route
    $data = [
        'origin_address' => $row['origin_address'],
        'destination_address' => $row['destination_address'],
        'origin_lat' => $row['origin_lat'],
        'origin_lng' => $row['origin_lng'],
        'destination_lat' => $row['destination_lat'],
        'destination_lng' => $row['destination_lng'],
        'carrier_type' => $row['carrier_type'],
        'provider_id' => $row['provider_id'],
        'origin_id' => $row['origin_id'] ?? null,
        'destination_id' => $row['destination_id'] ?? null
    ];

    $conn->begin_transaction();
    $rid = createRouteFromBooking($booking_id, $data);
    $conn->commit();

    echo json_encode(['success' => true, 'route_id' => (int)$rid]);
}

function getProviders() {
    global $conn;

    if (!table_exists('active_service_provider')) {
        echo json_encode(['providers' => []]);
        return;
    }

    $query = "SELECT provider_id, company_name, status FROM active_service_provider WHERE status <> 'Archived' ORDER BY company_name ASC";
    $res = $conn->query($query);
    if (!$res) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to load providers: ' . $conn->error]);
        return;
    }

    $providers = [];
    while ($row = $res->fetch_assoc()) {
        $providers[] = [
            'provider_id' => (int)$row['provider_id'],
            'company_name' => (string)$row['company_name'],
            'status' => (string)$row['status']
        ];
    }

    echo json_encode(['providers' => $providers]);
}

require_once '../connect.php';

function get_enum_values($table, $column) {
    global $conn;

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
    if ($table === '' || $column === '') {
        return null;
    }

    $sql = "
        SELECT COLUMN_TYPE
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row || empty($row['COLUMN_TYPE'])) {
        return null;
    }

    $type = strtolower((string)$row['COLUMN_TYPE']);
    if (strpos($type, 'enum(') !== 0) {
        return null;
    }

    if (!preg_match('/^enum\((.*)\)$/i', (string)$row['COLUMN_TYPE'], $m)) {
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

function try_normalize_enum_for_column($table, $column, $value) {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    $allowed = get_enum_values($table, $column);
    if ($allowed === null) {
        return $value;
    }

    foreach ($allowed as $v) {
        if (strcasecmp($v, $value) === 0) {
            return $v;
        }
    }

    return null;
}

function pick_enum_value($table, $column, $candidates, $fallback = null) {
    foreach ($candidates as $candidate) {
        $normalized = try_normalize_enum_for_column($table, $column, $candidate);
        if ($normalized !== null && $normalized !== '') {
            return $normalized;
        }
    }
    return $fallback;
}

function column_exists($table, $column) {
    global $conn;

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
    if ($table === '' || $column === '') {
        return false;
    }

    $sql = "
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

function table_exists($table) {
    global $conn;

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    if ($table === '') {
        return false;
    }

    $sql = "
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

function require_tables_or_fail($tables) {
    foreach ($tables as $t) {
        if (!table_exists($t)) {
            http_response_code(500);
            echo json_encode([
            ]);
            exit();
        }
    }
}

// Method routing
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

$scriptIndex = array_search('booking_api.php', $path_parts, true);
$sub = $scriptIndex === false ? [] : array_slice($path_parts, $scriptIndex + 1);

$resource = $sub[0] ?? null;
$id = $sub[1] ?? ($_GET['id'] ?? null);

// Backward compatibility
$endpoint = $resource ?? end($path_parts);

try {
    switch ($method) {
        case 'GET':
            if (($endpoint === 'booking' || $endpoint === 'bookings') && $id !== null && $id !== '' && is_numeric($id)) {
                getBooking((int)$id);
            } elseif ($endpoint === 'booking' && isset($_GET['id'])) {
                getBooking((int)$_GET['id']);
            } elseif ($endpoint === 'providers') {
                getProviders();
            } elseif ($endpoint === 'bookings') {
                getBookings();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'POST':
            // Support creating a route for an existing booking: /bookings/{id}/create_route
            if ($endpoint === 'bookings' && $id !== null && $id !== '' && is_numeric($id) && isset($sub[2]) && $sub[2] === 'create_route') {
                createBookingRoute((int)$id);
            } elseif ($endpoint === 'bookings' || $endpoint === 'booking') {
                createBooking();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'PUT':
            if (($endpoint === 'booking' || $endpoint === 'bookings') && $id !== null && $id !== '' && is_numeric($id)) {
                updateBooking((int)$id);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'DELETE':
            if (($endpoint === 'booking' || $endpoint === 'bookings') && $id !== null && $id !== '' && is_numeric($id)) {
                deleteBooking((int)$id);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}

function getBookings() {
    global $conn;

    require_tables_or_fail(['bookings']);
    
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $carrierType = isset($_GET['carrier_type']) ? $_GET['carrier_type'] : 'all';
    $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    $whereClause = '';
    $params = [];
    $types = '';
    
    if ($status !== 'all') {
        $whereClause = "WHERE b.status = ?";
        $params[] = $status;
        $types = 's';
    }

    if ($carrierType !== 'all') {
        $whereClause .= ($whereClause === '' ? 'WHERE ' : ' AND ') . 'b.carrier_type = ?';
        $params[] = $carrierType;
        $types .= 's';
    }

    if ($search !== '') {
        $whereClause .= ($whereClause === '' ? 'WHERE ' : ' AND ') . '(b.booking_reference LIKE ? OR b.customer_name LIKE ? OR b.origin_address LIKE ? OR b.destination_address LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssss';
    }

    $filterParams = $params;
    $filterTypes = $types;
    
    $hasProviderTable = table_exists('active_service_provider');
    $hasContactInfo = $hasProviderTable ? column_exists('active_service_provider', 'contact_info') : false;
    $providerJoin = $hasProviderTable ? 'LEFT JOIN active_service_provider sp ON b.provider_id = sp.provider_id' : '';
    $providerSelect = $hasProviderTable
        ? ('sp.company_name as provider_name, ' . ($hasContactInfo ? 'sp.contact_info' : 'NULL as contact_info'))
        : 'NULL as provider_name, NULL as contact_info';

    $query = "
        SELECT 
            b.booking_id,
            b.booking_reference,
            b.customer_name,
            b.customer_email,
            b.customer_phone,
            b.origin_address,
            b.destination_address,
            b.origin_lat,
            b.origin_lng,
            b.destination_lat,
            b.destination_lng,
            b.provider_id,
            b.route_id,
            b.carrier_type,
            b.cargo_type,
            b.weight,
            b.volume,
            b.dimensions,
            b.special_instructions,
            b.estimated_cost,
            b.estimated_transit_time,
            b.status,
            b.created_at,
            b.updated_at,
            $providerSelect
        FROM bookings b
        $providerJoin
        $whereClause
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare bookings query: ' . $conn->error]);
        return;
    }
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    // Get total count (must bind the same filters as the main query)
    $countQuery = "SELECT COUNT(*) as total FROM bookings b $whereClause";
    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare bookings count query: ' . $conn->error]);
        return;
    }
    if ($filterTypes !== '') {
        $countStmt->bind_param($filterTypes, ...$filterParams);
    }
    $countStmt->execute();
    $countRes = $countStmt->get_result();
    $countRow = $countRes ? $countRes->fetch_assoc() : null;
    $total = $countRow && isset($countRow['total']) ? $countRow['total'] : 0;
    
    echo json_encode([
        'bookings' => $bookings,
        'total' => intval($total),
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function getBooking($id) {
    global $conn;

    require_tables_or_fail(['bookings']);

    $hasProviderTable = table_exists('active_service_provider');
    $hasContactInfo = $hasProviderTable ? column_exists('active_service_provider', 'contact_info') : false;
    $providerJoin = $hasProviderTable ? 'LEFT JOIN active_service_provider sp ON b.provider_id = sp.provider_id' : '';
    $providerSelect = $hasProviderTable
        ? ('sp.company_name as provider_name, ' . ($hasContactInfo ? 'sp.contact_info' : 'NULL as contact_info'))
        : 'NULL as provider_name, NULL as contact_info';

    $query = "
        SELECT 
            b.*,
            $providerSelect
        FROM bookings b
        $providerJoin
        WHERE b.booking_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
    }
}

function createBooking() {
    global $conn;

    require_tables_or_fail(['bookings', 'routes', 'network_points']);

    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        return;
    }

    // Accept shipment-style payloads by mapping sender/receiver/package fields
    if (!isset($data['customer_name']) || trim((string)$data['customer_name']) === '') {
        $data['customer_name'] = $data['sender_name'] ?? '';
    }

    $receiverName = trim((string)($data['receiver_name'] ?? ''));
    $packageDescription = trim((string)($data['package'] ?? ''));
    $paymentMethod = trim((string)($data['payment_method'] ?? ''));
    $extraAddress = trim((string)($data['address'] ?? ''));

    // Validate required fields
    $required = ['customer_name', 'origin_address', 'destination_address', 'carrier_type'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }

    // Normalize carrier_type to allowed enum values if possible
    $carrierType = pick_enum_value('bookings', 'carrier_type', [
        $data['carrier_type'],
        $data['mode'] ?? '',
        $data['carrier'] ?? ''
    ], (string)$data['carrier_type']);
    $data['carrier_type'] = $carrierType;

    // Compose special instructions from shipment-style fields (keeps schema unchanged)
    $notes = [];
    if ($receiverName !== '') $notes[] = 'Receiver: ' . $receiverName;
    if ($packageDescription !== '') $notes[] = 'Package: ' . $packageDescription;
    if ($paymentMethod !== '') $notes[] = 'Payment: ' . $paymentMethod;
    if ($extraAddress !== '') $notes[] = 'Address: ' . $extraAddress;

    if (!isset($data['special_instructions']) || trim((string)$data['special_instructions']) === '') {
        $data['special_instructions'] = implode(' | ', $notes);
    } elseif (!empty($notes)) {
        $data['special_instructions'] = trim((string)$data['special_instructions']) . ' | ' . implode(' | ', $notes);
    }

    // Compute estimated cost if not provided
    $distanceKm = isset($data['distance_km']) ? (float)$data['distance_km'] : null;
    if (($distanceKm === null || $distanceKm <= 0) && isset($data['origin_lat'], $data['origin_lng'], $data['destination_lat'], $data['destination_lng'])) {
        $distanceKm = calculateDistance($data['origin_lat'], $data['origin_lng'], $data['destination_lat'], $data['destination_lng']);
    }

    if ((!isset($data['estimated_cost']) || $data['estimated_cost'] === null || $data['estimated_cost'] === '') && $distanceKm !== null && $distanceKm > 0) {
        $pricePerKm = 24.0;
        $baseFee = 0.0;
        $data['estimated_cost'] = ($distanceKm * $pricePerKm) + $baseFee;
    }

    if ((!isset($data['estimated_transit_time']) || $data['estimated_transit_time'] === null || $data['estimated_transit_time'] === '') && $distanceKm !== null && $distanceKm > 0) {
        $data['estimated_transit_time'] = estimateTransitTime($distanceKm, $data['carrier_type']);
    }

    if (!isset($data['status']) || trim((string)$data['status']) === '') {
        $data['status'] = 'pending';
    }
    $data['status'] = pick_enum_value('bookings', 'status', [$data['status'], 'pending'], 'pending');
    
    // Generate booking reference
    $booking_reference = 'BK' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    $query = "
        INSERT INTO bookings (
            booking_reference, customer_name, customer_email, customer_phone,
            origin_address, destination_address, origin_lat, origin_lng,
            destination_lat, destination_lng, carrier_type, cargo_type,
            weight, volume, dimensions, special_instructions,
            estimated_cost, estimated_transit_time, status, provider_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $conn->begin_transaction();
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare booking insert: ' . $conn->error]);
        return;
    }
    
    $params = [
        $booking_reference,
        $data['customer_name'] ?? '',
        $data['customer_email'] ?? '',
        $data['customer_phone'] ?? '',
        $data['origin_address'],
        $data['destination_address'],
        $data['origin_lat'] ?? null,
        $data['origin_lng'] ?? null,
        $data['destination_lat'] ?? null,
        $data['destination_lng'] ?? null,
        $data['carrier_type'],
        $data['cargo_type'] ?? 'general',
        $data['weight'] ?? null,
        $data['volume'] ?? null,
        $data['dimensions'] ?? '',
        $data['special_instructions'] ?? '',
        $data['estimated_cost'] ?? null,
        $data['estimated_transit_time'] ?? null,
        $data['status'],
        $data['provider_id'] ?? null
    ];
    
    // Keep permissive typing to avoid NULL binding edge-cases on some PHP/MySQL configs
    // 20 placeholders:
    // ssssss dddd ss dd ss d i s i
    $types = 'ssssssddddssddssdisi';
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;
        $route_id = null;
        
        // Create a route if a provider is assigned and we have enough info (coords OR distance_km)
        $hasProvider = isset($data['provider_id']) && $data['provider_id'] !== null && $data['provider_id'] !== '';
        $hasCoords = isset($data['origin_lat'], $data['origin_lng'], $data['destination_lat'], $data['destination_lng'])
            && $data['origin_lat'] !== '' && $data['origin_lng'] !== '' && $data['destination_lat'] !== '' && $data['destination_lng'] !== '';
        $hasDistance = isset($data['distance_km']) && is_numeric($data['distance_km']) && (float)$data['distance_km'] > 0;

        if ($hasProvider && ($hasCoords || $hasDistance)) {
            $route_id = createRouteFromBooking($booking_id, $data);
        }

        $conn->commit();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'route_id' => $route_id,
            'estimated_cost' => $data['estimated_cost'] ?? null,
            'estimated_transit_time' => $data['estimated_transit_time'] ?? null
        ]);
    } else {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create booking: ' . $conn->error]);
    }
}

function createRouteFromBooking($booking_id, $data) {
    global $conn;
    
    // Calculate distance and ETA (coords preferred; fallback to provided distance_km)
    $distance = null;
    $hasCoords = isset($data['origin_lat'], $data['origin_lng'], $data['destination_lat'], $data['destination_lng'])
        && $data['origin_lat'] !== '' && $data['origin_lng'] !== '' && $data['destination_lat'] !== '' && $data['destination_lng'] !== '';

    if ($hasCoords) {
        $distance = calculateDistance(
            $data['origin_lat'], $data['origin_lng'],
            $data['destination_lat'], $data['destination_lng']
        );
    } elseif (isset($data['distance_km']) && is_numeric($data['distance_km'])) {
        $distance = (float)$data['distance_km'];
    }

    if ($distance === null || $distance <= 0) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['error' => 'Cannot create route: missing coordinates or distance_km']);
        exit();
    }
    
    $eta = estimateTransitTime($distance, $data['carrier_type']);
    
    $origin_id = $data['origin_id'] ?? null;
    $destination_id = $data['destination_id'] ?? null;

    if (empty($origin_id) || empty($destination_id)) {
        $origin_id = ensureNetworkPoint($data['origin_address'] ?? 'Origin', $data['origin_lat'] ?? null, $data['origin_lng'] ?? null);
        $destination_id = ensureNetworkPoint($data['destination_address'] ?? 'Destination', $data['destination_lat'] ?? null, $data['destination_lng'] ?? null);
    }

    $routeStatus = pick_enum_value('routes', 'status', ['active', 'pending', 'approved'], 'active');
    $hasBookingId = column_exists('routes', 'booking_id');

    if ($hasBookingId) {
        $query = "
            INSERT INTO routes (
                origin_id, destination_id, carrier_type, provider_id,
                distance_km, eta_min, status, booking_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iisiddsi', $origin_id, $destination_id, $data['carrier_type'], $data['provider_id'], $distance, $eta, $routeStatus, $booking_id);
    } else {
        $query = "
            INSERT INTO routes (
                origin_id, destination_id, carrier_type, provider_id,
                distance_km, eta_min, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iisidds', $origin_id, $destination_id, $data['carrier_type'], $data['provider_id'], $distance, $eta, $routeStatus);
    }

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create route: ' . $conn->error]);
        exit();
    }

    $route_id = $conn->insert_id;

    $updateQuery = "UPDATE bookings SET route_id = ? WHERE booking_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ii', $route_id, $booking_id);
    if (!$updateStmt->execute()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to link booking to route: ' . $conn->error]);
        exit();
    }

    return $route_id;
}

function ensureNetworkPoint($name, $lat, $lng) {
    global $conn;

    $name = trim((string)$name);
    if ($name === '') {
        $name = 'Unknown Location';
    }

    $lat = ($lat === null || $lat === '') ? null : (float)$lat;
    $lng = ($lng === null || $lng === '') ? null : (float)$lng;

    $find = $conn->prepare('SELECT point_id FROM network_points WHERE point_name = ? LIMIT 1');
    if ($find) {
        $find->bind_param('s', $name);
        $find->execute();
        $res = $find->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $find->close();
        if ($row && isset($row['point_id'])) {
            return (int)$row['point_id'];
        }
    }

    $status = pick_enum_value('network_points', 'status', ['Active', 'active'], 'Active');
    $pointType = pick_enum_value('network_points', 'point_type', ['Warehouse', 'Port', 'Hub', 'Location', 'Other'], 'Warehouse');

    $country = 'Philippines';
    $city = '';

    $hasStatus = column_exists('network_points', 'status');
    $hasCountry = column_exists('network_points', 'country');
    $hasCity = column_exists('network_points', 'city');

    if ($hasStatus && $hasCountry && $hasCity) {
        $ins = $conn->prepare('INSERT INTO network_points (point_name, point_type, country, city, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $ins->bind_param('ssssdds', $name, $pointType, $country, $city, $lat, $lng, $status);
    } elseif ($hasCountry && $hasCity) {
        $ins = $conn->prepare('INSERT INTO network_points (point_name, point_type, country, city, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)');
        $ins->bind_param('ssssdd', $name, $pointType, $country, $city, $lat, $lng);
    } else {
        $ins = $conn->prepare('INSERT INTO network_points (point_name, point_type, latitude, longitude) VALUES (?, ?, ?, ?)');
        $ins->bind_param('ssdd', $name, $pointType, $lat, $lng);
    }

    if (!$ins || !$ins->execute()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create network point: ' . $conn->error]);
        exit();
    }

    return (int)$conn->insert_id;
}

function updateBooking($id) {
    global $conn;

    require_tables_or_fail(['bookings']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $setClause = [];
    $params = [];
    $types = '';
    
    $updatable_fields = [
        'customer_name', 'customer_email', 'customer_phone',
        'origin_address', 'destination_address', 'origin_lat', 'origin_lng',
        'destination_lat', 'destination_lng', 'carrier_type', 'cargo_type',
        'weight', 'volume', 'dimensions', 'special_instructions',
        'estimated_cost', 'estimated_transit_time', 'status', 'provider_id'
    ];
    
    foreach ($updatable_fields as $field) {
        if (isset($data[$field])) {
            $setClause[] = "$field = ?";
            $value = $data[$field];

            if (in_array($field, ['provider_id', 'estimated_transit_time'], true)) {
                $value = (int)$value;
                $types .= 'i';
            } elseif (in_array($field, ['origin_lat', 'origin_lng', 'destination_lat', 'destination_lng', 'weight', 'volume', 'estimated_cost'], true)) {
                $value = ($value === null || $value === '') ? null : (float)$value;
                $types .= 'd';
            } else {
                $value = (string)$value;
                $types .= 's';
            }

            $params[] = $value;
        }
    }
    
    if (empty($setClause)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid fields to update']);
        return;
    }
    
    $params[] = $id;
    $types .= 'i';
    
    $query = "UPDATE bookings SET " . implode(', ', $setClause) . " WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Booking updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update booking: ' . $conn->error]);
    }
}

function deleteBooking($id) {
    global $conn;

    require_tables_or_fail(['bookings']);
    
    // Check if booking exists
    $checkQuery = "SELECT booking_id FROM bookings WHERE booking_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        return;
    }
    
    // Delete associated route if exists
    $deleteRouteQuery = "DELETE FROM routes WHERE route_id = (SELECT route_id FROM bookings WHERE booking_id = ?)";
    $deleteRouteStmt = $conn->prepare($deleteRouteQuery);
    $deleteRouteStmt->bind_param('i', $id);
    $deleteRouteStmt->execute();
    
    // Delete booking
    $deleteQuery = "DELETE FROM bookings WHERE booking_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Booking deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete booking: ' . $conn->error]);
    }
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earth_radius * $c;
}

function estimateTransitTime($distance_km, $carrier_type) {
    $speeds = [
        'land' => 60,    // km/h average for trucks
        'air' => 500,    // km/h average for planes
        'sea' => 30      // km/h average for ships
    ];
    
    $speed = $speeds[$carrier_type] ?? 60;
    $time_hours = $distance_km / $speed;
    
    // Add handling time (30% of travel time)
    $total_time = $time_hours * 1.3;
    
    return intval($total_time * 60); // Convert to minutes
}
?>
