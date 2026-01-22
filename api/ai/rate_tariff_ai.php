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
$totalRate = isset($body['total_rate']) ? (float)$body['total_rate'] : null;
$costEstimate = isset($body['cost_estimate']) ? (float)$body['cost_estimate'] : null;

if ($routeId <= 0 || $totalRate === null || $totalRate <= 0) {
    ai_send_json(['success' => false, 'message' => 'Missing route_id or total_rate'], 400);
}

$profitMarginPct = null;
if ($costEstimate !== null && $costEstimate >= 0 && $totalRate > 0) {
    $profitMarginPct = round((($totalRate - $costEstimate) / $totalRate) * 100, 2);
}

$sql = "
    SELECT
        r.route_id,
        r.provider_id,
        r.carrier_type,
        r.distance_km,
        r.eta_min,
        np1.point_name AS origin,
        np2.point_name AS destination,
        sp.company_name AS provider_name
    FROM routes r
    LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
    LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
    LEFT JOIN active_service_provider sp ON r.provider_id = sp.provider_id
    WHERE r.route_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    ai_send_json(['success' => false, 'message' => 'Failed to prepare route lookup'], 500);
}
$stmt->bind_param('i', $routeId);
$stmt->execute();
$res = $stmt->get_result();
$route = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$route) {
    ai_send_json(['success' => false, 'message' => 'Route not found'], 404);
}

$facts = [
    'route' => [
        'route_id' => (int)$route['route_id'],
        'origin' => $route['origin'] ?? null,
        'destination' => $route['destination'] ?? null,
        'carrier_type' => $route['carrier_type'] ?? null,
        'distance_km' => isset($route['distance_km']) ? (float)$route['distance_km'] : null,
        'eta_min' => isset($route['eta_min']) ? (int)$route['eta_min'] : null,
        'provider_id' => isset($route['provider_id']) ? (int)$route['provider_id'] : null,
        'provider_name' => $route['provider_name'] ?? null
    ],
    'rate' => [
        'base_rate' => isset($body['base_rate']) ? (float)$body['base_rate'] : null,
        'tariff_amount' => isset($body['tariff_amount']) ? (float)$body['tariff_amount'] : null,
        'total_rate' => $totalRate,
        'unit' => $body['unit'] ?? 'per shipment',
        'quantity' => isset($body['quantity']) ? (float)$body['quantity'] : 1,
        'cargo_weight' => isset($body['cargo_weight']) ? (float)$body['cargo_weight'] : null,
        'cargo_type' => $body['cargo_type'] ?? null,
        'service_level' => $body['service_level'] ?? null,
        'delivery_type' => $body['delivery_type'] ?? null,
        'shipment_type' => $body['shipment_type'] ?? null
    ],
    'finance' => [
        'cost_estimate' => $costEstimate,
        'profit_margin_percent' => $profitMarginPct
    ]
];

$messages = [
    [
        'role' => 'system',
        'content' => 'You are a rate & tariff sanity checker. Use ONLY the provided data. Do not invent market prices. Output JSON: rate_status (ACCEPTABLE|REVIEW|UNACCEPTABLE), profit_margin (string, e.g. "18%" or "N/A"), recommendation (APPROVE|REVIEW|REJECT), warning (string).'
    ],
    [
        'role' => 'user',
        'content' => json_encode([
            'task' => 'Validate pricing sanity and margin risk',
            'facts' => $facts
        ])
    ]
];

$ai = openai_chat_json($messages, ['temperature' => 0.1, 'max_tokens' => 300]);

$status = strtoupper((string)($ai['rate_status'] ?? 'REVIEW'));
if (!in_array($status, ['ACCEPTABLE', 'REVIEW', 'UNACCEPTABLE'], true)) {
    $status = 'REVIEW';
}

$rec = strtoupper((string)($ai['recommendation'] ?? 'REVIEW'));
if (!in_array($rec, ['APPROVE', 'REVIEW', 'REJECT'], true)) {
    $rec = 'REVIEW';
}

$profitMarginStr = ai_safe_string($ai['profit_margin'] ?? '', 'N/A');
$warning = ai_safe_string($ai['warning'] ?? '', '');

ai_send_json([
    'success' => true,
    'route_id' => $routeId,
    'rate_status' => $status,
    'profit_margin' => $profitMarginStr,
    'recommendation' => $rec,
    'warning' => $warning,
    'facts_used' => $facts
]);
