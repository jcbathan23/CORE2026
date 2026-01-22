<?php
include('header.php');
include('sidebar.php');
include('navbar.php');
include('../connect.php');
?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
    /* Modern Color Palette - Professional Logistics Theme */
    :root {
        --primary-color: #1e3a8a;        /* Deep Blue - Trust & Professionalism */
        --secondary-color: #3b82f6;      /* Bright Blue - Primary Actions */
        --accent-color: #06b6d4;         /* Cyan - Highlights & AI */
        --success-color: #10b981;        /* Emerald - Success States */
        --warning-color: #f59e0b;        /* Amber - Warnings */
        --danger-color: #ef4444;         /* Red - Errors/Danger */
        --dark-color: #1f2937;          /* Dark Gray - Text */
        --light-color: #f8fafc;         /* Light Gray - Backgrounds */
        --border-color: #e2e8f0;        /* Light Border */
        --gradient-primary: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        --gradient-accent: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .rate-card {
        background: var(--gradient-primary);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .rate-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        pointer-events: none;
    }
    
    .rate-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
    }
    
    .tariff-card {
        background: var(--gradient-accent);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .tariff-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        pointer-events: none;
    }
    
    .tariff-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
    }
    
    .ai-indicator {
        background: var(--gradient-accent);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 24px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: var(--shadow-sm);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .ai-indicator::before {
        content: '🤖';
        font-size: 0.8rem;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 24px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .status-pending { 
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    .status-approved { 
        background: var(--gradient-success);
        color: white;
    }
    .status-rejected { 
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    .status-calculating { 
        background: var(--gradient-accent);
        color: white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .route-preview {
        background: var(--light-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1rem 0;
        border-left: 4px solid var(--secondary-color);
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }
    
    .calculation-formula {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: 12px;
        padding: 1.5rem;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
        margin: 1rem 0;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    .tariff-breakdown {
        background: var(--light-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1rem 0;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }
    
    .modal-header {
        border-bottom: 2px solid var(--border-color);
        background: var(--gradient-primary);
        color: white;
        border-radius: 12px 12px 0 0;
    }
    
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
    }
    
    .btn-ai-calculate {
        background: var(--gradient-primary);
        border: none;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 24px;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-md);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }
    
    .btn-ai-calculate:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
        background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%);
    }
    
    .btn-ai-calculate:active {
        transform: translateY(0);
    }
    
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 3rem;
        background: var(--light-color);
        border-radius: 12px;
        margin: 1rem 0;
    }
    
    .spinner-border {
        color: var(--secondary-color);
        width: 3rem;
        height: 3rem;
        border-width: 0.3rem;
    }
    
    .rate-timeline {
        position: relative;
        padding-left: 2rem;
    }
    
    .rate-timeline::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--secondary-color) 0%, var(--accent-color) 100%);
        border-radius: 2px;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
        padding: 1rem;
        background: var(--light-color);
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
    }
    
    .timeline-item:hover {
        transform: translateX(4px);
        box-shadow: var(--shadow-md);
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -2.25rem;
        top: 1.5rem;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--secondary-color);
        border: 3px solid white;
        box-shadow: var(--shadow-md);
    }
    
    .modern-header {
        background: linear-gradient(135deg, var(--light-color) 0%, white 100%);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }
    
    /* Override dark mode styles for header */
    body.dark-mode .modern-header {
        background: linear-gradient(180deg, #2b3f4e 0%, #1f3442 100%) !important;
        border-color: rgba(255,255,255,0.06) !important;
    }
    
    body.dark-mode .modern-header h3,
    body.dark-mode .modern-header p {
        color: #e6edf3 !important;
    }
    
    .btn {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border: none;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }
    
    .btn-primary {
        background: var(--gradient-primary);
        color: white;
    }
    
    .btn-success {
        background: var(--gradient-success);
        color: white;
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
    }
    
    .form-control, .form-select {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }
    
    .form-label {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .card-title {
        color: var(--dark-color);
        font-weight: 800;
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
    }
    
    .modern-table-container {
        background: white;
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    
    .modern-table {
        margin: 0;
        font-size: 0.9rem;
    }
    
    .modern-table thead {
        background: linear-gradient(135deg, var(--light-color) 0%, #e2e8f0 100%);
    }
    
    .modern-table th {
        font-weight: 800;
        color: var(--dark-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
        font-size: 0.85rem;
    }
    
    .modern-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
    }
    
    .modern-table tbody tr {
        transition: all 0.3s ease;
    }
    
    .modern-table tbody tr:hover {
        background: var(--light-color);
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }
    
    .btn-modern-view {
        background: var(--secondary-color);
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .btn-modern-view:hover {
        background: var(--primary-color);
        transform: scale(1.1);
    }
    
    .btn-modern-edit {
        background: var(--warning-color);
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .btn-modern-edit:hover {
        background: #d97706;
        transform: scale(1.1);
    }
    
    .btn-modern-delete {
        background: var(--danger-color);
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .btn-modern-delete:hover {
        background: #dc2626;
        transform: scale(1.1);
    }
    
    .mode-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 16px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: white;
        box-shadow: var(--shadow-sm);
    }
    
    .mode-land {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .mode-air {
        background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%);
    }
    
    .mode-sea {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }
    
    .company-name {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .route-info {
        line-height: 1.4;
    }
    
    .route-info .fw-medium {
        color: var(--dark-color);
        font-weight: 700;
    }
    
    .text-primary {
        color: var(--secondary-color) !important;
        font-weight: 700;
    }
    
    .text-success {
        color: var(--success-color) !important;
        font-weight: 700;
    }
    
    .text-muted {
        color: #64748b !important;
    }
    
    .alert {
        border: none;
        border-radius: 12px;
        padding: 1.5rem;
        font-weight: 600;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: var(--danger-color);
        border-left: 4px solid var(--danger-color);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .rate-card, .tariff-card {
            padding: 1.5rem;
        }
        
        .modern-header {
            padding: 1.5rem;
        }
        
        .modern-header h3 {
            font-size: 1.5rem;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
    
    /* Loading Animation */
    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }
        100% {
            background-position: 1000px 0;
        }
    }
    
    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 1000px 100%;
        animation: shimmer 2s infinite;
    }
</style>

<div class="content p-4">
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Rate & Tariff Management</h3>
            <p>AI-powered rate calculations and tariff approvals for approved routes</p>
        </div>
        <div>
            <button class="btn btn-primary ms-2" onclick="openAICalculationModal()">
                <i class="fas fa-robot me-2"></i>AI Calculate
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="rate-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?php 
                            $result = $conn->query("SELECT COUNT(*) as total FROM calculated_rates WHERE status = 'Pending'");
                            $row = $result->fetch_assoc();
                            echo $row['total'];
                        ?></h4>
                        <p class="mb-0">Pending Rates</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tariff-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?php 
                            $result = $conn->query("SELECT COUNT(*) as total FROM calculated_rates WHERE status = 'approved'");
                            $row = $result->fetch_assoc();
                            echo $row['total'];
                        ?></h4>
                        <p class="mb-0">Approved Rates</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="rate-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?php 
                            $result = $conn->query("SELECT COUNT(*) as total FROM routes WHERE status = 'approved'");
                            $row = $result->fetch_assoc();
                            echo $row['total'];
                        ?></h4>
                        <p class="mb-0">Approved Routes</p>
                    </div>
                    <i class="fas fa-route fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="tariff-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><?php 
                            $result = $conn->query("SELECT AVG(total_rate) as avg FROM calculated_rates WHERE status = 'approved'");
                            $row = $result->fetch_assoc();
                            echo '₱' . number_format($row['avg'] ?: 0, 2);
                        ?></h4>
                        <p class="mb-0">Avg Rate</p>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Filter Rates & Tariffs</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-control" onchange="filterRates()">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="calculating">Calculating</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Carrier Type</label>
                            <select id="carrierFilter" class="form-control" onchange="filterRates()">
                                <option value="all">All Types</option>
                                <option value="land">Land</option>
                                <option value="air">Air</option>
                                <option value="sea">Sea</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Provider</label>
                            <select id="providerFilter" class="form-control" onchange="filterRates()">
                                <option value="all">All Providers</option>
                                <?php
                                $providers = $conn->query("SELECT provider_id, company_name FROM active_service_provider WHERE status = 'Active'");
                                while($provider = $providers->fetch_assoc()):
                                ?>
                                <option value="<?= $provider['provider_id'] ?>"><?= htmlspecialchars($provider['company_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchFilter" class="form-control" placeholder="Search routes, rates..." onkeyup="filterRates()">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rates & Tariffs Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table" id="ratesTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-route me-2"></i>Route</th>
                        <th><i class="fas fa-building me-2"></i>Provider</th>
                        <th><i class="fas fa-truck me-2"></i>Type</th>
                        <th><i class="fas fa-calculator me-2"></i>Base Rate</th>
                        <th><i class="fas fa-percentage me-2"></i>Tariff</th>
                        <th><i class="fas fa-dollar-sign me-2"></i>Total Rate</th>
                        <th><i class="fas fa-robot me-2"></i>AI Status</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th><i class="fas fa-cog me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody id="ratesTableBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="text-center py-4">
                                <i class="fas fa-cloud-download-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Click 'Refresh Data' to load rates from database</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- AI Calculation Modal -->
<div class="modal fade" id="aiCalculationModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-robot me-2"></i>AI-Powered Rate Calculation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Route Selection</h6>
                        <select id="routeSelect" class="form-control mb-3" onchange="loadRouteDetails()">
                            <option value="">Select an approved route...</option>
                            <?php
                            $routes = $conn->query("SELECT r.route_id, np1.point_name as origin, np2.point_name as destination, r.carrier_type 
                                                   FROM routes r 
                                                   JOIN network_points np1 ON r.origin_id = np1.point_id 
                                                   JOIN network_points np2 ON r.destination_id = np2.point_id 
                                                   WHERE r.status = 'approved' 
                                                   ORDER BY r.created_at DESC");
                            while($route = $routes->fetch_assoc()):
                            ?>
                            <option value="<?= $route['route_id'] ?>">
                                <?= htmlspecialchars($route['origin']) ?> → <?= htmlspecialchars($route['destination']) ?> (<?= $route['carrier_type'] ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <div id="routeDetails" class="route-preview" style="display: none;">
                            <h6>Route Details</h6>
                            <div id="routeInfo"></div>
                        </div>
                        
                        <h6 class="mt-3">Calculation Parameters</h6>
                        <div class="mb-3">
                            <label class="form-label">Cargo Weight (kg)</label>
                            <input type="number" id="cargoWeight" class="form-control" placeholder="Enter cargo weight" value="100" onchange="updateDeliveryType()">
                            <small class="text-muted" id="deliveryTypeHint">Delivery type will be suggested based on weight</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Type</label>
                            <select id="deliveryType" class="form-control">
                                <option value="auto">Auto-detect based on weight</option>
                                <option value="motorcycle">Motorcycle (0-5 kg)</option>
                                <option value="bike">Bike (5-20 kg)</option>
                                <option value="truck">Truck (20+ kg)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cargo Type</label>
                            <select id="cargoType" class="form-control" onchange="updateTariffInfo()">
                                <option value="general">General Cargo</option>
                                <option value="perishable">Perishable Goods</option>
                                <option value="hazardous">Hazardous Materials</option>
                                <option value="fragile">Fragile Items</option>
                                <option value="oversized">Oversized Cargo</option>
                                <option value="documents">Documents/Papers (Special Pricing)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Level</label>
                            <select id="serviceLevel" class="form-control">
                                <option value="standard">Standard</option>
                                <option value="express">Express</option>
                                <option value="economy">Economy</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipment Type</label>
                            <select id="shipmentType" class="form-control" onchange="updateCurrencyOptions()">
                                <option value="domestic">Domestic (Philippines)</option>
                                <option value="international">International</option>
                            </select>
                        </div>
                        <div class="mb-3" id="currencySection" style="display: none;">
                            <label class="form-label">Target Currency</label>
                            <select id="targetCurrency" class="form-control" onchange="updateTariffInfo()">
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="JPY">JPY - Japanese Yen</option>
                                <option value="CNY">CNY - Chinese Yuan</option>
                                <option value="SGD">SGD - Singapore Dollar</option>
                                <option value="MYR">MYR - Malaysian Ringgit</option>
                                <option value="THB">THB - Thai Baht</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Government Tariff Source</label>
                            <select id="tariffSource" class="form-control" onchange="updateTariffInfo()">
                                <option value="ph_boc">Philippines Bureau of Customs</option>
                                <option value="international_wto">International WTO Standards</option>
                                <option value="asean">ASEAN Trade Agreement</option>
                                <option value="custom">Custom Rate</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>AI Calculation Result</h6>
                        <div id="aiResult" class="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Calculating...</span>
                            </div>
                            <p class="mt-2">AI is calculating optimal rates...</p>
                        </div>
                        <div id="calculationResult" style="display: none;">
                            <div class="calculation-formula">
                                <strong>AI Formula Applied:</strong>
                                <div id="formulaDisplay"></div>
                            </div>
                            <div class="tariff-breakdown">
                                <h6>Rate Breakdown</h6>
                                <div id="rateBreakdown"></div>
                            </div>
                            <div class="mt-3">
                                <h5>Total Calculated Rate: <span id="totalRate" class="text-primary"></span></h5>
                                <div id="convertedRate" class="mt-2" style="display: none;">
                                    <h6>Converted Rate: <span id="convertedAmount" class="text-success"></span></h6>
                                    <small class="text-muted" id="exchangeRateInfo"></small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6>Shipping Criteria Applied:</h6>
                                <div id="shippingCriteria" class="small text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-ai-calculate" onclick="performAICalculation()">
                    <i class="fas fa-robot me-2"></i>Calculate with AI
                </button>
                <button type="button" class="btn btn-success" id="saveRateBtn" onclick="saveCalculatedRate()" style="display: none;">
                    <i class="fas fa-save me-2"></i>Save Rate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rate Details Modal -->
<div class="modal fade" id="rateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Rate & Tariff Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="rateDetailsContent"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-success" id="approveRateBtn" onclick="approveRate()">
                    <i class="fas fa-check me-2"></i>Approve Rate
                </button>
                <button class="btn btn-danger" id="rejectRateBtn" onclick="rejectRate()">
                    <i class="fas fa-times me-2"></i>Reject Rate
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
let currentRateId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Auto-load rates on page load so data is visible after redirect
    loadRates();
});

async function loadRates() {
    try {
        // Show loading state
        const tbody = document.getElementById('ratesTableBody');
        tbody.innerHTML = '<tr><td colspan="10" class="text-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading rates...</td></tr>';
        
        const status = document.getElementById('statusFilter').value;
        const carrier = document.getElementById('carrierFilter').value;
        const provider = document.getElementById('providerFilter').value;
        const search = document.getElementById('searchFilter').value;

        // Build URL with proper encoding
        const params = new URLSearchParams({
            status: status,
            carrier_type: carrier,
            provider_id: provider,
            search: search
        });
        
        let url = `../api/rates_api.php/rates?${params.toString()}`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            let detail = '';
            try {
                const j = await response.json();
                detail = j && (j.message || j.error) ? ' - ' + (j.message || j.error) : '';
            } catch (e) {
                try { detail = ' - ' + (await response.text()); } catch (_) {}
            }
            throw new Error(`HTTP error! status: ${response.status}${detail}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            displayRates(data.rates);
        } else {
            throw new Error(data.message || 'API returned error');
        }
        
    } catch (error) {
        console.error('Error loading rates:', error);
        document.getElementById('ratesTableBody').innerHTML = 
            `<tr><td colspan="10" class="text-center text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading rates: ${error.message}
            </td></tr>`;
    }
}

function displayRates(rates) {
    const tbody = document.getElementById('ratesTableBody');
    
    if (rates.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center">No rates found</td></tr>';
        return;
    }
    
    tbody.innerHTML = rates.map(rate => {
        const statusNorm = (rate.status || '').toString().toLowerCase();
        return `
        <tr class="modern-table-row">
            <td><span class="fw-medium">#${rate.id}</span></td>
            <td>
                <div class="route-info">
                    <div class="fw-medium">${rate.origin} → ${rate.destination}</div>
                    <small class="text-muted">${rate.distance_km} km</small>
                </div>
            </td>
            <td>
                <div class="company-name">
                    <i class="fas fa-building me-2 text-muted"></i>
                    <span class="fw-medium">${rate.provider_name}</span>
                </div>
            </td>
            <td>
                ${getCarrierTypeBadge(rate.carrier_type)}
            </td>
            <td><strong>₱${parseFloat(rate.base_rate || 0).toFixed(2)}</strong></td>
            <td><strong>₱${parseFloat(rate.tariff_amount || 0).toFixed(2)}</strong></td>
            <td><strong class="text-primary">₱${parseFloat(rate.total_rate).toFixed(2)}</strong></td>
            <td>
                ${rate.ai_calculated ? '<span class="ai-indicator">AI Calculated</span>' : '<span class="badge bg-secondary">Manual</span>'}
            </td>
            <td>${getStatusBadge(rate.status)}</td>
            <td class="text-center">
                <div class="action-buttons">
                    <button class="btn btn-modern-view" onclick="viewRateDetails(${rate.id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${statusNorm === 'pending' ? `
                        <button class="btn btn-modern-edit" onclick="editRate(${rate.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-modern-delete" onclick="deleteRate(${rate.id})" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
    }).join('');
}

function getCarrierTypeBadge(type) {
    const badges = {
        'land': '<span class="mode-badge mode-land"><i class="fas fa-truck me-1"></i>LAND</span>',
        'air': '<span class="mode-badge mode-air"><i class="fas fa-plane me-1"></i>AIR</span>',
        'sea': '<span class="mode-badge mode-sea"><i class="fas fa-ship me-1"></i>SEA</span>'
    };
    return badges[type] || '<span class="badge bg-secondary">' + type.toUpperCase() + '</span>';
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="status-badge status-pending">Pending</span>',
        'approved': '<span class="status-badge status-approved">Approved</span>',
        'rejected': '<span class="status-badge status-rejected">Rejected</span>',
        'calculating': '<span class="status-badge status-calculating">Calculating</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
}

function filterRates() {
    loadRates();
}

function refreshRates() {
    loadRates();
}

function openAICalculationModal() {
    const modal = new bootstrap.Modal(document.getElementById('aiCalculationModal'));
    modal.show();
}

function updateDeliveryType() {
    const weight = parseFloat(document.getElementById('cargoWeight').value);
    const deliveryTypeSelect = document.getElementById('deliveryType');
    const hint = document.getElementById('deliveryTypeHint');
    
    if (deliveryTypeSelect.value === 'auto') {
        let suggestedType = '';
        let hintText = '';
        
        if (weight <= 5) {
            suggestedType = 'motorcycle';
            hintText = 'Recommended: Motorcycle for lightweight packages (0-5 kg)';
        } else if (weight <= 20) {
            suggestedType = 'bike';
            hintText = 'Recommended: Bike for medium packages (5-20 kg)';
        } else {
            suggestedType = 'truck';
            hintText = 'Recommended: Truck for heavy packages (20+ kg)';
        }
        
        deliveryTypeSelect.value = suggestedType;
        hint.textContent = hintText;
    }
}

function updateCurrencyOptions() {
    const shipmentType = document.getElementById('shipmentType').value;
    const currencySection = document.getElementById('currencySection');
    
    if (shipmentType === 'international') {
        currencySection.style.display = 'block';
    } else {
        currencySection.style.display = 'none';
    }
}

function updateTariffInfo() {
    const cargoType = document.getElementById('cargoType').value;
    const tariffSource = document.getElementById('tariffSource').value;
    const targetCurrency = document.getElementById('targetCurrency').value;
    const shipmentType = document.getElementById('shipmentType').value;
    
    // Update shipping criteria display
    const criteria = [];
    
    if (cargoType === 'documents') {
        criteria.push('📄 Document special pricing applied (25% premium for importance)');
    }
    
    if (shipmentType === 'international') {
        criteria.push(`🌍 International shipment to ${targetCurrency}`);
        criteria.push(`💱 Currency conversion will be applied`);
    }
    
    switch(tariffSource) {
        case 'ph_boc':
            criteria.push('🏛️ Philippines Bureau of Customs tariffs');
            break;
        case 'international_wto':
            criteria.push('🌐 WTO international standards');
            break;
        case 'asean':
            criteria.push('🤝 ASEAN Trade Agreement rates');
            break;
        case 'custom':
            criteria.push('⚙️ Custom tariff rates');
            break;
    }
    
    const criteriaDiv = document.getElementById('shippingCriteria');
    if (criteriaDiv) {
        criteriaDiv.innerHTML = criteria.map(c => `<div>• ${c}</div>`).join('');
    }
}

async function loadRouteDetails() {
    const routeId = document.getElementById('routeSelect').value;
    if (!routeId) {
        document.getElementById('routeDetails').style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch(`../api/rates_api.php/routes/${routeId}`);
        const payload = await response.json();
        if (!payload.success || !payload.route) {
            throw new Error(payload.message || 'Failed to load route');
        }
        const route = payload.route;
        
        document.getElementById('routeInfo').innerHTML = `
            <p><strong>Route:</strong> ${route.origin} → ${route.destination}</p>
            <p><strong>Distance:</strong> ${route.distance_km} km</p>
            <p><strong>ETA:</strong> ${route.eta_min} minutes</p>
            <p><strong>Carrier Type:</strong> ${route.carrier_type.toUpperCase()}</p>
        `;
        
        document.getElementById('routeDetails').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading route details:', error);
    }
}

async function performAICalculation() {
    const routeId = document.getElementById('routeSelect').value;
    const weight = document.getElementById('cargoWeight').value;
    const cargoType = document.getElementById('cargoType').value;
    const serviceLevel = document.getElementById('serviceLevel').value;
    const deliveryType = document.getElementById('deliveryType').value;
    const shipmentType = document.getElementById('shipmentType').value;
    const targetCurrency = document.getElementById('targetCurrency').value;
    const tariffSource = document.getElementById('tariffSource').value;
    
    if (!routeId || !weight) {
        alert('Please select a route and enter cargo weight');
        return;
    }
    
    // Show loading
    document.getElementById('aiResult').style.display = 'block';
    document.getElementById('calculationResult').style.display = 'none';
    document.getElementById('saveRateBtn').style.display = 'none';
    
    try {
        const response = await fetch('../api/rates_api.php/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                route_id: routeId,
                cargo_weight: weight,
                cargo_type: cargoType,
                service_level: serviceLevel,
                delivery_type: deliveryType,
                shipment_type: shipmentType,
                target_currency: targetCurrency,
                tariff_source: tariffSource
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Hide loading, show results
            document.getElementById('aiResult').style.display = 'none';
            document.getElementById('calculationResult').style.display = 'block';
            document.getElementById('saveRateBtn').style.display = 'inline-block';
            
            // Display calculation details
            document.getElementById('formulaDisplay').innerHTML = result.formula;
            document.getElementById('rateBreakdown').innerHTML = result.breakdown;
            document.getElementById('totalRate').innerHTML = `₱${result.total_rate.toFixed(2)}`;
            
            // Show converted rate if international
            if (shipmentType === 'international' && result.converted_rate) {
                document.getElementById('convertedRate').style.display = 'block';
                document.getElementById('convertedAmount').innerHTML = `${result.target_currency} ${result.converted_rate.toFixed(2)}`;
                document.getElementById('exchangeRateInfo').innerHTML = `Exchange rate: 1 PHP = ${result.exchange_rate} ${result.targetCurrency}`;
            } else {
                document.getElementById('convertedRate').style.display = 'none';
            }
            
            // Store calculation data for saving
            window.currentCalculation = result;

            // OpenAI-powered sanity check (keeps calculations accurate by using server-side formula output)
            try {
                const aiResp = await fetch('../api/ai/rate_tariff_ai.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        route_id: parseInt(routeId, 10),
                        base_rate: result.base_rate,
                        tariff_amount: result.tariff_amount,
                        total_rate: result.total_rate,
                        cargo_weight: parseFloat(weight),
                        cargo_type: cargoType,
                        service_level: serviceLevel,
                        delivery_type: deliveryType,
                        shipment_type: shipmentType
                    })
                });

                const aiResult = await aiResp.json();
                if (aiResp.ok && aiResult && aiResult.success) {
                    document.getElementById('shippingCriteria').innerHTML = `
                        <div class="mt-2">
                            <span class="ai-indicator">OpenAI Verified</span>
                        </div>
                        <div class="mt-2"><strong>Rate Status:</strong> ${aiResult.rate_status}</div>
                        <div><strong>Recommendation:</strong> ${aiResult.recommendation}</div>
                        <div><strong>Profit Margin:</strong> ${aiResult.profit_margin}</div>
                        ${aiResult.warning ? `<div class="text-warning"><strong>Warning:</strong> ${aiResult.warning}</div>` : ''}
                    `;
                } else {
                    document.getElementById('shippingCriteria').innerHTML = '';
                }
            } catch (e) {
                document.getElementById('shippingCriteria').innerHTML = '';
            }
            
        } else {
            throw new Error(result.message || 'Calculation failed');
        }
        
    } catch (error) {
        console.error('AI Calculation error:', error);
        document.getElementById('aiResult').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Calculation failed: ${error.message}
            </div>
        `;
    }
}

async function saveCalculatedRate() {
    if (!window.currentCalculation) {
        alert('No calculation data available');
        return;
    }
    
    try {
        const response = await fetch('../api/rates_api.php/rates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(window.currentCalculation)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Close modal and refresh rates
            bootstrap.Modal.getInstance(document.getElementById('aiCalculationModal')).hide();
            loadRates();
            alert('Rate calculated and saved successfully!');
        } else {
            throw new Error(result.message || 'Save failed');
        }
        
    } catch (error) {
        console.error('Save rate error:', error);
        alert('Error saving rate: ' + error.message);
    }
}

async function viewRateDetails(rateId) {
    currentRateId = rateId;
    
    try {
        const response = await fetch(`../api/rates_api.php/rates/${rateId}`);
        const payload = await response.json();
        if (!payload.success || !payload.rate) {
            throw new Error(payload.message || 'Failed to load rate');
        }
        const rate = payload.rate;
        
        document.getElementById('rateDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Route Information</h6>
                    <p><strong>Route:</strong> ${rate.origin} → ${rate.destination}</p>
                    <p><strong>Distance:</strong> ${rate.distance_km} km</p>
                    <p><strong>Carrier Type:</strong> ${rate.carrier_type.toUpperCase()}</p>
                    <p><strong>Provider:</strong> ${rate.provider_name}</p>
                </div>
                <div class="col-md-6">
                    <h6>Rate Information</h6>
                    <p><strong>Base Rate:</strong> ₱${parseFloat(rate.base_rate || 0).toFixed(2)}</p>
                    <p><strong>Tariff Amount:</strong> ₱${parseFloat(rate.tariff_amount || 0).toFixed(2)}</p>
                    <p><strong>Total Rate:</strong> <span class="text-primary">₱${parseFloat(rate.total_rate).toFixed(2)}</span></p>
                    <p><strong>Status:</strong> ${getStatusBadge(rate.status)}</p>
                    <p><strong>AI Calculated:</strong> ${rate.ai_calculated ? 'Yes' : 'No'}</p>
                </div>
            </div>
            ${rate.calculation_details ? `
                <div class="mt-3">
                    <h6>Calculation Details</h6>
                    <div class="calculation-formula">
                        ${rate.calculation_details}
                    </div>
                </div>
            ` : ''}
            <div class="rate-timeline mt-3">
                <h6>Rate History</h6>
                <div class="timeline-item">
                    <small class="text-muted">${rate.created_at}</small>
                    <p class="mb-0">Rate created with status: ${rate.status}</p>
                </div>
                ${rate.updated_at ? `
                    <div class="timeline-item">
                        <small class="text-muted">${rate.updated_at}</small>
                        <p class="mb-0">Rate updated</p>
                    </div>
                ` : ''}
            </div>
        `;
        
        // Show/hide action buttons based on status
        const statusNorm = (rate.status || '').toString().toLowerCase();
        document.getElementById('approveRateBtn').style.display = statusNorm === 'pending' ? 'inline-block' : 'none';
        document.getElementById('rejectRateBtn').style.display = statusNorm === 'pending' ? 'inline-block' : 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('rateDetailsModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error loading rate details:', error);
        alert('Error loading rate details');
    }
}

async function approveRate() {
    if (!currentRateId) return;
    
    try {
        const response = await fetch(`../api/rates_api.php/rates/${currentRateId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('rateDetailsModal')).hide();
            loadRates();
            alert('Rate approved successfully!');
        } else {
            throw new Error(result.message || 'Approval failed');
        }
        
    } catch (error) {
        console.error('Approve rate error:', error);
        alert('Error approving rate: ' + error.message);
    }
}

async function rejectRate() {
    if (!currentRateId) return;
    
    const reason = prompt('Please provide reason for rejection:');
    if (!reason) return;
    
    try {
        const response = await fetch(`../api/rates_api.php/rates/${currentRateId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                rejection_reason: reason
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('rateDetailsModal')).hide();
            loadRates();
            alert('Rate rejected successfully!');
        } else {
            throw new Error(result.message || 'Rejection failed');
        }
        
    } catch (error) {
        console.error('Reject rate error:', error);
        alert('Error rejecting rate: ' + error.message);
    }
}

function editRate(rateId) {
    // TODO: Implement edit functionality
    alert('Edit functionality coming soon');
}

async function deleteRate(rateId) {
    if (!confirm('Are you sure you want to delete this rate?')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/rates_api.php/rates/${rateId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadRates();
            alert('Rate deleted successfully');
        } else {
            throw new Error(result.message || 'Delete failed');
        }
        
    } catch (error) {
        console.error('Delete rate error:', error);
        alert('Error deleting rate: ' + error.message);
    }
}
</script>
