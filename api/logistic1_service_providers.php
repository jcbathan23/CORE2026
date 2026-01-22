<?php
/**
 * Logistic1 Service Providers API Endpoint
 * Fetches service provider data from Logistic1 API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the service provider data access class
require_once 'service_provider_data_access.php';

// Helper function to fetch data using the pull script
function fetchFromPullScript() {
    $pullScriptUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/core2_pull_service_providers.php';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $pullScriptUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return [
            'success' => false,
            'error' => 'Failed to connect to pull script',
            'message' => 'cURL error: ' . $curlError
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'Pull script returned HTTP ' . $httpCode,
            'message' => 'Response: ' . substr($response, 0, 500)
        ];
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        return [
            'success' => false,
            'error' => 'Invalid JSON from pull script',
            'message' => 'Response: ' . substr($response, 0, 500)
        ];
    }
    
    return $data;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'list';
    
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    // Get all service providers from Logistic1 using pull script
                    $result = fetchFromPullScript();
                    
                    if ($result['success']) {
                        echo json_encode([
                            'success' => true,
                            'data' => $result['data'],
                            'count' => $result['count'],
                            'api_version' => $result['api_version'] ?? 'v1',
                            'timestamp' => $result['timestamp'] ?? date('Y-m-d H:i:s'),
                            'source' => $result['source'] ?? 'Logistic1 API via Pull Script'
                        ]);
                    } else {
                        // Fallback to direct API access if pull script fails
                        $fallbackResult = ServiceProviderDataAccess::getAllServiceProviders();
                        
                        if ($fallbackResult['success']) {
                            echo json_encode([
                                'success' => true,
                                'data' => $fallbackResult['data'],
                                'count' => $fallbackResult['count'],
                                'api_version' => $fallbackResult['api_version'] ?? 'v1',
                                'timestamp' => $fallbackResult['timestamp'] ?? date('Y-m-d H:i:s'),
                                'source' => 'Logistic1 API (Direct Fallback)'
                            ]);
                        } else {
                            http_response_code(500);
                            echo json_encode([
                                'success' => false,
                                'error' => $result['error'] . ' | Fallback: ' . $fallbackResult['error'],
                                'message' => 'Both pull script and direct API failed',
                                'source' => 'Logistic1 API'
                            ]);
                        }
                    }
                    break;
                    
                case 'get':
                    // Get specific service provider by ID
                    $id = $_GET['id'] ?? null;
                    
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Missing provider ID',
                            'message' => 'Provider ID is required'
                        ]);
                        break;
                    }
                    
                    $result = ServiceProviderDataAccess::getServiceProviderById($id);
                    
                    if ($result['success']) {
                        echo json_encode([
                            'success' => true,
                            'data' => $result['data'],
                            'timestamp' => $result['timestamp'],
                            'source' => 'Logistic1 API'
                        ]);
                    } else {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => $result['error'],
                            'message' => 'Service provider not found',
                            'source' => 'Logistic1 API'
                        ]);
                    }
                    break;
                    
                case 'search':
                    // Search service providers
                    $query = $_GET['query'] ?? '';
                    
                    if (empty($query)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Missing search query',
                            'message' => 'Search query is required'
                        ]);
                        break;
                    }
                    
                    // Get all providers first using pull script
                    $allProviders = fetchFromPullScript();
                    
                    if ($allProviders['success']) {
                        $searchTerm = strtolower($query);
                        $matchingProviders = [];
                        
                        foreach ($allProviders['data'] as $provider) {
                            $searchFields = [
                                $provider['name'] ?? '',
                                $provider['contact'] ?? '',
                                $provider['email'] ?? '',
                                $provider['phone'] ?? '',
                                $provider['hub_location'] ?? '',
                                $provider['service_areas'] ?? '',
                                $provider['service_capabilities'] ?? '',
                                $provider['facility_type'] ?? '',
                                $provider['type'] ?? ''
                            ];
                            
                            foreach ($searchFields as $field) {
                                if (strpos(strtolower($field), $searchTerm) !== false) {
                                    $matchingProviders[] = $provider;
                                    break; // Found match, no need to check other fields
                                }
                            }
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $matchingProviders,
                            'count' => count($matchingProviders),
                            'query' => $query,
                            'timestamp' => date('Y-m-d H:i:s'),
                            'source' => 'Logistic1 API via Pull Script'
                        ]);
                    } else {
                        // Fallback to direct API search
                        $result = ServiceProviderDataAccess::searchServiceProviders($query);
                        
                        if ($result['success']) {
                            echo json_encode([
                                'success' => true,
                                'data' => $result['data'],
                                'count' => $result['count'],
                                'query' => $result['query'],
                                'timestamp' => $result['timestamp'],
                                'source' => 'Logistic1 API (Direct Fallback)'
                            ]);
                        } else {
                            http_response_code(500);
                            echo json_encode([
                                'success' => false,
                                'error' => $result['error'],
                                'message' => 'Failed to search service providers',
                                'source' => 'Logistic1 API'
                            ]);
                        }
                    }
                    break;
                    
                case 'stats':
                    // Get service provider statistics using pull script
                    $allProviders = fetchFromPullScript();
                    
                    if ($allProviders['success']) {
                        $total = count($allProviders['data']);
                        $serviceOnly = 0;
                        $bothTypes = 0;
                        $active = 0;
                        
                        foreach ($allProviders['data'] as $provider) {
                            $type = strtolower($provider['type'] ?? '');
                            $status = strtolower($provider['status'] ?? '');
                            
                            if ($type === 'service_provider') {
                                $serviceOnly++;
                            } elseif ($type === 'both') {
                                $bothTypes++;
                            }
                            
                            if ($status === 'active' || $status === '1' || $status === 'approved') {
                                $active++;
                            }
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => [
                                'total_service_providers' => $total,
                                'service_providers_only' => $serviceOnly,
                                'both_types' => $bothTypes,
                                'approved_count' => $active,
                                'active_count' => $active
                            ],
                            'timestamp' => date('Y-m-d H:i:s'),
                            'source' => 'Logistic1 API via Pull Script'
                        ]);
                    } else {
                        // Fallback to direct API stats
                        $result = ServiceProviderDataAccess::getServiceProviderStats();
                        
                        if ($result['success']) {
                            echo json_encode([
                                'success' => true,
                                'data' => $result['data'],
                                'timestamp' => $result['timestamp'],
                                'source' => 'Logistic1 API (Direct Fallback)'
                            ]);
                        } else {
                            http_response_code(500);
                            echo json_encode([
                                'success' => false,
                                'error' => $result['error'],
                                'message' => 'Failed to get statistics',
                                'source' => 'Logistic1 API'
                            ]);
                        }
                    }
                    break;
                    
                case 'contract':
                    // Get service provider contract
                    $id = $_GET['id'] ?? null;
                    
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'Missing provider ID',
                            'message' => 'Provider ID is required for contract lookup'
                        ]);
                        break;
                    }
                    
                    $result = ServiceProviderDataAccess::getServiceProviderContract($id);
                    
                    if ($result['success']) {
                        echo json_encode([
                            'success' => true,
                            'data' => $result['data'],
                            'timestamp' => $result['timestamp'],
                            'source' => 'Logistic1 API'
                        ]);
                    } else {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => $result['error'],
                            'message' => 'Contract not found for this provider',
                            'source' => 'Logistic1 API'
                        ]);
                    }
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid action',
                        'message' => 'Supported actions: list, get, search, stats, contract',
                        'available_actions' => [
                            'list' => 'Get all service providers',
                            'get' => 'Get service provider by ID (requires id parameter)',
                            'search' => 'Search service providers (requires query parameter)',
                            'stats' => 'Get service provider statistics',
                            'contract' => 'Get service provider contract (requires id parameter)'
                        ]
                    ]);
                    break;
            }
            break;
            
        case 'POST':
            // For future implementation - import providers to local database
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'import') {
                // This would be implemented to import providers from Logistic1 to local database
                echo json_encode([
                    'success' => false,
                    'error' => 'Import functionality not yet implemented',
                    'message' => 'This endpoint will be used to import providers from Logistic1 to local database'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid POST action',
                    'message' => 'Supported POST actions: import'
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed',
                'message' => 'Only GET and POST methods are supported'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'source' => 'Logistic1 API Endpoint'
    ]);
}
?>
