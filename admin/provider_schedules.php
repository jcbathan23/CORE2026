<?php
session_start();
include("../connect.php");

// Only allow logged-in providers
if (!isset($_SESSION['email']) || $_SESSION['account_type'] != 3) {
    header("Location: ../admin/loginpage.php");
    exit();
}

$email = $_SESSION['email'];
$company_name = "";
$account_status = ""; // "pending" or "active"

// Get company name and provider ID
$stmt = $conn->prepare("SELECT company_name, provider_id FROM active_service_provider WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($company_name, $provider_id);
if ($stmt->fetch()) { $account_status = "active"; }
$stmt->close();

if (empty($company_name)) {
    $stmt = $conn->prepare("SELECT company_name, provider_id FROM pending_service_provider WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($company_name, $provider_id);
    if ($stmt->fetch()) { $account_status = "pending"; }
    $stmt->close();
}

if (empty($company_name)) $company_name = "Service Provider";

// Count schedules by status
$stmt = $conn->prepare("SELECT status, COUNT(*) as total FROM schedules WHERE provider_id=? GROUP BY status");
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();
$scheduleCounts = [
    'scheduled'=>0,
    'in progress'=>0,
    'delayed'=>0,
    'completed'=>0
];
while($row = $result->fetch_assoc()){
    $status = strtolower($row['status']);
    $scheduleCounts[$status] = $row['total'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Provider Schedules</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="providerstyles.css" rel="stylesheet">
<style>
/* Modern Provider Layout */
body {
      background: white;
      min-height: 100vh;
    }


.main-content {
  margin-left: 250px;
  margin-top: 70px;
  min-height: calc(100vh - 70px);
  transition: margin-left 0.3s ease;
}

.content {
  padding: 24px;
}

/* Mobile Layout */
@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}

/* Dark Mode Support */
body.dark-mode {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
}

body.dark-mode .content {
  background: transparent;
}

body.dark-mode .table {
  background: rgba(30, 41, 59, 0.8) !important;
  color: #e2e8f0 !important;
}

body.dark-mode .table th {
  background: rgba(15, 23, 42, 0.8) !important;
  color: #f8fafc !important;
  border-color: rgba(255, 255, 255, 0.1) !important;
}

body.dark-mode .table td {
  border-color: rgba(255, 255, 255, 0.1) !important;
  color: #e2e8f0 !important;
}

body.dark-mode .btn {
  border-color: rgba(255, 255, 255, 0.2) !important;
}

body.dark-mode .card {
  background: rgba(30, 41, 59, 0.8) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #e2e8f0 !important;
}

body.dark-mode .card-header {
  background: rgba(15, 23, 42, 0.8) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

body.dark-mode .badge {
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

body.dark-mode .modal-content {
  background: rgba(30, 41, 59, 0.95) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #e2e8f0 !important;
}

body.dark-mode .modal-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

body.dark-mode .modal-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
}
</style>
</head>
<body>

<?php include('provider_sidebar.php'); ?>

<div class="main-content">
    <?php include('provider_navbar.php'); ?>

    <div class="content p-4">
    <h3 class="mb-4">My Schedules</h3>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label'=>'Scheduled','value'=>$scheduleCounts['scheduled'],'color'=>'#ffc107','icon'=>'fa-hourglass-half'],
            ['label'=>'In Progress','value'=>$scheduleCounts['in progress'],'color'=>'#0d6efd','icon'=>'fa-spinner'],
            ['label'=>'Delayed','value'=>$scheduleCounts['delayed'],'color'=>'#dc3545','icon'=>'fa-clock'],
            ['label'=>'Completed','value'=>$scheduleCounts['completed'],'color'=>'#28a745','icon'=>'fa-check-circle']
        ];
        foreach($cards as $card):
        ?>
        <div class="col-md-3">
            <div class="card shadow-sm text-center" style="border-top:4px solid <?= $card['color'] ?>; color: <?= $card['color'] ?>;">
                <div class="card-body">
                    <i class="fas <?= $card['icon'] ?> fa-2x mb-2"></i>
                    <h5 class="card-title"><?= $card['label'] ?></h5>
                    <h2><?= $card['value'] ?></h2>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Schedules Table -->
    <table class="table table-hover table-striped align-middle">
        <thead class="table-secondary">
            <tr>
                <th>ID</th>
                <th>Route</th>
                <th>SOP</th>
                <th>Date</th>
                <th>Time</th>
                <th>Rate</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
$sql = "SELECT s.schedule_id, s.sop_id, s.schedule_date, s.schedule_time, s.status, s.total_rate,
       r.route_id, r.origin_id, r.destination_id, r.carrier_type,
       sp.company_name, o.point_name AS origin_name, o.latitude AS origin_lat, o.longitude AS origin_lng,
       d.point_name AS dest_name, d.latitude AS dest_lat, d.longitude AS dest_lng,
       sop.title AS sop_title, sop.category, sop.content
FROM schedules s
JOIN routes r ON s.route_id = r.route_id
JOIN sop_documents sop ON s.sop_id = sop.sop_id
JOIN network_points o ON r.origin_id = o.point_id
JOIN network_points d ON r.destination_id = d.point_id
JOIN active_service_provider sp ON s.provider_id = sp.provider_id
WHERE s.provider_id = ? AND s.status != 'completed'
ORDER BY s.schedule_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $provider_id);
$stmt->execute();
$result = $stmt->get_result();

if($result && $result->num_rows > 0):
    while($row = $result->fetch_assoc()):
        $status = strtolower($row['status']);
        $badgeClass = match($status) {
            'scheduled' => 'bg-warning text-dark',
            'in progress' => 'bg-primary text-white',
            'delayed' => 'bg-danger text-white',
            'completed' => 'bg-success text-white',
            default => 'bg-secondary text-white',
        };
?>
<tr>
    <td><?= $row['schedule_id'] ?></td>
    <td><?= $row['route_id'] ?></td>
    <td><?= htmlspecialchars($row['sop_title']) ?></td>
    <td><?= $row['schedule_date'] ?></td>
    <td><?= $row['schedule_time'] ?></td>
    <td>₱ <?= number_format($row['total_rate'],2) ?></td>
    <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($row['status']) ?></span></td>
    <td>
        <!-- View Route Details Button -->
        <button class="btn btn-info btn-sm viewScheduleBtn"
            data-route_id="<?= $row['route_id'] ?>"
            data-origin="<?= htmlspecialchars($row['origin_name'], ENT_QUOTES) ?>"
            data-origin_lat="<?= $row['origin_lat'] ?>"
            data-origin_lng="<?= $row['origin_lng'] ?>"
            data-dest="<?= htmlspecialchars($row['dest_name'], ENT_QUOTES) ?>"
            data-dest_lat="<?= $row['dest_lat'] ?>"
            data-dest_lng="<?= $row['dest_lng'] ?>"
            data-mode="<?= htmlspecialchars($row['carrier_type'], ENT_QUOTES) ?>"
            data-schedule_id="<?= $row['schedule_id'] ?>"
            data-sop_title="<?= htmlspecialchars($row['sop_title'], ENT_QUOTES) ?>"
            data-date="<?= $row['schedule_date'] ?>"
            data-time="<?= $row['schedule_time'] ?>"
            data-total_rate="<?= $row['total_rate'] ?>">
            <i class="fas fa-eye"></i> Route
        </button>

        <!-- View SOP Button -->
        <button class="btn btn-secondary btn-sm viewSOPBtn"
            data-sop_title="<?= htmlspecialchars($row['sop_title'], ENT_QUOTES) ?>"
            data-sop_category="<?= htmlspecialchars($row['category'], ENT_QUOTES) ?>"
            data-sop_content="<?= htmlspecialchars($row['content'], ENT_QUOTES) ?>">
            <i class="fas fa-file-alt"></i> SOP
        </button>

        <!-- Status Buttons -->
        <button type="button" class="btn btn-primary btn-sm" onclick="updateScheduleStatus(<?= (int)$row['schedule_id'] ?>, 'in progress')">In Progress</button>
        <button type="button" class="btn btn-warning btn-sm" onclick="updateScheduleStatus(<?= (int)$row['schedule_id'] ?>, 'delayed')">Delayed</button>
        <button type="button" class="btn btn-success btn-sm" onclick="updateScheduleStatus(<?= (int)$row['schedule_id'] ?>, 'completed')">Completed</button>
    </td>
</tr>
<?php
    endwhile;
else:
?>
<tr>
    <td colspan="8" class="text-center">No schedules found.</td>
</tr>
<?php endif; $stmt->close(); ?>
        </tbody>
    </table>
</div>

<!-- View Schedule Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Schedule & Route Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Schedule ID:</strong> <span id="modalScheduleId"></span></p>
                        <p><strong>SOP:</strong> <span id="modalSOP"></span></p>
                        <p><strong>Date:</strong> <span id="modalDate"></span></p>
                        <p><strong>Time:</strong> <span id="modalTime"></span></p>
                        <p><strong>Rate:</strong> ₱ <span id="modalTotalRate"></span></p>
                        <p><strong>Origin:</strong> <span id="modalOrigin"></span></p>
                        <p><strong>Destination:</strong> <span id="modalDest"></span></p>
                        <p><strong>Mode:</strong> <span id="modalMode"></span></p>
                    </div>
                    <div class="col-md-8">
                        <div id="scheduleMap" style="height:500px; border:1px solid #ccc;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View SOP Modal -->
<div class="modal fade" id="viewSOPModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">SOP Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Title:</strong> <span id="modalSOPTitle"></span></p>
                <p><strong>Category:</strong> <span id="modalSOPCategory"></span></p>
                <p><strong>Content:</strong></p>
                <div id="modalSOPContent" style="white-space:pre-wrap; border:1px solid #ddd; padding:10px; border-radius:5px;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('provider_footer.php'); ?>

<script>
let mapSchedule = null;
let routeLayer = null;
let routingControl = null;

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.viewScheduleBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalScheduleId').textContent = btn.dataset.schedule_id;
            document.getElementById('modalSOP').textContent = btn.dataset.sop_title;
            document.getElementById('modalDate').textContent = btn.dataset.date;
            document.getElementById('modalTime').textContent = btn.dataset.time;
            document.getElementById('modalTotalRate').textContent = parseFloat(btn.dataset.total_rate).toFixed(2);
            document.getElementById('modalOrigin').textContent = btn.dataset.origin;
            document.getElementById('modalDest').textContent = btn.dataset.dest;
            document.getElementById('modalMode').textContent = btn.dataset.mode;

            const originLat = parseFloat(btn.dataset.origin_lat);
            const originLng = parseFloat(btn.dataset.origin_lng);
            const destLat = parseFloat(btn.dataset.dest_lat);
            const destLng = parseFloat(btn.dataset.dest_lng);
            const mode = btn.dataset.mode.toLowerCase();

            const modalEl = document.getElementById('viewScheduleModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            setTimeout(() => {
                if(!mapSchedule){
                    mapSchedule = L.map('scheduleMap').setView([originLat, originLng], 6);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(mapSchedule);
                }
                if(routingControl){ mapSchedule.removeControl(routingControl); routingControl = null; }
                if(routeLayer){ mapSchedule.removeLayer(routeLayer); routeLayer = null; }

                if(mode==='land'){
                    routingControl = L.Routing.control({
                        waypoints:[L.latLng(originLat, originLng), L.latLng(destLat, destLng)],
                        routeWhileDragging:false, draggableWaypoints:false, addWaypoints:false,
                        createMarker:()=>null, showAlternatives:false,
                        lineOptions:{styles:[{color:'blue', weight:4}]}
                    }).addTo(mapSchedule);
                    L.marker([originLat, originLng]).addTo(mapSchedule).bindPopup("Origin").openPopup();
                    L.marker([destLat, destLng]).addTo(mapSchedule).bindPopup("Destination");
                } else {
                    const color = (mode==='air')?'blue':'green';
                    routeLayer = L.polyline([[originLat, originLng],[destLat,destLng]], {color:color, dashArray:'10,10', weight:4}).addTo(mapSchedule);
                    L.marker([originLat, originLng]).addTo(mapSchedule).bindPopup("Origin").openPopup();
                    L.marker([destLat, destLng]).addTo(mapSchedule).bindPopup("Destination");
                    mapSchedule.fitBounds(routeLayer.getBounds(), {padding:[50,50]});
                }
            }, 200);
        });
    });

    document.querySelectorAll('.viewSOPBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modalSOPTitle').textContent = btn.dataset.sop_title;
            document.getElementById('modalSOPCategory').textContent = btn.dataset.sop_category;
            document.getElementById('modalSOPContent').textContent = btn.dataset.sop_content;

            const modalEl = document.getElementById('viewSOPModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });
});

async function updateScheduleStatus(scheduleId, status) {
    try {
        const response = await fetch(`../api/schedules_api.php/schedules/${scheduleId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status })
        });

        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to update schedule');
        }

        window.location.reload();
    } catch (error) {
        console.error('Error updating schedule status:', error);
        alert('Failed to update schedule status: ' + error.message);
    }
}
</script>
