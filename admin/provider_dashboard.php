<?php
session_start();
include("../connect.php");

// Only allow logged-in providers
if (!isset($_SESSION['email']) || $_SESSION['account_type'] != 3) {
    header("Location: ../admin/loginpage.php");
    exit();
}

$email = $_SESSION['email'];
$company_name = "";
$account_status = ""; // "pending" or "active"

// Get company name and provider ID
$provider_id = 0;
$stmt = $conn->prepare("SELECT company_name, provider_id, date_approved FROM active_service_provider WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($company_name, $provider_id, $date_approved);
if ($stmt->fetch()) {
    $account_status = "active";
}
$stmt->close();

if (empty($company_name)) {
    $stmt = $conn->prepare("SELECT company_name, date_submitted FROM pending_service_provider WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($company_name, $date_submitted);
    if ($stmt->fetch()) {
        $account_status = "pending";
    }
    $stmt->close();
}

if (empty($company_name)) {
    $company_name = "Service Provider";
}

// Get provider statistics
$totalRates = 0;
$activeRates = 0;
$totalSchedules = 0;
$activeSchedules = 0;

if ($account_status == "active" && $provider_id > 0) {
    // Get rates count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM freight_rates WHERE provider_id = ?");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $stmt->bind_result($totalRates);
    $stmt->fetch();
    $stmt->close();
    
    // Get active rates count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM freight_rates WHERE provider_id = ? AND status = 'Active'");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $stmt->bind_result($activeRates);
    $stmt->fetch();
    $stmt->close();
    
    // Get schedules count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM schedules WHERE provider_id = ?");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $stmt->bind_result($totalSchedules);
    $stmt->fetch();
    $stmt->close();
    
    // Get active schedules count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM schedules WHERE provider_id = ? AND status = 'scheduled'");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $stmt->bind_result($activeSchedules);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Provider Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="providerstyles.css" rel="stylesheet">
  <style>
    body {
      background: white;
      min-height: 100vh;
    }
    
    /* Modern Cards */
    .modern-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 15px !important;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }
    
    .modern-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Module Tiles */
    .module-tile {
      padding: 24px;
      margin-bottom: 20px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      background: white;
      border-radius: 16px !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .module-tile:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3) !important;
    }
    
    /* Clickable Cards */
    .clickable-card {
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    
    .clickable-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .clickable-card:hover::before {
      left: 100%;
    }
    
    .clickable-card:hover {
      transform: translateY(-5px) scale(1.03);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4) !important;
    }
    
    .clickable-card:active {
      transform: translateY(-2px) scale(1.01);
    }
    
    /* Tile Icon Styling */
    .tile-icon-wrapper {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
    }
    
    .tile-icon { 
      font-size: 22px;
      color: white;
    }
    
    .metric-small { 
      font-size: 12px; 
      color: black; 
      font-weight: 500;
    }
    
    .section-heading { 
      font-weight: 800; 
      letter-spacing: .5px;
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .mini-progress { 
      height: 8px; 
      border-radius: 10px; 
      background: rgba(255, 255, 255, 0.1);
      overflow: hidden;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .mini-progress > div { 
      height: 100%; 
      border-radius: 10px;
      background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
      transition: width 0.3s ease;
    }
    
    /* Status Badge Styling */
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-pending {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
    }
    
    .status-active {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
    }
    
    /* Enhanced Button Styling */
    .btn-modern {
      border: 2px solid;
      border-image: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) 1;
      color: #4f46e5;
      font-weight: 600;
      transition: all 0.3s ease;
      border-radius: 10px;
      padding: 8px 20px;
    }
    
    .btn-modern:hover {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      border-color: transparent;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
      color: white;
    }
    
    .btn-disabled {
      opacity: 0.5;
      pointer-events: none;
    }
    
    /* Animation for page load */
    .info-card, .module-tile {
      animation: fadeInUp 0.6s ease-out;
    }
    
    .module-tile:nth-child(1) { animation-delay: 0.1s; }
    .module-tile:nth-child(2) { animation-delay: 0.2s; }
    .module-tile:nth-child(3) { animation-delay: 0.3s; }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Text Colors for Dark Theme */
    .text-light-primary { color: black !important; }
    .text-light-secondary { color:  #4f46e5 !important; }
    .text-light-accent { color: black !important; }
    
    /* Main Content Layout */
    .main-content {
      margin-left: 250px;
      margin-top: 70px;
      min-height: calc(100vh - 70px);
      transition: margin-left 0.3s ease;
    }
    
    /* Mobile Layout */
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
      }
    }
    
    /* Dark Mode Dashboard Background */
    body.dark-mode {
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    }
    
    body.dark-mode .content {
      background: transparent;
    }
    
    body.dark-mode .module-tile {
      background: rgba(30, 41, 59, 0.8) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    body.dark-mode .metric-value {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .metric-label {
      color: #94a3b8 !important;
    }
    
    /* Light Mode Text Colors */
    .metric-value {
      color: #1e293b !important;
      font-weight: 700;
    }
    
    .metric-label {
      color: #1e293b !important;
      font-weight: 500;
    }
    
    .module-tile h5 {
      color: #1e293b !important;
    }
    
    .module-tile p {
      color: #1e293b !important;
    }
    
    .module-tile .text-muted {
      color: #1e293b !important;
    }
    
    .module-tile h6 {
      color: #1e293b !important;
    }
    
    .module-tile span {
      color: #1e293b !important;
    }
    
    .module-tile .metric-small {
      color: #1e293b !important;
    }
    
    .module-tile .btn {
      color: #1e293b !important;
    }
    
    /* Card text colors */
    .card-body h5,
    .card-body h6,
    .card-body p,
    .card-body span {
      color: #1e293b !important;
    }
    
    /* Dark mode card support */
    body.dark-mode .card {
      background: rgba(30, 41, 59, 0.8) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    body.dark-mode .card-body h5,
    body.dark-mode .card-body h6,
    body.dark-mode .card-body p,
    body.dark-mode .card-body span,
    body.dark-mode .card-body small {
      color: #e2e8f0 !important;
    }
    
    body.dark-mode .card-title {
      color: #f8fafc !important;
    }
    
    body.dark-mode .text-muted {
      color: #94a3b8 !important;
    }
  </style>
</head>
<body>

<?php include('provider_sidebar.php'); ?>

<div class="main-content">
    <?php include('provider_navbar.php'); ?>

    <div class="content p-4">
      
      <!-- Dashboard Cards Section -->
      <div class="row g-4 mb-4">
        <!-- Company Profile Card -->
        <div class="col-xl-4 col-lg-4 col-md-6">
          <div class="card h-100 shadow-sm border-0 modern-card">
            <div class="card-body d-flex flex-column">
              <div class="d-flex align-items-center mb-3">
                <div class="tile-icon-wrapper me-3">
                  <i class="fa-solid fa-building tile-icon"></i>
                </div>
                <h5 class="card-title mb-0 fw-bold">Company Profile</h5>
              </div>
              <div class="text-center flex-grow-1">
                <h4 class="fw-bold text-primary mb-2"><?= htmlspecialchars($company_name) ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars($email) ?></p>
                <span class="status-badge <?= $account_status == 'active' ? 'status-active' : 'status-pending' ?>">
                  <i class="fas <?= $account_status == 'active' ? 'fa-check-circle' : 'fa-clock' ?> me-1"></i>
                  <?= ucfirst($account_status) ?>
                </span>
              </div>
              <div class="mt-3">
                <div class="small text-muted mb-2">Account Status Progress</div>
                <div class="mini-progress">
                  <div style="width: <?= $account_status == 'active' ? '100' : '50' ?>%; background: <?= $account_status == 'active' ? '#10b981' : '#f59e0b' ?>"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Card -->
        <div class="col-xl-4 col-lg-4 col-md-6">
          <div class="card h-100 shadow-sm border-0 modern-card">
            <div class="card-body d-flex flex-column">
              <div class="d-flex align-items-center mb-3">
                <div class="tile-icon-wrapper me-3">
                  <i class="fa-solid fa-chart-bar tile-icon"></i>
                </div>
                <h5 class="card-title mb-0 fw-bold">Your Statistics</h5>
              </div>
              <div class="row text-center mb-3">
                <div class="col-6">
                  <h3 class="fw-bold text-primary mb-1"><?= $totalRates ?></h3>
                  <small class="text-muted">Total Rates</small>
                </div>
                <div class="col-6">
                  <h3 class="fw-bold text-primary mb-1"><?= $totalSchedules ?></h3>
                  <small class="text-muted">Total Schedules</small>
                </div>
              </div>
              <div class="row text-center mb-3">
                <div class="col-6">
                  <h5 class="fw-bold text-success mb-1"><?= $activeRates ?></h5>
                  <small class="text-muted">Active Rates</small>
                </div>
                <div class="col-6">
                  <h5 class="fw-bold text-success mb-1"><?= $activeSchedules ?></h5>
                  <small class="text-muted">Active Schedules</small>
                </div>
              </div>
              <div class="mt-auto">
                <div class="small text-muted mb-2">Activity Progress</div>
                <div class="mini-progress">
                  <div style="width: <?= $totalRates > 0 ? (($activeRates / $totalRates) * 100) : 0 ?>%; background: #10b981"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-xl-4 col-lg-4 col-md-12">
          <div class="card h-100 shadow-sm border-0 modern-card">
            <div class="card-body d-flex flex-column">
              <div class="d-flex align-items-center mb-3">
                <div class="tile-icon-wrapper me-3">
                  <i class="fa-solid fa-bolt tile-icon"></i>
                </div>
                <h5 class="card-title mb-0 fw-bold">Quick Actions</h5>
              </div>
              <div class="text-center mb-3">
                <h2 class="fw-bold text-primary mb-1"><?= date('d') ?></h2>
                <p class="text-muted small mb-1"><?= date('l, F Y') ?></p>
                <small class="text-muted">Today's Dashboard</small>
              </div>
              <div class="d-grid gap-2 mt-auto">
                <?php if ($account_status == 'active'): ?>
                  <button class="btn btn-modern" onclick="window.location.href='provider_rates.php'">
                    <i class="fas fa-plus me-2"></i>Add New Rate
                  </button>
                  <button class="btn btn-modern" onclick="window.location.href='provider_schedules.php'">
                    <i class="fas fa-calendar-plus me-2"></i>Create Schedule
                  </button>
                <?php else: ?>
                  <div class="text-center text-muted">
                    <i class="fas fa-clock mb-2 fs-3"></i>
                    <p class="small mb-0">Waiting for approval...</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Module Access Header -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="section-heading d-flex align-items-center gap-2">
          <i class="fa-solid fa-th-large"></i> Service Modules
        </div>
      </div>

      <!-- Module Access Cards -->
      <div class="row g-3 mb-4">
        <div class="col-xl-4 col-lg-4 col-md-6">
          <div class="module-tile shadow-soft rounded-4 p-4 h-100 clickable-card modern-card <?php echo ($account_status == 'pending') ? 'btn-disabled' : ''; ?>" 
               onclick="<?php echo ($account_status == 'active') ? "navigateToModule('provider_rates.php')" : ''; ?>"
               <?php if($account_status == 'pending') echo 'data-bs-toggle="tooltip" data-bs-placement="top" title="You cannot access this until approved"'; ?>>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="tile-icon-wrapper">
                <i class="fa-solid fa-tags tile-icon"></i>
              </div>
              <span class="fw-semibold fs-5 text-light-primary">Rates Management</span>
            </div>
            <div class="display-6 fw-bold text-light-primary mb-2"><?= $totalRates ?></div>
            <div class="d-flex justify-content-between mb-3">
              <div>
                <div class="text-success fw-semibold"><?= $activeRates ?></div>
                <div class="metric-small">Active</div>
              </div>
              <div class="text-end">
                <div class="text-light-secondary fw-semibold"><?= $totalRates - $activeRates ?></div>
                <div class="metric-small">Inactive</div>
              </div>
            </div>
            <div class="small text-light-secondary mb-3">Manage your pricing and tariffs</div>
            <div class="mt-auto">
              <div class="d-flex align-items-center justify-content-between">
                <div class="metric-small text-light-secondary">
                  <?php echo ($account_status == 'active') ? 'Click to manage' : 'Requires approval'; ?>
                </div>
                <i class="fas fa-arrow-right text-light-secondary small"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6">
          <div class="module-tile shadow-soft rounded-4 p-4 h-100 clickable-card modern-card <?php echo ($account_status == 'pending') ? 'btn-disabled' : ''; ?>" 
               onclick="<?php echo ($account_status == 'active') ? "navigateToModule('provider_schedules.php')" : ''; ?>"
               <?php if($account_status == 'pending') echo 'data-bs-toggle="tooltip" data-bs-placement="top" title="You cannot access this until approved"'; ?>>
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="tile-icon-wrapper">
                <i class="fa-solid fa-calendar-alt tile-icon"></i>
              </div>
              <span class="fw-semibold fs-5 text-light-primary">My Schedules</span>
            </div>
            <div class="display-6 fw-bold text-light-primary mb-2"><?= $totalSchedules ?></div>
            <div class="d-flex justify-content-between mb-3">
              <div>
                <div class="text-success fw-semibold"><?= $activeSchedules ?></div>
                <div class="metric-small">Active</div>
              </div>
              <div class="text-end">
                <div class="text-light-secondary fw-semibold"><?= $totalSchedules - $activeSchedules ?></div>
                <div class="metric-small">Inactive</div>
              </div>
            </div>
            <div class="small text-light-secondary mb-3">View and update your service schedules</div>
            <div class="mt-auto">
              <div class="d-flex align-items-center justify-content-between">
                <div class="metric-small text-light-secondary">
                  <?php echo ($account_status == 'active') ? 'Click to manage' : 'Requires approval'; ?>
                </div>
                <i class="fas fa-arrow-right text-light-secondary small"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-12">
          <div class="module-tile shadow-soft rounded-4 p-4 h-100 clickable-card modern-card" 
               onclick="navigateToModule('provider_profile.php')">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="tile-icon-wrapper">
                <i class="fa-solid fa-user tile-icon"></i>
              </div>
              <span class="fw-semibold fs-5 text-light-primary">My Profile</span>
            </div>
            <div class="display-6 fw-bold text-light-primary mb-2">
              <i class="fas fa-edit"></i>
            </div>
            <div class="small text-light-secondary mb-3">Update your company information and settings</div>
            <div class="mt-auto">
              <div class="d-flex align-items-center justify-content-between">
                <div class="metric-small text-light-secondary">Always accessible</div>
                <i class="fas fa-arrow-right text-light-secondary small"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Navigation function for module cards
function navigateToModule(url) {
  // Add a subtle loading effect
  const card = event.currentTarget;
  card.style.transform = 'scale(0.95)';
  card.style.opacity = '0.8';
  
  setTimeout(() => {
    window.location.href = url;
  }, 150);
}

// Initialize tooltips and dropdowns
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Note: Dropdown initialization is handled by navbar.php
  
  // Add ripple effect to clickable cards
  document.querySelectorAll('.clickable-card').forEach(card => {
    card.addEventListener('click', function(e) {
      if (this.classList.contains('btn-disabled')) return;
      
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.classList.add('ripple');
      
      this.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });
});
</script>

<style>
/* Ripple effect */
.ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  transform: scale(0);
  animation: ripple-animation 0.6s linear;
  pointer-events: none;
}

@keyframes ripple-animation {
  to {
    transform: scale(4);
    opacity: 0;
  }
}
</style>

<?php include('provider_footer.php'); ?>
