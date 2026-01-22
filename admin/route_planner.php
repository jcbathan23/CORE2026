<?php
require_once __DIR__ . '/auth.php';
header('Location: manage_routes.php');
exit;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$extraCSS = '
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css">
';

include('header.php');
include('sidebar.php');
include('navbar.php');
include('../connect.php');
?>

<style>
/* Route Planner: enforce dark-mode readability locally */
body.dark-mode .card { background:#0f172a !important; color:#e6edf3 !important; border:1px solid rgba(255,255,255,0.08) !important; }
body.dark-mode .card .card-title { color:#e6edf3 !important; }
body.dark-mode .form-label, body.dark-mode label { color:#e6edf3 !important; opacity:1 !important; }
body.dark-mode .form-control, body.dark-mode select.form-control, body.dark-mode .form-select {
  background-color:#1f2937 !important; color:#e5e7eb !important; border:1px solid #273449 !important;
}
body.dark-mode .form-control:focus, body.dark-mode select.form-control:focus, body.dark-mode .form-select:focus { box-shadow: 0 0 0 .25rem rgba(37,99,235,.25) !important; border-color:#2563eb !important; }
body.dark-mode .form-control::placeholder { color:#94a3b8 !important; }
body.dark-mode .form-select option[disabled] { color:#94a3b8 !important; }


  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
  }

</style>

<div class="content">
  <h3 class="mb-4">ROUTE PLANNER</h3>

<?php if (isset($_GET['success']) && $_GET['success'] === 'route_saved'): ?>
<div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
  New Route Saved Successfully!
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

  <div class="row">
    <div class="col-md-4">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Route Options</h5>

          <!-- Carrier Mode -->
          <div class="mb-3">
            <label class="form-label">Carrier Mode</label>
            <select id="mode" class="form-control">
              <option value="land">Land</option>
              <option value="air">Air</option>
              <option value="sea">Sea</option>
            </select>
          </div>

          <!-- Origin -->
          <div class="mb-3">
            <label class="form-label">Origin</label>
            <select id="origin" class="form-control">
              <option disabled selected>Select Origin</option>
              <?php
              $q1 = "SELECT point_id, point_name, city, latitude, longitude, point_type FROM network_points WHERE status='Active'";
              $r1 = mysqli_query($conn, $q1);
              while ($row = mysqli_fetch_assoc($r1)):
                  $coords = $row['latitude'] . "," . $row['longitude'];
              ?>
              <option value="<?= $row['point_id'] ?>,<?= $coords ?>" data-type="<?= htmlspecialchars($row['point_type']) ?>">
                  <?= htmlspecialchars($row['point_name'] . " (" . $row['city'] . ")") ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Destination -->
          <div class="mb-3">
            <label class="form-label">Destination</label>
            <select id="destination" class="form-control">
              <option disabled selected>Select Destination</option>
              <?php
              $q2 = "SELECT point_id, point_name, city, latitude, longitude, point_type FROM network_points WHERE status='Active'";
              $r2 = mysqli_query($conn, $q2);
              while ($row = mysqli_fetch_assoc($r2)):
                  $coords = $row['latitude'] . "," . $row['longitude'];
              ?>
              <option value="<?= $row['point_id'] ?>,<?= $coords ?>" data-type="<?= htmlspecialchars($row['point_type']) ?>">
                  <?= htmlspecialchars($row['point_name'] . " (" . $row['city'] . ")") ?>
              </option>
              <?php endwhile; ?>
            </select>
          </div>


          <!-- Service Provider -->
          <div class="mb-3">
            <label class="form-label">Service Provider</label>
            <select id="provider" class="form-control">
              <option disabled selected>Select Service Provider</option>
              <?php
              $query = "SELECT provider_id, company_name FROM active_service_provider WHERE status='Active'";
              $result = mysqli_query($conn, $query);
              while ($row = mysqli_fetch_assoc($result)):
              ?>
              <option value="<?= $row['provider_id'] ?>"><?= htmlspecialchars($row['company_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <button onclick="handleRoute()" class="btn btn-success w-100">Draw Route</button>
        </div>
      </div>

      <!-- Route Info -->
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Route Info</h5>
          <div id="routeInfo" class="small text-muted">
            <em>No route drawn yet.</em>
          </div>
        </div>
      </div>

      <!-- Save Form -->
      <form method="POST" action="save_route.php" class="mt-3">
        <input type="hidden" name="origin_id" id="saveOrigin">
        <input type="hidden" name="destination_id" id="saveDestination">
        <input type="hidden" name="provider_id" id="saveProvider">
        <input type="hidden" name="carrier_type" id="saveMode">
        <input type="hidden" name="distance" id="saveDistance">
        <input type="hidden" name="eta" id="saveETA">
        <button type="submit" class="btn btn-primary w-100">Save Route</button>
      </form>
    </div>

    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div id="routeMap" style="height: 800px; border-radius: 8px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.min.js"></script>

<script>
const map = L.map('routeMap').setView([13.5, 122], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

let routeControl, lineLayer, startMarker, endMarker;

const modeSelect = document.getElementById('mode');
const originSelect = document.getElementById('origin');
const destSelect = document.getElementById('destination');

function filterOptions(mode) {
  const apply = (opt, idx) => {
    // keep placeholder (index 0) visible and disabled
    if (idx === 0) return;
    const t = (opt.dataset.type || '').toLowerCase().trim();
    let show = true;
    if (mode === 'air') show = (t === 'airport');
    else if (mode === 'sea') show = (t === 'port');
    else show = true;
    opt.hidden = !show;
    opt.disabled = !show;
  };
  Array.from(originSelect.options).forEach(apply);
  Array.from(destSelect.options).forEach(apply);

  if (originSelect.selectedOptions.length && originSelect.selectedOptions[0].disabled) originSelect.selectedIndex = 0;
  if (destSelect.selectedOptions.length && destSelect.selectedOptions[0].disabled) destSelect.selectedIndex = 0;
}

modeSelect.addEventListener('change', function(){
  filterOptions(this.value);
});

document.addEventListener('DOMContentLoaded', function(){
  filterOptions(modeSelect.value);
});

function handleRoute() {
  const originVal = document.getElementById("origin").value;
  const destVal = document.getElementById("destination").value;
  const mode = document.getElementById("mode").value;

  if (!originVal || !destVal) {
    alert("Please select both origin and destination.");
    return;
  }

  const [originId, lat1, lon1] = originVal.split(",");
  const [destId, lat2, lon2] = destVal.split(",");
  const start = L.latLng(lat1, lon1);
  const end = L.latLng(lat2, lon2);

  if (routeControl) map.removeControl(routeControl);
  if (lineLayer) map.removeLayer(lineLayer);
  if (startMarker) map.removeLayer(startMarker);
  if (endMarker) map.removeLayer(endMarker);

  startMarker = L.marker(start).addTo(map).bindPopup("Origin").openPopup();
  endMarker = L.marker(end).addTo(map).bindPopup("Destination").openPopup();

  let distanceKm, etaMin;

  if (mode === "land") {
    routeControl = L.Routing.control({
      waypoints: [start, end],
      routeWhileDragging: false,
      addWaypoints: false,
      draggableWaypoints: false,
      createMarker: () => null,
      showAlternatives: false,
      fitSelectedRoutes: true,
      lineOptions: { styles: [{ color: 'red', weight: 5 }] }
    }).addTo(map);

    routeControl.on('routesfound', function (e) {
      const route = e.routes[0];
      distanceKm = (route.summary.totalDistance / 1000).toFixed(2);

      // Apply realistic traffic factor for PH
      const trafficFactor = 2; // doubles the ETA due to congestion
      etaMin = Math.round((route.summary.totalTime / 60) * trafficFactor);

      showInfo(distanceKm, etaMin, mode, originId, destId);
    });
  } else {
    const color = mode === 'air' ? 'blue' : 'green';
    lineLayer = L.polyline([start, end], { color: color, dashArray: '10,10', weight: 3 }).addTo(map);
    map.fitBounds(lineLayer.getBounds());

    const R = 6371;
    const dLat = (end.lat - start.lat) * Math.PI / 180;
    const dLon = (end.lng - start.lng) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 + Math.cos(start.lat*Math.PI/180) * Math.cos(end.lat*Math.PI/180) * Math.sin(dLon/2)**2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    distanceKm = (R * c).toFixed(2);

    let avgSpeed;
    if (mode === 'air') avgSpeed = 800;
    else if (mode === 'sea') avgSpeed = 30;

    etaMin = Math.round((distanceKm / avgSpeed) * 60);

    showInfo(distanceKm, etaMin, mode, originId, destId);
  }
}

function showInfo(distanceKm, etaMin, mode, originId, destId) {
  const originOption = document.getElementById("origin").selectedOptions[0];
  const destOption = document.getElementById("destination").selectedOptions[0];
  const providerOption = document.getElementById("provider").selectedOptions[0];

  const originText = originOption.text;
  const destText = destOption.text;
  const providerName = providerOption.text;
  const providerId = providerOption.value;

  document.getElementById("routeInfo").innerHTML = `
    <ul class="list-unstyled mb-0">
      <li><strong>🧭 Origin:</strong> ${originText}</li>
      <li><strong>📍 Destination:</strong> ${destText}</li>
      <li><strong>🚚 Carrier Type:</strong> ${mode}</li>
      <li><strong>🏢 Service Provider:</strong> ${providerName}</li>
      <li><strong>📏 Distance:</strong> ${distanceKm} km</li>
      <li><strong>⏱️ ETA:</strong> ${etaMin} mins</li>
    </ul>
  `;

  document.getElementById("saveOrigin").value = originId;
  document.getElementById("saveDestination").value = destId;
  document.getElementById("saveProvider").value = providerId;
  document.getElementById("saveMode").value = mode;
  document.getElementById("saveDistance").value = distanceKm;
  document.getElementById("saveETA").value = etaMin;
}

// Auto-fade alert
setTimeout(() => {
  const alert = document.querySelector('.auto-fade');
  if(alert) alert.classList.remove('show');
}, 4000);
</script>

<?php include('footer.php'); ?>
