<?php
/* =========================================
   CORE 2 – BOOK SHIPMENT VIA CORE 3 ROUTES API
   Single-file implementation
========================================= */

/* ---------- HEADERS ---------- */
header("Content-Type: application/json; charset=UTF-8");

/* ---------- CONFIG ---------- */
define("CORE3_ROUTES_API", "https://core3-domain/api/routes_api.php");
define("CORE2_API_KEY", "CORE2_SECURE_KEY_2026");
define("API_TIMEOUT", 15);
define("LOG_FILE", __DIR__ . "/core2.log");

function get_base_url() {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function core2_booking_api_url() {
    $base = get_base_url();
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/api')), '/');
    if ($dir === '') {
        $dir = '/api';
    }
    return $base . $dir . '/booking_api.php/bookings';
}

/* ---------- LOGGER (AUDIT TRAIL) ---------- */
function core2Log($action, $data = []) {
    $log = [
        "timestamp" => date("Y-m-d H:i:s"),
        "action" => $action,
        "data" => $data,
        "ip" => $_SERVER['REMOTE_ADDR'] ?? "CLI"
    ];

    file_put_contents(
        LOG_FILE,
        json_encode($log) . PHP_EOL,
        FILE_APPEND
    );
}

/* ---------- READ INPUT ---------- */
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "Invalid JSON payload"
    ]);
    exit();
}

/* ---------- REQUIRED FIELDS ---------- */
$shipment_id    = $input['shipment_id'] ?? null;
$origin         = $input['origin'] ?? ($input['origin_address'] ?? null);
$destination    = $input['destination'] ?? ($input['destination_address'] ?? null);
$price          = $input['price'] ?? ($input['estimated_cost'] ?? null);
$payment_method = $input['payment_method'] ?? null;

if (!$shipment_id || !$origin || !$destination || !$price || !$payment_method) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "error" => "Missing required fields"
    ]);
    exit();
}

/* ---------- CORE 2 → CORE 3 (ROUTES API) ---------- */
$payload = [
    "shipment_id" => $shipment_id,
    "origin" => $origin,
    "destination" => $destination,
    "price" => $price,
    "payment_method" => $payment_method
];

$ch = curl_init(CORE3_ROUTES_API);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-API-KEY: " . CORE2_API_KEY
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => API_TIMEOUT
]);

$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    core2Log("CORE3_CONNECTION_ERROR", curl_error($ch));
    curl_close($ch);

    http_response_code(502);
    echo json_encode([
        "success" => false,
        "error" => "Unable to connect to Core 3"
    ]);
    exit();
}

curl_close($ch);

/* ---------- LOG TRANSACTION ---------- */
core2Log("CORE3_BOOKSHIPMENT", [
    "request" => $payload,
    "status" => $status,
    "response" => $response
]);

$core3Response = json_decode($response, true);

/* ---------- HANDLE CORE 3 RESPONSE ---------- */
if (!$core3Response || !isset($core3Response['success']) || !$core3Response['success']) {
    http_response_code(502);
    echo json_encode([
        "success" => false,
        "error" => "Shipment booking failed in Core 3",
        "core3_response" => $core3Response
    ]);
    exit();
}

/* ---------- CREATE CORE2 BOOKING (so it shows in admin/manage_routes.php) ---------- */
$core2BookingUrl = core2_booking_api_url();

$customerName = $input['customer_name'] ?? ($input['sender_name'] ?? null);
$carrierType = $input['carrier_type'] ?? ($input['mode'] ?? null);
$providerId = $input['provider_id'] ?? null;

// Keep booking creation resilient: manage_routes.php needs these fields to display rows.
if (!$customerName) {
    $customerName = 'Unknown Sender';
}
if (!$carrierType) {
    $carrierType = 'land';
}

$core3Data = (is_array($core3Response) && isset($core3Response['data']) && is_array($core3Response['data']))
    ? $core3Response['data']
    : [];

$specialNotes = [];
$specialNotes[] = 'CORE3 shipment_id: ' . $shipment_id;
if (isset($core3Response['data'])) {
    $specialNotes[] = 'CORE3 response: ' . json_encode($core3Response['data']);
}

$core2Payload = [
    'customer_name' => $customerName,
    'customer_email' => $input['customer_email'] ?? null,
    'customer_phone' => $input['customer_phone'] ?? null,
    'origin_address' => $origin,
    'destination_address' => $destination,
    'carrier_type' => $carrierType,
    'cargo_type' => $input['cargo_type'] ?? 'general',
    'weight' => $input['weight'] ?? null,
    'volume' => $input['volume'] ?? null,
    'dimensions' => $input['dimensions'] ?? null,
    'distance_km' => $input['distance_km'] ?? ($core3Data['distance_km'] ?? null),
    'estimated_cost' => $price,
    'payment_method' => $payment_method,
    'receiver_name' => $input['receiver_name'] ?? null,
    'package' => $input['package'] ?? null,
    'special_instructions' => $input['special_instructions'] ?? implode(' | ', $specialNotes),
    'provider_id' => $providerId,
    'origin_lat' => $input['origin_lat'] ?? ($core3Data['origin_lat'] ?? null),
    'origin_lng' => $input['origin_lng'] ?? ($core3Data['origin_lng'] ?? null),
    'destination_lat' => $input['destination_lat'] ?? ($core3Data['destination_lat'] ?? null),
    'destination_lng' => $input['destination_lng'] ?? ($core3Data['destination_lng'] ?? null)
];

$ch2 = curl_init($core2BookingUrl);
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($core2Payload),
    CURLOPT_TIMEOUT => API_TIMEOUT
]);

$core2ResponseRaw = curl_exec($ch2);
$core2Status = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$core2CurlErr = curl_errno($ch2) ? curl_error($ch2) : null;
curl_close($ch2);

core2Log('CORE2_CREATE_BOOKING', [
    'url' => $core2BookingUrl,
    'request' => $core2Payload,
    'status' => $core2Status,
    'curl_error' => $core2CurlErr,
    'response' => $core2ResponseRaw
]);

if ($core2CurlErr) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Core3 booking succeeded but Core2 booking failed (connection error)',
        'core3_response' => $core3Response,
        'core2_error' => $core2CurlErr
    ]);
    exit();
}

$core2Response = json_decode($core2ResponseRaw, true);
if ($core2Status < 200 || $core2Status >= 300 || !$core2Response || empty($core2Response['success'])) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'error' => 'Core3 booking succeeded but Core2 booking failed',
        'core3_response' => $core3Response,
        'core2_status' => $core2Status,
        'core2_response' => $core2Response,
        'core2_raw' => $core2ResponseRaw
    ]);
    exit();
}

/* ---------- SUCCESS RESPONSE (CORE 2) ---------- */
echo json_encode([
    "success" => true,
    "message" => "Shipment booked successfully via Core 3 and created in Core 2 bookings",
    "core3" => $core3Response,
    "core2" => $core2Response
]);
