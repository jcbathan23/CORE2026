<?php
require_once __DIR__ . '/auth.php';
header('Location: manage_routes.php');
exit;
?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
  }

  /* Point Type Badges */
  .point-badge{display:inline-flex;align-items:center;gap:6px;padding:0.35rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:600;letter-spacing:0.3px}
  .point-badge i{font-size:.9rem}
  .point-port{background:linear-gradient(135deg,#0d6efd,#6ea8fe);color:#fff}
  .point-airport{background:linear-gradient(135deg,#dc3545,#e35d6a);color:#fff}
  .point-warehouse{background:linear-gradient(135deg,#f39c12,#f1c40f);color:#212529}
  .point-terminal{background:linear-gradient(135deg,#6f42c1,#b07fe3);color:#fff}
</style>

<div class="content p-4">
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Network Points Management</h3>
            <p>Manage network points and their geographical locations</p>
        </div>
        <button type="button" class="btn btn-modern-primary" data-bs-toggle="modal" data-bs-target="#addPointModal">
            <i class="fas fa-plus me-2"></i>Add New Point
        </button>
    </div>

  <?php if (isset($_GET['success']) && $_GET['success'] === 'point_saved'): ?>
    <div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
      New Point Saved Successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'point_deleted'): ?>
    <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
      Network Point Deleted!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>


 <!-- Add Point Modal -->
<div class="modal fade" id="addPointModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="addPointForm" method="POST" action="save_point.php" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Add Network Point</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetForm()"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label>Name</label>
              <input type="text" name="point_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Type</label>
              <select name="point_type" class="form-control">
                <option value="Port">Port</option>
                 <option value="Airport">Airport</option>
                <option value="Warehouse">Warehouse</option>
                <option value="Terminal">Terminal</option>
              </select>
            </div>
            <div class="mb-3">
              <label>City / Address</label>
              <input type="text" name="city" id="cityInput" class="form-control" required>
            </div>
            <div class="mb-3 d-flex gap-2">
              <button type="button" class="btn btn-primary" id="searchAddressBtn">
                <i class="fa-solid fa-magnifying-glass"></i> Search Address
              </button>
              <span class="text-danger d-none" id="mapError"></span>
            </div>
            <div class="mb-3">
              <label>Latitude</label>
              <input type="text" id="latitude" name="latitude" class="form-control" readonly required>
            </div>
            <div class="mb-3">
              <label>Longitude</label>
              <input type="text" id="longitude" name="longitude" class="form-control" readonly required>
            </div>
            <div class="mb-3">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <label>Select Location on Map (Optional)</label>
            <div id="pointMap" style="height:600px; border:1px solid #ccc;"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetForm()">Cancel</button>
        <button type="submit" class="btn btn-success">Save</button>
      </div>
    </form>
  </div>
</div>

  <!-- View Modal -->
  <div class="modal fade" id="viewPointModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title">Point Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <p><strong>ID:</strong> <span id="view_id"></span></p>
              <p><strong>Name:</strong> <span id="view_name"></span></p>
              <p><strong>Type:</strong> <span id="view_type"></span></p>
              <p><strong>Exact Address:</strong> <span id="view_city"></span></p>
              <p><strong>Status:</strong> <span id="view_status"></span></p>
              <p><strong>Latitude:</strong> <span id="view_lat"></span></p>
              <p><strong>Longitude:</strong> <span id="view_lng"></span></p>
            </div>
            <div class="col-md-6">
              <div id="viewMap" style="height:600px; border:1px solid #ccc;"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Edit Point Modal -->
<div class="modal fade" id="editPointModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="editPointForm" method="POST" action="update_point.php" class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">Edit Network Point</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetEditForm()"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <input type="hidden" name="point_id" id="edit_id">
            <div class="mb-3">
              <label>Name</label>
              <input type="text" name="point_name" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Type</label>
              <select name="point_type" id="edit_type" class="form-control">
                <option value="Port">Port</option>
                <option value="Airport">Airport</option>
                <option value="Warehouse">Warehouse</option>
                <option value="Terminal">Terminal</option>
              </select>
            </div>
            <div class="mb-3">
              <label>Exact Address</label>
              <input type="text" name="city" id="edit_city" class="form-control" required>
            </div>
            <div class="mb-3 d-flex gap-2">
              <button type="button" class="btn btn-primary" id="editSearchBtn">
                <i class="fa-solid fa-magnifying-glass"></i> Search Address
              </button>
            </div>
            <div class="mb-3">
              <label>Latitude</label>
              <input type="text" name="latitude" id="edit_lat" class="form-control" readonly required>
            </div>
            <div class="mb-3">
              <label>Longitude</label>
              <input type="text" name="longitude" id="edit_lng" class="form-control" readonly required>
            </div>
            <div class="mb-3">
              <label>Status</label>
              <select name="status" id="edit_status" class="form-control">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <label>Select Location on Map</label>
            <div id="editMap" style="height:600px; border:1px solid #ccc;"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetEditForm()">Cancel</button>
        <button type="submit" class="btn btn-warning">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePointModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <form id="deletePointForm" method="POST" action="delete_point.php" class="modal-content">
      <input type="hidden" name="table" value="network_points">
      <input type="hidden" name="id" id="delete_id">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p>Are you sure you want to delete this point?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>
<!-- All Points Map -->
<div class="mb-4">
  <div id="allPointsMap" style="height:600px; border-radius: 8px; border:1px solid #ccc;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const mapContainer = document.getElementById('allPointsMap');
  if(!mapContainer) return;

  const allPointsMap = L.map('allPointsMap').setView([13.5, 122], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(allPointsMap);

  const markers = L.featureGroup().addTo(allPointsMap);

  <?php
    $result = $conn->query("SELECT * FROM network_points WHERE latitude != '' AND longitude != ''");
    while($row = $result->fetch_assoc()):
      $lat = floatval($row['latitude']);
      $lng = floatval($row['longitude']);
      $name = addslashes($row['point_name']);
      $type = addslashes($row['point_type']);
      $city = addslashes($row['city']);

      // Assign color based on point type
      switch($type) {
          case 'Port': $markerColor = 'blue'; break;
          case 'Airport': $markerColor = 'red'; break;
          case 'Warehouse': $markerColor = 'orange'; break;
          case 'Terminal': $markerColor = 'purple'; break;
          default: $markerColor = 'gray'; break;
      }
  ?>
  L.circleMarker([<?= $lat ?>, <?= $lng ?>], {
      radius: 8,
      color: '<?= $markerColor ?>',
      fillColor: '<?= $markerColor ?>',
      fillOpacity: 0.8
    })
    .bindPopup('<strong><?= $name ?></strong><br>Type: <?= $type ?><br>City: <?= $city ?>')
    .addTo(markers);
  <?php endwhile; ?>

  // Fit map to markers
  if(markers.getLayers().length) {
    allPointsMap.fitBounds(markers.getBounds().pad(0.1));
  } else {
    allPointsMap.setView([13.5, 122], 6);
  }

  // Add Legend
  const legend = L.control({position: 'bottomright'});
  legend.onAdd = function(map) {
    const div = L.DomUtil.create('div', 'info legend');
    const types = ['Port','Airport','Warehouse','Terminal'];
    const colors = ['blue','red','orange','purple'];

    let html = '<strong>Point Types</strong><br>';
    for(let i=0;i<types.length;i++){
      html += `<i style="background:${colors[i]}; width:15px; height:15px; display:inline-block; margin-right:5px;"></i> ${types[i]}<br>`;
    }
    div.innerHTML = html;
    return div;
  };
  legend.addTo(allPointsMap);
});
</script>

<style>
/* Legend styling */
.leaflet-control .legend {
  background: white;
  padding: 6px 10px;
  border-radius: 5px;
  box-shadow: 0 0 15px rgba(0,0,0,0.2);
  font-size: 14px;
  line-height: 18px;
}
</style>
                                                     

    <!-- Modern Network Points Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Name</th>
                        <th><i class="fas fa-tag me-2"></i>Type</th>
                        <th><i class="fas fa-city me-2"></i>Address</th>
                        <th><i class="fas fa-globe me-2"></i>Latitude</th>
                        <th><i class="fas fa-globe me-2"></i>Longitude</th>
                        <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                        <th class="text-center"><i class="fas fa-tools me-2"></i>Actions</th>
                    </tr>
                </thead>
    <tbody>
      <?php
      $result = $conn->query("SELECT * FROM network_points");
      while($row = $result->fetch_assoc()):
      ?>
      <tr class="modern-table-row">
        <td><span class="fw-medium"><?= $row['point_id'] ?></span></td>
        <td>
            <div class="contact-info">
                <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                <span class="fw-medium"><?= htmlspecialchars($row['point_name'], ENT_QUOTES) ?></span>
            </div>
        </td>
        <td>
            <?php 
              $ptype = strtolower(trim($row['point_type']));
              $cls = 'point-badge ';
              if ($ptype === 'port') $cls .= 'point-port';
              elseif ($ptype === 'airport') $cls .= 'point-airport';
              elseif ($ptype === 'warehouse') $cls .= 'point-warehouse';
              elseif ($ptype === 'terminal') $cls .= 'point-terminal';
              else $cls .= 'point-distribution';
            ?>
            <span class="<?= $cls ?>">
                <i class="fas fa-tag me-1"></i>
                <?= htmlspecialchars(strtoupper($row['point_type']), ENT_QUOTES) ?>
            </span>
        </td>
        <td><?= htmlspecialchars($row['city'], ENT_QUOTES) ?></td>
        <td><code class="code-display"><?= $row['latitude'] ?></code></td>
        <td><code class="code-display"><?= $row['longitude'] ?></code></td>
        <td>
            <span class="modern-badge <?= $row['status'] === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                <i class="fas fa-<?= $row['status'] === 'Active' ? 'check-circle' : 'pause-circle' ?> me-1"></i>
                <?= $row['status'] ?>
            </span>
        </td>
        <td class="text-center">
            <div class="action-buttons">
                <button type="button" class="btn btn-modern-view viewBtn" data-bs-toggle="modal" data-bs-target="#viewPointModal"
                  data-id="<?= $row['point_id'] ?>"
                  data-name="<?= htmlspecialchars($row['point_name'], ENT_QUOTES) ?>"
                  data-type="<?= htmlspecialchars($row['point_type'], ENT_QUOTES) ?>"
                  data-city="<?= htmlspecialchars($row['city'], ENT_QUOTES) ?>"
                  data-lat="<?= $row['latitude'] ?>"
                  data-lng="<?= $row['longitude'] ?>"
                  data-status="<?= htmlspecialchars($row['status'], ENT_QUOTES) ?>"
                  title="View Point Details">
                  <i class="fas fa-eye"></i>
                </button>
                <a href="#" class="btn btn-modern-edit editBtn" data-bs-toggle="modal" data-bs-target="#editPointModal"
                   data-id="<?= $row['point_id'] ?>"
                   data-name="<?= htmlspecialchars($row['point_name'], ENT_QUOTES) ?>"
                   data-type="<?= htmlspecialchars($row['point_type'], ENT_QUOTES) ?>"
                   data-city="<?= htmlspecialchars($row['city'], ENT_QUOTES) ?>"
                   data-lat="<?= $row['latitude'] ?>"
                   data-lng="<?= $row['longitude'] ?>"
                   data-status="<?= htmlspecialchars($row['status'], ENT_QUOTES) ?>"
                   title="Edit Point">
                   <i class="fas fa-edit"></i>
                </a>
                <a href="#" class="btn btn-modern-delete deleteBtn" 
                   data-id="<?= $row['point_id'] ?>"
                   title="Delete Point">
                   <i class="fas fa-trash-alt"></i>
                </a>
            </div>
        </td>
      </tr>
      <?php endwhile; ?>
            </tbody>
            </table>
        </div>
    </div>

<style>
.table-responsive {
  margin-top: 15px;
}
.table th, .table td {
  vertical-align: middle;
  padding: 0.65rem 0.75rem;
}
.table-hover tbody tr:hover {
  background-color: #f1f7ff;
}
.badge {
  font-size: 0.85rem;
  padding: 0.45em 0.65em;
}
.btn-group .btn {
  transition: 0.2s;
}
.btn-group .btn:hover {
  transform: scale(1.05);
}
.text-primary {
  font-weight: 500;
}
</style>

</div>

<?php include('footer.php'); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const $ = id => document.getElementById(id);

  let mapAdd = null, markerAdd = null;
  const addModal = $('addPointModal');
  const cityInput = $('cityInput');
  const searchBtn = $('searchAddressBtn');
  const latInput = $('latitude');
  const lngInput = $('longitude');
  const addForm = $('addPointForm');

  window.resetForm = function() {
    cityInput.value = '';
    latInput.value = '';
    lngInput.value = '';
    if(markerAdd) {
      mapAdd.removeLayer(markerAdd);
      markerAdd = null;
    }
  }

  if(addModal) {
    addModal.addEventListener('shown.bs.modal', function() {
      if(!mapAdd) {
        mapAdd = L.map('pointMap').setView([13.5, 122], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapAdd);

        // Click to pin and update address
        mapAdd.on('click', function(e) {
          const lat = e.latlng.lat;
          const lng = e.latlng.lng;

          if(markerAdd) mapAdd.removeLayer(markerAdd);
          markerAdd = L.marker([lat, lng]).addTo(mapAdd);

          latInput.value = lat.toFixed(6);
          lngInput.value = lng.toFixed(6);

          // Reverse geocode
          fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
              if(data.address) {
                let address = '';
                if(data.address.city) address += data.address.city;
                else if(data.address.town) address += data.address.town;
                else if(data.address.village) address += data.address.village;
                if(data.address.state) address += (address ? ', ' : '') + data.address.state;
                if(data.address.country) address += (address ? ', ' : '') + data.address.country;

                cityInput.value = address;
              } else {
                cityInput.value = '';
              }
            })
            .catch(err => console.error(err));
        });
      }
      setTimeout(() => mapAdd.invalidateSize(), 200);
    });

    // Search address button
    searchBtn.addEventListener('click', function() {
      const address = cityInput.value.trim();
      if(!address) return;

      fetch(`geocode.php?address=${encodeURIComponent(address)}`)
        .then(res => res.json())
        .then(data => {
          const validLat = !isNaN(parseFloat(data.lat));
          const validLng = !isNaN(parseFloat(data.lon));

          if(validLat && validLng) {
            latInput.value = parseFloat(data.lat).toFixed(6);
            lngInput.value = parseFloat(data.lon).toFixed(6);

            if(markerAdd) mapAdd.removeLayer(markerAdd);
            markerAdd = L.marker([data.lat, data.lon]).addTo(mapAdd);
            mapAdd.setView([data.lat, data.lon], 12);
          } else {
            latInput.value = '';
            lngInput.value = '';
            if(markerAdd) {
              mapAdd.removeLayer(markerAdd);
              markerAdd = null;
            }
          }
        })
        .catch(err => console.error(err));
    });

    // Ensure latest marker coords are captured before submit
    addForm.addEventListener('submit', function(e) {
      if(markerAdd) {
        latInput.value = markerAdd.getLatLng().lat.toFixed(6);
        lngInput.value = markerAdd.getLatLng().lng.toFixed(6);
      }
    });
  }
});
// VIEW MAP
let mapView = null, markerView = null;
let clickedBtn = null;
const viewModal = document.getElementById('viewPointModal');

// Capture which button was clicked
document.body.addEventListener('click', function(e) {
  const btn = e.target.closest('.viewBtn');
  if(btn) clickedBtn = btn;
});

if(viewModal) {
  viewModal.addEventListener('shown.bs.modal', function() {
    if(!clickedBtn) return;

    document.getElementById('view_id').textContent = clickedBtn.dataset.id || '';
    document.getElementById('view_name').textContent = clickedBtn.dataset.name || '';
    document.getElementById('view_type').textContent = clickedBtn.dataset.type || '';
    document.getElementById('view_city').textContent = clickedBtn.dataset.city || '';
    document.getElementById('view_lat').textContent = clickedBtn.dataset.lat || '';
    document.getElementById('view_lng').textContent = clickedBtn.dataset.lng || '';
    document.getElementById('view_status').textContent = clickedBtn.dataset.status || '';

    const lat = parseFloat(clickedBtn.dataset.lat);
    const lng = parseFloat(clickedBtn.dataset.lng);
    const hasCoords = !isNaN(lat) && !isNaN(lng);

    // Initialize map only once
    if(!mapView) {
      const center = hasCoords ? [lat,lng] : [13.5,122];
      mapView = L.map('viewMap').setView(center, hasCoords ? 12 : 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(mapView);
    }

    // Remove previous marker
    if(markerView) mapView.removeLayer(markerView);

    if(hasCoords) {
      markerView = L.marker([lat,lng]).addTo(mapView);
      mapView.setView([lat,lng], 12);
    } else {
      mapView.setView([13.5,122],6);
    }

    setTimeout(()=>mapView.invalidateSize(),200);
    clickedBtn = null;
  });

  viewModal.addEventListener('hidden.bs.modal', function() {
    if(markerView) {
      mapView.removeLayer(markerView);
      markerView = null;
    }
  });
}
// EDIT MAP
let mapEdit = null, markerEdit = null;
const editModal = document.getElementById('editPointModal');

window.resetEditForm = function() {
  document.getElementById('editPointForm').reset();
  if(markerEdit) {
    mapEdit.removeLayer(markerEdit);
    markerEdit = null;
  }
};

document.querySelectorAll('.editBtn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.getElementById('edit_id').value = btn.dataset.id;
    document.getElementById('edit_name').value = btn.dataset.name;
    document.getElementById('edit_type').value = btn.dataset.type;
    document.getElementById('edit_city').value = btn.dataset.city;
    document.getElementById('edit_lat').value = btn.dataset.lat;
    document.getElementById('edit_lng').value = btn.dataset.lng;
    document.getElementById('edit_status').value = btn.dataset.status;

    const lat = parseFloat(btn.dataset.lat);
    const lng = parseFloat(btn.dataset.lng);
    const hasCoords = !isNaN(lat) && !isNaN(lng);

    if(!mapEdit) {
      mapEdit = L.map('editMap').setView(hasCoords ? [lat,lng] : [13.5,122], hasCoords ? 12 : 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(mapEdit);

      mapEdit.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        if(markerEdit) mapEdit.removeLayer(markerEdit);
        markerEdit = L.marker([lat,lng]).addTo(mapEdit);

        document.getElementById('edit_lat').value = lat.toFixed(6);
        document.getElementById('edit_lng').value = lng.toFixed(6);

        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
          .then(res => res.json())
          .then(data => {
            let address = '';
            if(data.address) {
              if(data.address.city) address += data.address.city;
              else if(data.address.town) address += data.address.town;
              else if(data.address.village) address += data.address.village;
              if(data.address.state) address += (address ? ', ' : '') + data.address.state;
              if(data.address.country) address += (address ? ', ' : '') + data.address.country;
            }
            document.getElementById('edit_city').value = address;
          });
      });
    }

    if(markerEdit) mapEdit.removeLayer(markerEdit);
    if(hasCoords) {
      markerEdit = L.marker([lat,lng]).addTo(mapEdit);
      mapEdit.setView([lat,lng],12);
    } else {
      mapEdit.setView([13.5,122],6);
    }

    setTimeout(()=>mapEdit.invalidateSize(),200);
  });
});

// SEARCH BUTTON IN EDIT MODAL
document.getElementById('editSearchBtn').addEventListener('click', function() {
  const address = document.getElementById('edit_city').value.trim();
  if(!address) return;

  fetch(`geocode.php?address=${encodeURIComponent(address)}`)
    .then(res => res.json())
    .then(data => {
      const lat = parseFloat(data.lat);
      const lng = parseFloat(data.lon);
      if(!isNaN(lat) && !isNaN(lng)) {
        document.getElementById('edit_lat').value = lat.toFixed(6);
        document.getElementById('edit_lng').value = lng.toFixed(6);

        if(markerEdit) mapEdit.removeLayer(markerEdit);
        markerEdit = L.marker([lat,lng]).addTo(mapEdit);
        mapEdit.setView([lat,lng],12);
      }
    });
});

// DELETE BUTTON FUNCTIONALITY
const deleteModalEl = document.getElementById('deletePointModal');
let deleteIdInput = document.getElementById('delete_id');

document.querySelectorAll('.deleteBtn').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const pointId = this.dataset.id;
    deleteIdInput.value = pointId;

    // Show modal
    const deleteModal = new bootstrap.Modal(deleteModalEl);
    deleteModal.show();
  });
});

</script>