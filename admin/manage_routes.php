<?php
include('header.php');
include('sidebar.php');
include('navbar.php');
include('../connect.php');
?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
  }
  /* Mode Badges */
  .mode-badge{display:inline-flex;align-items:center;gap:6px;padding:0.35rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:600;letter-spacing:0.3px}
  .mode-badge i{font-size:.9rem}
  .mode-land{background:linear-gradient(135deg,#198754,#20c997);color:#fff}
  .mode-air{background:linear-gradient(135deg,#0d6efd,#6ea8fe);color:#fff}
  .mode-sea{background:linear-gradient(135deg,#0aa2c0,#20c997);color:#fff}

  /* Custom marker styles */
  .custom-marker {
    background: transparent !important;
    border: none !important;
  }
  
  .route-marker {
    background: transparent !important;
    border: none !important;
  }
  
  /* Route animation */
  .booking-route {
    animation: dashAnimation 20s linear infinite;
  }
  
  @keyframes dashAnimation {
    to {
      stroke-dashoffset: -100;
    }
  }
  
  /* Legend styles */
  .legend {
    font-family: Arial, sans-serif !important;
  }
  
  /* Popup enhancements */
  .leaflet-popup-content-wrapper {
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
  }
  
  .leaflet-popup-content {
    margin: 0 !important;
  }
</style>

<div class="content p-4">
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Bookings & Routes Management</h3>
            <p>View and manage shipping bookings and their routes</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="refreshBookings()">
                <i class="fas fa-sync-alt me-2"></i>Refresh Bookings
            </button>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Filter Bookings</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-control" onchange="filterBookings()">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Carrier Type</label>
                            <select id="carrierFilter" class="form-control" onchange="filterBookings()">
                                <option value="all">All Types</option>
                                <option value="land">Land</option>
                                <option value="air">Air</option>
                                <option value="sea">Sea</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select id="dateFilter" class="form-control" onchange="filterBookings()">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" id="searchFilter" class="form-control" placeholder="Search customer, reference..." onkeyup="filterBookings()">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bookings Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-file-alt me-2"></i>Reference</th>
                        <th><i class="fas fa-user me-2"></i>Customer</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Origin</th>
                        <th><i class="fas fa-flag-checkered me-2"></i>Destination</th>
                        <th><i class="fas fa-truck me-2"></i>Type</th>
                        <th><i class="fas fa-building me-2"></i>Provider</th>
                        <th><i class="fas fa-dollar-sign me-2"></i>Cost</th>
                        <th><i class="fas fa-clock me-2"></i>ETA</th>
                        <th><i class="fas fa-info-circle me-2"></i>Status</th>
                        <th><i class="fas fa-cog me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableBody">
                    <tr>
                        <td colspan="11" class="text-center">
                            <div class="text-center py-4">
                                <i class="fas fa-cloud-download-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Click 'Refresh Bookings' to load bookings from database</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Send this booking to Rate & Tariff now?</p>
                <input type="hidden" id="approve_booking_id" />
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" onclick="approveBookingConfirm()">OK</button>
            </div>
        </div>
    </div>
</div>

    <!-- Map Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Routes Map</h5>
                </div>
                <div class="card-body p-0">
                    <div id="bookingsMap" style="height: 500px; border-radius: 8px;"></div>
                </div>
            </div>
        </div>
    </div>
                        </div>

<!-- View Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Booking ID:</strong> <span id="booking_id"></span></p>
                        <p><strong>Reference:</strong> <span id="booking_reference"></span></p>
                        <p><strong>Customer:</strong> <span id="customer_name"></span></p>
                        <p><strong>Email:</strong> <span id="customer_email"></span></p>
                        <p><strong>Phone:</strong> <span id="customer_phone"></span></p>
                        <p><strong>Origin:</strong> <span id="origin_address"></span></p>
                        <p><strong>Destination:</strong> <span id="destination_address"></span></p>
                        <p><strong>Carrier Type:</strong> <span id="carrier_type"></span></p>
                        <p><strong>Cargo Type:</strong> <span id="cargo_type"></span></p>
                        <p><strong>Weight:</strong> <span id="weight"></span></p>
                        <p><strong>Cost:</strong> <span id="estimated_cost"></span></p>
                        <p><strong>Status:</strong> <span id="booking_status"></span></p>
                        <p><strong>Provider:</strong> <span id="provider_name"></span></p>
                    </div>
                    <div class="col-md-8">
                        <div class="row g-2">
                            <div class="col-12 col-lg-8">
                                <div id="bookingRouteMap" style="height:500px; border:1px solid #ccc;"></div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div id="bookingRouteInstructions" style="height:500px; border:1px solid #ccc; overflow:auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="routeAiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="routeAiTitle">Route AI Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="routeAiBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_booking_id" />
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Reference</label>
                        <input type="text" id="edit_booking_reference" class="form-control" disabled />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select id="edit_status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text" id="edit_customer_name" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Carrier Type</label>
                        <select id="edit_carrier_type" class="form-control">
                            <option value="land">Land</option>
                            <option value="air">Air</option>
                            <option value="sea">Sea</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Provider</label>
                        <select id="edit_provider_id" class="form-control">
                            <option value="">Unassigned</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Customer Email</label>
                        <input type="email" id="edit_customer_email" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Phone</label>
                        <input type="text" id="edit_customer_phone" class="form-control" />
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Origin Address</label>
                        <input type="text" id="edit_origin_address" class="form-control" />
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Destination Address</label>
                        <input type="text" id="edit_destination_address" class="form-control" />
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" id="edit_weight" class="form-control" step="0.01" min="0" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cargo Type</label>
                        <input type="text" id="edit_cargo_type" class="form-control" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estimated Cost</label>
                        <input type="number" id="edit_estimated_cost" class="form-control" step="0.01" min="0" />
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Origin Lat</label>
                        <input type="number" id="edit_origin_lat" class="form-control" step="0.000001" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Origin Lng</label>
                        <input type="number" id="edit_origin_lng" class="form-control" step="0.000001" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Destination Lat</label>
                        <input type="number" id="edit_destination_lat" class="form-control" step="0.000001" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Destination Lng</label>
                        <input type="number" id="edit_destination_lng" class="form-control" step="0.000001" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveEditedBooking()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to update this booking status?</p>
                <input type="hidden" id="update_booking_id">
                <input type="hidden" id="update_status_value">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmStatusUpdate()">Update Status</button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Leaflet Routing Machine -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.min.js"></script>

<script>
let bookingsMap = null;
let bookingRoutesLayer = null;
let currentBookingData = [];
let bookingModalMap = null;
let bookingModalControls = [];
let currentModalBooking = null;
let bookingsMapRenderToken = 0;

// Approve flow helpers
function approveBookingStart(bookingId) {
    const input = document.getElementById('approve_booking_id');
    if (input) input.value = bookingId;
    const modal = new bootstrap.Modal(document.getElementById('approveConfirmModal'));
    modal.show();
}

function approveBookingConfirm() {
    const id = document.getElementById('approve_booking_id').value;
    const mEl = document.getElementById('approveConfirmModal');
    try { bootstrap.Modal.getInstance(mEl).hide(); } catch (e) {}
    approveBookingToRate(id);
}

async function approveBookingToRate(bookingId) {
    try {
        // Try to find booking from current cache first
        let booking = (currentBookingData || []).find(b => String(b.booking_id) === String(bookingId));
        if (!booking) {
            // Fetch minimal booking details if not cached
            const r = await fetch(`../api/booking_api.php/bookings/${bookingId}`);
            if (!r.ok) throw new Error('Failed to load booking');
            booking = await r.json();
        }

        const routeId = parseInt(booking.route_id || '0', 10);
        const providerId = parseInt(booking.provider_id || '0', 10);
        const carrierType = (booking.carrier_type || '').toString();
        const totalRate = parseFloat(booking.estimated_cost || '0');

        if (!routeId || routeId <= 0) {
            // Try to auto-create a route if provider and coords exist
            try {
                const cr = await fetch(`../api/booking_api.php/bookings/${bookingId}/create_route`, { method: 'POST' });
                const cj = await cr.json().catch(() => null);
                if (cr.ok && cj && cj.success && cj.route_id) {
                    booking.route_id = cj.route_id;
                } else {
                    const msg = (cj && (cj.message || cj.error)) ? (cj.message || cj.error) : 'Assign a provider and route before approving.';
                    alert(msg);
                    return;
                }
            } catch (e) {
                alert('Unable to create route automatically. Assign a provider and route before approving.');
                return;
            }
        }
        if (!providerId || providerId <= 0) {
            alert('Please assign a Provider to this booking before approving.');
            return;
        }
        if (!Number.isFinite(totalRate) || totalRate <= 0) {
            alert('Estimated cost is missing. Please set an estimated cost before approving.');
            return;
        }

        const payload = {
            route_id: routeId,
            provider_id: providerId,
            carrier_type: carrierType,
            base_rate: 0,
            tariff_amount: 0,
            total_rate: totalRate,
            formula: `Sent from booking ${booking.booking_reference || ''}`
        };

        const resp = await fetch('../api/rate_tariff_api.php/save_rate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await resp.json().catch(() => null);
        if (!resp.ok || !data || data.success !== true) {
            const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Failed to approve and send to Rate & Tariff';
            throw new Error(msg);
        }

        window.location.href = 'rate_tariff_management.php';
    } catch (e) {
        console.error('approveBookingToRate error:', e);
        alert('Error: ' + (e && e.message ? e.message : 'Unable to approve booking'));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initializeBookingsMap();
    // Don't auto-load bookings - wait for user to click Refresh Bookings

    const viewModalEl = document.getElementById('viewBookingModal');
    if (viewModalEl) {
        viewModalEl.addEventListener('shown.bs.modal', function() {
            if (currentModalBooking) {
                initializeBookingRouteMap(currentModalBooking);
                if (bookingModalMap) {
                    try { bookingModalMap.invalidateSize(true); } catch (e) {}
                }
            }
        });

        viewModalEl.addEventListener('hidden.bs.modal', function() {
            destroyBookingRouteMap();
            currentModalBooking = null;
        });
    }
});

function destroyBookingRouteMap() {
    const mapContainer = document.getElementById('bookingRouteMap');
    const instructionsContainer = document.getElementById('bookingRouteInstructions');

    if (Array.isArray(bookingModalControls) && bookingModalControls.length) {
        bookingModalControls.forEach((c) => {
            try { c.remove(); } catch (e) {}
        });
    }
    bookingModalControls = [];

    if (bookingModalMap) {
        try { bookingModalMap.off(); } catch (e) {}
        try { bookingModalMap.remove(); } catch (e) {}
        bookingModalMap = null;
    }

    if (mapContainer) {
        mapContainer.innerHTML = '';
        // Avoid "Map container is already initialized" when re-opening the modal
        try {
            if (mapContainer._leaflet_id) {
                delete mapContainer._leaflet_id;
            }
        } catch (e) {
            try { mapContainer._leaflet_id = null; } catch (e2) {}
        }
    }

    if (instructionsContainer) {
        instructionsContainer.innerHTML = '';
    }
}

function initializeBookingsMap() {
    bookingsMap = L.map('bookingsMap').setView([13.5, 122], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(bookingsMap);

    bookingRoutesLayer = L.featureGroup().addTo(bookingsMap);

    // Create custom icons
    const portIcon = L.divIcon({
        html: '<div style="background-color: #0066cc; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-ship" style="color: white; font-size: 10px;"></i></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, -10],
        className: 'custom-marker'
    });

    const warehouseIcon = L.divIcon({
        html: '<div style="background-color: #ff6600; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-warehouse" style="color: white; font-size: 10px;"></i></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, -10],
        className: 'custom-marker'
    });

    const airportIcon = L.divIcon({
        html: '<div style="background-color: #00cc66; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-plane" style="color: white; font-size: 10px;"></i></div>',
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, -10],
        className: 'custom-marker'
    });

    // Add network points with custom icons
    <?php
    // Show ports as ship icons
    $portsRes = $conn->query("SELECT point_name, city, latitude, longitude, status FROM network_points WHERE point_type='Port' AND country='Philippines' AND latitude IS NOT NULL AND longitude IS NOT NULL");
    if ($portsRes) {
      while($p = $portsRes->fetch_assoc()):
        $plat = floatval($p['latitude']);
        $plng = floatval($p['longitude']);
        $pname = addslashes($p['point_name']);
        $pcity = addslashes($p['city']);
        $pstatus = addslashes($p['status']);
        $pcolor = ($pstatus === 'Active') ? '#0066cc' : '#999999';
    ?>
    L.marker([<?= $plat ?>, <?= $plng ?>], {icon: portIcon})
        .addTo(bookingsMap)
        .bindPopup(`
            <div style="padding: 8px;">
                <div style="font-weight: bold; color: #0066cc; margin-bottom: 4px;">
                    <i class="fas fa-ship"></i> PORT
                </div>
                <div><strong><?= $pname ?></strong></div>
                <div><?= $pcity ?></div>
                <div style="font-size: 12px; color: #666;">Status: <?= $pstatus ?></div>
            </div>
        `);
    <?php
      endwhile;
    }

    // Show warehouses as warehouse icons
    $whRes = $conn->query("SELECT point_name, city, latitude, longitude, status FROM network_points WHERE point_type='Warehouse' AND country='Philippines' AND latitude IS NOT NULL AND longitude IS NOT NULL");
    if ($whRes) {
      while($w = $whRes->fetch_assoc()):
        $wlat = floatval($w['latitude']);
        $wlng = floatval($w['longitude']);
        $wname = addslashes($w['point_name']);
        $wcity = addslashes($w['city']);
        $wstatus = addslashes($w['status']);
        $wcolor = ($wstatus === 'Active') ? '#ff6600' : '#999999';
    ?>
    L.marker([<?= $wlat ?>, <?= $wlng ?>], {icon: warehouseIcon})
        .addTo(bookingsMap)
        .bindPopup(`
            <div style="padding: 8px;">
                <div style="font-weight: bold; color: #ff6600; margin-bottom: 4px;">
                    <i class="fas fa-warehouse"></i> WAREHOUSE
                </div>
                <div><strong><?= $wname ?></strong></div>
                <div><?= $wcity ?></div>
                <div style="font-size: 12px; color: #666;">Status: <?= $wstatus ?></div>
            </div>
        `);
    <?php
      endwhile;
    }

    // Show airports as plane icons
    $airportRes = $conn->query("SELECT point_name, city, latitude, longitude, status FROM network_points WHERE point_type='Airport' AND latitude IS NOT NULL AND longitude IS NOT NULL");
    if ($airportRes) {
      while($a = $airportRes->fetch_assoc()):
        $alat = floatval($a['latitude']);
        $alng = floatval($a['longitude']);
        $aname = addslashes($a['point_name']);
        $acity = addslashes($a['city']);
        $astatus = addslashes($a['status']);
    ?>
    L.marker([<?= $alat ?>, <?= $alng ?>], {icon: airportIcon})
        .addTo(bookingsMap)
        .bindPopup(`
            <div style="padding: 8px;">
                <div style="font-weight: bold; color: #00cc66; margin-bottom: 4px;">
                    <i class="fas fa-plane"></i> AIRPORT
                </div>
                <div><strong><?= $aname ?></strong></div>
                <div><?= $acity ?></div>
                <div style="font-size: 12px; color: #666;">Status: <?= $astatus ?></div>
            </div>
        `);
    <?php
      endwhile;
    }
    ?>

    // Add legend
    const legend = L.control({position: 'bottomright'});
    legend.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'legend');
        div.style.background = 'white';
        div.style.padding = '10px';
        div.style.borderRadius = '5px';
        div.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
        div.style.fontSize = '12px';
        
        div.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 8px;">LEGEND</div>
            <div style="display: flex; align-items: center; margin-bottom: 4px;">
                <div style="background-color: #0066cc; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                    <i class="fas fa-ship" style="color: white; font-size: 8px;"></i>
                </div>
                <span>Seaport</span>
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 4px;">
                <div style="background-color: #ff6600; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                    <i class="fas fa-warehouse" style="color: white; font-size: 8px;"></i>
                </div>
                <span>Warehouse</span>
            </div>
            <div style="display: flex; align-items: center; margin-bottom: 4px;">
                <div style="background-color: #00cc66; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                    <i class="fas fa-plane" style="color: white; font-size: 8px;"></i>
                </div>
                <span>Airport</span>
            </div>
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd;">
                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                    <div style="background-color: #198754; width: 16px; height: 3px; margin-right: 8px;"></div>
                    <span>Land Route</span>
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                    <div style="background-color: #0d6efd; width: 16px; height: 3px; margin-right: 8px;"></div>
                    <span>Air Route</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <div style="background-color: #0aa2c0; width: 16px; height: 3px; margin-right: 8px;"></div>
                    <span>Sea Route</span>
                </div>
            </div>
        `;
        return div;
    };
    legend.addTo(bookingsMap);
}

async function loadBookings() {
    try {
        // Show loading state
        const tbody = document.getElementById('bookingsTableBody');
        tbody.innerHTML = '<tr><td colspan="11" class="text-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading bookings...</td></tr>';
        
        const status = document.getElementById('statusFilter').value;
        const carrier = document.getElementById('carrierFilter').value;
        const date = document.getElementById('dateFilter').value;
        const search = document.getElementById('searchFilter').value;

        // Build URL with proper encoding
        const params = new URLSearchParams({
            status: status,
            carrier_type: carrier,
            search: search
        });
        
        let url = `../api/booking_api.php/bookings?${params.toString()}`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            // Try to extract server error details (e.g., missing table hint)
            let serverDetail = '';
            try {
                const errJson = await response.json();
                serverDetail = errJson.hint ? `${errJson.error || ''} | ${errJson.hint}`.trim() : (errJson.error || errJson.message || JSON.stringify(errJson));
            } catch (e) {
                try {
                    serverDetail = await response.text();
                } catch (_) {
                    serverDetail = '';
                }
            }
            const statusText = response.statusText || 'Error';
            throw new Error(`HTTP ${response.status} ${statusText}${serverDetail ? ` - ${serverDetail}` : ''}`);
        }
        
        const data = await response.json();
        
        if (data.bookings) {
            currentBookingData = data.bookings;
            displayBookings(data.bookings);
            displayBookingRoutes(data.bookings);
        } else {
            throw new Error('No booking data received');
        }
        
    } catch (error) {
        console.error('Error loading bookings:', error);
        document.getElementById('bookingsTableBody').innerHTML = 
            `<tr><td colspan="11" class="text-center text-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading bookings: ${error.message}
            </td></tr>`;
    }
}

function displayBookings(bookings) {
    const tbody = document.getElementById('bookingsTableBody');
    
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center">No bookings found</td></tr>';
        return;
    }
    
    tbody.innerHTML = bookings.map(booking => `
        <tr class="modern-table-row">
            <td><span class="fw-medium">${booking.booking_id}</span></td>
            <td><span class="badge bg-primary">${booking.booking_reference}</span></td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-user me-2 text-muted"></i>
                    <div>
                        <div class="fw-medium">${booking.customer_name}</div>
                        <small class="text-muted">${booking.customer_email || 'No email'}</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="modern-badge badge-user">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    ${booking.origin_address.length > 30 ? booking.origin_address.substring(0, 30) + '...' : booking.origin_address}
                </span>
            </td>
            <td>
                <span class="modern-badge badge-service-provider">
                    <i class="fas fa-flag-checkered me-1"></i>
                    ${booking.destination_address.length > 30 ? booking.destination_address.substring(0, 30) + '...' : booking.destination_address}
                </span>
            </td>
            <td>
                ${getCarrierTypeBadge(booking.carrier_type)}
            </td>
            <td>
                <div class="company-name">
                    <i class="fas fa-building me-2 text-muted"></i>
                    <span class="fw-medium">${booking.provider_name || 'Unassigned'}</span>
                </div>
            </td>
            <td><strong>₱${booking.estimated_cost || 'N/A'}</strong></td>
            <td><strong>${booking.estimated_transit_time ? booking.estimated_transit_time + ' min' : 'N/A'}</strong></td>
            <td>${getStatusBadge(booking.status)}</td>
            <td class="text-center">
                <div class="action-buttons">
                    <button class="btn btn-success btn-sm" onclick="approveBookingStart(${booking.booking_id})" title="Approve">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-modern-view" onclick="viewBooking(${booking.booking_id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
    
                    <button class="btn btn-modern-edit" onclick="editBooking(${booking.booking_id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-modern-delete" onclick="deleteBooking(${booking.booking_id})" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function displayBookingRoutes(bookings) {
    // Clear existing routes
    bookingRoutesLayer.clearLayers();

    bookingsMapRenderToken++;
    const myToken = bookingsMapRenderToken;

    const PORTS = {
        Luzon: [
            { name: 'Manila Port', lat: 14.5869, lng: 120.9650 },
            { name: 'Batangas Port', lat: 13.7575, lng: 121.0613 },
            { name: 'Subic Bay Port', lat: 14.8311, lng: 120.2814 }
        ],
        Visayas: [
            { name: 'Cebu Port', lat: 10.3157, lng: 123.9175 },
            { name: 'Iloilo Port', lat: 10.6931, lng: 122.5583 },
            { name: 'Bacolod Port', lat: 10.6770, lng: 122.9590 }
        ],
        Mindanao: [
            { name: 'Cagayan de Oro Port', lat: 8.4880, lng: 124.6472 },
            { name: 'Davao Port', lat: 7.0731, lng: 125.6128 },
            { name: 'Zamboanga Port', lat: 6.9100, lng: 122.0730 }
        ]
    };

    const AIRPORTS = {
        Luzon: [
            { name: 'NAIA (Manila)', lat: 14.5086, lng: 121.0194 },
            { name: 'Clark International Airport', lat: 15.1859, lng: 120.5603 },
            { name: 'Bicol International Airport', lat: 13.1573, lng: 123.7356 }
        ],
        Visayas: [
            { name: 'Mactan-Cebu International Airport', lat: 10.3075, lng: 123.9790 },
            { name: 'Iloilo International Airport', lat: 10.7136, lng: 122.5450 },
            { name: 'Bacolod-Silay Airport', lat: 10.7764, lng: 123.0150 }
        ],
        Mindanao: [
            { name: 'Davao International Airport', lat: 7.1255, lng: 125.6458 },
            { name: 'Laguindingan Airport (CDO)', lat: 8.4156, lng: 124.6113 },
            { name: 'Zamboanga International Airport', lat: 6.9224, lng: 122.0596 }
        ]
    };

    function haversineKm(a, b) {
        const R = 6371;
        const toRad = (d) => d * Math.PI / 180;
        const dLat = toRad(b.lat - a.lat);
        const dLng = toRad(b.lng - a.lng);
        const lat1 = toRad(a.lat);
        const lat2 = toRad(b.lat);
        const s = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * (Math.sin(dLng / 2) ** 2);
        return 2 * R * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
    }

    function getIslandGroup(lat, lng) {
        const la = parseFloat(lat);
        const ln = parseFloat(lng);
        if (!Number.isFinite(la) || !Number.isFinite(ln)) return null;

        // Rough bounding boxes (good enough for island-group routing logic)
        if (la >= 12.0 && la <= 21.5) return 'Luzon';
        if (la >= 9.0 && la < 12.0) return 'Visayas';
        if (la >= 4.5 && la < 9.0) return 'Mindanao';
        return null;
    }

    function nearestPort(point, group) {
        const ports = PORTS[group] || [];
        if (!ports.length) return null;
        let best = ports[0];
        let bestD = haversineKm(point, best);
        for (let i = 1; i < ports.length; i++) {
            const d = haversineKm(point, ports[i]);
            if (d < bestD) {
                bestD = d;
                best = ports[i];
            }
        }
        return best;
    }

    function nearestAirport(point, group) {
        const airports = AIRPORTS[group] || [];
        if (!airports.length) return null;
        let best = airports[0];
        let bestD = haversineKm(point, best);
        for (let i = 1; i < airports.length; i++) {
            const d = haversineKm(point, airports[i]);
            if (d < bestD) {
                bestD = d;
                best = airports[i];
            }
        }
        return best;
    }

    function buildRouteWaypoints(booking) {
        const origin = { lat: parseFloat(booking.origin_lat), lng: parseFloat(booking.origin_lng) };
        const dest = { lat: parseFloat(booking.destination_lat), lng: parseFloat(booking.destination_lng) };

        const originGroup = getIslandGroup(origin.lat, origin.lng);
        const destGroup = getIslandGroup(dest.lat, dest.lng);
        const isAir = (booking.carrier_type || '').toLowerCase() === 'air';

        // Default straight route
        const route = [
            { type: 'origin', name: 'Origin', ...origin },
            { type: 'destination', name: 'Destination', ...dest }
        ];

        // For any inter-island pair (and non-air), force a port-to-port leg
        if (originGroup && destGroup && originGroup !== destGroup) {
            if (isAir) {
                const fromAirport = nearestAirport(origin, originGroup);
                const toAirport = nearestAirport(dest, destGroup);
                if (fromAirport && toAirport) {
                    return [
                        { type: 'origin', name: 'Origin', ...origin },
                        { type: 'stop', name: fromAirport.name, lat: fromAirport.lat, lng: fromAirport.lng },
                        { type: 'stop', name: toAirport.name, lat: toAirport.lat, lng: toAirport.lng },
                        { type: 'destination', name: 'Destination', ...dest }
                    ];
                }
            } else {
                const fromPort = nearestPort(origin, originGroup);
                const toPort = nearestPort(dest, destGroup);
                if (fromPort && toPort) {
                    return [
                        { type: 'origin', name: 'Origin', ...origin },
                        { type: 'stop', name: fromPort.name, lat: fromPort.lat, lng: fromPort.lng },
                        { type: 'stop', name: toPort.name, lat: toPort.lat, lng: toPort.lng },
                        { type: 'destination', name: 'Destination', ...dest }
                    ];
                }
            }
        }

        return route;
    }

    const osrmRouter = L.Routing.osrmv1({
        serviceUrl: 'https://router.project-osrm.org/route/v1'
    });

    function osrmRoute(latlngs) {
        return new Promise((resolve, reject) => {
            try {
                osrmRouter.route(
                    latlngs.map(ll => L.Routing.waypoint(L.latLng(ll.lat, ll.lng))),
                    (err, routes) => {
                        if (err || !routes || !routes.length) {
                            reject(err || new Error('No routes'));
                            return;
                        }
                        resolve(routes[0]);
                    },
                    { alternatives: false }
                );
            } catch (e) {
                reject(e);
            }
        });
    }

    function safeAddLayer(layer) {
        if (myToken !== bookingsMapRenderToken) return;
        bookingRoutesLayer.addLayer(layer);
    }

    const portIcon = L.divIcon({
        html: '<div style="background-color: #ffc107; border: 3px solid white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-anchor" style="color: #212529; font-size: 11px;"></i></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
        popupAnchor: [0, -11],
        className: 'route-marker'
    });

    const airportStopIcon = L.divIcon({
        html: '<div style="background-color: #0d6efd; border: 3px solid white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-plane" style="color: white; font-size: 11px;"></i></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
        popupAnchor: [0, -11],
        className: 'route-marker'
    });

    const stopNumberIcon = (n) => L.divIcon({
        html: `<div style="background-color: #ffc107; border: 3px solid white; border-radius: 50%; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); font-weight: 800; color: #212529; font-size: 12px;">${n}</div>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
        popupAnchor: [0, -13],
        className: 'route-marker'
    });
    
    // Create custom route markers
    const originIcon = L.divIcon({
        html: '<div style="background-color: #28a745; border: 3px solid white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-play" style="color: white; font-size: 12px;"></i></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -12],
        className: 'route-marker'
    });
    
    const destIcon = L.divIcon({
        html: '<div style="background-color: #dc3545; border: 3px solid white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-stop" style="color: white; font-size: 12px;"></i></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -12],
        className: 'route-marker'
    });
    
    bookings.forEach(booking => {
        if (booking.origin_lat && booking.origin_lng && 
            booking.destination_lat && booking.destination_lng) {
            
            const color = getRouteColor(booking.carrier_type);
            const waypoints = buildRouteWaypoints(booking);
            const isAir = (booking.carrier_type || '').toLowerCase() === 'air';
            const isInterIsland = waypoints.length > 2;
            const midLegColor = isAir ? '#0d6efd' : '#0aa2c0';

            const stopsText = waypoints
                .filter(w => w.type === 'stop')
                .map(w => w.name)
                .join(' → ') || 'Direct';

            const popupHtml = `
                <div style="padding: 8px; min-width: 200px;">
                    <div style="font-weight: bold; margin-bottom: 6px; color: ${color};">
                        <i class="fas fa-route"></i> ${booking.booking_reference}
                    </div>
                    <div><strong>Customer:</strong> ${booking.customer_name}</div>
                    <div><strong>Route:</strong> ${booking.origin_address} → ${booking.destination_address}</div>
                    <div><strong>Stops:</strong> ${stopsText}</div>
                    <div><strong>Type:</strong> ${(booking.carrier_type || '').toUpperCase()}</div>
                    <div><strong>Status:</strong> ${booking.status}</div>
                    <div><strong>Cost:</strong> ₱${booking.estimated_cost || 'N/A'}</div>
                </div>
            `;

            const origin = waypoints[0];
            const dest = waypoints[waypoints.length - 1];
            const stop1 = isInterIsland ? waypoints[1] : null;
            const stop2 = isInterIsland ? waypoints[waypoints.length - 2] : null;

            const TRIVIAL_LEG_KM = 0.25;

            if (isInterIsland && stop1 && stop2) {
                const leg1Trivial = haversineKm(origin, stop1) <= TRIVIAL_LEG_KM;
                const leg2Trivial = haversineKm(stop2, dest) <= TRIVIAL_LEG_KM;

                safeAddLayer(L.polyline([[stop1.lat, stop1.lng], [stop2.lat, stop2.lng]], {
                    color: midLegColor,
                    weight: 4,
                    opacity: 0.75,
                    dashArray: '8, 10'
                }).bindPopup(popupHtml));

                if (!leg1Trivial) {
                    osrmRoute([origin, stop1])
                        .then(r => {
                            if (myToken !== bookingsMapRenderToken) return;
                            const coords = (r.coordinates || []).map(c => [c.lat, c.lng]);
                            if (coords.length) {
                                safeAddLayer(L.polyline(coords, { color, weight: 4, opacity: 0.85, className: 'booking-route' }).bindPopup(popupHtml));
                            }
                        })
                        .catch(() => {
                            if (myToken !== bookingsMapRenderToken) return;
                            safeAddLayer(L.polyline([[origin.lat, origin.lng], [stop1.lat, stop1.lng]], { color, weight: 4, opacity: 0.85, className: 'booking-route' }).bindPopup(popupHtml));
                        });
                } else {
                    safeAddLayer(L.polyline([[origin.lat, origin.lng], [stop1.lat, stop1.lng]], { color, weight: 4, opacity: 0.25, className: 'booking-route' }).bindPopup(popupHtml));
                }

                if (!leg2Trivial) {
                    osrmRoute([stop2, dest])
                        .then(r => {
                            if (myToken !== bookingsMapRenderToken) return;
                            const coords = (r.coordinates || []).map(c => [c.lat, c.lng]);
                            if (coords.length) {
                                safeAddLayer(L.polyline(coords, { color, weight: 4, opacity: 0.85, className: 'booking-route' }).bindPopup(popupHtml));
                            }
                        })
                        .catch(() => {
                            if (myToken !== bookingsMapRenderToken) return;
                            safeAddLayer(L.polyline([[stop2.lat, stop2.lng], [dest.lat, dest.lng]], { color, weight: 4, opacity: 0.85, className: 'booking-route' }).bindPopup(popupHtml));
                        });
                } else {
                    safeAddLayer(L.polyline([[stop2.lat, stop2.lng], [dest.lat, dest.lng]], { color, weight: 4, opacity: 0.25, className: 'booking-route' }).bindPopup(popupHtml));
                }
            } else {
                osrmRoute([origin, dest])
                    .then(r => {
                        if (myToken !== bookingsMapRenderToken) return;
                        const coords = (r.coordinates || []).map(c => [c.lat, c.lng]);
                        if (coords.length) {
                            safeAddLayer(L.polyline(coords, {
                                color: color,
                                weight: 4,
                                opacity: 0.85,
                                className: 'booking-route'
                            }).bindPopup(popupHtml));
                        }
                    })
                    .catch(() => {
                        if (myToken !== bookingsMapRenderToken) return;
                        safeAddLayer(L.polyline([[origin.lat, origin.lng], [dest.lat, dest.lng]], {
                            color: color,
                            weight: 4,
                            opacity: 0.8,
                            dashArray: '10, 5',
                            className: 'booking-route'
                        }).bindPopup(popupHtml));
                    });
            }

            // Add stop markers
            waypoints.forEach(wp => {
                if (wp.type === 'stop') {
                    const isAirStop = (booking.carrier_type || '').toLowerCase() === 'air';
                    const m = L.marker([wp.lat, wp.lng], { icon: isAirStop ? airportStopIcon : portIcon }).bindPopup(
                        `<div style="padding: 6px;">
                            <div style="font-weight: bold; color: ${isAirStop ? '#0d6efd' : '#b8860b'}; margin-bottom: 4px;"><i class="fas ${isAirStop ? 'fa-plane' : 'fa-anchor'}"></i> STOP</div>
                            <div><strong>${booking.booking_reference}</strong></div>
                            <div>${wp.name}</div>
                        </div>`
                    );
                    safeAddLayer(m);
                }
            });
            
            // Add custom origin marker
            safeAddLayer(
                L.marker([booking.origin_lat, booking.origin_lng], {icon: originIcon})
                    .bindPopup(`
                        <div style="padding: 6px;">
                            <div style="font-weight: bold; color: #28a745; margin-bottom: 4px;">
                                <i class="fas fa-play"></i> ORIGIN
                            </div>
                            <div><strong>${booking.booking_reference}</strong></div>
                            <div>${booking.origin_address}</div>
                            <div style="font-size: 11px; color: #666;">Customer: ${booking.customer_name}</div>
                        </div>
                    `)
            );
                
            // Add custom destination marker
            safeAddLayer(
                L.marker([booking.destination_lat, booking.destination_lng], {icon: destIcon})
                    .bindPopup(`
                        <div style="padding: 6px;">
                            <div style="font-weight: bold; color: #dc3545; margin-bottom: 4px;">
                                <i class="fas fa-stop"></i> DESTINATION
                            </div>
                            <div><strong>${booking.booking_reference}</strong></div>
                            <div>${booking.destination_address}</div>
                            <div style="font-size: 11px; color: #666;">Status: ${booking.status}</div>
                        </div>
                    `)
            );
        }
    });
    
    // Fit map to show all routes
    if (bookingRoutesLayer.getLayers().length > 0) {
        bookingsMap.fitBounds(bookingRoutesLayer.getBounds().pad(0.15));
    }
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
        'pending': '<span class="badge bg-warning">Pending</span>',
        'confirmed': '<span class="badge bg-info">Confirmed</span>',
        'in_transit': '<span class="badge bg-primary">In Transit</span>',
        'delivered': '<span class="badge bg-success">Delivered</span>',
        'cancelled': '<span class="badge bg-danger">Cancelled</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
}

function getRouteColor(carrierType) {
    const colors = {
        'land': '#198754',
        'air': '#0d6efd', 
        'sea': '#0aa2c0'
    };
    return colors[carrierType] || '#6c757d';
}

async function viewBooking(bookingId) {
    try {
        const response = await fetch(`../api/booking_api.php/bookings/${bookingId}`);
        const booking = await response.json();
        
        // Populate modal
        document.getElementById('booking_id').textContent = booking.booking_id;
        document.getElementById('booking_reference').textContent = booking.booking_reference;
        document.getElementById('customer_name').textContent = booking.customer_name;
        document.getElementById('customer_email').textContent = booking.customer_email || 'N/A';
        document.getElementById('customer_phone').textContent = booking.customer_phone || 'N/A';
        document.getElementById('origin_address').textContent = booking.origin_address;
        document.getElementById('destination_address').textContent = booking.destination_address;
        document.getElementById('carrier_type').textContent = booking.carrier_type.toUpperCase();
        document.getElementById('cargo_type').textContent = booking.cargo_type || 'N/A';
        document.getElementById('weight').textContent = booking.weight ? booking.weight + ' kg' : 'N/A';
        document.getElementById('estimated_cost').textContent = booking.estimated_cost ? '₱' + booking.estimated_cost : 'N/A';
        document.getElementById('booking_status').innerHTML = getStatusBadge(booking.status);
        document.getElementById('provider_name').textContent = booking.provider_name || 'Unassigned';
        
        // Show modal and initialize map on modal shown
        destroyBookingRouteMap();
        currentModalBooking = booking;

        const modalEl = document.getElementById('viewBookingModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        if (modalEl && modalEl.classList.contains('show')) {
            // Modal already open; initialize immediately
            initializeBookingRouteMap(booking);
            if (bookingModalMap) {
                try { bookingModalMap.invalidateSize(true); } catch (e) {}
            }
        } else {
            modal.show();
        }
        
    } catch (error) {
        console.error('Error loading booking:', error);
        alert('Error loading booking details');
    }
}

function initializeBookingRouteMap(booking) {
    const mapContainer = document.getElementById('bookingRouteMap');
    if (!mapContainer) return;

    const instructionsContainer = document.getElementById('bookingRouteInstructions');
    if (instructionsContainer) {
        instructionsContainer.innerHTML = '';
    }
    
    // Clear any previously initialized Leaflet map in this container
    destroyBookingRouteMap();
    currentModalBooking = booking;

    bookingModalMap = L.map('bookingRouteMap').setView([booking.origin_lat, booking.origin_lng], 6);
    const map = bookingModalMap;
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    const color = getRouteColor(booking.carrier_type);

    // Reuse the same multi-leg logic as the main map
    const origin = { lat: parseFloat(booking.origin_lat), lng: parseFloat(booking.origin_lng) };
    const dest = { lat: parseFloat(booking.destination_lat), lng: parseFloat(booking.destination_lng) };
    const getIslandGroup = (lat) => {
        const la = parseFloat(lat);
        if (!Number.isFinite(la)) return null;
        if (la >= 12.0 && la <= 21.5) return 'Luzon';
        if (la >= 9.0 && la < 12.0) return 'Visayas';
        if (la >= 4.5 && la < 9.0) return 'Mindanao';
        return null;
    };
    const haversineKm = (a, b) => {
        const R = 6371;
        const toRad = (d) => d * Math.PI / 180;
        const dLat = toRad(b.lat - a.lat);
        const dLng = toRad(b.lng - a.lng);
        const lat1 = toRad(a.lat);
        const lat2 = toRad(b.lat);
        const s = Math.sin(dLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * (Math.sin(dLng / 2) ** 2);
        return 2 * R * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
    };
    const ports = {
        Luzon: [
            { name: 'Manila Port', lat: 14.5869, lng: 120.9650 },
            { name: 'Batangas Port', lat: 13.7575, lng: 121.0613 },
            { name: 'Subic Bay Port', lat: 14.8311, lng: 120.2814 }
        ],
        Visayas: [
            { name: 'Cebu Port', lat: 10.3157, lng: 123.9175 },
            { name: 'Iloilo Port', lat: 10.6931, lng: 122.5583 },
            { name: 'Bacolod Port', lat: 10.6770, lng: 122.9590 }
        ],
        Mindanao: [
            { name: 'Cagayan de Oro Port', lat: 8.4880, lng: 124.6472 },
            { name: 'Davao Port', lat: 7.0731, lng: 125.6128 },
            { name: 'Zamboanga Port', lat: 6.9100, lng: 122.0730 }
        ]
    };

    const airports = {
        Luzon: [
            { name: 'NAIA (Manila)', lat: 14.5086, lng: 121.0194 },
            { name: 'Clark International Airport', lat: 15.1859, lng: 120.5603 },
            { name: 'Bicol International Airport', lat: 13.1573, lng: 123.7356 }
        ],
        Visayas: [
            { name: 'Mactan-Cebu International Airport', lat: 10.3075, lng: 123.9790 },
            { name: 'Iloilo International Airport', lat: 10.7136, lng: 122.5450 },
            { name: 'Bacolod-Silay Airport', lat: 10.7764, lng: 123.0150 }
        ],
        Mindanao: [
            { name: 'Davao International Airport', lat: 7.1255, lng: 125.6458 },
            { name: 'Laguindingan Airport (CDO)', lat: 8.4156, lng: 124.6113 },
            { name: 'Zamboanga International Airport', lat: 6.9224, lng: 122.0596 }
        ]
    };
    const nearestPort = (point, group) => {
        const list = ports[group] || [];
        if (!list.length) return null;
        let best = list[0];
        let bestD = haversineKm(point, best);
        for (let i = 1; i < list.length; i++) {
            const d = haversineKm(point, list[i]);
            if (d < bestD) {
                bestD = d;
                best = list[i];
            }
        }
        return best;
    };

    const nearestAirport = (point, group) => {
        const list = airports[group] || [];
        if (!list.length) return null;
        let best = list[0];
        let bestD = haversineKm(point, best);
        for (let i = 1; i < list.length; i++) {
            const d = haversineKm(point, list[i]);
            if (d < bestD) {
                bestD = d;
                best = list[i];
            }
        }
        return best;
    };

    let waypoints = [origin, dest];
    const originGroup = getIslandGroup(origin.lat);
    const destGroup = getIslandGroup(dest.lat);
    const isAir = (booking.carrier_type || '').toLowerCase() === 'air';
    if (originGroup && destGroup && originGroup !== destGroup) {
        if (isAir) {
            const a1 = nearestAirport(origin, originGroup);
            const a2 = nearestAirport(dest, destGroup);
            if (a1 && a2) {
                waypoints = [origin, a1, a2, dest];
            }
        } else {
            const p1 = nearestPort(origin, originGroup);
            const p2 = nearestPort(dest, destGroup);
            if (p1 && p2) {
                waypoints = [origin, p1, p2, dest];
            }
        }
    }

    const originIcon = L.divIcon({
        html: '<div style="background-color: #28a745; border: 3px solid white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-play" style="color: white; font-size: 12px;"></i></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -12],
        className: 'route-marker'
    });

    const destIcon = L.divIcon({
        html: '<div style="background-color: #dc3545; border: 3px solid white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-stop" style="color: white; font-size: 12px;"></i></div>',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -12],
        className: 'route-marker'
    });

    const portIcon = L.divIcon({
        html: '<div style="background-color: #ffc107; border: 3px solid white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="fas fa-anchor" style="color: #212529; font-size: 11px;"></i></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
        popupAnchor: [0, -11],
        className: 'route-marker'
    });

    const stopNumberIcon = (n) => L.divIcon({
        html: `<div style="background-color: #ffc107; border: 3px solid white; border-radius: 50%; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); font-weight: 800; color: #212529; font-size: 12px;">${n}</div>`,
        iconSize: [26, 26],
        iconAnchor: [13, 13],
        popupAnchor: [0, -13],
        className: 'route-marker'
    });

    const formatDistance = (m) => {
        const km = (m || 0) / 1000;
        if (!Number.isFinite(km)) return '';
        return km >= 1 ? `${km.toFixed(1)} km` : `${Math.round(m)} m`;
    };

    const formatDuration = (s) => {
        const sec = s || 0;
        if (!Number.isFinite(sec)) return '';
        const min = Math.round(sec / 60);
        const h = Math.floor(min / 60);
        const mm = min % 60;
        if (h <= 0) return `${min} min`;
        return `${h} h ${mm} min`;
    };

    const renderFallback = () => {
        if (instructionsContainer) {
            const stops = waypoints
                .map((p, idx) => {
                    if (idx === 0) return booking.origin_address;
                    if (idx === waypoints.length - 1) return booking.destination_address;
                    return p.name ? p.name : 'Port Stop';
                })
                .join(' → ');
            instructionsContainer.innerHTML = `
                <div style="padding: 12px;">
                    <div style="font-weight: 700; margin-bottom: 8px;">Route</div>
                    <div style="font-size: 13px; color: #333;">${stops}</div>
                </div>
            `;
        }

        const routeCoords = waypoints.map(p => [p.lat, p.lng]);
        L.polyline(routeCoords, {
            color: color,
            weight: 4,
            opacity: 0.8
        }).addTo(map);

        L.marker([origin.lat, origin.lng], { icon: originIcon })
            .addTo(map)
            .bindPopup(`<strong>Origin</strong><br>${booking.origin_address}`)
            .openPopup();

        if (waypoints.length > 2) {
            let stopNo = 1;
            for (let i = 1; i < waypoints.length - 1; i++) {
                const wp = waypoints[i];
                const label = wp.name ? wp.name : 'Port Stop';
                L.marker([wp.lat, wp.lng], { icon: stopNumberIcon(stopNo) })
                    .addTo(map)
                    .bindPopup(`<strong>Stop ${stopNo}</strong><br>${label}`);
                stopNo++;
            }
        }

        L.marker([dest.lat, dest.lng], { icon: destIcon })
            .addTo(map)
            .bindPopup(`<strong>Destination</strong><br>${booking.destination_address}`);

        map.fitBounds(routeCoords, {padding: [50, 50]});
    };

    const isInterIsland = waypoints.length > 2;
    const canUseOsrmDirect = !isAir && !isInterIsland;

    // Inter-island accuracy: land legs via OSRM + dashed sea leg between ports
    if (isInterIsland) {
        // Markers
        L.marker([origin.lat, origin.lng], { icon: originIcon })
            .addTo(map)
            .bindPopup(`<strong>Origin</strong><br>${booking.origin_address}`)
            .openPopup();

        let stopNo = 1;
        for (let i = 1; i < waypoints.length - 1; i++) {
            const wp = waypoints[i];
            const label = wp.name ? wp.name : 'Port Stop';
            L.marker([wp.lat, wp.lng], { icon: stopNumberIcon(stopNo) })
                .addTo(map)
                .bindPopup(`<strong>Stop ${stopNo}</strong><br>${label}`);
            stopNo++;
        }

        L.marker([dest.lat, dest.lng], { icon: destIcon })
            .addTo(map)
            .bindPopup(`<strong>Destination</strong><br>${booking.destination_address}`);

        const seaFrom = waypoints[1];
        const seaTo = waypoints[waypoints.length - 2];
        const midLegLabel = isAir ? 'Air leg' : 'Sea leg';
        const midLegColor = isAir ? '#0d6efd' : '#0aa2c0';
        L.polyline(
            [
                [seaFrom.lat, seaFrom.lng],
                [seaTo.lat, seaTo.lng]
            ],
            {
                color: midLegColor,
                weight: 4,
                opacity: 0.75,
                dashArray: '8, 10'
            }
        ).addTo(map);

        // Instructions container skeleton
        if (instructionsContainer) {
            const p1Name = seaFrom.name ? seaFrom.name : 'Port Stop';
            const p2Name = seaTo.name ? seaTo.name : 'Port Stop';

            const originLabelRaw = booking.origin_address;
            const destLabelRaw = booking.destination_address;
            const originCloseToStop = haversineKm(origin, seaFrom) <= 0.25;
            const destCloseToStop = haversineKm(seaTo, dest) <= 0.25;

            const originLabel = originCloseToStop ? p1Name : originLabelRaw;
            const destLabel = destCloseToStop ? p2Name : destLabelRaw;

            const labels = [originLabel, p1Name, p2Name, destLabel].filter(Boolean);
            const deduped = [];
            for (let i = 0; i < labels.length; i++) {
                const cur = String(labels[i]);
                const prev = deduped.length ? String(deduped[deduped.length - 1]) : null;
                if (!prev || cur.toLowerCase() !== prev.toLowerCase()) {
                    deduped.push(cur);
                }
            }

            instructionsContainer.innerHTML = `
                <div style="padding: 10px 12px; border-bottom: 1px solid #eee;">
                    <div style="font-weight: 800;">Route (Inter-island)</div>
                    <div style="font-size: 12px; color: #555; margin-top: 4px;">${deduped.join(' → ')}</div>
                </div>
                <div id="leg1Directions"></div>
                <div style="padding: 10px 12px; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                    <div style="font-weight: 700;">${midLegLabel}</div>
                    <div style="font-size: 12px; color: #555; margin-top: 4px;">${p1Name} → ${p2Name}</div>
                </div>
                <div id="leg2Directions"></div>
            `;
        }

        const router = L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' });
        const hideControlUi = (ctl) => {
            const c = ctl && ctl.getContainer ? ctl.getContainer() : null;
            if (c) c.style.display = 'none';
        };

        const renderLegDirections = (targetId, title, routeObj) => {
            const target = document.getElementById(targetId);
            if (!target) return;

            const summary = routeObj && routeObj.summary ? routeObj.summary : null;
            const dist = summary ? formatDistance(summary.totalDistance) : '';
            const dur = summary ? formatDuration(summary.totalTime) : '';
            const header = `
                <div style="padding: 10px 12px; border-bottom: 1px solid #eee;">
                    <div style="font-weight: 700;">${title}</div>
                    <div style="font-size: 12px; color: #555; margin-top: 4px;">${dist}${dist && dur ? ' , ' : ''}${dur}</div>
                </div>
            `;

            const instructions = (routeObj && Array.isArray(routeObj.instructions)) ? routeObj.instructions : [];
            const items = instructions
                .map((ins) => {
                    const text = ins && ins.text ? ins.text : '';
                    const d = ins && Number.isFinite(ins.distance) ? formatDistance(ins.distance) : '';
                    if (!text) return '';
                    return `<div style="padding: 8px 12px; border-bottom: 1px solid #f3f3f3; font-size: 13px;">${text}<div style="font-size: 11px; color: #777; margin-top: 2px;">${d}</div></div>`;
                })
                .join('');

            target.innerHTML = header + (items || `<div style="padding: 10px 12px; color: #777; font-size: 12px;">No detailed steps available.</div>`);
        };

        const TRIVIAL_LEG_KM = 0.25;
        const leg1IsTrivial = haversineKm(origin, seaFrom) <= TRIVIAL_LEG_KM;
        const leg2IsTrivial = haversineKm(seaTo, dest) <= TRIVIAL_LEG_KM;

        let leg1Route = null;
        let leg2Route = null;
        const tryRenderBothLegs = () => {
            if (leg1Route) renderLegDirections('leg1Directions', 'Land leg 1', leg1Route);
            if (leg2Route) renderLegDirections('leg2Directions', 'Land leg 2', leg2Route);
        };

        const renderTrivialLeg = (targetId, title, message) => {
            const target = document.getElementById(targetId);
            if (!target) return;
            target.innerHTML = `
                <div style="padding: 10px 12px; border-bottom: 1px solid #eee;">
                    <div style="font-weight: 700;">${title}</div>
                    <div style="font-size: 12px; color: #555; margin-top: 4px;">${message}</div>
                </div>
            `;
        };

        try {
            if (leg1IsTrivial) {
                renderTrivialLeg('leg1Directions', 'Land leg 1', 'Origin is already at the stop point.');
            } else {
                const leg1 = L.Routing.control({
                    waypoints: [L.latLng(origin.lat, origin.lng), L.latLng(seaFrom.lat, seaFrom.lng)],
                    router,
                    routeWhileDragging: false,
                    draggableWaypoints: false,
                    addWaypoints: false,
                    showAlternatives: false,
                    fitSelectedRoutes: false,
                    show: false,
                    lineOptions: { styles: [{ color: color, opacity: 0.85, weight: 5 }] },
                    createMarker: () => null
                }).addTo(map);
                hideControlUi(leg1);
                bookingModalControls.push(leg1);

                leg1.on('routesfound', function(e) {
                    leg1Route = e && e.routes && e.routes[0] ? e.routes[0] : null;
                    tryRenderBothLegs();
                });
                leg1.on('routingerror', function() {
                    try { leg1.remove(); } catch (e) {}
                    renderFallback();
                });
            }

            if (leg2IsTrivial) {
                renderTrivialLeg('leg2Directions', 'Land leg 2', 'Destination is already at the stop point.');
            } else {
                const leg2 = L.Routing.control({
                    waypoints: [L.latLng(seaTo.lat, seaTo.lng), L.latLng(dest.lat, dest.lng)],
                    router,
                    routeWhileDragging: false,
                    draggableWaypoints: false,
                    addWaypoints: false,
                    showAlternatives: false,
                    fitSelectedRoutes: false,
                    show: false,
                    lineOptions: { styles: [{ color: color, opacity: 0.85, weight: 5 }] },
                    createMarker: () => null
                }).addTo(map);
                hideControlUi(leg2);
                bookingModalControls.push(leg2);

                leg2.on('routesfound', function(e) {
                    leg2Route = e && e.routes && e.routes[0] ? e.routes[0] : null;
                    tryRenderBothLegs();
                });
                leg2.on('routingerror', function() {
                    try { leg2.remove(); } catch (e) {}
                    renderFallback();
                });
            }

            const allCoords = waypoints.map(p => [p.lat, p.lng]);
            map.fitBounds(allCoords, { padding: [50, 50] });
        } catch (e) {
            renderFallback();
        }

        return;
    }

    if (!canUseOsrmDirect) {
        renderFallback();
        return;
    }

    // Same-island: full OSRM route + default itinerary UI (moved to the right panel)
    try {
        const routingControl = L.Routing.control({
            waypoints: waypoints.map(p => L.latLng(p.lat, p.lng)),
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1'
            }),
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            showAlternatives: false,
            fitSelectedRoutes: true,
            show: false,
            lineOptions: {
                styles: [{ color: color, opacity: 0.85, weight: 5 }]
            },
            createMarker: function(i, wp, nWps) {
                if (i === 0) {
                    return L.marker(wp.latLng, { icon: originIcon }).bindPopup(`<strong>Origin</strong><br>${booking.origin_address}`);
                }
                if (i === nWps - 1) {
                    return L.marker(wp.latLng, { icon: destIcon }).bindPopup(`<strong>Destination</strong><br>${booking.destination_address}`);
                }
                return L.marker(wp.latLng, { icon: portIcon });
            }
        }).addTo(map);
        bookingModalControls.push(routingControl);

        routingControl.on('routingerror', function() {
            try {
                routingControl.remove();
            } catch (e) {}
            renderFallback();
        });

        const rc = routingControl.getContainer();
        if (instructionsContainer && rc) {
            instructionsContainer.appendChild(rc);
        }
    } catch (e) {
        renderFallback();
    }

    try { map.invalidateSize(true); } catch (e) {}
}

function filterBookings() {
    loadBookings();
}

function refreshBookings() {
    loadBookings();
}

function updateBookingStatus(status) {
    const bookingId = document.getElementById('booking_id').textContent;
    document.getElementById('update_booking_id').value = bookingId;
    document.getElementById('update_status_value').value = status;
    
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

async function confirmStatusUpdate() {
    const bookingId = document.getElementById('update_booking_id').value;
    const status = document.getElementById('update_status_value').value;
    
    try {
        const response = await fetch(`../api/booking_api.php/bookings/${bookingId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: status })
        });
        
        if (response.ok) {
            // Close modals
            bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
            bootstrap.Modal.getInstance(document.getElementById('viewBookingModal')).hide();
            
            // Refresh bookings
            loadBookings();
            
            // Show success message
            alert('Booking status updated successfully');
        } else {
            throw new Error('Failed to update status');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('Error updating booking status');
    }
}

function editBooking(bookingId) {
    (async () => {
        try {
            const response = await fetch(`../api/booking_api.php/bookings/${bookingId}`);
            if (!response.ok) {
                throw new Error('Failed to load booking');
            }
            const booking = await response.json();

            // Load providers list and set selected
            try {
                const pres = await fetch('../api/booking_api.php/providers');
                const pdata = await pres.json().catch(() => ({ providers: [] }));
                const sel = document.getElementById('edit_provider_id');
                if (sel) {
                    const current = String(booking.provider_id ?? '');
                    // Reset options leaving the first Unassigned
                    sel.innerHTML = '<option value="">Unassigned</option>' +
                        (Array.isArray(pdata.providers) ? pdata.providers.map(p => {
                            const pid = String(p.provider_id);
                            const name = (p.company_name || '').toString();
                            const selected = current !== '' && pid === current ? ' selected' : '';
                            return `<option value="${pid}"${selected}>${name}</option>`;
                        }).join('') : '');
                }
            } catch (e) {
                // Ignore provider loading errors; keep Unassigned option
            }

            document.getElementById('edit_booking_id').value = booking.booking_id;
            document.getElementById('edit_booking_reference').value = booking.booking_reference || '';
            document.getElementById('edit_status').value = (booking.status || 'pending').toLowerCase();
            document.getElementById('edit_customer_name').value = booking.customer_name || '';
            document.getElementById('edit_carrier_type').value = (booking.carrier_type || 'land').toLowerCase();
            document.getElementById('edit_customer_email').value = booking.customer_email || '';
            document.getElementById('edit_customer_phone').value = booking.customer_phone || '';
            document.getElementById('edit_origin_address').value = booking.origin_address || '';
            document.getElementById('edit_destination_address').value = booking.destination_address || '';
            document.getElementById('edit_weight').value = booking.weight ?? '';
            document.getElementById('edit_cargo_type').value = booking.cargo_type || '';
            document.getElementById('edit_estimated_cost').value = booking.estimated_cost ?? '';
            document.getElementById('edit_origin_lat').value = booking.origin_lat ?? '';
            document.getElementById('edit_origin_lng').value = booking.origin_lng ?? '';
            document.getElementById('edit_destination_lat').value = booking.destination_lat ?? '';
            document.getElementById('edit_destination_lng').value = booking.destination_lng ?? '';

            const modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
            modal.show();
        } catch (error) {
            console.error('Error loading booking for edit:', error);
            alert('Error loading booking for edit');
        }
    })();
}

async function saveEditedBooking() {
    const bookingId = document.getElementById('edit_booking_id').value;
    if (!bookingId) {
        alert('Missing booking id');
        return;
    }

    const payload = {
        status: document.getElementById('edit_status').value,
        customer_name: document.getElementById('edit_customer_name').value,
        carrier_type: document.getElementById('edit_carrier_type').value,
        provider_id: (function(){ const v = document.getElementById('edit_provider_id').value; return v === '' ? null : parseInt(v, 10); })(),
        customer_email: document.getElementById('edit_customer_email').value,
        customer_phone: document.getElementById('edit_customer_phone').value,
        origin_address: document.getElementById('edit_origin_address').value,
        destination_address: document.getElementById('edit_destination_address').value,
        weight: document.getElementById('edit_weight').value === '' ? null : parseFloat(document.getElementById('edit_weight').value),
        cargo_type: document.getElementById('edit_cargo_type').value,
        estimated_cost: document.getElementById('edit_estimated_cost').value === '' ? null : parseFloat(document.getElementById('edit_estimated_cost').value),
        origin_lat: document.getElementById('edit_origin_lat').value === '' ? null : parseFloat(document.getElementById('edit_origin_lat').value),
        origin_lng: document.getElementById('edit_origin_lng').value === '' ? null : parseFloat(document.getElementById('edit_origin_lng').value),
        destination_lat: document.getElementById('edit_destination_lat').value === '' ? null : parseFloat(document.getElementById('edit_destination_lat').value),
        destination_lng: document.getElementById('edit_destination_lng').value === '' ? null : parseFloat(document.getElementById('edit_destination_lng').value)
    };

    try {
        const response = await fetch(`../api/booking_api.php/bookings/${bookingId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json().catch(() => null);
        if (!response.ok) {
            const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Failed to save booking';
            throw new Error(msg);
        }

        bootstrap.Modal.getInstance(document.getElementById('editBookingModal')).hide();
        loadBookings();
        alert('Booking updated successfully');
    } catch (error) {
        console.error('Error saving booking:', error);
        alert('Error saving booking: ' + error.message);
    }
}

async function deleteBooking(bookingId) {
    if (!confirm('Are you sure you want to delete this booking?')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/booking_api.php/bookings/${bookingId}`, {
            method: 'DELETE'
        });
        
        if (response.ok) {
            loadBookings();
            alert('Booking deleted successfully');
        } else {
            throw new Error('Failed to delete booking');
        }
    } catch (error) {
        console.error('Error deleting booking:', error);
        alert('Error deleting booking');
    }
}

async function reviewRouteAI(routeId, bookingRef) {
    const rid = parseInt(routeId || '0', 10);
    const modalEl = document.getElementById('routeAiModal');
    const modal = new bootstrap.Modal(modalEl);

    const titleEl = document.getElementById('routeAiTitle');
    if (titleEl) titleEl.textContent = bookingRef ? ('Route AI Review - ' + bookingRef) : 'Route AI Review';

    const bodyEl = document.getElementById('routeAiBody');
    if (bodyEl) {
        bodyEl.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div><div class="mt-2">Running AI review...</div></div>';
    }
    modal.show();

    if (!rid) {
        if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-warning mb-0">No route is linked to this booking yet.</div>';
        return;
    }

    try {
        const resp = await fetch('../api/ai/routes_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ route_id: rid })
        });

        const data = await resp.json().catch(() => null);
        if (!resp.ok || !data || !data.success) {
            const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'AI review failed.';
            if (bodyEl) bodyEl.innerHTML = `<div class="alert alert-danger mb-0">${msg}</div>`;
            return;
        }

        if (bodyEl) {
            bodyEl.innerHTML = `
                <div class="mb-2"><strong>Route Status:</strong> ${data.route_status}</div>
                <div class="mb-2"><strong>Estimated Delay Risk:</strong> ${data.estimated_delay_risk}</div>
                <div class="mb-2"><strong>Suggested Adjustment:</strong><div class="mt-1">${(data.suggested_adjustment || '').toString()}</div></div>
                <div class="mb-0"><strong>Notes:</strong><div class="mt-1">${(data.notes || '').toString()}</div></div>
            `;
        }
    } catch (e) {
        if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Network error while calling AI review.</div>';
    }
}
</script>
