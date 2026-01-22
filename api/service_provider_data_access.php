<?php
/**
 * Service Provider Data Access Class
 * For use by core modules to access service provider information
 * Uses HTTP API calls to http://localhost:8000/api/service-providers-simple.php
 */

// API Configuration
define('API_BASE_URL', 'https://logistic1.slatefreight-ph.com/api/service-providers-simple.php');
define('CONTRACT_API_URL', 'https://logistic1.slatefreight-ph.com/api/contracts.php');
define('API_KEY', 'Log1');

class ServiceProviderDataAccess {
    
    /**
     * Cache for API responses to avoid multiple calls
     */
    private static $cache = [];
    
    /**
     * Try different API key authentication methods
     * @param string $baseUrl Base API URL
     * @return array API response data
     */
    private static function tryAPIKeyMethods($baseUrl) {
        $methods = [
            // Method 1: Both URL parameter and header
            [
                'url' => $baseUrl . '?api_key=' . urlencode(API_KEY),
                'headers' => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'X-API-Key: ' . API_KEY,
                    'User-Agent: CORE2/ServiceProviderDataAccess'
                ]
            ],
            // Method 2: Only URL parameter
            [
                'url' => $baseUrl . '?api_key=' . urlencode(API_KEY),
                'headers' => [
                    'Accept: application/json',
                    'User-Agent: CORE2/ServiceProviderDataAccess'
                ]
            ],
            // Method 3: Only header
            [
                'url' => $baseUrl,
                'headers' => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'X-API-Key: ' . API_KEY,
                    'User-Agent: CORE2/ServiceProviderDataAccess'
                ]
            ],
            // Method 4: Authorization header
            [
                'url' => $baseUrl,
                'headers' => [
                    'Accept: application/json',
                    'Authorization: Bearer ' . API_KEY,
                    'User-Agent: CORE2/ServiceProviderDataAccess'
                ]
            ]
        ];
        
        foreach ($methods as $method) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $method['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER => $method['headers'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            
            $response = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // If successful, return the result
            if ($response !== false && $httpCode == 200) {
                $data = json_decode($response, true);
                if (is_array($data) && (!isset($data['success']) || $data['success'] !== false)) {
                    return $data ?: [
                        'success' => false,
                        'error' => 'Invalid JSON response',
                        'message' => 'API returned invalid JSON from: ' . $method['url']
                    ];
                }
            }
        }
        
        // If all methods failed, return error
        return [
            'success' => false,
            'error' => 'All API key methods failed',
            'message' => 'Unable to authenticate with any method. HTTP: ' . ($httpCode ?? 'unknown') . ' | cURL: ' . ($curlErr ?? 'none')
        ];
    }

    /**
     * Make HTTP API call with caching
     * @param string $url API endpoint URL
     * @return array API response data
     */
    private static function makeAPICall($url) {
        // Check cache first
        if (isset(self::$cache[$url])) {
            return self::$cache[$url];
        }
        
        $result = null;
        
        // Prefer cURL in production for better compatibility
        if (function_exists('curl_init')) {
            $result = self::tryAPIKeyMethods($url);
        } else {
            // Fallback to file_get_contents if cURL isn't available
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'X-API-Key: ' . API_KEY,
                        'Content-Type: application/json'
                    ],
                    'timeout' => 10,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ]);
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                $result = [
                    'success' => false,
                    'error' => 'Failed to connect to API',
                    'message' => 'Could not reach the API endpoint: ' . $url
                ];
            } else {
                $data = json_decode($response, true);
                $result = $data ?: [
                    'success' => false,
                    'error' => 'Invalid JSON response',
                    'message' => 'API returned invalid JSON from: ' . $url
                ];
            }
        }
        
        // Cache the result
        self::$cache[$url] = $result;
        return $result;
    }
    
    
    /**
     * Get all service providers via API
     * @return array Service providers data
     */
    public static function getAllServiceProviders() {
        try {
            $url = API_BASE_URL . '?api_key=' . API_KEY;
            $result = self::makeAPICall($url);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'data' => $result['data'],
                    'count' => $result['count'],
                    'api_version' => $result['api_version'] ?? 'v1',
                    'timestamp' => $result['timestamp'] ?? date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['error'],
                    'data' => [],
                    'count' => 0
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API call error: ' . $e->getMessage(),
                'data' => [],
                'count' => 0
            ];
        }
    }
    
    /**
     * Get service provider by ID via API
     * @param int $providerId Service provider ID
     * @return array Service provider data
     */
    public static function getServiceProviderById($providerId) {
        try {
            // Get all providers and filter by ID
            $allProviders = self::getAllServiceProviders();
            
            if ($allProviders['success']) {
                foreach ($allProviders['data'] as $provider) {
                    if ($provider['id'] == $providerId) {
                        return [
                            'success' => true,
                            'data' => $provider,
                            'timestamp' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                
                return [
                    'success' => false,
                    'error' => 'Service provider not found',
                    'data' => null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $allProviders['error'],
                    'data' => null
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API call error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Search service providers via API
     * @param string $query Search query
     * @return array Matching service providers
     */
    public static function searchServiceProviders($query) {
        try {
            // Get all providers and filter by search query
            $allProviders = self::getAllServiceProviders();
            
            if ($allProviders['success']) {
                $searchTerm = strtolower($query);
                $matchingProviders = [];
                
                foreach ($allProviders['data'] as $provider) {
                    $searchFields = [
                        $provider['supplier_name'],
                        $provider['contact_person'],
                        $provider['email'],
                        $provider['hub_location'],
                        $provider['service_capabilities'],
                        $provider['facility_type'],
                        $provider['service_areas']
                    ];
                    
                    foreach ($searchFields as $field) {
                        if (strpos(strtolower($field), $searchTerm) !== false) {
                            $matchingProviders[] = $provider;
                            break; // Found match, no need to check other fields
                        }
                    }
                }
                
                return [
                    'success' => true,
                    'data' => $matchingProviders,
                    'count' => count($matchingProviders),
                    'query' => $query,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $allProviders['error'],
                    'data' => [],
                    'count' => 0
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API call error: ' . $e->getMessage(),
                'data' => [],
                'count' => 0
            ];
        }
    }
    
    /**
     * Get service provider contract via API
     * @param int $providerId Service provider ID
     * @return array Contract data
     */
    public static function getServiceProviderContract($providerId) {
        try {
            $url = CONTRACT_API_URL . '?supplier_id=' . $providerId . '&api_key=' . API_KEY;
            $result = self::makeAPICall($url);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'data' => $result['data'],
                    'timestamp' => $result['timestamp'] ?? date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['error'],
                    'data' => null
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API call error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Get service provider statistics via API
     * @return array Statistics data
     */
    public static function getServiceProviderStats() {
        try {
            $allProviders = self::getAllServiceProviders();
            
            if ($allProviders['success']) {
                $total = count($allProviders['data']);
                $serviceOnly = 0;
                $bothTypes = 0;
                
                foreach ($allProviders['data'] as $provider) {
                    if ($provider['supplier_type'] === 'service_provider') {
                        $serviceOnly++;
                    } elseif ($provider['supplier_type'] === 'both') {
                        $bothTypes++;
                    }
                }
                
                return [
                    'success' => true,
                    'data' => [
                        'total_service_providers' => $total,
                        'service_providers_only' => $serviceOnly,
                        'both_types' => $bothTypes,
                        'approved_count' => $total
                    ],
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $allProviders['error'],
                    'data' => null
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API call error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}

// Example usage for core modules
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    // This is a direct access to the file, show usage examples
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Service Provider Data Access - Usage Examples</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";
    echo "</head><body>";
    
    echo "<h1>🏢 Service Provider Data Access Class</h1>";
    echo "<p>This class provides methods for core modules to access service provider data.</p>";
    
    echo "<h2>📋 Available Methods:</h2>";
    echo "<ul>";
    echo "<li><strong>getAllServiceProviders()</strong> - Get all service providers</li>";
    echo "<li><strong>getServiceProviderById(\$id)</strong> - Get specific service provider</li>";
    echo "<li><strong>searchServiceProviders(\$query)</strong> - Search service providers</li>";
    echo "<li><strong>getServiceProviderContract(\$id)</strong> - Get service provider contract</li>";
    echo "<li><strong>getServiceProviderStats()</strong> - Get statistics</li>";
    echo "</ul>";
    
    echo "<h2>💻 Usage Examples:</h2>";
    
    echo "<h3>1. Get All Service Providers:</h3>";
    echo "<pre>";
    echo "require_once 'service_provider_data_access.php';\n";
    echo "\$result = ServiceProviderDataAccess::getAllServiceProviders();\n";
    echo "if (\$result['success']) {\n";
    echo "    echo 'Found ' . \$result['count'] . ' service providers';\n";
    echo "    foreach (\$result['data'] as \$provider) {\n";
    echo "        echo \$provider['supplier_name'];\n";
    echo "    }\n";
    echo "}";
    echo "</pre>";
    
    echo "<h3>2. Get Service Provider by ID:</h3>";
    echo "<pre>";
    echo "\$result = ServiceProviderDataAccess::getServiceProviderById(73);\n";
    echo "if (\$result['success']) {\n";
    echo "    \$provider = \$result['data'];\n";
    echo "    echo 'Provider: ' . \$provider['supplier_name'];\n";
    echo "    echo 'Email: ' . \$provider['email'];\n";
    echo "}";
    echo "</pre>";
    
    echo "<h3>3. Search Service Providers:</h3>";
    echo "<pre>";
    echo "\$result = ServiceProviderDataAccess::searchServiceProviders('warehouse');\n";
    echo "if (\$result['success']) {\n";
    echo "    echo 'Found ' . \$result['count'] . ' matching providers';\n";
    echo "}";
    echo "</pre>";
    
    echo "<h3>4. Get Contract:</h3>";
    echo "<pre>";
    echo "\$result = ServiceProviderDataAccess::getServiceProviderContract(73);\n";
    echo "if (\$result['success']) {\n";
    echo "    \$contract = \$result['data'];\n";
    echo "    echo 'Contract ID: ' . \$contract['contract_id'];\n";
    echo "    echo 'Content: ' . \$contract['contract_content'];\n";
    echo "}";
    echo "</pre>";
    
    echo "<h2>🧪 Live Test:</h2>";
    
    // Test the methods
    $allProviders = ServiceProviderDataAccess::getAllServiceProviders();
    echo "<h3>All Service Providers Test:</h3>";
    if ($allProviders['success']) {
        echo "<p>✅ Success! Found " . $allProviders['count'] . " service provider(s)</p>";
        echo "<p>API Version: " . ($allProviders['api_version'] ?? 'v1') . "</p>";
        echo "<p>Timestamp: " . ($allProviders['timestamp'] ?? 'N/A') . "</p>";
        
        if (!empty($allProviders['data'])) {
            // Create a comprehensive table for all service providers
            echo "<h4>📋 Service Providers Table:</h4>";
            echo "<div style='overflow-x:auto;margin:20px 0;'>";
            echo "<table style='width:100%;border-collapse:collapse;background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);'>";
            echo "<thead>";
            echo "<tr style='background:#f8f9fa;'>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>ID</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Name</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Email</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Contact Person</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Phone</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Type</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Hub Location</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Service Areas</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Created</th>";
            echo "<th style='padding:12px;border:1px solid #dee2e6;text-align:left;font-weight:bold;'>Contract</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            foreach ($allProviders['data'] as $index => $provider) {
                $rowStyle = $index % 2 === 0 ? 'background:#ffffff;' : 'background:#f8f9fa;';
                echo "<tr style='$rowStyle'>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;'>" . $provider['id'] . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;font-weight:bold;color:#007bff;'>" . htmlspecialchars($provider['supplier_name']) . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;'>" . htmlspecialchars($provider['email']) . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;'>" . htmlspecialchars($provider['contact_person']) . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;'>" . htmlspecialchars($provider['phone']) . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;'>";
                $typeColor = $provider['supplier_type'] === 'service_provider' ? '#28a745' : '#6c757d';
                echo "<span style='background:$typeColor;color:white;padding:4px 8px;border-radius:4px;font-size:12px;'>" . htmlspecialchars($provider['supplier_type']) . "</span>";
                echo "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;max-width:200px;word-wrap:break-word;'>" . htmlspecialchars($provider['hub_location'] ?? 'Not specified') . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;'>" . htmlspecialchars($provider['service_areas'] ?? 'Not specified') . "</td>";
                echo "<td style='padding:12px;border:1px solid #dee2e6;font-size:12px;color:#6c757d;'>" . date('M d, Y', strtotime($provider['created_at'])) . "</td>";
                
                // Contract status
                echo "<td style='padding:12px;border:1px solid #dee2e6;text-align:center;'>";
                $contractTest = ServiceProviderDataAccess::getServiceProviderContract($provider['id']);
                if ($contractTest['success']) {
                    echo "<span style='background:#28a745;color:white;padding:4px 8px;border-radius:4px;font-size:12px;'>✅ Available</span>";
                    echo "<br><small style='color:#6c757d;'>ID: " . $contractTest['data']['contract_id'] . "</small>";
                } else {
                    echo "<span style='background:#dc3545;color:white;padding:4px 8px;border-radius:4px;font-size:12px;'>❌ None</span>";
                }
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            
            // Additional details section
            echo "<h4>📊 Additional Information:</h4>";
            echo "<div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin:20px 0;'>";
            
            foreach ($allProviders['data'] as $provider) {
                echo "<div style='background:#f8f9fa;padding:20px;border-radius:8px;border:1px solid #dee2e6;'>";
                echo "<h5 style='margin:0 0 15px 0;color:#495057;border-bottom:2px solid #007bff;padding-bottom:10px;'>" . htmlspecialchars($provider['supplier_name']) . " - Details</h5>";
                
                echo "<div style='margin-bottom:10px;'>";
                echo "<strong>Service Capabilities:</strong><br>";
                echo "<span style='color:#6c757d;'>" . htmlspecialchars($provider['service_capabilities'] ?? 'Not specified') . "</span>";
                echo "</div>";
                
                echo "<div style='margin-bottom:10px;'>";
                echo "<strong>Facility Type:</strong><br>";
                echo "<span style='color:#6c757d;'>" . htmlspecialchars($provider['facility_type'] ?? 'Not specified') . "</span>";
                echo "</div>";
                
                // Contract details
                $contractTest = ServiceProviderDataAccess::getServiceProviderContract($provider['id']);
                if ($contractTest['success']) {
                    echo "<div style='margin-bottom:10px;'>";
                    echo "<strong>Contract Information:</strong><br>";
                    echo "<span style='color:#28a745;'>Contract ID: " . $contractTest['data']['contract_id'] . "</span><br>";
                    echo "<span style='color:#6c757d;font-size:12px;'>DTRS Reference: " . htmlspecialchars($contractTest['data']['dtrs_reference']) . "</span><br>";
                    echo "<span style='color:#6c757d;font-size:12px;'>Content Length: " . strlen($contractTest['data']['contract_content']) . " characters</span>";
                    echo "</div>";
                }
                
                echo "</div>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>❌ Error: " . htmlspecialchars($allProviders['error']) . "</p>";
    }
    
    echo "</body></html>";
}
?>
