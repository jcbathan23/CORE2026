<!DOCTYPE html>
<html>
<head>
    <title>Comprehensive Logistic1 API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #d1ecf1; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .method { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Comprehensive Logistic1 API Test</h1>
    
    <?php
    $api_url = 'https://logistic1.slatefreight-ph.com/api/service.php';
    $api_key = 'Log1';
    
    echo "<p><strong>Testing API:</strong> <code>$api_url</code></p>";
    echo "<p><strong>API Key:</strong> <code>$api_key</code></p>";
    
    $test_methods = [
        [
            'name' => 'No Authentication',
            'url' => $api_url,
            'headers' => ['Accept: application/json']
        ],
        [
            'name' => 'URL Parameter: api_key',
            'url' => $api_url . '?api_key=' . urlencode($api_key),
            'headers' => ['Accept: application/json']
        ],
        [
            'name' => 'URL Parameter: key',
            'url' => $api_url . '?key=' . urlencode($api_key),
            'headers' => ['Accept: application/json']
        ],
        [
            'name' => 'URL Parameter: token',
            'url' => $api_url . '?token=' . urlencode($api_key),
            'headers' => ['Accept: application/json']
        ],
        [
            'name' => 'Header: X-API-Key',
            'url' => $api_url,
            'headers' => ['Accept: application/json', 'X-API-Key: ' . $api_key]
        ],
        [
            'name' => 'Header: Authorization Bearer',
            'url' => $api_url,
            'headers' => ['Accept: application/json', 'Authorization: Bearer ' . $api_key]
        ],
        [
            'name' => 'Header: Authorization',
            'url' => $api_url,
            'headers' => ['Accept: application/json', 'Authorization: ' . $api_key]
        ],
        [
            'name' => 'Combined: URL + Header',
            'url' => $api_url . '?api_key=' . urlencode($api_key),
            'headers' => ['Accept: application/json', 'X-API-Key: ' . $api_key]
        ]
    ];
    
    $working_method = null;
    
    foreach ($test_methods as $index => $method) {
        echo "<div class='method'>";
        echo "<h3>Method " . ($index + 1) . ": " . $method['name'] . "</h3>";
        echo "<p><strong>URL:</strong> <code>" . htmlspecialchars($method['url']) . "</code></p>";
        echo "<p><strong>Headers:</strong> <code>" . implode(', ', $method['headers']) . "</code></p>";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $method['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $method['headers'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        echo "<p><strong>HTTP Code:</strong> $http_code</p>";
        echo "<p><strong>Content Type:</strong> $content_type</p>";
        
        if ($response === false) {
            echo "<div class='test-result error'>❌ cURL Error: $curl_error</div>";
        } elseif ($http_code == 200) {
            $json_data = json_decode($response, true);
            if ($json_data) {
                if (isset($json_data['success'])) {
                    if ($json_data['success'] === true) {
                        echo "<div class='test-result success'>✅ SUCCESS! This method works!</div>";
                        echo "<p><strong>Data Count:</strong> " . (isset($json_data['count']) ? $json_data['count'] : count($json_data['data'] ?? [])) . "</p>";
                        $working_method = $method;
                        
                        if (isset($json_data['data']) && is_array($json_data['data']) && count($json_data['data']) > 0) {
                            echo "<h4>Sample Data:</h4>";
                            echo "<pre>" . json_encode(array_slice($json_data['data'], 0, 1), JSON_PRETTY_PRINT) . "</pre>";
                        }
                    } else {
                        echo "<div class='test-result error'>❌ API Error: " . ($json_data['message'] ?? $json_data['error'] ?? 'Unknown error') . "</div>";
                    }
                } else {
                    // No success field, assume it's working if we got data
                    if (is_array($json_data) && count($json_data) > 0) {
                        echo "<div class='test-result success'>✅ SUCCESS! This method works! (No success field, but got data)</div>";
                        echo "<p><strong>Data Count:</strong> " . count($json_data) . "</p>";
                        $working_method = $method;
                        
                        echo "<h4>Sample Data:</h4>";
                        echo "<pre>" . json_encode(array_slice($json_data, 0, 1), JSON_PRETTY_PRINT) . "</pre>";
                    } else {
                        echo "<div class='test-result warning'>⚠️ Got JSON but no data</div>";
                    }
                }
            } else {
                echo "<div class='test-result warning'>⚠️ HTTP 200 but invalid JSON</div>";
                echo "<h4>Raw Response (first 300 chars):</h4>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . "</pre>";
            }
        } else {
            echo "<div class='test-result error'>❌ HTTP Error: $http_code</div>";
            if ($response) {
                echo "<h4>Response (first 300 chars):</h4>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . "</pre>";
            }
        }
        
        echo "</div>";
        
        // If we found a working method, we can break here
        if ($working_method) {
            break;
        }
    }
    
    echo "<div class='method'>";
    echo "<h2>🎯 Result Summary</h2>";
    
    if ($working_method) {
        echo "<div class='test-result success'>";
        echo "<h3>✅ WORKING METHOD FOUND!</h3>";
        echo "<p><strong>Method:</strong> " . $working_method['name'] . "</p>";
        echo "<p><strong>URL:</strong> <code>" . htmlspecialchars($working_method['url']) . "</code></p>";
        echo "<p><strong>Headers:</strong> <code>" . implode(', ', $working_method['headers']) . "</code></p>";
        echo "<h4>🔧 Implementation Code:</h4>";
        echo "<pre>";
        echo "// Use this configuration in your pull script:\n";
        echo "\$ch = curl_init();\n";
        echo "curl_setopt_array(\$ch, [\n";
        echo "    CURLOPT_URL => '" . $working_method['url'] . "',\n";
        echo "    CURLOPT_RETURNTRANSFER => true,\n";
        echo "    CURLOPT_TIMEOUT => 20,\n";
        echo "    CURLOPT_HTTPHEADER => [\n";
        foreach ($working_method['headers'] as $header) {
            echo "        '" . $header . "',\n";
        }
        echo "    ],\n";
        echo "    CURLOPT_SSL_VERIFYPEER => false,\n";
        echo "    CURLOPT_SSL_VERIFYHOST => 0\n";
        echo "]);\n";
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div class='test-result error'>";
        echo "<h3>❌ NO WORKING METHOD FOUND</h3>";
        echo "<p>None of the tested authentication methods worked. This could mean:</p>";
        echo "<ul>";
        echo "<li>The API key 'Log1' is incorrect</li>";
        echo "<li>The API endpoint URL is wrong</li>";
        echo "<li>Your server IP needs to be whitelisted</li>";
        echo "<li>The API uses a different authentication method</li>";
        echo "<li>The API service is currently down</li>";
        echo "</ul>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ol>";
        echo "<li>Contact the Logistic1 team to verify the correct API key and endpoint</li>";
        echo "<li>Ask if your server IP needs to be whitelisted</li>";
        echo "<li>Check if there's API documentation available</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    echo "</div>";
    ?>
</body>
</html>
