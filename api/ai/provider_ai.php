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
if ($providerId <= 0) {
    ai_send_json(['success' => false, 'message' => 'Missing provider_id'], 400);
}

$stmt = $conn->prepare('SELECT provider_id, company_name, email, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, status, created_at FROM active_service_provider WHERE provider_id = ? LIMIT 1');
if (!$stmt) {
    ai_send_json(['success' => false, 'message' => 'Failed to prepare provider query'], 500);
}
$stmt->bind_param('i', $providerId);
$stmt->execute();
$res = $stmt->get_result();
$provider = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$provider) {
    ai_send_json(['success' => false, 'message' => 'Provider not found'], 404);
}

$metrics = [
    'routes_total' => 0,
    'routes_approved' => 0,
    'schedules_total' => 0,
    'schedules_completed' => 0,
    'schedules_delayed' => 0,
    'delay_rate' => null
];

$stmt = $conn->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN LOWER(status) = \'approved\' THEN 1 ELSE 0 END) as approved FROM routes WHERE provider_id = ?');
if ($stmt) {
    $stmt->bind_param('i', $providerId);
    $stmt->execute();
    $r = $stmt->get_result();
    $row = $r ? $r->fetch_assoc() : null;
    $stmt->close();
    if ($row) {
        $metrics['routes_total'] = (int)($row['total'] ?? 0);
        $metrics['routes_approved'] = (int)($row['approved'] ?? 0);
    }
}

$stmt = $conn->prepare('SELECT COUNT(*) as total, SUM(CASE WHEN LOWER(status) = \'completed\' THEN 1 ELSE 0 END) as completed, SUM(CASE WHEN LOWER(status) = \'delayed\' THEN 1 ELSE 0 END) as delayed FROM schedules WHERE provider_id = ?');
if ($stmt) {
    $stmt->bind_param('i', $providerId);
    $stmt->execute();
    $r = $stmt->get_result();
    $row = $r ? $r->fetch_assoc() : null;
    $stmt->close();
    if ($row) {
        $metrics['schedules_total'] = (int)($row['total'] ?? 0);
        $metrics['schedules_completed'] = (int)($row['completed'] ?? 0);
        $metrics['schedules_delayed'] = (int)($row['delayed'] ?? 0);
        if ($metrics['schedules_total'] > 0) {
            $metrics['delay_rate'] = round($metrics['schedules_delayed'] / $metrics['schedules_total'], 4);
        }
    }
}

$facts = [
    'provider' => [
        'provider_id' => (int)$provider['provider_id'],
        'company_name' => $provider['company_name'] ?? null,
        'email' => $provider['email'] ?? null,
        'contact_person' => $provider['contact_person'] ?? null,
        'contact_number' => $provider['contact_number'] ?? null,
        'address' => $provider['address'] ?? null,
        'services' => $provider['services'] ?? null,
        'iso_certified' => $provider['iso_certified'] ?? null,
        'business_permit' => $provider['business_permit'] ?? null,
        'company_profile' => $provider['company_profile'] ?? null,
        'status' => $provider['status'] ?? null,
        'created_at' => $provider['created_at'] ?? null
    ],
    'metrics' => $metrics
];

$messages = [
    [
        'role' => 'system',
        'content' => 'You are a logistics compliance and risk assessor. Use ONLY the provided facts. If information is missing, mark it as unknown and avoid guessing. Output a JSON object with keys: provider_score (0-100 integer), recommendation (APPROVE|REVIEW|REJECT), risk_level (LOW|MEDIUM|HIGH), notes (string), red_flags (array of strings).'
    ],
    [
        'role' => 'user',
        'content' => json_encode([
            'task' => 'Score provider eligibility and detect red flags',
            'facts' => $facts
        ])
    ]
];

$ai = openai_chat_json($messages, ['temperature' => 0.1, 'max_tokens' => 450]);

$score = isset($ai['provider_score']) ? (int)$ai['provider_score'] : null;
if ($score === null) {
    $score = 50;
}
$score = max(0, min(100, $score));

$rec = strtoupper((string)($ai['recommendation'] ?? 'REVIEW'));
if (!in_array($rec, ['APPROVE', 'REVIEW', 'REJECT'], true)) {
    $rec = 'REVIEW';
}

$risk = strtoupper((string)($ai['risk_level'] ?? 'MEDIUM'));
if (!in_array($risk, ['LOW', 'MEDIUM', 'HIGH'], true)) {
    $risk = 'MEDIUM';
}

$notes = ai_safe_string($ai['notes'] ?? '', '');
$redFlags = $ai['red_flags'] ?? [];
if (!is_array($redFlags)) {
    $redFlags = [];
}
$redFlags = array_values(array_filter(array_map(function ($v) {
    return ai_safe_string($v, '');
}, $redFlags), function ($v) {
    return $v !== '';
}));

ai_send_json([
    'success' => true,
    'provider_id' => $providerId,
    'provider_score' => $score,
    'recommendation' => $rec,
    'risk_level' => $risk,
    'notes' => $notes,
    'red_flags' => $redFlags,
    'facts_used' => $facts
]);
