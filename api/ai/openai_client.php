<?php

require_once __DIR__ . '/ai_utils.php';

function openai_chat_json($messages, $options = []) {
    $apiKey = ai_env('OPENAI_API_KEY');
    if (!$apiKey) {
        ai_send_json([
            'success' => false,
            'message' => 'OPENAI_API_KEY is not configured on the server'
        ], 500);
    }

    $model = ai_env('OPENAI_MODEL', 'gpt-4o-mini');
    $temperature = isset($options['temperature']) ? (float)$options['temperature'] : 0.2;
    $maxTokens = isset($options['max_tokens']) ? (int)$options['max_tokens'] : 500;

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => $temperature,
        'max_tokens' => $maxTokens,
        'response_format' => ['type' => 'json_object']
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);

    $raw = curl_exec($ch);
    $errno = curl_errno($ch);
    $err = $errno ? curl_error($ch) : null;
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno) {
        ai_send_json([
            'success' => false,
            'message' => 'OpenAI request failed: ' . $err
        ], 502);
    }

    $decoded = json_decode((string)$raw, true);
    if (!is_array($decoded)) {
        ai_send_json([
            'success' => false,
            'message' => 'OpenAI returned invalid JSON',
            'raw' => $raw
        ], 502);
    }

    if ($http < 200 || $http >= 300) {
        ai_send_json([
            'success' => false,
            'message' => 'OpenAI error',
            'status' => $http,
            'error' => $decoded
        ], 502);
    }

    $content = $decoded['choices'][0]['message']['content'] ?? '';
    $out = json_decode((string)$content, true);
    if (!is_array($out)) {
        $out = ai_extract_json_object($content);
    }

    if (!is_array($out)) {
        ai_send_json([
            'success' => false,
            'message' => 'OpenAI did not return valid JSON object',
            'raw' => $content
        ], 502);
    }

    return $out;
}
