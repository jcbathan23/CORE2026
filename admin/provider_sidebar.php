<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="modern-sidebar">
  <div class="sidebar-header">
    <div class="logo d-flex align-items-center">
      <img src="logo.png" alt="Logo" class="logo-img me-2">
    </div>      
  </div>
  
  <div class="menu">
    <ul class="nav flex-column">
      <!-- Dashboard -->
      <li class="nav-item">
        <a href="provider_dashboard.php" class="nav-link <?= ($currentPage == 'provider_dashboard.php') ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
          <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
      </li>

      <?php if ($account_status == 'active'): ?>
      <!-- Rates Management -->
      <li class="nav-item">
        <a href="provider_rates.php" class="nav-link <?= ($currentPage == 'provider_rates.php') ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="Manage Rates">
          <i class="fas fa-tags"></i> <span>My Rates</span>
        </a>
      </li>

      <!-- Schedules -->
      <li class="nav-item">
        <a href="provider_schedules.php" class="nav-link <?= ($currentPage == 'provider_schedules.php') ? 'active' : '' ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="My Schedules">
          <i class="fas fa-calendar-alt"></i> <span>My Schedules</span>
        </a>
      </li>
      <?php else: ?>
      <!-- Disabled items for pending providers -->
      <li class="nav-item">
        <a href="#" class="nav-link disabled" data-bs-toggle="tooltip" data-bs-placement="right" title="Available after approval">
          <i class="fas fa-tags"></i> <span>My Rates</span>
          <i class="fas fa-lock ms-auto"></i>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link disabled" data-bs-toggle="tooltip" data-bs-placement="right" title="Available after approval">
          <i class="fas fa-calendar-alt"></i> <span>My Schedules</span>
          <i class="fas fa-lock ms-auto"></i>
        </a>
      </li>
      <?php endif; ?>

    </ul>
  </div>

  <!-- Provider Info -->
  <div class="provider-info mt-auto text-center p-3">
    <div class="provider-status-badge <?= $account_status == 'active' ? 'status-active' : 'status-pending' ?>">
      <i class="fas <?= $account_status == 'active' ? 'fa-check-circle' : 'fa-clock' ?> me-1"></i>
      <?= ucfirst($account_status) ?>
    </div>
    <div class="provider-name"><?= htmlspecialchars($company_name) ?></div>
  </div>

  <!-- Logout Button -->
  <div class="logout-section p-3">
    <a href="#" class="logout-btn" id="providerLogoutBtn">
      <i class="fas fa-sign-out-alt me-2"></i>
      <span>Logout</span>
    </a>
  </div>
</div>

<style>
/* Modern Provider Sidebar Styles */
.modern-sidebar {
  width: 250px;
  height: 100vh;
  background: linear-gradient(180deg, #2b3f4e 0%, #1f3442 100%);
  color: #e6edf3;
  position: fixed;
  left: 0;
  top: 0;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  overflow-x: hidden;
  transition: all 0.3s ease;
  box-shadow: 4px 0 20px rgba(0,0,0,0.25);
  border-right: 1px solid rgba(255,255,255,0.06);
  backdrop-filter: blur(6px);
  z-index: 1000;
}

/* Sidebar Header */
.sidebar-header {
  padding: 20px 15px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo-img {
  height: 130px;
  width: auto;
}

/* Menu Styles */
.menu ul {
  list-style: none;
  padding: 12px 10px 16px;
}

.menu ul li a {
  text-decoration: none;
  display: flex;
  align-items: center;
  font-size: 15px;
  gap: 12px;
  font-weight: 600;
  padding: 14px 16px;
  margin-bottom: 8px;
  color: #dbe7f3;
  border-radius: 12px;
  background: transparent;
  border: 1px solid transparent;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.menu ul li a i {
  font-size: 18px;
  width: 20px;
  text-align: center;
}

.menu ul li a:hover {
  background: linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0.08));
  color: #ffffff;
  border-color: rgba(255,255,255,0.20);
  box-shadow: 0 6px 16px rgba(0,0,0,0.18);
  transform: translateY(-2px);
}

.menu ul li a.active {
  background: linear-gradient(180deg, rgba(79, 70, 229, 0.8), rgba(124, 58, 237, 0.8));
  color: #ffffff;
  border: 1px solid rgba(255,255,255,0.3);
  box-shadow: 0 8px 24px rgba(79, 70, 229, 0.4);
}

.menu ul li a.disabled {
  opacity: 0.5;
  pointer-events: none;
  color: #64748b;
}

.menu ul li a.disabled .fa-lock {
  font-size: 12px;
  color: #ef4444;
}

/* Provider Info Section */
.provider-info {
  border-top: 1px solid rgba(255,255,255,0.1);
  margin-top: auto;
}

.provider-status-badge {
  display: inline-flex;
  align-items: center;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.provider-status-badge.status-active {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.provider-status-badge.status-pending {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
}

.provider-name {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 500;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Logout Section */
.logout-section {
  border-top: 1px solid rgba(255,255,255,0.1);
}

.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: 1px solid rgba(255,255,255,0.2);
}

.logout-btn:hover {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
  color: white;
}

/* Dark Mode Support */
body.dark-mode .modern-sidebar {
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  border-right-color: rgba(255,255,255,0.05);
}

body.dark-mode .sidebar-header {
  border-bottom-color: rgba(255,255,255,0.05);
}

body.dark-mode .provider-info {
  border-top-color: rgba(255,255,255,0.05);
}

body.dark-mode .logout-section {
  border-top-color: rgba(255,255,255,0.05);
}

/* Mobile Sidebar Toggle */
.modern-sidebar.mobile-hidden {
  transform: translateX(-100%);
}

/* Responsive */
@media (max-width: 768px) {
  .modern-sidebar {
    left: -250px;
    z-index: 1080;
    transition: left 0.3s ease;
  }
  
  .modern-sidebar.mobile-show {
    left: 0;
  }
}

/* Sidebar collapsed state */
.modern-sidebar.collapsed {
  left: -250px;
  transition: left 0.3s ease;
}

/* When sidebar is toggled on desktop */
@media (min-width: 769px) {
  .modern-sidebar.collapsed {
    left: -180px;
  }
  
  .modern-sidebar.collapsed + .main-content {
    margin-left: 70px;
  }
}

@media (max-width: 768px) {
  .modern-sidebar {
    width: 250px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .modern-sidebar:not(.mobile-hidden) {
    transform: translateX(0);
  }

@media (max-width: 576px) {
  .modern-sidebar {
    width: 70px;
  }
  
  .modern-sidebar .brand-text,
  .modern-sidebar .menu ul li a span,
  .modern-sidebar .provider-name,
  .modern-sidebar .logout-btn span {
    display: none;
  }
  
  .modern-sidebar .menu ul li a {
    justify-content: center;
    padding: 14px 8px;
  }
  
  .modern-sidebar .provider-status-badge {
    padding: 6px;
    border-radius: 50%;
  }
  
  .modern-sidebar .provider-status-badge span {
    display: none;
  }
  
  .modern-sidebar .logout-btn {
    padding: 12px 8px;
  }
}
</style>

<!-- SweetAlert2 for Logout -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Provider Logout with SweetAlert
  const logoutBtn = document.getElementById('providerLogoutBtn');
  
  function getSwalTheme() {
    const dark = document.body.classList.contains('dark-mode');
    return {
      background: dark ? '#1e293b' : '#fff',
      color: dark ? '#f8fafc' : '#000',
      confirmButtonColor: '#ef4444',
      cancelButtonColor: dark ? '#94a3b8' : '#6c757d'
    };
  }
  
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const theme = getSwalTheme();
      
      Swal.fire({
        title: 'Confirm Logout',
        text: "Are you sure you want to log out of the Provider Portal?",
        icon: 'question',
        background: theme.background,
        color: theme.color,
        showCancelButton: true,
        confirmButtonColor: theme.confirmButtonColor,
        cancelButtonColor: theme.cancelButtonColor,
        confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Yes, Log Out',
        cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          try { 
            localStorage.clear(); 
            sessionStorage.clear(); 
          } catch(e) {}
          
          Swal.fire({
            title: 'Logging out...',
            icon: 'info',
            background: theme.background,
            color: theme.color,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
          });
          
          setTimeout(() => { 
            window.location.href = '../admin/loginpage.php'; 
          }, 1000);
        }
      });
    });
  }

  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>
