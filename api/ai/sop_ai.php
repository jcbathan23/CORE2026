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
$sopId = isset($body['sop_id']) ? (int)$body['sop_id'] : 0;
$action = ai_safe_string($body['action'] ?? '', '');

if ($sopId <= 0 || $action === '') {
    ai_send_json(['success' => false, 'message' => 'Missing sop_id or action'], 400);
}

$stmt = $conn->prepare('SELECT sop_id, title, category, content, status, created_at FROM sop_documents WHERE sop_id = ? LIMIT 1');
if (!$stmt) {
    ai_send_json(['success' => false, 'message' => 'Failed to prepare SOP query'], 500);
}
$stmt->bind_param('i', $sopId);
$stmt->execute();
$res = $stmt->get_result();
$sop = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$sop) {
    ai_send_json(['success' => false, 'message' => 'SOP not found'], 404);
}

$reference = 'SOP-' . str_pad((string)$sopId, 4, '0', STR_PAD_LEFT);

$facts = [
    'sop' => [
        'sop_id' => (int)$sop['sop_id'],
        'reference' => $reference,
        'title' => $sop['title'] ?? null,
        'category' => $sop['category'] ?? null,
        'status' => $sop['status'] ?? null,
        'content' => $sop['content'] ?? null,
        'created_at' => $sop['created_at'] ?? null
    ],
    'action' => $action,
    'context' => isset($body['context']) ? $body['context'] : null
];

$messages = [
    [
        'role' => 'system',
        'content' => 'You are an SOP compliance checker. Use ONLY the provided SOP content and the given action/context. Do not invent rules not present in the SOP text. Output JSON: sop_status (COMPLIANT|NON_COMPLIANT|REVIEW), reference (string), explanation (string).'
    ],
    [
        'role' => 'user',
        'content' => json_encode([
            'task' => 'Check if an action complies with the SOP',
            'facts' => $facts
        ])
    ]
];

$ai = openai_chat_json($messages, ['temperature' => 0.1, 'max_tokens' => 350]);

$status = strtoupper((string)($ai['sop_status'] ?? 'REVIEW'));
if (!in_array($status, ['COMPLIANT', 'NON_COMPLIANT', 'REVIEW'], true)) {
    $status = 'REVIEW';
}

$explanation = ai_safe_string($ai['explanation'] ?? '', '');
$refOut = ai_safe_string($ai['reference'] ?? '', $reference);

ai_send_json([
    'success' => true,
    'sop_status' => $status,
    'reference' => $refOut,
    'explanation' => $explanation,
    'facts_used' => [
        'sop_id' => (int)$sopId,
        'reference' => $reference
    ]
]);
