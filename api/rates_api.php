<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function table_exists($table) {
    global $conn;
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    if ($table === '') return false;
    $sql = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

require_once '../connect.php';

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

function normalize_status_for_column($table, $column, $status) {
    $normalized = try_normalize_status_for_column($table, $column, $status);
    if ($normalized === null) {
        send_json([
            'success' => false,
            'message' => 'Invalid status for ' . $table . '.' . $column,
            'allowed_statuses' => get_enum_values($table, $column)
        ], 400);
    }
    return $normalized;
}

function column_exists($table, $column) {
    global $conn;

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$column);
    if ($table === '' || $column === '') {
        return false;
    }

    $sql = "SHOW COLUMNS FROM `$table` LIKE ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $row ? true : false;
}

function pick_status($table, $column, $candidates) {
    foreach ($candidates as $candidate) {
        $normalized = try_normalize_status_for_column($table, $column, $candidate);
        if ($normalized !== null && $normalized !== '') {
            return $normalized;
        }
    }
    return null;
}

function calculatePhilippinesRate($route, $weight, $cargoType, $serviceLevel, $deliveryType = 'auto', $shipmentType = 'domestic', $targetCurrency = 'PHP', $tariffSource = 'ph_boc') {
    $baseRates = [
        'land' => ['per_km' => 8.50, 'per_kg' => 2.75, 'base_fee' => 150.00],
        'sea' => ['per_km' => 3.25, 'per_kg' => 1.50, 'base_fee' => 500.00],
        'air' => ['per_km' => 15.75, 'per_kg' => 8.25, 'base_fee' => 800.00]
    ];

    $deliveryAdjustments = [
        'motorcycle' => 0.9,
        'bike' => 1.0,
        'truck' => 1.2
    ];

    $cargoMultipliers = [
        'general' => 1.0,
        'perishable' => 1.3,
        'hazardous' => 2.1,
        'fragile' => 1.5,
        'oversized' => 1.8,
        'documents' => 1.25
    ];

    $serviceMultipliers = [
        'standard' => 1.0,
        'express' => 1.6,
        'economy' => 0.8
    ];

    $governmentTariffs = [
        'ph_boc' => ['land' => 0.15, 'sea' => 0.12, 'air' => 0.18],
        'international_wto' => ['land' => 0.12, 'sea' => 0.10, 'air' => 0.15],
        'asean' => ['land' => 0.10, 'sea' => 0.08, 'air' => 0.12],
        'custom' => ['land' => 0.20, 'sea' => 0.15, 'air' => 0.25]
    ];

    $exchangeRates = [
        'USD' => 0.018,
        'EUR' => 0.016,
        'GBP' => 0.014,
        'JPY' => 2.65,
        'CNY' => 0.13,
        'SGD' => 0.024,
        'MYR' => 0.085,
        'THB' => 0.62
    ];

    $additionalTaxes = [
        'USD' => 0.05,
        'EUR' => 0.08,
        'GBP' => 0.20,
        'JPY' => 0.10,
        'CNY' => 0.13,
        'SGD' => 0.07,
        'MYR' => 0.06,
        'THB' => 0.07
    ];

    $carrierType = strtolower($route['carrier_type']);
    $baseRate = $baseRates[$carrierType] ?? $baseRates['land'];

    $distanceRate = $route['distance_km'] * $baseRate['per_km'];
    $weightRate = $weight * $baseRate['per_kg'];
    $calculatedBase = max($baseRate['base_fee'], $distanceRate + $weightRate);

    if ($deliveryType === 'auto') {
        if ($weight <= 5) {
            $deliveryType = 'motorcycle';
        } elseif ($weight <= 20) {
            $deliveryType = 'bike';
        } else {
            $deliveryType = 'truck';
        }
    }

    $deliveryMultiplier = $deliveryAdjustments[$deliveryType] ?? 1.0;
    $cargoMultiplier = $cargoMultipliers[$cargoType] ?? 1.0;
    $serviceMultiplier = $serviceMultipliers[$serviceLevel] ?? 1.0;

    $finalBaseRate = $calculatedBase * $cargoMultiplier * $serviceMultiplier * $deliveryMultiplier;

    $tariffRates = $governmentTariffs[$tariffSource] ?? $governmentTariffs['ph_boc'];
    $tariffRate = $tariffRates[$carrierType] ?? 0.15;
    $tariffAmount = $finalBaseRate * $tariffRate;

    $totalRatePHP = $finalBaseRate + $tariffAmount;

    $convertedRate = null;
    $exchangeRate = null;
    $additionalTaxAmount = 0;

    if ($shipmentType === 'international' && $targetCurrency !== 'PHP') {
        $exchangeRate = $exchangeRates[$targetCurrency] ?? 1;
        $convertedRate = $totalRatePHP * $exchangeRate;

        $additionalTaxRate = $additionalTaxes[$targetCurrency] ?? 0;
        $additionalTaxAmount = $convertedRate * $additionalTaxRate;
        $convertedRate += $additionalTaxAmount;
    }

    $formula = "Base: ({$route['distance_km']} km × ₱{$baseRate['per_km']}) + ({$weight} kg × ₱{$baseRate['per_kg']}) = ₱" . number_format($distanceRate + $weightRate, 2) . "\n";
    $formula .= "Cargo Multiplier ({$cargoType}): × {$cargoMultiplier}\n";
    $formula .= "Service Multiplier ({$serviceLevel}): × {$serviceMultiplier}\n";
    $formula .= "Delivery Adjustment ({$deliveryType}): × {$deliveryMultiplier}\n";
    $formula .= "Final Base Rate: ₱" . number_format($finalBaseRate, 2) . "\n";
    $formula .= "Government Tariff (" . strtoupper($tariffSource) . " - {$carrierType}): " . ($tariffRate * 100) . "% = ₱" . number_format($tariffAmount, 2) . "\n";
    $formula .= "Total Rate (PHP): ₱" . number_format($totalRatePHP, 2);

    if ($shipmentType === 'international' && $convertedRate !== null) {
        $formula .= "\nConverted Rate ({$targetCurrency}): " . number_format($convertedRate, 2);
        if ($additionalTaxAmount > 0) {
            $formula .= " (incl. " . number_format($additionalTaxes[$targetCurrency] * 100, 0) . "% tax)";
        }
    }

    $breakdown = "
        <div class='mb-2'><strong>Distance Rate:</strong> {$route['distance_km']} km × ₱{$baseRate['per_km']} = ₱" . number_format($distanceRate, 2) . "</div>
        <div class='mb-2'><strong>Weight Rate:</strong> {$weight} kg × ₱{$baseRate['per_kg']} = ₱" . number_format($weightRate, 2) . "</div>
        <div class='mb-2'><strong>Subtotal:</strong> ₱" . number_format($distanceRate + $weightRate, 2) . "</div>
        <div class='mb-2'><strong>Cargo Adjustment ({$cargoType}):</strong> × {$cargoMultiplier} = ₱" . number_format(($distanceRate + $weightRate) * $cargoMultiplier, 2) . "</div>
        <div class='mb-2'><strong>Service Adjustment ({$serviceLevel}):</strong> × {$serviceMultiplier} = ₱" . number_format(($distanceRate + $weightRate) * $cargoMultiplier * $serviceMultiplier, 2) . "</div>
        <div class='mb-2'><strong>Delivery Adjustment ({$deliveryType}):</strong> × {$deliveryMultiplier} = ₱" . number_format($finalBaseRate, 2) . "</div>
        <div class='mb-2'><strong>Government Tariff (" . strtoupper($tariffSource) . " - " . strtoupper($carrierType) . "):</strong> " . ($tariffRate * 100) . "% = ₱" . number_format($tariffAmount, 2) . "</div>
        <div class='mt-2'><strong>Total (PHP):</strong> ₱" . number_format($totalRatePHP, 2) . "</div>";

    if ($shipmentType === 'international' && $convertedRate !== null) {
        $breakdown .= "<div class='mt-2'><strong>Exchange Rate:</strong> 1 PHP = {$exchangeRate} {$targetCurrency}</div>";
        if ($additionalTaxAmount > 0) {
            $breakdown .= "<div class='mb-2'><strong>Additional Tax ({$targetCurrency}):</strong> " . number_format($additionalTaxes[$targetCurrency] * 100, 0) . "% = " . number_format($additionalTaxAmount, 2) . "</div>";
        }
        $breakdown .= "<div class='mb-2'><strong>Total ({$targetCurrency}):</strong> " . number_format($convertedRate, 2) . "</div>";
    }

    $breakdown .= "</div>";

    return [
        'base_rate' => $finalBaseRate,
        'tariff_amount' => $tariffAmount,
        'total_rate' => $totalRatePHP,
        'converted_rate' => $convertedRate,
        'target_currency' => $targetCurrency,
        'exchange_rate' => $exchangeRate,
        'formula' => nl2br($formula),
        'breakdown' => $breakdown,
        'tariff_rate' => $tariffRate,
        'tariff_source' => $tariffSource,
        'delivery_type' => $deliveryType,
        'shipment_type' => $shipmentType
    ];
}

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = $path === '' ? [] : explode('/', $path);
$scriptIndex = array_search('rates_api.php', $parts, true);
$sub = $scriptIndex === false ? [] : array_slice($parts, $scriptIndex + 1);

$resource = $sub[0] ?? 'rates';
$id = $sub[1] ?? ($_GET['id'] ?? null);
$action = $sub[2] ?? null;

try {
    if ($method === 'GET') {
        if ($resource === 'rates') {
            if ($id !== null && $id !== '' && is_numeric($id)) {
                get_rate((int)$id);
            }
            list_rates();
        }

        if ($resource === 'routes') {
            if ($id === null || $id === '' || !is_numeric($id)) {
                send_json(['success' => false, 'message' => 'Missing route id'], 400);
            }
            get_route_details((int)$id);
        }

        send_json(['success' => false, 'message' => 'Endpoint not found'], 404);
    }

    if ($method === 'POST') {
        if ($resource === 'calculate') {
            ai_calculate();
        }

        if ($resource === 'rates' && ($id === null || $id === '')) {
            create_rate();
        }

        if ($resource === 'rates' && $id !== null && $id !== '' && is_numeric($id) && $action !== null) {
            $normalized = strtolower((string)$action);
            if ($normalized === 'approve') {
                approve_rate((int)$id);
            }
            if ($normalized === 'reject') {
                reject_rate((int)$id);
            }
        }

        send_json(['success' => false, 'message' => 'Endpoint not found'], 404);
    }

    if ($method === 'DELETE') {
        if ($resource === 'rates' && $id !== null && $id !== '' && is_numeric($id)) {
            delete_rate((int)$id);
        }
        send_json(['success' => false, 'message' => 'Endpoint not found'], 404);
    }

    send_json(['success' => false, 'message' => 'Method not allowed'], 405);

} catch (Throwable $e) {
    send_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function list_rates() {
    global $conn;

    if (!table_exists('calculated_rates')) {
        send_json(['success' => true, 'rates' => []]);
    }

    $status = $_GET['status'] ?? 'all';
    $carrierType = $_GET['carrier_type'] ?? 'all';
    $providerId = $_GET['provider_id'] ?? 'all';
    $search = $_GET['search'] ?? '';

    $whereConditions = [];
    $params = [];
    $types = '';

    if ($status !== 'all') {
        $whereConditions[] = 'cr.status = ?';
        $params[] = $status;
        $types .= 's';
    }

    if ($carrierType !== 'all') {
        $whereConditions[] = 'r.carrier_type = ?';
        $params[] = $carrierType;
        $types .= 's';
    }

    if ($providerId !== 'all') {
        $whereConditions[] = 'cr.provider_id = ?';
        $params[] = (int)$providerId;
        $types .= 'i';
    }

    if (!empty($search)) {
        $whereConditions[] = '(np1.point_name LIKE ? OR np2.point_name LIKE ? OR sp.company_name LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'sss';
    }

    // Build SELECT columns defensively in case some columns are missing in calculated_rates
    $hasUnit = column_exists('calculated_rates', 'unit');
    $hasQty = column_exists('calculated_rates', 'quantity');
    $hasBase = column_exists('calculated_rates', 'base_rate');
    $hasTariff = column_exists('calculated_rates', 'tariff_amount');
    $hasAI = column_exists('calculated_rates', 'ai_calculated');
    $hasDetails = column_exists('calculated_rates', 'calculation_details');
    $hasCreated = column_exists('calculated_rates', 'created_at');
    $hasStatus = column_exists('calculated_rates', 'status');

    $selectUnit = $hasUnit ? 'cr.unit' : "'per shipment' AS unit";
    $selectQty = $hasQty ? 'cr.quantity' : '1 AS quantity';
    $selectBase = $hasBase ? 'COALESCE(cr.base_rate, 0) as base_rate' : '0 AS base_rate';
    $selectTariff = $hasTariff ? 'COALESCE(cr.tariff_amount, 0) as tariff_amount' : '0 AS tariff_amount';
    $selectAI = $hasAI ? 'COALESCE(cr.ai_calculated, 0) as ai_calculated' : '0 AS ai_calculated';
    $selectDetails = $hasDetails ? 'cr.calculation_details' : "NULL AS calculation_details";
    $selectCreated = $hasCreated ? 'cr.created_at' : 'NOW() AS created_at';
    $selectStatus = $hasStatus ? 'cr.status' : "'pending' AS status";

    $hasRoutes = table_exists('routes');
    $hasProviders = table_exists('active_service_provider');
    $hasNP = table_exists('network_points');

    $selectDistance = $hasRoutes ? 'r.distance_km' : 'NULL AS distance_km';
    $selectEta = $hasRoutes ? 'r.eta_min' : 'NULL AS eta_min';
    $selectProviderName = $hasProviders ? 'sp.company_name as provider_name' : "NULL AS provider_name";
    $selectOrigin = ($hasRoutes && $hasNP) ? 'np1.point_name as origin' : 'NULL AS origin';
    $selectDestination = ($hasRoutes && $hasNP) ? 'np2.point_name as destination' : 'NULL AS destination';

    $joins = '';
    if ($hasRoutes) {
        $joins .= ' LEFT JOIN routes r ON cr.route_id = r.route_id';
    }
    if ($hasProviders) {
        $joins .= ' LEFT JOIN active_service_provider sp ON cr.provider_id = sp.provider_id';
    }
    if ($hasRoutes && $hasNP) {
        $joins .= ' LEFT JOIN network_points np1 ON r.origin_id = np1.point_id LEFT JOIN network_points np2 ON r.destination_id = np2.point_id';
    }

    // Rebuild filters with awareness of available tables/columns
    $whereConditions = [];
    $params = [];
    $types = '';
    $hasStatusCol = column_exists('calculated_rates', 'status');
    if ($status !== 'all' && $hasStatusCol) {
        $whereConditions[] = 'cr.status = ?';
        $params[] = $status;
        $types .= 's';
    }
    if ($carrierType !== 'all' && $hasRoutes) {
        $whereConditions[] = 'r.carrier_type = ?';
        $params[] = $carrierType;
        $types .= 's';
    }
    if ($providerId !== 'all') {
        $whereConditions[] = 'cr.provider_id = ?';
        $params[] = (int)$providerId;
        $types .= 'i';
    }
    if (!empty($search)) {
        if ($hasRoutes && $hasNP && $hasProviders) {
            $whereConditions[] = '(np1.point_name LIKE ? OR np2.point_name LIKE ? OR sp.company_name LIKE ?)';
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        } elseif ($hasRoutes && $hasNP) {
            $whereConditions[] = '(np1.point_name LIKE ? OR np2.point_name LIKE ?)';
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'ss';
        } elseif ($hasProviders) {
            $whereConditions[] = '(sp.company_name LIKE ?)';
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $types .= 's';
        }
    }

    $whereClause = !empty($whereConditions) ? ('WHERE ' . implode(' AND ', $whereConditions)) : '';
    // Safety: ensure no placeholders remain without params
    if (empty($params) && strpos($whereClause, '?') !== false) {
        $whereClause = '';
    }

    $query = "
        SELECT
            cr.id,
            cr.route_id,
            cr.provider_id,
            cr.carrier_type,
            $selectUnit,
            $selectQty,
            cr.total_rate,
            $selectBase,
            $selectTariff,
            $selectAI,
            $selectDetails,
            $selectCreated,
            $selectStatus,
            $selectDistance,
            $selectEta,
            $selectProviderName,
            $selectOrigin,
            $selectDestination
        FROM calculated_rates cr
        $joins
        $whereClause
        ORDER BY ".($hasCreated ? 'cr.created_at' : 'cr.id')." DESC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query: ' . $conn->error], 500);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Query execution failed: ' . $conn->error], 500);
    }
    $result = $stmt->get_result();
    if (!$result) {
        send_json(['success' => false, 'message' => 'Failed to fetch result: ' . $conn->error], 500);
    }

    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[] = $row;
    }

    send_json(['success' => true, 'rates' => $rates]);
}

function get_route_details($routeId) {
    global $conn;

    $query = "
        SELECT
            r.route_id,
            r.origin_id,
            r.destination_id,
            r.carrier_type,
            r.provider_id,
            r.distance_km,
            r.eta_min,
            np1.point_name as origin,
            np2.point_name as destination
        FROM routes r
        JOIN network_points np1 ON r.origin_id = np1.point_id
        JOIN network_points np2 ON r.destination_id = np2.point_id
        WHERE r.route_id = ? AND r.status = 'approved'
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query'], 500);
    }

    $stmt->bind_param('i', $routeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if (!$row) {
        send_json(['success' => false, 'message' => 'Route not found or not approved'], 404);
    }

    send_json(['success' => true, 'route' => $row]);
}

function ai_calculate() {
    global $conn;

    $data = get_json_body();

    $routeId = (int)($data['route_id'] ?? 0);
    $weight = (float)($data['cargo_weight'] ?? 0);
    $cargoType = (string)($data['cargo_type'] ?? 'general');
    $serviceLevel = (string)($data['service_level'] ?? 'standard');
    $deliveryType = (string)($data['delivery_type'] ?? 'auto');
    $shipmentType = (string)($data['shipment_type'] ?? 'domestic');
    $targetCurrency = (string)($data['target_currency'] ?? 'PHP');
    $tariffSource = (string)($data['tariff_source'] ?? 'ph_boc');

    if ($routeId <= 0 || $weight <= 0) {
        send_json(['success' => false, 'message' => 'Invalid route ID or weight'], 400);
    }

    $query = "
        SELECT r.*, sp.company_name
        FROM routes r
        JOIN active_service_provider sp ON r.provider_id = sp.provider_id
        WHERE r.route_id = ? AND r.status = 'approved'
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query'], 500);
    }

    $stmt->bind_param('i', $routeId);
    $stmt->execute();
    $result = $stmt->get_result();

    $route = $result->fetch_assoc();
    if (!$route) {
        send_json(['success' => false, 'message' => 'Route not found or not approved'], 404);
    }

    $calculation = calculatePhilippinesRate($route, $weight, $cargoType, $serviceLevel, $deliveryType, $shipmentType, $targetCurrency, $tariffSource);

    send_json([
        'success' => true,
        'route_id' => $routeId,
        'provider_id' => (int)$route['provider_id'],
        'carrier_type' => $route['carrier_type'],
        'cargo_weight' => $weight,
        'cargo_type' => $cargoType,
        'service_level' => $serviceLevel,
        'delivery_type' => $calculation['delivery_type'],
        'shipment_type' => $shipmentType,
        'target_currency' => $targetCurrency,
        'tariff_source' => $tariffSource,
        'base_rate' => $calculation['base_rate'],
        'tariff_amount' => $calculation['tariff_amount'],
        'total_rate' => $calculation['total_rate'],
        'converted_rate' => $calculation['converted_rate'],
        'exchange_rate' => $calculation['exchange_rate'],
        'formula' => $calculation['formula'],
        'breakdown' => $calculation['breakdown'],
        'tariff_rate' => $calculation['tariff_rate']
    ]);
}

function create_rate() {
    global $conn;

    $data = get_json_body();

    $routeId = (int)($data['route_id'] ?? 0);
    $providerId = (int)($data['provider_id'] ?? 0);
    $carrierType = (string)($data['carrier_type'] ?? '');
    $baseRate = (float)($data['base_rate'] ?? 0);
    $tariffAmount = (float)($data['tariff_amount'] ?? 0);
    $totalRate = (float)($data['total_rate'] ?? 0);
    $formula = (string)($data['formula'] ?? '');

    if ($routeId <= 0 || $providerId <= 0 || $totalRate <= 0) {
        send_json(['success' => false, 'message' => 'Missing required fields'], 400);
    }

    $status = pick_status('calculated_rates', 'status', ['pending', 'Pending']);
    if ($status === null) {
        $status = 'pending';
    }

    $hasUpdatedAt = column_exists('calculated_rates', 'updated_at');

    $query = "
        INSERT INTO calculated_rates
        (route_id, provider_id, carrier_type, unit, quantity, total_rate, base_rate, tariff_amount, ai_calculated, calculation_details, status" . ($hasUpdatedAt ? ", updated_at" : "") . ")
        VALUES (?, ?, ?, 'per shipment', 1, ?, ?, ?, 1, ?, ?" . ($hasUpdatedAt ? ", NOW()" : "") . ")
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare insert'], 500);
    }

    $stmt->bind_param('iisdddss', $routeId, $providerId, $carrierType, $totalRate, $baseRate, $tariffAmount, $formula, $status);

    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to save rate'], 500);
    }

    send_json(['success' => true, 'message' => 'Rate calculated and saved successfully', 'rate_id' => (int)$conn->insert_id], 201);
}

function get_rate($rateId) {
    global $conn;

    $query = "
        SELECT
            cr.*,
            r.distance_km,
            r.eta_min,
            sp.company_name as provider_name,
            np1.point_name as origin,
            np2.point_name as destination
        FROM calculated_rates cr
        JOIN routes r ON cr.route_id = r.route_id
        JOIN active_service_provider sp ON cr.provider_id = sp.provider_id
        JOIN network_points np1 ON r.origin_id = np1.point_id
        JOIN network_points np2 ON r.destination_id = np2.point_id
        WHERE cr.id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare query'], 500);
    }

    $stmt->bind_param('i', $rateId);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if (!$row) {
        send_json(['success' => false, 'message' => 'Rate not found'], 404);
    }

    send_json(['success' => true, 'rate' => $row]);
}

function approve_rate($rateId) {
    global $conn;

    $status = pick_status('calculated_rates', 'status', ['approved', 'Approved']);
    if ($status === null) {
        $status = 'approved';
    }

    $hasUpdatedAt = column_exists('calculated_rates', 'updated_at');
    $sql = 'UPDATE calculated_rates SET status = ?' . ($hasUpdatedAt ? ', updated_at = NOW()' : '') . ' WHERE id = ?';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare update'], 500);
    }

    $stmt->bind_param('si', $status, $rateId);
    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to approve rate'], 500);
    }

    send_json(['success' => true, 'message' => 'Rate approved successfully']);
}

function reject_rate($rateId) {
    global $conn;

    $data = get_json_body();
    $reason = (string)($data['rejection_reason'] ?? '');

    $status = pick_status('calculated_rates', 'status', ['rejected', 'Rejected']);
    if ($status === null) {
        $status = 'rejected';
    }

    $hasUpdatedAt = column_exists('calculated_rates', 'updated_at');
    $sql = 'UPDATE calculated_rates SET status = ?' . ($hasUpdatedAt ? ', updated_at = NOW()' : '') . ' WHERE id = ?';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare update'], 500);
    }

    $stmt->bind_param('si', $status, $rateId);
    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to reject rate'], 500);
    }

    if ($reason !== '') {
        send_json(['success' => true, 'message' => 'Rate rejected successfully', 'rejection_reason' => $reason]);
    }

    send_json(['success' => true, 'message' => 'Rate rejected successfully']);
}

function delete_rate($rateId) {
    global $conn;

    $stmt = $conn->prepare('DELETE FROM calculated_rates WHERE id = ?');
    if (!$stmt) {
        send_json(['success' => false, 'message' => 'Failed to prepare delete'], 500);
    }

    $stmt->bind_param('i', $rateId);
    if (!$stmt->execute()) {
        send_json(['success' => false, 'message' => 'Failed to delete rate'], 500);
    }

    send_json(['success' => true, 'message' => 'Rate deleted successfully']);
}
