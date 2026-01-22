<!--sidebar-->
<?php
require_once __DIR__ . '/auth.php';
$currentPage = basename($_SERVER['PHP_SELF']);

// Group pages
$serviceProviderPages = ['pending_providers.php', 'active_providers.php'];
$routePages = ['manage_routes.php'];
$ratePages = ['rate_tariff_management.php'];
$sopPages = ['view_sop.php', 'archived_sop.php'];
$schedulePages = ['schedule_routes.php', 'confirmed_timetables.php'];

// Add modern button styles
function getButtonClass($isActive) {
    $baseClasses = 'nav-link d-flex align-items-center py-2 px-3 rounded-pill transition-all';
    $activeClasses = $isActive ? 'bg-gradient-primary text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100';
    return $baseClasses . ' ' . $activeClasses;
}
?>

<div class="sidebar">
  <div class="sidebar-header">
    <div class="logo d-flex align-items-center">
      <img src="logo.png" alt="Logo" class="logo-img me-2">
    </div>      
  </div>
  
  <div class="menu">
    <ul class="nav flex-column">

      <!-- Dashboard -->
      <li class="nav-item mb-2">
        <a href="dashboard.php" class="<?= getButtonClass($currentPage == 'dashboard.php') ?>" data-bs-toggle="tooltip">
          <i class="fas fa-home me-2"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <!-- Service Provider Management -->
      <li class="nav-item mb-2">
        <a class="<?= getButtonClass(in_array($currentPage, $serviceProviderPages)) ?>" data-bs-toggle="collapse" href="#serviceProviderMenu">
          <i class="fas fa-handshake me-2"></i>
          <span>Service Providers</span>
          <i class="fas fa-chevron-down ms-auto transition-transform" id="serviceProviderChevron"></i>
        </a>
        <div class="collapse ms-3 mt-2" id="serviceProviderMenu">
          <div class="d-flex flex-column">
            <a href="pending_providers.php" class="<?= getButtonClass($currentPage == 'pending_providers.php') ?> mb-1">
              <i class="fas fa-hourglass-half me-2"></i>
              <span>Pending</span>
            </a>
            <a href="active_providers.php" class="<?= getButtonClass($currentPage == 'active_providers.php') ?>">
              <i class="fas fa-list-ul me-2"></i>
              <span>Active Providers</span>
            </a>
          </div>
        </div>
      </li>

      <!-- Routes -->
      <li class="nav-item mb-2">
        <a class="<?= getButtonClass(in_array($currentPage, $routePages)) ?>" data-bs-toggle="collapse" href="#routeMenu">
          <i class="fas fa-route me-2"></i>
          <span>Routes</span>
          <i class="fas fa-chevron-down ms-auto transition-transform" id="routeChevron"></i>
        </a>
        <div class="collapse ms-3 mt-2" id="routeMenu">
          <div class="d-flex flex-column">
            <a href="manage_routes.php" class="<?= getButtonClass($currentPage == 'manage_routes.php') ?>">
              <i class="fas fa-route me-2"></i>
              <span>Manage Routes</span>
            </a>
          </div>
        </div>
      </li>

      <!-- Rate & Tariff Management -->
      <li class="nav-item mb-2">
        <a class="<?= getButtonClass(in_array($currentPage, $ratePages)) ?>" data-bs-toggle="collapse" href="#rateMenu">
          <i class="fas fa-coins me-2"></i>
          <span>Rate & Tariff</span>
          <i class="fas fa-chevron-down ms-auto transition-transform" id="rateChevron"></i>
        </a>
        <div class="collapse ms-3 mt-2" id="rateMenu">
          <div class="d-flex flex-column">
            <a href="rate_tariff_management.php" class="<?= getButtonClass($currentPage == 'rate_tariff_management.php') ?>">
              <i class="fas fa-robot me-2"></i>
              <span>AI Rate & Tariff Management</span>
            </a>
          </div>
        </div>
      </li>

      <!-- Schedules & Transit Timetable -->
      <li class="nav-item mb-2">
        <a class="<?= getButtonClass(in_array($currentPage, $schedulePages)) ?>" data-bs-toggle="collapse" href="#scheduleMenu">
          <i class="fas fa-calendar-alt me-2"></i>
          <span>Schedules</span>
          <i class="fas fa-chevron-down ms-auto transition-transform" id="scheduleChevron"></i>
        </a>
        <div class="collapse ms-3 mt-2" id="scheduleMenu">
          <div class="d-flex flex-column">
            <a href="scheduling_calendar.php" class="<?= getButtonClass($currentPage == 'scheduling_calendar.php') ?> mb-1">
              <i class="fas fa-clock me-2"></i>
              <span>Schedule Routes</span>
            </a>
          </div>
        </div>
      </li>

      <!-- SOP Manager -->
      <li class="nav-item">
        <a class="<?= getButtonClass(in_array($currentPage, $sopPages)) ?>" data-bs-toggle="collapse" href="#sopMenu">
          <i class="fas fa-book-open me-2"></i>
          <span>SOP Manager</span>
          <i class="fas fa-chevron-down ms-auto transition-transform" id="sopChevron"></i>
        </a>
        <div class="collapse ms-3 mt-2" id="sopMenu">
          <div class="d-flex flex-column">
            <a href="view_sop.php" class="<?= getButtonClass($currentPage == 'view_sop.php') ?> mb-1">
              <i class="fas fa-clipboard-list me-2"></i>
              <span>View SOPs</span>
            </a>
            <a href="archived_sop.php" class="<?= getButtonClass($currentPage == 'archived_sop.php') ?>">
              <i class="fas fa-archive me-2"></i>
              <span>Archived SOPs</span>
            </a>
          </div>
        </div>
      </li>
    </ul>
  </div>

  <div class="admin-info mt-auto text-center p-3">
    <span class="admin-text text-white px-3 py-2 rounded">ADMIN</span>
  </div>
</div>
