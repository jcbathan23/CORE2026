<?php require '../connect.php'; 
// Access control for admin area: ensure only logged-in admin accounts proceed
require_once __DIR__ . '/auth.php';
if (isset($extraCSS)) echo $extraCSS;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="icon" type="image/png" href="logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles1.css">
  <script src="responsive-utils.js" defer></script>
  <style>
    /* Force dark-mode table visibility across all admin modules */
    body.dark-mode .table,
    body.dark-mode .table td,
    body.dark-mode .table th { color:#e6edf3 !important; border-color: rgba(255,255,255,0.10) !important; }
    body.dark-mode .table > :not(caption) > * > * { background-color:#0b1220 !important; }
    body.dark-mode .table thead,
    body.dark-mode .table thead th,
    body.dark-mode .table thead td,
    body.dark-mode .table-secondary { background-color:#1f2937 !important; color:#e6edf3 !important; border-color: rgba(255,255,255,0.12) !important; }
    body.dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: rgba(255,255,255,0.04) !important; }
    body.dark-mode .table-hover > tbody > tr:hover > * { background-color: rgba(148,163,184,0.12) !important; color:#ffffff !important; }
    /* Links and muted text inside tables */
    body.dark-mode table a { color:#93c5fd !important; }
    body.dark-mode table a:hover { color:#bfdbfe !important; }
    body.dark-mode .table .text-muted { color:#cbd5e1 !important; }

    /* Navbar/Header dark-mode enforcement */
    body.dark-mode .navbar,
    body.dark-mode .navbar.bg-white { /* override Bootstrap bg-white */
      background: linear-gradient(180deg, #2b3f4e 0%, #1f3442 100%) !important;
      border-bottom: 1px solid rgba(255,255,255,0.08) !important;
      color: #e6edf3 !important;
    }
    body.dark-mode .navbar h5,
    body.dark-mode .navbar .datetime,
    body.dark-mode .navbar #toggleSidebar,
    body.dark-mode .navbar .nav-links a,
    body.dark-mode .navbar .nav-links i,
    body.dark-mode .navbar .text-success { color: #e6edf3 !important; }
    body.dark-mode .navbar .text-muted { color:#cbd5e1 !important; }
    body.dark-mode .navbar .btn-outline-secondary { color:#e6edf3 !important; border-color: rgba(255,255,255,0.28) !important; }
    body.dark-mode .navbar .btn-outline-secondary:hover,
    body.dark-mode .navbar .btn-outline-secondary:focus { background: rgba(255,255,255,0.12) !important; color:#fff !important; border-color: rgba(255,255,255,0.38) !important; box-shadow: 0 0 0 .2rem rgba(255,255,255,0.10) !important; }
    body.dark-mode .dropdown-menu { background-color:#1f3442 !important; color:#e6edf3 !important; border-color: rgba(255,255,255,0.10) !important; }
    body.dark-mode .dropdown-item { color:#e6edf3 !important; }
    /* Notifications dropdown items */
    body.dark-mode .dropdown-menu .notif-item a {
      color:#e6edf3 !important;
      background-color: transparent !important; /* override inline #000 text defaults */
      border-radius: 5px;
    }
    body.dark-mode .dropdown-menu .notif-item a:hover {
      background-color: rgba(255,255,255,0.06) !important;
      color:#ffffff !important;
    }
    body.dark-mode .dropdown-menu .notif-unread a {
      background-color: rgba(59,130,246,0.10) !important; /* subtle blue */
    }
    body.dark-mode .dropdown-menu .notif-unread a:hover {
      background-color: rgba(59,130,246,0.16) !important;
    }
    body.dark-mode .dropdown-menu .dropdown-divider { border-top-color: rgba(255,255,255,0.12) !important; }
    body.dark-mode .dropdown-menu small.text-muted { color:#cbd5e1 !important; }
    body.dark-mode #notifCount { background-color:#ef4444 !important; color:#fff !important; }
  </style>
</head>
<body>
<?php include __DIR__ . '/loader.php'; ?>
<script>
  // Global dark mode utilities
  (function() {
    function setIconState() {
      const btn = document.getElementById('darkModeToggle');
      if (!btn) return;
      const icon = btn.querySelector('i');
      if (!icon) return;
      const enabled = document.body.classList.contains('dark-mode');
      
      // Clear existing classes and set new ones
      icon.className = '';
      if (enabled) {
        icon.className = 'fas fa-sun';
        btn.setAttribute('title', 'Switch to Light Mode');
      } else {
        icon.className = 'fas fa-moon';
        btn.setAttribute('title', 'Switch to Dark Mode');
      }
    }

    window.setDarkMode = function(on) {
      if (on) {
        document.body.classList.add('dark-mode');
        document.documentElement.classList.add('dark-mode');
        try { localStorage.setItem('dark-mode','enabled'); } catch(e){}
      } else {
        document.body.classList.remove('dark-mode');
        document.documentElement.classList.remove('dark-mode');
        try { localStorage.setItem('dark-mode','disabled'); } catch(e){}
      }
      
      setIconState();
      
      // Trigger custom event for charts and other components
      window.dispatchEvent(new CustomEvent('darkModeToggle', {
        detail: { isDarkMode: on }
      }));
    };

    // Initialize early from localStorage (to reduce flash)
    try {
      const enabled = localStorage.getItem('dark-mode') === 'enabled';
      if (enabled) {
        document.body.classList.add('dark-mode');
        document.documentElement.classList.add('dark-mode');
      }
    } catch(e) {}

    // Ensure icon reflects state once DOM is ready
    document.addEventListener('DOMContentLoaded', function(){
      setIconState();
      
      // Backup: attach click if navbar script didn't
      const btn = document.getElementById('darkModeToggle');
      if (btn && !btn.__dmBound) {
        btn.addEventListener('click', function(ev){
          ev.preventDefault();
          const currentMode = document.body.classList.contains('dark-mode');
          window.setDarkMode(!currentMode);
        });
        btn.__dmBound = true;
      }
    });

    // Sync across tabs
    window.addEventListener('storage', function(e){
      if (e.key === 'dark-mode') {
        const enabled = e.newValue === 'enabled';
        if (enabled) {
          document.body.classList.add('dark-mode');
          document.documentElement.classList.add('dark-mode');
        } else {
          document.body.classList.remove('dark-mode');
          document.documentElement.classList.remove('dark-mode');
        }
        setIconState();
      }
    });
  })();
  </script>
