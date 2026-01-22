<?php
require '../connect.php';
require 'functions.php';
require_once __DIR__ . '/auth.php';
?>
<!--navbar-->
<?php
// Load current admin profile for navbar display
$sessionEmail = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$displayName = 'Admin';
$displayEmail = $sessionEmail ?: 'admin@core2.com';
$avatarWeb = null;
if ($sessionEmail) {
  $tbl = mysqli_query($conn, "SHOW TABLES LIKE 'admin_profiles'");
  if ($tbl && mysqli_num_rows($tbl) > 0) {
    $stmt = $conn->prepare('SELECT name, avatar FROM admin_profiles WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $sessionEmail);
    if ($stmt->execute()) {
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        if (!empty($row['name'])) { $displayName = $row['name']; }
        if (!empty($row['avatar'])) { $avatarWeb = 'uploads/' . $row['avatar']; }
      }
    }
    $stmt->close();
  }
}
// Verify file exists for avatar
$avatarAbs = $avatarWeb ? (__DIR__ . '/' . $avatarWeb) : null;
if ($avatarAbs && !file_exists($avatarAbs)) { $avatarWeb = null; }
// Fetch latest 5 notifications with timestamp difference
$notifQuery = mysqli_query($conn, "
    SELECT *, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS minutes_ago 
    FROM notifications 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Count unread notifications
$notifCountQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0");
$notifCount = mysqli_fetch_assoc($notifCountQuery)['total'];
?>

<div class="navbar modern-navbar d-flex align-items-center">
  <!-- Left Side -->
  <div class="navbar-left d-flex align-items-center">
    <!-- Desktop Toggle Button -->
    <button id="toggleSidebar" class="modern-toggle-btn me-4">
      <i class="fas fa-bars"></i>
    </button>
    
    <!-- Mobile Hamburger Menu -->
    <button id="mobileMenuToggle" class="mobile-menu-toggle me-3" style="display: none;">
      <i class="fas fa-bars"></i>
    </button>
    
    <div class="navbar-brand d-flex align-items-center me-4">
      <img src="logo.png" alt="SLATE Logo" class="navbar-logo me-2" style="height: 32px; width: auto;">
      <div class="brand-text d-none d-lg-block">
        <span class="brand-title">SLATE | CORE II</span>
      </div>
    </div>
  </div>

  <!-- Right Side -->
  <div class="navbar-right d-flex align-items-center">
    <!-- DateTime Display -->
    <div class="datetime-display me-4 d-none d-md-flex">
      <div id="fullDateTime" class="full-datetime">Sat, Sep 27, 2025, 09:14:47 PM</div>
    </div>

    <!-- Notifications Dropdown -->
    <div class="dropdown me-3">
      <button class="modern-icon-btn position-relative" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if($notifCount > 0): ?>
          <span id="notifCount" class="notification-badge"><?= $notifCount ?></span>
        <?php endif; ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg notif-dropdown"
          aria-labelledby="notificationsDropdown" 
          data-bs-popper="static" 
          style="min-width: 320px; max-height: 300px; overflow-y: auto;">
        <li class="d-flex justify-content-between align-items-center mb-2 px-2">
          <strong>Notifications</strong>
          <button id="clearNotifs" class="btn btn-sm btn-link text-danger p-0">Clear All</button>
        </li>
        <li><hr class="dropdown-divider"></li>

        <?php if(mysqli_num_rows($notifQuery) > 0): ?>
          <?php while($notif = mysqli_fetch_assoc($notifQuery)): ?>
            <?php
              $minutes = $notif['minutes_ago'];
              if($minutes < 1) $timeText = 'Just now';
              elseif($minutes < 60) $timeText = $minutes.'m ago';
              elseif($minutes < 1440) $timeText = floor($minutes/60).'h ago';
              else $timeText = floor($minutes/1440).'d ago';

              $unreadClass = $notif['is_read'] == 0 ? 'notif-unread' : '';
            ?>
            <li class="notif-item mb-1 <?= $unreadClass ?>">
              <a href="<?= $notif['link'] ?? '#' ?>" 
                 class="d-flex justify-content-between align-items-center text-decoration-none">
                <span>
                  <?= htmlspecialchars($notif['message']) ?>
                  <?php if($notif['is_read'] == 0): ?>
                    <span class="badge bg-success ms-1">New</span>
                  <?php endif; ?>
                </span>
                <small class="text-muted ms-2"><?= $timeText ?></small>
              </a>
            </li>
          <?php endwhile; ?>
        <?php else: ?>
          <li id="noNotif" class="text-center text-muted py-2">No notifications</li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="modern-icon-btn me-3" id="darkModeToggle" data-bs-toggle="tooltip" 
       data-bs-placement="bottom" title="Toggle Dark Mode">
      <i class="fas fa-moon"></i>
    </button>

    <!-- User Profile Dropdown -->
    <div class="dropdown">
      <button class="modern-profile-btn" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="profile-avatar">
          <?php if ($avatarWeb): ?>
            <img src="<?= htmlspecialchars($avatarWeb) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
          <?php else: ?>
            <i class="fas fa-user"></i>
          <?php endif; ?>
        </div>
        <div class="profile-info d-none d-lg-block"><span class="profile-role"><?= htmlspecialchars($displayName) ?></span></div>
        <i class="fas fa-chevron-down profile-arrow"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end modern-dropdown-menu" aria-labelledby="userDropdown">
        <li class="dropdown-header">
          <div class="user-info">
            <div class="user-avatar">
              <?php if ($avatarWeb): ?>
                <img src="<?= htmlspecialchars($avatarWeb) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
              <?php else: ?>
                <i class="fas fa-user"></i>
              <?php endif; ?>
            </div>
            <div class="user-details">
              <span class="user-name"><?= htmlspecialchars($displayName) ?></span>
              <span class="user-email"><?= htmlspecialchars($displayEmail) ?></span>
            </div>
          </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item modern-dropdown-item" href="profile.php">
          <i class="fas fa-user-circle me-2"></i>My Profile
        </a></li>
        <!-- SWEETALERT LOGOUT -->
        <li><a class="dropdown-item modern-dropdown-item" href="#" id="logoutLink">
          <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a></li>
      </ul>
    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

  // Initialize dropdowns
  document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => new bootstrap.Dropdown(el));

  // Ensure ALL Bootstrap modals are vertically centered across admin
  function centerAllModals(root = document) {
    root.querySelectorAll('.modal .modal-dialog').forEach(el => {
      if (!el.classList.contains('modal-dialog-centered')) {
        el.classList.add('modal-dialog-centered');
      }
    });
  }
  // Initial pass for already-present modals
  centerAllModals();
  // Observe DOM changes to catch modals injected later
  const modalObserver = new MutationObserver((mutations) => {
    for (const m of mutations) {
      if (m.type === 'childList') {
        m.addedNodes.forEach(node => {
          if (node.nodeType === 1) { // ELEMENT_NODE
            if (node.matches && node.matches('.modal, .modal *')) {
              centerAllModals(node.nodeType === 1 ? node : undefined);
            } else if (node.querySelector) {
              const maybeModal = node.querySelector('.modal');
              if (maybeModal) centerAllModals(maybeModal);
            }
          }
        });
      }
    }
  });
  modalObserver.observe(document.body, { childList: true, subtree: true });

  // SweetAlert Logout with Dark Mode Support
  const logoutLink = document.getElementById('logoutLink');
  function getSwalTheme() {
    const dark = document.body.classList.contains('dark-mode');
    return {
      background: dark ? '#1e293b' : '#fff',
      color: dark ? '#f8fafc' : '#000',
      confirmButtonColor: '#4F6EF7',
      cancelButtonColor: dark ? '#94a3b8' : '#6c757d'
    };
  }
  if (logoutLink) {
    logoutLink.addEventListener('click', function (e) {
      e.preventDefault();
      const theme = getSwalTheme();
      Swal.fire({
        title: 'Confirm Logout',
        text: "Are you sure you want to log out of CORE II System?",
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
          try { localStorage.clear(); sessionStorage.clear(); } catch(e) {}
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
          setTimeout(() => { window.location.href = 'logout.php'; }, 1000);
        }
      });
    });
  }

  // Clear notifications
  const clearBtn = document.getElementById('clearNotifs');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      fetch('clear_notifications.php')
        .then(res => res.text())
        .then(() => {
          document.querySelectorAll('.notif-item').forEach(item => item.remove());
          document.getElementById('noNotif').classList.remove('d-none');
          const notifCount = document.getElementById('notifCount');
          if (notifCount) notifCount.style.display = 'none';
        });
    });
  }

  // DateTime updater
  function updateDateTime() {
    const now = new Date();
    const options = { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const formatter = new Intl.DateTimeFormat('en-CA', options);
    const parts = formatter.formatToParts(now);
    const year = parts.find(p => p.type === 'year').value;
    const month = parseInt(parts.find(p => p.type === 'month').value);
    const day = parts.find(p => p.type === 'day').value;
    const hour = parseInt(parts.find(p => p.type === 'hour').value);
    const minute = parts.find(p => p.type === 'minute').value;
    const second = parts.find(p => p.type === 'second').value;
    const philippinesDate = new Date(year, month - 1, day, hour, minute, second);
    const weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const weekday = weekdays[philippinesDate.getDay()];
    const monthName = months[philippinesDate.getMonth()];
    let displayHour = hour % 12 || 12;
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const formattedHour = displayHour.toString().padStart(2, '0');
    const formattedDateTime = `${weekday}, ${monthName} ${day}, ${year}, ${formattedHour}:${minute}:${second} ${ampm}`;
    document.getElementById('fullDateTime').textContent = formattedDateTime;
  }
  updateDateTime();
  setInterval(updateDateTime, 1000);

  // Dark mode toggle - use global function from header.php
  const dmBtn = document.getElementById('darkModeToggle');
  if (dmBtn && !dmBtn.__dmBound) {
    dmBtn.addEventListener('click', function(e){
      e.preventDefault();
      const currentMode = document.body.classList.contains('dark-mode');
      window.setDarkMode(!currentMode);
      
      // Trigger custom event for other components to listen
      window.dispatchEvent(new CustomEvent('darkModeToggle', {
        detail: { isDarkMode: !currentMode }
      }));
      
      // Adjust dropdown + SweetAlert color live
      document.querySelectorAll('.swal2-popup').forEach(el => {
        el.style.background = !currentMode ? '#1e293b' : '#fff';
        el.style.color = !currentMode ? '#f8fafc' : '#000';
      });
    });
    dmBtn.__dmBound = true;
  }

  // Mobile menu functionality
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const sidebar = document.querySelector('.sidebar');
  let sidebarOverlay = document.querySelector('.sidebar-overlay');
  
  // Create overlay if it doesn't exist
  if (!sidebarOverlay) {
    sidebarOverlay = document.createElement('div');
    sidebarOverlay.className = 'sidebar-overlay';
    document.body.appendChild(sidebarOverlay);
  }
  
  if (mobileMenuToggle && sidebar) {
    mobileMenuToggle.addEventListener('click', function(e) {
      e.preventDefault();
      toggleMobileSidebar();
    });
    
    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
      closeMobileSidebar();
    });
    
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && sidebar.classList.contains('show')) {
        closeMobileSidebar();
      }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768 && sidebar.classList.contains('show')) {
        closeMobileSidebar();
      }
    });
  }
  
  function toggleMobileSidebar() {
    sidebar.classList.toggle('show');
    sidebarOverlay.classList.toggle('show');
    document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
  }
  
  function closeMobileSidebar() {
    sidebar.classList.remove('show');
    sidebarOverlay.classList.remove('show');
    document.body.style.overflow = '';
  }
});
</script>

<style>
  .navbar h5 { letter-spacing: .3px; }
  .notif-item a { padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: #000; transition: background 0.2s; border-radius: 5px;}
  .notif-item a:hover { background-color: #f8f9fa; }
  .notif-unread a { background-color: #e6f7ff; }
  .notif-unread a:hover { background-color: #cceeff; }
  .notif-item small { font-size: 0.75rem; }
  .notif-item .badge { font-size: 0.65rem; }
  #notifCount { font-size: 0.7rem; padding: 2px 5px; }
  .dropdown-menu { z-index: 1055 !important; }
  /* DARK MODE STYLES */
  body.dark-mode {
    background-color: #0f172a;
    color: #f8fafc;
  }
  body.dark-mode .notif-dropdown {
    background-color: #1e293b !important;
    color: #f8fafc;
  }
  body.dark-mode .notif-item a { color: #f8fafc; }
  body.dark-mode .notif-item a:hover { background-color: #334155; }
  body.dark-mode .notif-unread a { background-color: #1e3a8a; }
  body.dark-mode .notif-unread a:hover { background-color: #1e40af; }
</style>
