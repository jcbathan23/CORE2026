<?php

require_once __DIR__ . '/ai_utils.php';
require_once __DIR__ . '/openai_client.php';

ai_handle_cors_and_options();
ai_require_admin_session();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    ai_send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

require_once __DIR__ . '/../../connect.php';

$body = ai_get_json_body();
$routeId = isset($body['route_id']) ? (int)$body['route_id'] : 0;
if ($routeId <= 0) {
    ai_send_json(['success' => false, 'message' => 'Missing route_id'], 400);
}

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
        np1.point_name AS origin_name,
        np2.point_name AS destination_name,
        np1.latitude AS origin_lat,
        np1.longitude AS origin_lng,
        np2.latitude AS destination_lat,
        np2.longitude AS destination_lng
    FROM routes r
    LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
    LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
    WHERE r.route_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    ai_send_json(['success' => false, 'message' => 'Failed to prepare route query'], 500);
}
$stmt->bind_param('i', $routeId);
$stmt->execute();
$res = $stmt->get_result();
$route = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$route) {
    ai_send_json(['success' => false, 'message' => 'Route not found'], 404);
}

$distance = isset($route['distance_km']) ? (float)$route['distance_km'] : 0.0;
$etaMin = isset($route['eta_min']) ? (int)$route['eta_min'] : 0;
$carrier = strtolower((string)($route['carrier_type'] ?? ''));

$speedKmph = null;
if ($distance > 0 && $etaMin > 0) {
    $speedKmph = round($distance / ($etaMin / 60.0), 2);
}

$thresholds = [
    'land' => ['min' => 5, 'max' => 120],
    'sea' => ['min' => 2, 'max' => 60],
    'air' => ['min' => 50, 'max' => 900]
];
$th = $thresholds[$carrier] ?? $thresholds['land'];

$unrealisticEta = null;
if ($speedKmph !== null) {
    $unrealisticEta = ($speedKmph < $th['min'] || $speedKmph > $th['max']);
}

$facts = [
    'route' => [
        'route_id' => (int)$route['route_id'],
        'origin' => $route['origin_name'] ?? null,
        'destination' => $route['destination_name'] ?? null,
        'carrier_type' => $route['carrier_type'] ?? null,
        'provider_id' => isset($route['provider_id']) ? (int)$route['provider_id'] : null,
        'distance_km' => $distance,
        'eta_min' => $etaMin,
        'status' => $route['status'] ?? null,
        'origin_lat' => $route['origin_lat'] ?? null,
        'origin_lng' => $route['origin_lng'] ?? null,
        'destination_lat' => $route['destination_lat'] ?? null,
        'destination_lng' => $route['destination_lng'] ?? null
    ],
    'derived' => [
        'speed_kmph' => $speedKmph,
        'speed_thresholds_kmph' => $th,
        'unrealistic_eta' => $unrealisticEta
    ]
];

$messages = [
    [
        'role' => 'system',
        'content' => 'You are a route feasibility analyst for logistics. Use ONLY the provided facts and derived metrics. Do not guess weather or traffic. Output JSON: route_status (VALID|REVIEW|INVALID), estimated_delay_risk (LOW|MEDIUM|HIGH), suggested_adjustment (string), notes (string).'
    ],
    [
        'role' => 'user',
        'content' => json_encode([
            'task' => 'Validate route feasibility and ETA sanity',
            'facts' => $facts
        ])
    ]
];

$ai = openai_chat_json($messages, ['temperature' => 0.1, 'max_tokens' => 350]);

$routeStatus = strtoupper((string)($ai['route_status'] ?? 'REVIEW'));
if (!in_array($routeStatus, ['VALID', 'REVIEW', 'INVALID'], true)) {
    $routeStatus = 'REVIEW';
}

$delayRisk = strtoupper((string)($ai['estimated_delay_risk'] ?? 'MEDIUM'));
if (!in_array($delayRisk, ['LOW', 'MEDIUM', 'HIGH'], true)) {
    $delayRisk = 'MEDIUM';
}

$suggested = ai_safe_string($ai['suggested_adjustment'] ?? '', '');
$notes = ai_safe_string($ai['notes'] ?? '', '');

ai_send_json([
    'success' => true,
    'route_id' => $routeId,
    'route_status' => $routeStatus,
    'estimated_delay_risk' => $delayRisk,
    'suggested_adjustment' => $suggested,
    'notes' => $notes,
    'facts_used' => $facts
]);
