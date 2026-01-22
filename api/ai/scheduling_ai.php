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
$providerId = isset($body['provider_id']) ? (int)$body['provider_id'] : 0;
$routeId = isset($body['route_id']) ? (int)$body['route_id'] : 0;
$scheduleDate = ai_safe_string($body['schedule_date'] ?? '', '');
$scheduleTime = ai_safe_string($body['schedule_time'] ?? '', '');
$excludeScheduleId = isset($body['exclude_schedule_id']) ? (int)$body['exclude_schedule_id'] : 0;

if ($providerId <= 0 || $routeId <= 0 || $scheduleDate === '' || $scheduleTime === '') {
    ai_send_json(['success' => false, 'message' => 'Missing provider_id, route_id, schedule_date, or schedule_time'], 400);
}

$targetMinutes = ai_parse_time_to_minutes($scheduleTime);

$conflict = false;
$nearby = [];

$stmt = null;
if ($excludeScheduleId > 0) {
    $stmt = $conn->prepare('SELECT schedule_id, schedule_date, schedule_time, status FROM schedules WHERE provider_id = ? AND schedule_date = ? AND schedule_id != ?');
} else {
    $stmt = $conn->prepare('SELECT schedule_id, schedule_date, schedule_time, status FROM schedules WHERE provider_id = ? AND schedule_date = ?');
}
if ($stmt) {
    if ($excludeScheduleId > 0) {
        $stmt->bind_param('isi', $providerId, $scheduleDate, $excludeScheduleId);
    } else {
        $stmt->bind_param('is', $providerId, $scheduleDate);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($res && ($row = $res->fetch_assoc())) {
        $rowMin = ai_parse_time_to_minutes($row['schedule_time'] ?? '');
        $diff = null;
        if ($targetMinutes !== null && $rowMin !== null) {
            $diff = abs($rowMin - $targetMinutes);
            if ($diff <= 60) {
                $conflict = true;
                $nearby[] = [
                    'schedule_id' => (int)$row['schedule_id'],
                    'schedule_time' => $row['schedule_time'],
                    'status' => $row['status'],
                    'minutes_diff' => $diff
                ];
            }
        }
    }
    $stmt->close();
}

$sqlRoute = "
    SELECT r.route_id, r.distance_km, r.eta_min, r.carrier_type,
           np1.point_name AS origin, np2.point_name AS destination
    FROM routes r
    LEFT JOIN network_points np1 ON r.origin_id = np1.point_id
    LEFT JOIN network_points np2 ON r.destination_id = np2.point_id
    WHERE r.route_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sqlRoute);
$route = null;
if ($stmt) {
    $stmt->bind_param('i', $routeId);
    $stmt->execute();
    $res = $stmt->get_result();
    $route = $res ? $res->fetch_assoc() : null;
    $stmt->close();
}

$facts = [
    'candidate' => [
        'provider_id' => $providerId,
        'route_id' => $routeId,
        'schedule_date' => $scheduleDate,
        'schedule_time' => $scheduleTime
    ],
    'route' => $route ? [
        'origin' => $route['origin'] ?? null,
        'destination' => $route['destination'] ?? null,
        'distance_km' => isset($route['distance_km']) ? (float)$route['distance_km'] : null,
        'eta_min' => isset($route['eta_min']) ? (int)$route['eta_min'] : null,
        'carrier_type' => $route['carrier_type'] ?? null
    ] : null,
    'conflict_check' => [
        'schedule_conflict' => $conflict,
        'nearby_schedules' => $nearby
    ]
];

$messages = [
    [
        'role' => 'system',
        'content' => 'You are a scheduling optimizer. Use ONLY the provided facts. Output JSON: schedule_conflict (boolean), sla_risk (LOW|MEDIUM|HIGH), optimization (string). If conflict_check.schedule_conflict is true, schedule_conflict must be true.'
    ],
    [
        'role' => 'user',
        'content' => json_encode([
            'task' => 'Detect schedule conflicts and suggest optimization',
            'facts' => $facts
        ])
    ]
];

$ai = openai_chat_json($messages, ['temperature' => 0.1, 'max_tokens' => 250]);

$scheduleConflict = (bool)($ai['schedule_conflict'] ?? false);
if ($conflict) {
    $scheduleConflict = true;
}

$slaRisk = strtoupper((string)($ai['sla_risk'] ?? 'MEDIUM'));
if (!in_array($slaRisk, ['LOW', 'MEDIUM', 'HIGH'], true)) {
    $slaRisk = 'MEDIUM';
}

$optimization = ai_safe_string($ai['optimization'] ?? '', '');

ai_send_json([
    'success' => true,
    'schedule_conflict' => $scheduleConflict,
    'sla_risk' => $slaRisk,
    'optimization' => $optimization,
    'facts_used' => $facts
]);
