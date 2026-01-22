// Provider Navbar JavaScript - Universal functionality for all provider pages
console.log('Provider navbar script loaded');

document.addEventListener('DOMContentLoaded', function() {
  console.log('Provider navbar DOM ready');
  // DateTime updater for provider
  function updateProviderDateTime() {
    const now = new Date();
    const options = { 
      timeZone: 'Asia/Manila', 
      year: 'numeric', 
      month: '2-digit', 
      day: '2-digit', 
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit', 
      hour12: false 
    };
    
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
    
    const dateTimeEl = document.getElementById('providerDateTime');
    if (dateTimeEl) {
      dateTimeEl.textContent = formattedDateTime;
    }
  }
  
  updateProviderDateTime();
  setInterval(updateProviderDateTime, 1000);

  // Dark mode toggle for provider
  const darkModeBtn = document.getElementById('providerDarkModeToggle');
  
  // Initialize dark mode from localStorage
  if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    if (darkModeBtn) {
      darkModeBtn.querySelector('i').className = 'fas fa-sun';
    }
  }
  
  if (darkModeBtn) {
    darkModeBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const isDarkMode = document.body.classList.toggle('dark-mode');
      
      // Update icon
      const icon = this.querySelector('i');
      icon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
      
      // Save to localStorage
      localStorage.setItem('darkMode', isDarkMode);
      
      // Trigger custom event for other components
      window.dispatchEvent(new CustomEvent('darkModeToggle', {
        detail: { isDarkMode: isDarkMode }
      }));
      
      // Update tooltip
      this.setAttribute('data-bs-original-title', isDarkMode ? 'Switch to Light Mode' : 'Toggle Dark Mode');
    });
  }

  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Initialize Bootstrap dropdowns with enhanced error handling
  function initializeDropdowns() {
    try {
      var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
      var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
      });
      console.log('Provider navbar dropdowns initialized:', dropdownList.length);
      return dropdownList.length > 0;
    } catch (error) {
      console.error('Error initializing Bootstrap dropdowns:', error);
      return false;
    }
  }
  
  // Try Bootstrap initialization first
  const bootstrapSuccess = initializeDropdowns();
  
  // Enhanced manual fallback for dropdown functionality
  const providerDropdown = document.getElementById('providerUserDropdown');
  if (providerDropdown) {
    console.log('Provider dropdown button found');
    
    // Remove any existing event listeners to prevent duplicates
    providerDropdown.removeEventListener('click', handleDropdownClick);
    
    function handleDropdownClick(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const dropdownMenu = providerDropdown.nextElementSibling;
      if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
        const isCurrentlyOpen = dropdownMenu.classList.contains('show');
        
        // Close all other dropdowns first
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
          menu.classList.remove('show');
          const button = menu.previousElementSibling;
          if (button) button.setAttribute('aria-expanded', 'false');
        });
        
        // Toggle current dropdown
        if (!isCurrentlyOpen) {
          dropdownMenu.classList.add('show');
          providerDropdown.setAttribute('aria-expanded', 'true');
          console.log('Dropdown opened');
        } else {
          dropdownMenu.classList.remove('show');
          providerDropdown.setAttribute('aria-expanded', 'false');
          console.log('Dropdown closed');
        }
      }
    }
    
    // Add click event listener
    providerDropdown.addEventListener('click', handleDropdownClick);
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!providerDropdown.contains(e.target)) {
        const dropdownMenu = providerDropdown.nextElementSibling;
        if (dropdownMenu && dropdownMenu.classList.contains('show')) {
          dropdownMenu.classList.remove('show');
          providerDropdown.setAttribute('aria-expanded', 'false');
          console.log('Dropdown closed by outside click');
        }
      }
    });
    
    // Handle escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const dropdownMenu = providerDropdown.nextElementSibling;
        if (dropdownMenu && dropdownMenu.classList.contains('show')) {
          dropdownMenu.classList.remove('show');
          providerDropdown.setAttribute('aria-expanded', 'false');
          console.log('Dropdown closed by escape key');
        }
      }
    });
  } else {
    console.warn('Provider dropdown button not found');
  }

  // Provider logout from navbar
  const navLogoutBtn = document.getElementById('providerNavLogout');
  if (navLogoutBtn) {
    navLogoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      // Trigger the same logout as sidebar
      const sidebarLogoutBtn = document.getElementById('providerLogoutBtn');
      if (sidebarLogoutBtn) {
        sidebarLogoutBtn.click();
      } else {
        // Fallback logout if sidebar logout not available
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: 'Confirm Logout',
            text: "Are you sure you want to log out of the Provider Portal?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Yes, Log Out',
            cancelButtonText: '<i class="fas fa-times me-1"></i> Cancel',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              try { 
                localStorage.clear(); 
                sessionStorage.clear(); 
              } catch(e) {}
              window.location.href = '../admin/loginpage.php';
            }
          });
        } else {
          // Simple confirmation if SweetAlert not available
          if (confirm('Are you sure you want to log out?')) {
            try { 
              localStorage.clear(); 
              sessionStorage.clear(); 
            } catch(e) {}
            window.location.href = '../admin/loginpage.php';
          }
        }
      }
    });
  }

  // Sidebar toggle for all screen sizes
  const toggleBtn = document.getElementById('toggleProviderSidebar');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      const sidebar = document.querySelector('.modern-sidebar');
      const mainContent = document.querySelector('.main-content');
      
      if (sidebar) {
        // For mobile
        if (window.innerWidth <= 768) {
          sidebar.classList.toggle('mobile-show');
        } else {
          // For desktop
          sidebar.classList.toggle('collapsed');
          if (mainContent) {
            if (sidebar.classList.contains('collapsed')) {
              mainContent.style.marginLeft = '70px';
            } else {
              mainContent.style.marginLeft = '250px';
            }
          }
        }
      }
    });
  }
  
  // Handle window resize
  window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.modern-sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (window.innerWidth > 768) {
      // Reset mobile classes on desktop
      if (sidebar) {
        sidebar.classList.remove('mobile-show');
        if (!sidebar.classList.contains('collapsed') && mainContent) {
          mainContent.style.marginLeft = '250px';
        }
      }
    } else {
      // Reset desktop classes on mobile
      if (sidebar) {
        sidebar.classList.remove('collapsed');
        if (mainContent) {
          mainContent.style.marginLeft = '0';
        }
      }
    }
  });
});
