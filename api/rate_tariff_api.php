<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include('../connect.php');

function column_exists($table, $column) {
    global $conn;
    $sql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = $res && $res->num_rows > 0;
    $stmt->close();
    return $ok;
}

// Helper function for Philippines-specific rate calculations
function calculatePhilippinesRate($route, $weight, $cargoType, $serviceLevel, $deliveryType = 'auto', $shipmentType = 'domestic', $targetCurrency = 'PHP', $tariffSource = 'ph_boc') {
    // Base rates for Philippines (in PHP)
    $baseRates = [
        'land' => [
            'per_km' => 8.50,  // Base rate per km for land transport
            'per_kg' => 2.75,   // Additional rate per kg
            'base_fee' => 150.00 // Minimum base fee
        ],
        'sea' => [
            'per_km' => 3.25,
            'per_kg' => 1.50,
            'base_fee' => 500.00
        ],
        'air' => [
            'per_km' => 15.75,
            'per_kg' => 8.25,
            'base_fee' => 800.00
        ]
    ];
    
    // Delivery type adjustments
    $deliveryAdjustments = [
        'motorcycle' => 0.9,   // 10% discount for motorcycle
        'bike' => 1.0,          // Standard rate for bike
        'truck' => 1.2          // 20% premium for truck
    ];
    
    // Cargo type multipliers
    $cargoMultipliers = [
        'general' => 1.0,
        'perishable' => 1.3,
        'hazardous' => 2.1,
        'fragile' => 1.5,
        'oversized' => 1.8,
        'documents' => 1.25    // 25% premium for documents
    ];
    
    // Service level multipliers
    $serviceMultipliers = [
        'standard' => 1.0,
        'express' => 1.6,
        'economy' => 0.8
    ];
    
    // Government tariff rates based on source
    $governmentTariffs = [
        'ph_boc' => [
            'land' => 0.15,  // 15% total for land (VAT + handling)
            'sea' => 0.12,   // 12% for sea (VAT + port fees)
            'air' => 0.18    // 18% for air (VAT + airport fees)
        ],
        'international_wto' => [
            'land' => 0.12,
            'sea' => 0.10,
            'air' => 0.15
        ],
        'asean' => [
            'land' => 0.10,
            'sea' => 0.08,
            'air' => 0.12
        ],
        'custom' => [
            'land' => 0.20,
            'sea' => 0.15,
            'air' => 0.25
        ]
    ];
    
    // Currency exchange rates (PHP to target currency)
    $exchangeRates = [
        'USD' => 0.018,
        'EUR' => 0.016,
        'GBP' => 0.014,
        'JPY' => 2.65,
        'CNY' => 0.13,
        'SGD' => 0.024,
        'MYR' => 0.085,
        'THB' => 0.62
    ];
    
    // Additional taxes by currency
    $additionalTaxes = [
        'USD' => 0.05,   // 5% additional tax for USD
        'EUR' => 0.08,   // 8% VAT for EUR
        'GBP' => 0.20,   // 20% VAT for GBP
        'JPY' => 0.10,   // 10% consumption tax for JPY
        'CNY' => 0.13,   // 13% VAT for CNY
        'SGD' => 0.07,   // 7% GST for SGD
        'MYR' => 0.06,   // 6% SST for MYR
        'THB' => 0.07    // 7% VAT for THB
    ];
    
    $carrierType = strtolower($route['carrier_type']);
    $baseRate = $baseRates[$carrierType] ?? $baseRates['land'];
    
    // Calculate base rate
    $distanceRate = $route['distance_km'] * $baseRate['per_km'];
    $weightRate = $weight * $baseRate['per_kg'];
    $calculatedBase = max($baseRate['base_fee'], $distanceRate + $weightRate);
    
    // Apply delivery type adjustment
    $deliveryMultiplier = $deliveryAdjustments[$deliveryType] ?? 1.0;
    
    // Apply multipliers
    $cargoMultiplier = $cargoMultipliers[$cargoType] ?? 1.0;
    $serviceMultiplier = $serviceMultipliers[$serviceLevel] ?? 1.0;
    
    $finalBaseRate = $calculatedBase * $cargoMultiplier * $serviceMultiplier * $deliveryMultiplier;
    
    // Calculate government tariff
    $tariffRates = $governmentTariffs[$tariffSource] ?? $governmentTariffs['ph_boc'];
    $tariffRate = $tariffRates[$carrierType] ?? 0.15;
    $tariffAmount = $finalBaseRate * $tariffRate;
    
    // Calculate total rate in PHP
    $totalRatePHP = $finalBaseRate + $tariffAmount;
    
    // Apply currency conversion and additional taxes for international shipments
    $convertedRate = null;
    $exchangeRate = null;
    $additionalTaxAmount = 0;
    
    if ($shipmentType === 'international' && $targetCurrency !== 'PHP') {
        $exchangeRate = $exchangeRates[$targetCurrency] ?? 1;
        $convertedRate = $totalRatePHP * $exchangeRate;
        
        // Apply additional taxes for international shipments
        $additionalTaxRate = $additionalTaxes[$targetCurrency] ?? 0;
        $additionalTaxAmount = $convertedRate * $additionalTaxRate;
        $convertedRate += $additionalTaxAmount;
    }
    
    // Generate formula explanation
    $formula = "Base: ({$route['distance_km']} km × ₱{$baseRate['per_km']}) + ({$weight} kg × ₱{$baseRate['per_kg']}) = ₱" . number_format($distanceRate + $weightRate, 2) . "\n";
    $formula .= "Cargo Multiplier ({$cargoType}): × {$cargoMultiplier}\n";
    $formula .= "Service Multiplier ({$serviceLevel}): × {$serviceMultiplier}\n";
    $formula .= "Delivery Adjustment ({$deliveryType}): × {$deliveryMultiplier}\n";
    $formula .= "Final Base Rate: ₱" . number_format($finalBaseRate, 2) . "\n";
    $formula .= "Government Tariff (" . strtoupper($tariffSource) . " - {$carrierType}): " . ($tariffRate * 100) . "% = ₱" . number_format($tariffAmount, 2) . "\n";
    $formula .= "Total Rate (PHP): ₱" . number_format($totalRatePHP, 2);
    
    if ($shipmentType === 'international' && $convertedRate !== null) {
        $formula .= "\nConverted Rate ({$targetCurrency}): " . number_format($convertedRate, 2);
        if ($additionalTaxAmount > 0) {
            $formula .= " (incl. " . number_format($additionalTaxes[$targetCurrency] * 100, 0) . "% tax)";
        }
    }
    
    // Generate breakdown
    $breakdown = "
        <div class='mb-2'><strong>Distance Rate:</strong> {$route['distance_km']} km × ₱{$baseRate['per_km']} = ₱" . number_format($distanceRate, 2) . "</div>
        <div class='mb-2'><strong>Weight Rate:</strong> {$weight} kg × ₱{$baseRate['per_kg']} = ₱" . number_format($weightRate, 2) . "</div>
        <div class='mb-2'><strong>Subtotal:</strong> ₱" . number_format($distanceRate + $weightRate, 2) . "</div>
        <div class='mb-2'><strong>Cargo Adjustment ({$cargoType}):</strong> × {$cargoMultiplier} = ₱" . number_format(($distanceRate + $weightRate) * $cargoMultiplier, 2) . "</div>
        <div class='mb-2'><strong>Service Adjustment ({$serviceLevel}):</strong> × {$serviceMultiplier} = ₱" . number_format(($distanceRate + $weightRate) * $cargoMultiplier * $serviceMultiplier, 2) . "</div>
        <div class='mb-2'><strong>Delivery Adjustment ({$deliveryType}):</strong> × {$deliveryMultiplier} = ₱" . number_format($finalBaseRate, 2) . "</div>
        <div class='mb-2'><strong>Government Tariff (" . strtoupper($tariffSource) . " - " . strtoupper($carrierType) . "):</strong> " . ($tariffRate * 100) . "% = ₱" . number_format($tariffAmount, 2) . "</div>
        <div class='mt-2'><strong>Total (PHP):</strong> ₱" . number_format($totalRatePHP, 2) . "</div>";
    
    if ($shipmentType === 'international' && $convertedRate !== null) {
        $breakdown .= "<div class='mt-2'><strong>Exchange Rate:</strong> 1 PHP = {$exchangeRate} {$targetCurrency}</div>";
        if ($additionalTaxAmount > 0) {
            $breakdown .= "<div class='mb-2'><strong>Additional Tax ({$targetCurrency}):</strong> " . number_format($additionalTaxes[$targetCurrency] * 100, 0) . "% = " . number_format($additionalTaxAmount, 2) . "</div>";
        }
        $breakdown .= "<div class='mb-2'><strong>Total ({$targetCurrency}):</strong> " . number_format($convertedRate, 2) . "</div>";
    }
    
    $breakdown .= "</div>";
    
    return [
        'base_rate' => $finalBaseRate,
        'tariff_amount' => $tariffAmount,
        'total_rate' => $totalRatePHP,
        'converted_rate' => $convertedRate,
        'target_currency' => $targetCurrency,
        'exchange_rate' => $exchangeRate,
        'formula' => nl2br($formula),
        'breakdown' => $breakdown,
        'tariff_rate' => $tariffRate,
        'tariff_source' => $tariffSource,
        'delivery_type' => $deliveryType,
        'shipment_type' => $shipmentType
    ];
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Extract endpoint from URL - handle both /rates and /rates/ patterns
if (strpos($path, '/rates') !== false) {
    $endpoint = 'rates';
} elseif (strpos($path, '/route_details') !== false) {
    $endpoint = 'route_details';
} elseif (strpos($path, '/ai_calculate') !== false) {
    $endpoint = 'ai_calculate';
} elseif (strpos($path, '/save_rate') !== false) {
    $endpoint = 'save_rate';
} elseif (strpos($path, '/rate_details') !== false) {
    $endpoint = 'rate_details';
} elseif (strpos($path, '/approve_rate') !== false) {
    $endpoint = 'approve_rate';
} elseif (strpos($path, '/reject_rate') !== false) {
    $endpoint = 'reject_rate';
} elseif (strpos($path, '/delete_rate') !== false) {
    $endpoint = 'delete_rate';
} else {
    $path_parts = explode('/', $path);
    $endpoint = end($path_parts);
}

try {
    switch ($endpoint) {
        case 'rates':
            if ($method === 'GET') {
                // Get rates with filters
                $status = $_GET['status'] ?? 'all';
                $carrierType = $_GET['carrier_type'] ?? 'all';
                $providerId = $_GET['provider_id'] ?? 'all';
                $search = $_GET['search'] ?? '';
                
                $whereConditions = [];
                $params = [];
                $types = '';
                
                if ($status !== 'all') {
                    $whereConditions[] = "cr.status = ?";
                    $params[] = $status;
                    $types .= 's';
                }
                
                if ($carrierType !== 'all') {
                    $whereConditions[] = "r.carrier_type = ?";
                    $params[] = $carrierType;
                    $types .= 's';
                }
                
                if ($providerId !== 'all') {
                    $whereConditions[] = "r.provider_id = ?";
                    $params[] = $providerId;
                    $types .= 'i';
                }
                
                if (!empty($search)) {
                    $whereConditions[] = "(np1.point_name LIKE ? OR np2.point_name LIKE ? OR sp.company_name LIKE ?)";
                    $searchParam = "%{$search}%";
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $types .= 'sss';
                }
                
                $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
                
                $query = "
                    SELECT 
                        cr.id,
                        cr.route_id,
                        cr.provider_id,
                        cr.carrier_type,
                        cr.unit,
                        cr.quantity,
                        cr.total_rate,
                        COALESCE(cr.base_rate, 0) as base_rate,
                        COALESCE(cr.tariff_amount, 0) as tariff_amount,
                        COALESCE(cr.ai_calculated, 0) as ai_calculated,
                        cr.calculation_details,
                        cr.created_at,
                        cr.status,
                        r.distance_km,
                        r.eta_min,
                        sp.company_name as provider_name,
                        np1.point_name as origin,
                        np2.point_name as destination
                    FROM calculated_rates cr
                    JOIN routes r ON cr.route_id = r.route_id
                    JOIN active_service_provider sp ON cr.provider_id = sp.provider_id
                    JOIN network_points np1 ON r.origin_id = np1.point_id
                    JOIN network_points np2 ON r.destination_id = np2.point_id
                    {$whereClause}
                    ORDER BY cr.created_at DESC
                ";
                
                $stmt = $conn->prepare($query);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                $rates = [];
                while ($row = $result->fetch_assoc()) {
                    $rates[] = $row;
                }
                
                echo json_encode(['success' => true, 'rates' => $rates]);
            }
            break;
            
        case 'route_details':
            if ($method === 'GET') {
                $routeId = $_GET['id'] ?? 0;
                
                $query = "
                    SELECT 
                        r.route_id,
                        r.origin_id,
                        r.destination_id,
                        r.carrier_type,
                        r.provider_id,
                        r.distance_km,
                        r.eta_min,
                        np1.point_name as origin,
                        np2.point_name as destination
                    FROM routes r
                    JOIN network_points np1 ON r.origin_id = np1.point_id
                    JOIN network_points np2 ON r.destination_id = np2.point_id
                    WHERE r.route_id = ? AND r.status = 'approved'
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $routeId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    echo json_encode(['success' => true, 'route' => $row]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Route not found or not approved']);
                }
            }
            break;
            
        case 'ai_calculate':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $routeId = $data['route_id'] ?? 0;
                $weight = $data['cargo_weight'] ?? 0;
                $cargoType = $data['cargo_type'] ?? 'general';
                $serviceLevel = $data['service_level'] ?? 'standard';
                $deliveryType = $data['delivery_type'] ?? 'auto';
                $shipmentType = $data['shipment_type'] ?? 'domestic';
                $targetCurrency = $data['target_currency'] ?? 'PHP';
                $tariffSource = $data['tariff_source'] ?? 'ph_boc';
                
                if ($routeId <= 0 || $weight <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid route ID or weight']);
                    break;
                }
                
                // Get route details
                $query = "
                    SELECT r.*, sp.company_name
                    FROM routes r
                    JOIN active_service_provider sp ON r.provider_id = sp.provider_id
                    WHERE r.route_id = ? AND r.status = 'approved'
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $routeId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if (!$row = $result->fetch_assoc()) {
                    echo json_encode(['success' => false, 'message' => 'Route not found or not approved']);
                    break;
                }
                
                // Perform AI calculation with enhanced parameters
                $calculation = calculatePhilippinesRate($row, $weight, $cargoType, $serviceLevel, $deliveryType, $shipmentType, $targetCurrency, $tariffSource);
                
                echo json_encode([
                    'success' => true,
                    'route_id' => $routeId,
                    'provider_id' => $row['provider_id'],
                    'carrier_type' => $row['carrier_type'],
                    'cargo_weight' => $weight,
                    'cargo_type' => $cargoType,
                    'service_level' => $serviceLevel,
                    'delivery_type' => $deliveryType,
                    'shipment_type' => $shipmentType,
                    'target_currency' => $targetCurrency,
                    'tariff_source' => $tariffSource,
                    'base_rate' => $calculation['base_rate'],
                    'tariff_amount' => $calculation['tariff_amount'],
                    'total_rate' => $calculation['total_rate'],
                    'converted_rate' => $calculation['converted_rate'],
                    'exchange_rate' => $calculation['exchange_rate'],
                    'formula' => $calculation['formula'],
                    'breakdown' => $calculation['breakdown'],
                    'tariff_rate' => $calculation['tariff_rate']
                ]);
            }
            break;
            
        case 'save_rate':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $routeId = $data['route_id'] ?? 0;
                $providerId = $data['provider_id'] ?? 0;
                $carrierType = $data['carrier_type'] ?? '';
                $totalRate = $data['total_rate'] ?? 0;
                $baseRate = $data['base_rate'] ?? 0;
                $tariffAmount = $data['tariff_amount'] ?? 0;
                $formula = $data['formula'] ?? '';
                
                if ($routeId <= 0 || $providerId <= 0 || $totalRate <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                    break;
                }
                
                // Build dynamic insert based on existing columns
                $table = 'calculated_rates';
                $cols = ['route_id', 'provider_id', 'carrier_type', 'total_rate'];
                $types = 'iisd';
                $params = [$routeId, $providerId, $carrierType, $totalRate];

                if (column_exists($table, 'base_rate')) { $cols[] = 'base_rate'; $types .= 'd'; $params[] = $baseRate; }
                if (column_exists($table, 'tariff_amount')) { $cols[] = 'tariff_amount'; $types .= 'd'; $params[] = $tariffAmount; }
                if (column_exists($table, 'ai_calculated')) { $cols[] = 'ai_calculated'; $types .= 'i'; $params[] = 1; }
                if (column_exists($table, 'calculation_details')) { $cols[] = 'calculation_details'; $types .= 's'; $params[] = $formula; }
                if (column_exists($table, 'unit')) { $cols[] = 'unit'; $types .= 's'; $params[] = 'per shipment'; }
                if (column_exists($table, 'quantity')) { $cols[] = 'quantity'; $types .= 'i'; $params[] = 1; }
                if (column_exists($table, 'status')) { $cols[] = 'status'; $types .= 's'; $params[] = 'Pending'; }

                $placeholders = implode(',', array_fill(0, count($cols), '?'));
                $query = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . $placeholders . ')';

                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare save: ' . $conn->error]);
                    break;
                }
                $stmt->bind_param($types, ...$params);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Rate calculated and saved successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to save rate']);
                }
            }
            break;
            
        case 'rate_details':
            if ($method === 'GET') {
                $rateId = $_GET['id'] ?? 0;
                
                $query = "
                    SELECT 
                        cr.*,
                        r.distance_km,
                        r.eta_min,
                        sp.company_name as provider_name,
                        np1.point_name as origin,
                        np2.point_name as destination
                    FROM calculated_rates cr
                    JOIN routes r ON cr.route_id = r.route_id
                    JOIN active_service_provider sp ON cr.provider_id = sp.provider_id
                    JOIN network_points np1 ON r.origin_id = np1.point_id
                    JOIN network_points np2 ON r.destination_id = np2.point_id
                    WHERE cr.id = ?
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $rateId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    echo json_encode(['success' => true, 'rate' => $row]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Rate not found']);
                }
            }
            break;
            
        case 'approve_rate':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $rateId = $data['rate_id'] ?? 0;
                
                if ($rateId <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid rate ID']);
                    break;
                }
                
                $query = "UPDATE calculated_rates SET status = 'approved', updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $rateId);
                
                if ($stmt->execute()) {
                    // Add notification
                    $rateQuery = "SELECT cr.*, sp.company_name FROM calculated_rates cr JOIN active_service_provider sp ON cr.provider_id = sp.provider_id WHERE cr.id = ?";
                    $rateStmt = $conn->prepare($rateQuery);
                    $rateStmt->bind_param('i', $rateId);
                    $rateStmt->execute();
                    $rateResult = $rateStmt->get_result();
                    $rateData = $rateResult->fetch_assoc();
                    
                    $notificationQuery = "INSERT INTO notifications (message, type, link, is_read, created_at) VALUES (?, 'service_provider', ?, 0, NOW())";
                    $notificationStmt = $conn->prepare($notificationQuery);
                    $message = "Your rate #{$rateId} has been approved.";
                    $link = "rate_tariff_management.php?rate_id={$rateId}";
                    $notificationStmt->bind_param('ss', $message, $link);
                    $notificationStmt->execute();
                    
                    echo json_encode(['success' => true, 'message' => 'Rate approved successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to approve rate']);
                }
            }
            break;
            
        case 'reject_rate':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $rateId = $data['rate_id'] ?? 0;
                $reason = $data['rejection_reason'] ?? '';
                
                if ($rateId <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid rate ID']);
                    break;
                }
                
                $query = "UPDATE calculated_rates SET status = 'rejected', updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $rateId);
                
                if ($stmt->execute()) {
                    // Add notification
                    $rateQuery = "SELECT cr.*, sp.company_name FROM calculated_rates cr JOIN active_service_provider sp ON cr.provider_id = sp.provider_id WHERE cr.id = ?";
                    $rateStmt = $conn->prepare($rateQuery);
                    $rateStmt->bind_param('i', $rateId);
                    $rateStmt->execute();
                    $rateResult = $rateStmt->get_result();
                    $rateData = $rateResult->fetch_assoc();
                    
                    $notificationQuery = "INSERT INTO notifications (message, type, link, is_read, created_at) VALUES (?, 'service_provider', ?, 0, NOW())";
                    $notificationStmt = $conn->prepare($notificationQuery);
                    $message = "Your rate #{$rateId} has been rejected. Reason: {$reason}";
                    $link = "rate_tariff_management.php?rate_id={$rateId}";
                    $notificationStmt->bind_param('ss', $message, $link);
                    $notificationStmt->execute();
                    
                    echo json_encode(['success' => true, 'message' => 'Rate rejected successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to reject rate']);
                }
            }
            break;
            
        case 'delete_rate':
            if ($method === 'DELETE') {
                $rateId = $_GET['id'] ?? 0;
                
                if ($rateId <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid rate ID']);
                    break;
                }
                
                $query = "DELETE FROM calculated_rates WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $rateId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Rate deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete rate']);
                }
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>
