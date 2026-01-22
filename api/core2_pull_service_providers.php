<?php
// core2_pull_service_providers.php
header("Content-Type: application/json; charset=UTF-8");

// External Logistic 1 API endpoint
define('LOGISTIC1_URL', 'https://logistic1.slatefreight-ph.com/api/service-providers-simple.php');
define('API_KEY', 'Log1');

// Test different API key values that might work
$possible_api_keys = [
    'Log1',
    'log1', 
    'LOG1',
    'logistic1',
    'Logistic1',
    'LOGISTIC1',
    'api_key',
    'test',
    'demo'
];

// Helper function to send JSON response
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Function to try different API key methods and values
function tryAPICall($url, $possible_keys) {
    // Different parameter names to try
    $param_names = ['api_key', 'key', 'token', 'apikey', 'access_key'];
    
    // Different header names to try  
    $header_names = ['X-API-Key', 'Authorization', 'X-Auth-Token', 'X-Access-Token'];
    
    foreach ($possible_keys as $api_key) {
        // Method 1: URL parameter variations
        foreach ($param_names as $param) {
            $test_url = $url . '?' . $param . '=' . urlencode($api_key);
            $result = testSingleMethod($test_url, [
                'Accept: application/json',
                'User-Agent: CORE2/ServiceProvider-Fetch'
            ], "URL param: $param=$api_key");
            
            if ($result['success']) return $result;
        }
        
        // Method 2: Header variations
        foreach ($header_names as $header_name) {
            if ($header_name === 'Authorization') {
                // Try both Bearer and direct
                $auth_headers = [
                    $header_name . ': Bearer ' . $api_key,
                    $header_name . ': ' . $api_key
                ];
            } else {
                $auth_headers = [$header_name . ': ' . $api_key];
            }
            
            foreach ($auth_headers as $auth_header) {
                $result = testSingleMethod($url, [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    $auth_header,
                    'User-Agent: CORE2/ServiceProvider-Fetch'
                ], "Header: $auth_header");
                
                if ($result['success']) return $result;
            }
        }
        
        // Method 3: Combined URL + Header
        foreach ($param_names as $param) {
            $test_url = $url . '?' . $param . '=' . urlencode($api_key);
            $result = testSingleMethod($test_url, [
                'Accept: application/json',
                'Content-Type: application/json',
                'X-API-Key: ' . $api_key,
                'User-Agent: CORE2/ServiceProvider-Fetch'
            ], "Combined: $param=$api_key + X-API-Key header");
            
            if ($result['success']) return $result;
        }
    }
    
    return [
        'success' => false,
        'error' => 'All API key methods and values failed',
        'message' => 'Tested ' . count($possible_keys) . ' different API keys with multiple methods'
    ];
}

// Helper function to test a single method
function testSingleMethod($url, $headers, $description) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    
    $raw = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // If successful, return the result
    if ($raw !== false && $httpCode == 200) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            // Check if it's a valid response (either no success field, or success=true)
            if (!isset($decoded['success']) || $decoded['success'] === true) {
                return [
                    'success' => true,
                    'data' => $raw,
                    'method_description' => $description,
                    'http_code' => $httpCode,
                    'url' => $url,
                    'headers' => $headers
                ];
            }
        }
    }
    
    return [
        'success' => false,
        'error' => $curlErr ?: "HTTP $httpCode",
        'description' => $description,
        'response' => substr($raw ?: '', 0, 200)
    ];
}

// Try to fetch data from Logistic 1 using different methods and API keys
$apiResult = tryAPICall(LOGISTIC1_URL, $possible_api_keys);

if (!$apiResult['success']) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to connect to Logistic 1: ' . $apiResult['error'],
        'http_code' => $apiResult['http_code'],
        'response_preview' => $apiResult['response'] ?? null
    ], 502);
}

$raw = $apiResult['data'];
$httpCode = $apiResult['http_code'];

// Decode JSON
$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    jsonResponse([
        'success' => false,
        'message' => 'Invalid JSON from Logistic 1',
        'body' => substr($raw, 0, 1000),
    ], 502);
}

// Normalize provider fields
function pick($arr, $keys, $default = null) {
    foreach ($keys as $k) {
        if (isset($arr[$k]) && $arr[$k] !== '') return $arr[$k];
    }
    return $default;
}

$list = [];
if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
    // associative root - check for common data containers
    $candidate = $decoded['data'] ?? $decoded['providers'] ?? $decoded['items'] ?? $decoded['service_providers'] ?? [];
    $list = is_array($candidate) ? $candidate : [];
} else {
    $list = $decoded;
}

$normalized = [];
foreach ($list as $idx => $p) {
    if (!is_array($p)) continue;
    $normalized[] = [
        'id' => pick($p, ['id', 'provider_id', 'ID', 'supplier_id'], $idx + 1),
        'name' => pick($p, ['name','provider_name','company','company_name','supplier_name'], null),
        'type' => pick($p, ['type','provider_type','supplier_type'], null),
        'contact' => pick($p, ['contact','contact_person','contact_name'], null),
        'email' => pick($p, ['email','email_address'], null),
        'phone' => pick($p, ['phone','contact_number','mobile'], null),
        'status' => pick($p, ['status','active'], null),
        'hub_location' => pick($p, ['hub_location','location','address'], null),
        'service_areas' => pick($p, ['service_areas','areas','coverage'], null),
        'service_capabilities' => pick($p, ['service_capabilities','capabilities','services'], null),
        'facility_type' => pick($p, ['facility_type','facility','type'], null),
        'created_at' => pick($p, ['created_at','date_created','created'], null),
        '_raw' => $p
    ];
}

jsonResponse([
    'success' => true,
    'count' => count($normalized),
    'data' => $normalized,
    'source' => 'Logistic1 API',
    'timestamp' => date('Y-m-d H:i:s'),
    'api_version' => 'v1'
]);
?>
