document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar");
    const toggleButton = document.getElementById("toggleSidebar");
    const dropdownLinks = document.querySelectorAll(".nav-item > .nav-link");
    const tooltipInstances = new Map(); // Track each tooltip instance

    // Ensure sidebar is visible and not collapsed by default
    if (sidebar) {
        sidebar.classList.remove("collapsed");
        sidebar.style.display = "flex";
    }

    // Sidebar toggle button
    if (toggleButton) {
        toggleButton.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");

            if (sidebar.classList.contains("collapsed")) {
                enableTooltips();
                hideDropdownIcons();
            } else {
                disableTooltips();
                showDropdownIcons();
            }
        });
    }

    // Dropdown logic (only one submenu open at a time)
    dropdownLinks.forEach(link => {
        const submenu = link.nextElementSibling;

        if (submenu && submenu.classList.contains("submenu")) {
            link.addEventListener("click", function (e) {
                e.preventDefault();

                // Close other open submenus
                document.querySelectorAll(".submenu.show").forEach(openSubmenu => {
                    if (openSubmenu !== submenu) {
                        openSubmenu.classList.remove("show");

                        // Reset icon of closed submenu
                        const openIcon = openSubmenu.previousElementSibling.querySelector(".dropdown-icon");
                        if (openIcon) {
                            openIcon.classList.remove("rotate");
                        }
                    }
                });

                // Toggle current submenu
                const isOpen = submenu.classList.contains("show");
                submenu.classList.toggle("show");

                // Change dropdown icon
                const icon = this.querySelector(".dropdown-icon");
                if (icon) {
                    icon.classList.toggle("rotate", !isOpen);
                }

                // Tooltip logic (kapag collapsed)
                if (sidebar.classList.contains("collapsed")) {
                    const instance = bootstrap.Tooltip.getInstance(link);
                    if (!isOpen && instance) {
                        instance.hide();
                        instance.dispose();
                        tooltipInstances.delete(link);
                    } else if (isOpen && !tooltipInstances.has(link)) {
                        const newInstance = new bootstrap.Tooltip(link);
                        tooltipInstances.set(link, newInstance);
                    }
                }
            });
        }
    });

    // Tooltip setup
    function enableTooltips() {
        const elements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        elements.forEach(el => {
            if (!tooltipInstances.has(el)) {
                const instance = new bootstrap.Tooltip(el);
                tooltipInstances.set(el, instance);
            }
        });
    }

    function disableTooltips() {
        tooltipInstances.forEach((instance, el) => {
            instance.dispose();
        });
        tooltipInstances.clear();
    }

    function hideDropdownIcons() {
        document.querySelectorAll(".dropdown-icon").forEach(i => i.style.display = "none");
    }

    function showDropdownIcons() {
        document.querySelectorAll(".dropdown-icon").forEach(i => i.style.display = "inline-block");
    }

    // Initialize tooltips if sidebar is already collapsed
    if (sidebar.classList.contains("collapsed")) {
        enableTooltips();
        hideDropdownIcons();
    }

    // Auto-fade alert messages
    const alerts = document.querySelectorAll('.auto-fade');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.classList.remove('show'); // triggers Bootstrap fade out
            setTimeout(function () {
                alert.remove();
            }, 500); // wait for fade-out to complete
        }, 3000); // wait 3 seconds before fading
    });
});

// ================= DATE & TIME =================
function updateDateTime() {
  const now = new Date();

  const options = {
    weekday: 'short',  // e.g. Mon
    year: 'numeric',
    month: 'short',    // e.g. Sep
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  };

  document.getElementById("datetime").textContent =
    now.toLocaleDateString("en-US", options);
}

// update every second
setInterval(updateDateTime, 1000);
updateDateTime();

// ================= DARK MODE =================
const darkToggle = document.getElementById("darkModeToggle");
const icon = darkToggle.querySelector("i");

// check saved mode in localStorage
if (localStorage.getItem("dark-mode") === "enabled") {
  document.body.classList.add("dark-mode");
  icon.classList.replace("fa-moon", "fa-sun");
}

darkToggle.addEventListener("click", (e) => {
  e.preventDefault();
  document.body.classList.toggle("dark-mode");

  if (document.body.classList.contains("dark-mode")) {
    icon.classList.replace("fa-moon", "fa-sun");
    localStorage.setItem("dark-mode", "enabled");
  } else {
    icon.classList.replace("fa-sun", "fa-moon");
    localStorage.setItem("dark-mode", "disabled");
  }
});

// ================= LEAFLET MAP =================
document.addEventListener("DOMContentLoaded", () => {
  let map = L.map('pointMap').setView([13.5, 122], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  let marker;

  map.on("click", function(e) {
    const { lat, lng } = e.latlng;
    if (marker) {
      map.removeLayer(marker);
    }
    marker = L.marker([lat, lng]).addTo(map);
    document.getElementById("latitude").value = lat.toFixed(6);
    document.getElementById("longitude").value = lng.toFixed(6);
  });

  // Reset map when modal opens
  document.getElementById('addPointModal').addEventListener('shown.bs.modal', () => {
    map.invalidateSize();
    map.setView([13.5, 122], 6); // re-center to Philippines
  });
});
