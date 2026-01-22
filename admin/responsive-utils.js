/**
 * Responsive Utilities for Fleet Management System
 * Handles mobile-specific functionality and responsive behavior
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize responsive utilities
    initResponsiveUtils();
    
    // Handle window resize events
    window.addEventListener('resize', debounce(handleWindowResize, 250));
    
    // Handle orientation change
    window.addEventListener('orientationchange', function() {
        setTimeout(handleWindowResize, 100);
    });
});

function initResponsiveUtils() {
    // Add responsive classes based on screen size
    updateResponsiveClasses();
    
    // Initialize touch gestures for mobile
    initTouchGestures();
    
    // Initialize responsive tables
    initResponsiveTables();
    
    // Initialize mobile-specific features
    initMobileFeatures();
}

function updateResponsiveClasses() {
    const body = document.body;
    const width = window.innerWidth;
    
    // Remove existing responsive classes
    body.classList.remove('mobile', 'tablet', 'desktop', 'large-desktop');
    
    // Add appropriate class based on screen size
    if (width <= 576) {
        body.classList.add('mobile');
    } else if (width <= 768) {
        body.classList.add('tablet');
    } else if (width <= 1200) {
        body.classList.add('desktop');
    } else {
        body.classList.add('large-desktop');
    }
}

function handleWindowResize() {
    updateResponsiveClasses();
    handleTableResponsiveness();
    handleModalResponsiveness();
    
    // Close mobile sidebar if screen becomes larger
    if (window.innerWidth > 768) {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
}

function initTouchGestures() {
    // Add touch-friendly interactions
    const clickableElements = document.querySelectorAll('.btn, .nav-link, .dropdown-toggle');
    
    clickableElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.classList.add('touch-active');
        });
        
        element.addEventListener('touchend', function() {
            setTimeout(() => {
                this.classList.remove('touch-active');
            }, 150);
        });
    });
}

function initResponsiveTables() {
    const tables = document.querySelectorAll('.modern-table');
    
    tables.forEach(table => {
        makeTableResponsive(table);
    });
}

function makeTableResponsive(table) {
    if (window.innerWidth <= 576) {
        // Convert to stacked layout for very small screens
        convertToStackedTable(table);
    } else {
        // Ensure horizontal scrolling for larger mobile screens
        ensureHorizontalScroll(table);
    }
}

function convertToStackedTable(table) {
    if (table.classList.contains('table-stack')) return;
    
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, index) => {
            if (headers[index]) {
                cell.setAttribute('data-label', headers[index]);
            }
        });
    });
    
    table.classList.add('table-stack');
}

function ensureHorizontalScroll(table) {
    table.classList.remove('table-stack');
    
    const container = table.closest('.table-responsive') || table.closest('.modern-table-container');
    if (container) {
        container.style.overflowX = 'auto';
        container.style.webkitOverflowScrolling = 'touch';
    }
}

function handleTableResponsiveness() {
    const tables = document.querySelectorAll('.modern-table');
    tables.forEach(makeTableResponsive);
}

function handleModalResponsiveness() {
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        const dialog = modal.querySelector('.modal-dialog');
        if (dialog) {
            if (window.innerWidth <= 576) {
                dialog.classList.add('modal-fullscreen-sm-down');
            } else {
                dialog.classList.remove('modal-fullscreen-sm-down');
            }
        }
    });
}

function initMobileFeatures() {
    // Add pull-to-refresh for mobile (if supported)
    if ('ontouchstart' in window) {
        initPullToRefresh();
    }
    
    // Improve form inputs on mobile
    improveMobileFormInputs();
    
    // Add mobile-specific keyboard shortcuts
    addMobileKeyboardShortcuts();
}

function initPullToRefresh() {
    let startY = 0;
    let currentY = 0;
    let pullDistance = 0;
    const threshold = 80;
    
    document.addEventListener('touchstart', function(e) {
        if (window.scrollY === 0) {
            startY = e.touches[0].clientY;
        }
    });
    
    document.addEventListener('touchmove', function(e) {
        if (window.scrollY === 0 && startY) {
            currentY = e.touches[0].clientY;
            pullDistance = currentY - startY;
            
            if (pullDistance > 0 && pullDistance < threshold) {
                // Visual feedback for pull-to-refresh
                document.body.style.transform = `translateY(${pullDistance * 0.5}px)`;
                document.body.style.transition = 'none';
            }
        }
    });
    
    document.addEventListener('touchend', function() {
        if (pullDistance > threshold) {
            // Trigger refresh
            location.reload();
        }
        
        // Reset
        document.body.style.transform = '';
        document.body.style.transition = '';
        startY = 0;
        pullDistance = 0;
    });
}

function improveMobileFormInputs() {
    const inputs = document.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        // Prevent zoom on focus for iOS
        if (input.type === 'text' || input.type === 'email' || input.tagName === 'TEXTAREA') {
            input.addEventListener('focus', function() {
                if (window.innerWidth <= 768) {
                    const viewport = document.querySelector('meta[name=viewport]');
                    if (viewport) {
                        viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
                    }
                }
            });
            
            input.addEventListener('blur', function() {
                if (window.innerWidth <= 768) {
                    const viewport = document.querySelector('meta[name=viewport]');
                    if (viewport) {
                        viewport.setAttribute('content', 'width=device-width, initial-scale=1.0');
                    }
                }
            });
        }
    });
}

function addMobileKeyboardShortcuts() {
    // Add swipe gestures for navigation
    let startX = 0;
    let startY = 0;
    
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', function(e) {
        if (!startX || !startY) return;
        
        const endX = e.changedTouches[0].clientX;
        const endY = e.changedTouches[0].clientY;
        
        const diffX = startX - endX;
        const diffY = startY - endY;
        
        // Only trigger if horizontal swipe is dominant
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX > 0) {
                // Swipe left - could open sidebar
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && !sidebar.classList.contains('show') && window.innerWidth <= 768) {
                    const mobileToggle = document.getElementById('mobileMenuToggle');
                    if (mobileToggle) mobileToggle.click();
                }
            } else {
                // Swipe right - could close sidebar
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('show')) {
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.click();
                }
            }
        }
        
        startX = 0;
        startY = 0;
    });
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS for touch interactions
const style = document.createElement('style');
style.textContent = `
    .touch-active {
        opacity: 0.7;
        transform: scale(0.98);
        transition: all 0.1s ease;
    }
    
    @media (max-width: 576px) {
        .modal-fullscreen-sm-down {
            width: 100vw;
            max-width: none;
            height: 100vh;
            margin: 0;
        }
        
        .modal-fullscreen-sm-down .modal-content {
            height: 100vh;
            border: 0;
            border-radius: 0;
        }
    }
    
    /* Improve touch targets */
    @media (max-width: 768px) {
        .btn, .nav-link, .dropdown-toggle {
            min-height: 44px;
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .table .btn {
            min-height: 36px;
            min-width: 36px;
        }
    }
`;
document.head.appendChild(style);
