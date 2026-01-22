<?php
require '../connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Access control: ensure only logged-in admin users can view this page
require_once __DIR__ . '/auth.php';

// Get current year and last 6 months
$currentYear = date('Y');
$months = [];
$monthLabels = [];
for($i=5; $i>=0; $i--){
    $months[] = date('m', strtotime("-$i month"));
    $monthLabels[] = date('M', strtotime("-$i month"));
}

// Initialize arrays for charts
$spPendingData = $spActiveData = $spInactiveData = [];
$tariffsData = $sopData = [];

// Loop through months to get counts
foreach($months as $month){
    $spPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pending_service_provider WHERE MONTH(date_submitted)='$month' AND YEAR(date_submitted)='$currentYear'"))['total'];
    $spActive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Active' AND MONTH(date_approved)='$month' AND YEAR(date_approved)='$currentYear'"))['total'];
    $spInactive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Inactive' AND MONTH(date_approved)='$month' AND YEAR(date_approved)='$currentYear'"))['total'];
    
    $spPendingData[] = $spPending;
    $spActiveData[] = $spActive;
    $spInactiveData[] = $spInactive;

    $tariffsData[] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM freight_rates WHERE MONTH(created_at)='$month' AND YEAR(created_at)='$currentYear'"))['total'];
    $sopData[] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sop_documents WHERE MONTH(created_at)='$month' AND YEAR(created_at)='$currentYear'"))['total'];
}

// Totals for cards - Updated to match actual module data
$totalPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pending_service_provider"))['total'];
$totalActiveSP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Active'"))['total'];
$totalInactiveSP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Inactive'"))['total'];

// Routes data (manage_routes.php)
// Active routes are those with status != 'completed' (pending, in-progress, etc.)
$totalRoutes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM routes"))['total'];
$activeRoutes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM routes WHERE status != 'completed'"))['total'];

// Service Points data (network_manage.php)
$totalPoints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM network_points"))['total'];
$activePoints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM network_points WHERE status='Active'"))['total'];

// Schedules data (schedule_routes.php)
$totalSchedules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM schedules"))['total'];
$activeSchedules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM schedules WHERE status='scheduled'"))['total'];

// SOPs data (view_sop.php)
$totalSOP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sop_documents"))['total'];
$activeSOP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sop_documents WHERE status='Active'"))['total'];

// Tariffs data (set_tariffs.php)
// Align card counts with the Set Rates module which operates on active service providers
// Total excludes archived providers to correctly compute Inactive as Total - Active
$totalTariffs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status IN ('Active','Inactive')"))['total'];
$activeTariffs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Active'"))['total'];

// Additional system data
$totalAdmin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(email) AS total FROM admin_list"))['total'];
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(email) AS total FROM newaccounts"))['total'];

$recentActivities = [];
$ra_sql = "SELECT id, message, type, link, created_at FROM notifications ORDER BY created_at DESC LIMIT 8";
if ($ra_res = mysqli_query($conn, $ra_sql)) {
    while ($row = mysqli_fetch_assoc($ra_res)) {
        $recentActivities[] = $row;
    }
}

include('header.php');
include('sidebar.php');
include('navbar.php');
?>

<style>
  body{
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
  }
  
  /* Dark mode override for dashboard */
  body.dark-mode {
    background: linear-gradient(180deg, #2b3f4e 0%, #1f3442 100%) !important;
  }
  
  /* Modern Card Styling */
  .modern-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95) !important;
  }
  
  .modern-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
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
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
  }
  
  .clickable-card:hover::before {
    left: 100%;
  }
  
  .clickable-card:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
  }
  
  .clickable-card:active {
    transform: translateY(-2px) scale(1.01);
  }
  
  /* Tile Icon Styling */
  .tile-icon-wrapper {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  }
  
  .tile-icon { 
    font-size: 20px;
  }
  
  .metric-small { 
    font-size: 12px; 
    color: #6b7280; 
    font-weight: 500;
  }
  
  .section-heading { 
    font-weight: 800; 
    letter-spacing: .5px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .mini-progress { 
    height: 8px; 
    border-radius: 10px; 
    background: #e5e7eb;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  
  .mini-progress > div { 
    height: 100%; 
    border-radius: 10px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
  }
  
  /* Enhanced Button Styling */
  .btn-outline-primary {
    border: 2px solid;
    border-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%) 1;
    color: #667eea;
    font-weight: 600;
    transition: all 0.3s ease;
  }
  
  .btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
  }
  
  /* Dark Mode Enhancements */
  body.dark-mode .modern-card {
    background: rgba(31, 52, 66, 0.95) !important;
    border: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  body.dark-mode .tile-icon-wrapper {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
  }
  
  body.dark-mode .mini-progress > div {
    background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
  }
  
  /* Animation for page load */
  .info-card, .summary-tile {
    animation: fadeInUp 0.6s ease-out;
  }
  
  .summary-tile:nth-child(1) { animation-delay: 0.1s; }
  .summary-tile:nth-child(2) { animation-delay: 0.2s; }
  .summary-tile:nth-child(3) { animation-delay: 0.3s; }
  .summary-tile:nth-child(4) { animation-delay: 0.4s; }
  .summary-tile:nth-child(5) { animation-delay: 0.5s; }
  .summary-tile:nth-child(6) { animation-delay: 0.6s; }
  
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
</style>

<style>
  .activity-wrap { max-height: 260px; overflow: auto; padding-right: 4px; }
  .activity-item { display:flex; align-items:flex-start; gap:12px; padding:10px 12px; border-radius:12px; text-decoration:none; background:rgba(255,255,255,0.75); border:1px solid rgba(99,102,241,0.15); transition: all .25s ease; position:relative; }
  .activity-item:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(2,6,23,.08); background: rgba(255,255,255,0.95); }
  .activity-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; box-shadow: 0 6px 16px rgba(0,0,0,.12); color:#fff; flex:0 0 38px; }
  .activity-icon.admin { background: linear-gradient(135deg,#60a5fa,#2563eb); }
  .activity-icon.service_provider { background: linear-gradient(135deg,#34d399,#059669); }
  .activity-icon.warning { background: linear-gradient(135deg,#fbbf24,#f59e0b); }
  .activity-icon.error { background: linear-gradient(135deg,#f87171,#ef4444); }
  .activity-icon.info { background: linear-gradient(135deg,#a78bfa,#7c3aed); }
  .activity-body { flex:1; min-width:0; }
  .activity-title { font-weight:600; font-size:13.5px; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .activity-meta { display:flex; gap:8px; align-items:center; margin-top:4px; }
  .activity-time { font-size:12px; color:#6b7280; }
  .activity-badge { font-size:11px; padding:2px 8px; border-radius:999px; background:rgba(99,102,241,.12); color:#4f46e5; font-weight:600; }
  .activity-chevron { color:#9ca3af; margin-left:8px; }
  .activity-item:hover .activity-chevron { color:#6b7280; }
  .activity-wrap::-webkit-scrollbar { height:8px; width:8px; }
  .activity-wrap::-webkit-scrollbar-thumb { background: linear-gradient(180deg,#c7d2fe,#a5b4fc); border-radius:8px; }
  .activity-wrap::-webkit-scrollbar-track { background: transparent; }
  body.dark-mode .activity-item { background: rgba(31,52,66,.7); border-color: rgba(255,255,255,.08); }
  body.dark-mode .activity-item:hover { background: rgba(31,52,66,.95); }
  body.dark-mode .activity-title { color:#e5e7eb; }
  body.dark-mode .activity-time { color:#9ca3af; }
  body.dark-mode .activity-badge { background: rgba(99,102,241,.18); color:#c7d2fe; }
</style>

<div class="content p-4">

  <!-- Info Cards -->
  <div class="row g-3 mb-4">
    <!-- Weather & Environment -->
    <div class="col-xl-4 col-lg-4 col-md-4">
      <div class="info-card shadow-soft rounded-4 p-4 h-100 modern-card">
        <div class="d-flex align-items-center mb-3 fw-semibold text-info">
          <i class="fa-solid fa-cloud-sun me-2 fs-4"></i> 
          <span>Weather & Environment</span>
        </div>
        <div class="text-center">
          <div class="display-5 fw-bold text-primary mb-2">28°C</div>
          <div class="small text-muted mb-3">Manila, Philippines • Partly Cloudy</div>
        </div>
        <div class="row text-center mb-3">
          <div class="col-6">
            <div class="text-info fw-semibold fs-6">75%</div>
            <div class="metric-small">Humidity</div>
          </div>
          <div class="col-6">
            <div class="text-info fw-semibold fs-6">15 km/h</div>
            <div class="metric-small">Wind Speed</div>
          </div>
        </div>
        <div class="mt-auto">
          <div class="metric-small mb-2">Air Quality: Good</div>
          <div class="mini-progress"><div style="width: 70%; background:#22c55e"></div></div>
        </div>
      </div>
    </div>

    <!-- Today's Dashboard -->
    <div class="col-xl-4 col-lg-4 col-md-4">
      <div class="info-card shadow-soft rounded-4 p-4 h-100 modern-card">
        <div class="d-flex align-items-center mb-3 fw-semibold text-success">
          <i class="fa-regular fa-calendar me-2 fs-4"></i> 
          <span>Today's Dashboard</span>
        </div>
        <div class="text-center">
          <div class="display-5 fw-bold text-success mb-2"><?= date('d') ?></div>
          <div class="small text-muted mb-1"><?= date('l, F Y') ?></div>
          <div class="metric-small mb-3">Administrative Overview</div>
        </div>
        <div class="row text-center mb-3">
          <div class="col-4">
            <div class="text-success fw-semibold fs-6">5</div>
            <div class="metric-small">Active Sessions</div>
          </div>
          <div class="col-4">
            <div class="text-warning fw-semibold fs-6"><?= $totalPending ?></div>
            <div class="metric-small">Pending Tasks</div>
          </div>
          <div class="col-4">
            <div class="text-primary fw-semibold fs-6">Operational</div>
            <div class="metric-small">System Status</div>
          </div>
        </div>
        <div class="mt-auto">
          <div class="mini-progress"><div style="width: 85%; background:#22c55e"></div></div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-4">
      <div class="info-card shadow-soft rounded-4 p-4 h-100 modern-card">
        <div class="d-flex align-items-center mb-2 fw-semibold text-primary">
          <i class="fa-solid fa-bolt me-2 fs-4"></i>
          <span>Recent Activity</span>
        </div>
        <div class="activity-wrap">
          <?php if (!empty($recentActivities)): ?>
            <?php foreach ($recentActivities as $act): ?>
              <?php
                $type = strtolower($act['type'] ?? 'info');
                $icon = 'fa-circle-info';
                $iconClass = 'info';
                if ($type === 'admin') { $icon = 'fa-shield-halved'; $iconClass = 'admin'; }
                elseif ($type === 'service_provider') { $icon = 'fa-truck-fast'; $iconClass = 'service_provider'; }
                elseif ($type === 'warning') { $icon = 'fa-triangle-exclamation'; $iconClass = 'warning'; }
                elseif ($type === 'error') { $icon = 'fa-circle-xmark'; $iconClass = 'error'; }
                $msg = htmlspecialchars($act['message'] ?? '', ENT_QUOTES, 'UTF-8');
                $link = !empty($act['link']) ? $act['link'] : '#';
                $createdRaw = $act['created_at'] ?? '';
              ?>
              <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" class="activity-item mb-2">
                <div class="activity-icon <?= $iconClass ?>"><i class="fa-solid <?= $icon ?>"></i></div>
                <div class="activity-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="activity-title" title="<?= $msg ?>"><?= $msg ?></div>
                    <span class="activity-badge text-nowrap"><?= ucwords($type) ?></span>
                  </div>
                  <div class="activity-meta">
                    <span class="activity-time" data-time="<?= htmlspecialchars($createdRaw, ENT_QUOTES, 'UTF-8') ?>"></span>
                  </div>
                </div>
                <i class="fas fa-chevron-right activity-chevron"></i>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted small py-4">No recent activity</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Module Summary Header -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="section-heading d-flex align-items-center gap-2"><i class="fa-solid fa-border-all"></i> Module Summary</div>
    <div>
      <a href="dashboard_export.php" class="btn btn-outline-primary btn-sm" title="Download dashboard summary as Excel (CSV)">
        <i class="fa-solid fa-file-excel me-1"></i> Download Excel
      </a>
    </div>
  </div>

  <!-- Module Summary Tiles -->
  <div class="row g-3 mb-4">
    <?php
      $summaryTiles = [
        ['icon'=>'fa-building','title'=>'Service Providers','total'=>$totalActiveSP + $totalInactiveSP,'primary'=>$totalActiveSP,'secondary'=>$totalInactiveSP,'pcolor'=>'text-primary','scolor'=>'text-muted','link'=>'active_providers.php','desc'=>''],
        ['icon'=>'fa-route','title'=>'Routes','total'=>$totalRoutes,'primary'=>$activeRoutes,'secondary'=>$totalRoutes - $activeRoutes,'pcolor'=>'text-success','scolor'=>'text-muted','link'=>'manage_routes.php','desc'=>''],
        ['icon'=>'fa-calendar-days','title'=>'Schedules','total'=>$totalSchedules,'primary'=>$activeSchedules,'secondary'=>$totalSchedules - $activeSchedules,'pcolor'=>'text-warning','scolor'=>'text-muted','link'=>'confirmed_timetables.php','desc'=>''],
        ['icon'=>'fa-location-dot','title'=>'Network Points','total'=>$totalPoints,'primary'=>$activePoints,'secondary'=>$totalPoints - $activePoints,'pcolor'=>'text-success','scolor'=>'text-muted','link'=>'manage_routes.php','desc'=>''],
        ['icon'=>'fa-file-lines','title'=>'SOPs','total'=>$totalSOP,'primary'=>$activeSOP,'secondary'=>$totalSOP - $activeSOP,'pcolor'=>'text-primary','scolor'=>'text-muted','link'=>'view_sop.php','desc'=>''],
        ['icon'=>'fa-money-bill-wave','title'=>'Tariffs','total'=>$totalTariffs,'primary'=>$activeTariffs,'secondary'=>$totalTariffs - $activeTariffs,'pcolor'=>'text-danger','scolor'=>'text-muted','link'=>'set_tariffs.php','desc'=>''],
      ];
      foreach($summaryTiles as $t):
    ?>
      <div class="col-xxl-2 col-xl-3 col-lg-4 col-md-6">
        <div class="summary-tile shadow-soft rounded-4 p-3 h-100 clickable-card" onclick="navigateToModule('<?= $t['link'] ?>')" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $t['desc'] ?>">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="tile-icon-wrapper">
              <i class="fa-solid <?= $t['icon'] ?> tile-icon text-white"></i>
            </div>
            <span class="fw-semibold fs-6"><?= $t['title'] ?></span>
          </div>
          <div class="display-6 fw-bold text-dark mb-2"><?= $t['total'] ?></div>
          <div class="d-flex justify-content-between mb-2">
            <div>
              <div class="<?= $t['pcolor'] ?> fw-semibold"><?= $t['primary'] ?></div>
              <div class="metric-small">Active</div>
            </div>
            <div class="text-end">
              <div class="<?= $t['scolor'] ?> fw-semibold"><?= $t['secondary'] ?></div>
              <div class="metric-small">Inactive</div>
            </div>
          </div>
          <div class="mt-auto">
            <div class="d-flex align-items-center justify-content-between">
              <div class="metric-small text-muted">Click to manage</div>
              <i class="fas fa-arrow-right text-muted small"></i>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Charts Section -->
  <div class="row g-3 mb-4">
      <div class="col-xl-4 col-lg-6 col-md-12">
          <div class="chart-card">
              <h6>Service Providers (Last 6 Months)</h6>
              <div class="canvas-wrapper">
                  <canvas id="providerChart"></canvas>
              </div>
          </div>
      </div>
      <div class="col-xl-4 col-lg-12 col-md-12">
          <div class="chart-card">
              <h6>Routes & Points</h6>
              <div class="canvas-wrapper">
                  <canvas id="routesPointsChart"></canvas>
              </div>
          </div>
      </div>
      <div class="col-xl-4 col-lg-6 col-md-12">
          <div class="chart-card">
              <h6>Tariffs & SOPs (Last 6 Months)</h6>
              <div class="canvas-wrapper">
                  <canvas id="tariffSOPChart"></canvas>
              </div>
          </div>
      </div>
  </div>

  <div class="row g-3 mb-4">
      <div class="col-12">
          <div class="chart-card">
              <h6>Dashboard Overview (Totals)</h6>
              <div class="canvas-wrapper">
                  <canvas id="overviewLineChart"></canvas>
              </div>
          </div>
      </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
const monthLabels = <?= json_encode($monthLabels) ?>;
const spPendingData = <?= json_encode($spPendingData) ?>;
const spActiveData = <?= json_encode($spActiveData) ?>;
const spInactiveData = <?= json_encode($spInactiveData) ?>;
const tariffsData = <?= json_encode($tariffsData) ?>;
const sopData = <?= json_encode($sopData) ?>;
const totalRoutes = <?= $totalRoutes ?>;
const activeRoutes = <?= $activeRoutes ?>;
const totalPoints = <?= $totalPoints ?>;
const activePoints = <?= $activePoints ?>;
const totalPending = <?= $totalPending ?>;
const totalActiveSP = <?= $totalActiveSP ?>;
const totalInactiveSP = <?= $totalInactiveSP ?>;
const totalAdmin = <?= $totalAdmin ?>;
const totalUsers = <?= $totalUsers ?>;
const totalTariffs = <?= $totalTariffs ?>;
const activeTariffs = <?= $activeTariffs ?>;
const totalSOP = <?= $totalSOP ?>;
const activeSOP = <?= $activeSOP ?>;
const totalSchedules = <?= $totalSchedules ?>;
const activeSchedules = <?= $activeSchedules ?>;

// Dark mode chart configuration
function getChartConfig(isDark = false) {
  return {
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          color: isDark ? '#e6edf3' : '#374151',
          font: {
            family: 'Inter, sans-serif'
          }
        }
      }
    },
    scales: {
      x: {
        ticks: {
          color: isDark ? '#cbd5e1' : '#6b7280'
        },
        grid: {
          color: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
        }
      },
      y: {
        ticks: {
          color: isDark ? '#cbd5e1' : '#6b7280'
        },
        grid: {
          color: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
        },
        beginAtZero: true
      }
    }
  };
}

// Check if dark mode is enabled
function isDarkMode() {
  return document.body.classList.contains('dark-mode');
}

// Service Providers Stacked Area Chart
const providerChart = new Chart(document.getElementById('providerChart'), {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [
            { label: 'Pending', data: spPendingData, borderColor: '#ffc107', backgroundColor:'rgba(255,193,7,0.4)', fill:true, tension:0.4 },
            { label: 'Active', data: spActiveData, borderColor: '#28a745', backgroundColor:'rgba(40,167,69,0.4)', fill:true, tension:0.4 },
            { label: 'Inactive', data: spInactiveData, borderColor: '#dc3545', backgroundColor:'rgba(220,53,69,0.4)', fill:true, tension:0.4 }
        ]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        ...getChartConfig(isDarkMode())
    }
});

// Tariffs & SOPs Line Chart
const tariffSOPChart = new Chart(document.getElementById('tariffSOPChart'), {
    type: 'line',
    data: { 
        labels: monthLabels, 
        datasets: [
            { label:'Tariffs', data:tariffsData, borderColor:'#fd7e14', fill:false, tension:0.4 },
            { label:'SOPs', data:sopData, borderColor:'#20c997', fill:false, tension:0.4 }
        ]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        ...getChartConfig(isDarkMode())
    }
});

// Routes & Points & Schedules Bar Chart
const routesPointsChart = new Chart(document.getElementById('routesPointsChart'), {
    type:'bar',
    data:{
        labels:['Total Routes','Active Routes','Total Points','Active Points','Total Schedules','Active Schedules'],
        datasets:[{
            label:'Count',
            data:[totalRoutes,activeRoutes,totalPoints,activePoints,totalSchedules,activeSchedules],
            backgroundColor:['#6f42c1','#8e5ec7','#17a2b8','#3bb3c3','#ffc107','#ffcd39']
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        ...getChartConfig(isDarkMode()),
        plugins:{
            legend:{
                display:false
            }
        }
    }
});

// Dashboard Overview Line Chart (Totals)
const overviewLineChart = new Chart(document.getElementById('overviewLineChart'), {
    type:'line',
    data:{
        labels:['Pending SP','Active SP','Inactive SP','Total Routes','Active Routes','Total Points','Active Points','Total Schedules','Active Schedules','Total Tariffs','Active Tariffs','Total SOPs','Active SOPs'],
        datasets:[{
            label:'Total Counts',
            data:[totalPending,totalActiveSP,totalInactiveSP,totalRoutes,activeRoutes,totalPoints,activePoints,totalSchedules,activeSchedules,totalTariffs,activeTariffs,totalSOP,activeSOP],
            borderColor:'#007bff',
            backgroundColor:'rgba(0,123,255,0.3)',
            fill:true,
            tension:0.4,
            pointRadius:5,
            pointBackgroundColor:'#007bff'
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        ...getChartConfig(isDarkMode()),
        scales:{
            ...getChartConfig(isDarkMode()).scales,
            y:{ 
                ...getChartConfig(isDarkMode()).scales.y,
                title:{
                    display:true,
                    text:'Count',
                    color: isDarkMode() ? '#e6edf3' : '#374151'
                }
            },
            x:{ 
                ...getChartConfig(isDarkMode()).scales.x,
                title:{
                    display:true,
                    text:'Module Data',
                    color: isDarkMode() ? '#e6edf3' : '#374151'
                }
            }
        }
    }
});

// Update charts when dark mode is toggled
window.addEventListener('darkModeToggle', function(e) {
    const isDark = e.detail.isDarkMode;
    const newConfig = getChartConfig(isDark);
    
    // Update all charts
    [providerChart, tariffSOPChart, routesPointsChart, overviewLineChart].forEach(chart => {
        if (chart) {
            chart.options = {
                ...chart.options,
                ...newConfig
            };
            
            // Update specific scales for overview chart
            if (chart === overviewLineChart) {
                chart.options.scales.y.title.color = isDark ? '#e6edf3' : '#374151';
                chart.options.scales.x.title.color = isDark ? '#e6edf3' : '#374151';
            }
            
            chart.update();
        }
    });
});
</script>

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

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Add ripple effect to clickable cards
  document.querySelectorAll('.clickable-card').forEach(card => {
    card.addEventListener('click', function(e) {
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

  function timeAgo(dateStr){
    const dt = new Date(dateStr.replace(' ', 'T'));
    if (isNaN(dt.getTime())) return dateStr;
    const rtf = new Intl.RelativeTimeFormat(undefined, {numeric:'auto'});
    const diff = (Date.now() - dt.getTime())/1000;
    const thresholds = [
      ['year', 31536000],
      ['month', 2592000],
      ['week', 604800],
      ['day', 86400],
      ['hour', 3600],
      ['minute', 60]
    ];
    for (const [unit, sec] of thresholds){
      const value = Math.floor(diff / sec);
      if (value >= 1) return rtf.format(-value, unit);
    }
    return 'just now';
  }

  function renderActivityTimes(){
    document.querySelectorAll('.activity-time').forEach(el => {
      const raw = el.getAttribute('data-time');
      if (!raw) return;
      el.textContent = timeAgo(raw);
      try { el.setAttribute('title', new Date(raw.replace(' ', 'T')).toLocaleString()); } catch(e) {}
    });
  }
  renderActivityTimes();
  setInterval(renderActivityTimes, 60000);
});
</script>

<style>
/* Ripple effect */
.ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.6);
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

<?php include('footer.php'); ?>
