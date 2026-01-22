<?php

function ai_send_json($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
    exit();
}

function ai_handle_cors_and_options() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

function ai_get_json_body() {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function ai_require_admin_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $acct = isset($_SESSION['account_type']) ? (int)$_SESSION['account_type'] : null;
    if (!isset($_SESSION['email']) || $acct === null || $acct === 3) {
        ai_send_json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
}

function ai_env($key, $default = null) {
    $v = getenv($key);
    if ($v === false || $v === null || $v === '') {
        return $default;
    }
    return $v;
}

function ai_safe_string($v, $fallback = '') {
    if ($v === null) return $fallback;
    $s = trim((string)$v);
    return $s === '' ? $fallback : $s;
}

function ai_parse_time_to_minutes($time) {
    $time = trim((string)$time);
    if ($time === '') return null;

    $parts = explode(':', $time);
    if (count($parts) < 2) return null;

    $h = (int)$parts[0];
    $m = (int)$parts[1];
    return ($h * 60) + $m;
}

function ai_extract_json_object($text) {
    $text = (string)$text;
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false || $end <= $start) {
        return null;
    }
    $candidate = substr($text, $start, $end - $start + 1);
    $decoded = json_decode($candidate, true);
    return is_array($decoded) ? $decoded : null;
}
