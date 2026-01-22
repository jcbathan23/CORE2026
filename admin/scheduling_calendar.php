<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';

include('header.php');
include('sidebar.php');
include('navbar.php');

$allowedStatuses = ['pending', 'scheduled', 'in progress', 'delayed', 'completed', 'cancelled'];

$sql = "SELECT s.schedule_id, s.route_id, s.rate_id, s.provider_id, s.sop_id,
               s.schedule_date, s.schedule_time, s.created_at, s.status, s.total_rate,
               sp.company_name,
               r.carrier_type, r.origin_id, r.destination_id,
               o.point_name AS origin_name,
               d.point_name AS destination_name,
               sop.title AS sop_title
        FROM schedules s
        LEFT JOIN active_service_provider sp ON s.provider_id = sp.provider_id
        LEFT JOIN routes r ON s.route_id = r.route_id
        LEFT JOIN network_points o ON r.origin_id = o.point_id
        LEFT JOIN network_points d ON r.destination_id = d.point_id
        LEFT JOIN sop_documents sop ON s.sop_id = sop.sop_id
        ORDER BY
            CASE WHEN LOWER(s.status) = 'completed' THEN 0 ELSE 1 END,
            s.schedule_date DESC,
            s.schedule_time DESC,
            s.schedule_id DESC";

$result = mysqli_query($conn, $sql);
$schedules = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['status_norm'] = strtolower(trim((string)($row['status'] ?? '')));
        if ($row['status_norm'] === '') { $row['status_norm'] = 'scheduled'; }
        $schedules[] = $row;
    }
}

function badgeClass($statusNorm) {
    return match ($statusNorm) {
        'completed' => 'bg-success text-white',
        'in progress' => 'bg-primary text-white',
        'delayed' => 'bg-warning text-dark',
        'cancelled' => 'bg-danger text-white',
        'pending' => 'bg-secondary text-white',
        'scheduled' => 'bg-info text-white',
        default => 'bg-dark text-white',
    };
}

function eventColor($statusNorm) {
    return match ($statusNorm) {
        'completed' => '#bbf7d0',
        'cancelled' => '#dc2626',
        'in progress' => '#2563eb',
        'delayed' => '#f59e0b',
        'pending' => '#64748b',
        'scheduled' => '#0ea5e9',
        default => '#334155',
    };
}

$calendarEvents = [];
foreach ($schedules as $s) {
    $start = null;
    if (!empty($s['schedule_date']) && !empty($s['schedule_time'])) {
        $start = $s['schedule_date'] . 'T' . $s['schedule_time'];
    }

    $origin = $s['origin_name'] ?? '';
    $dest = $s['destination_name'] ?? '';
    $titleParts = [];
    $titleParts[] = 'Schedule #' . $s['schedule_id'];
    if ($origin !== '' && $dest !== '') {
        $titleParts[] = $origin . ' → ' . $dest;
    }

    $calendarEvents[] = [
        'id' => (string)$s['schedule_id'],
        'title' => implode(' - ', $titleParts),
        'start' => $start,
        'backgroundColor' => eventColor($s['status_norm']),
        'borderColor' => eventColor($s['status_norm']),
        'textColor' => ($s['status_norm'] === 'completed') ? '#065f46' : '#ffffff',
        'classNames' => [($s['status_norm'] === 'completed') ? 'fc-event-completed' : ''],
        'extendedProps' => [
            'status' => $s['status_norm'],
            'route_id' => $s['route_id'],
            'provider' => $s['company_name'] ?? 'N/A',
            'origin' => $origin ?: 'N/A',
            'destination' => $dest ?: 'N/A',
            'mode' => $s['carrier_type'] ?? 'N/A',
            'sop' => $s['sop_title'] ?? 'N/A',
            'total_rate' => $s['total_rate'] ?? null,
            'date' => $s['schedule_date'] ?? null,
            'time' => $s['schedule_time'] ?? null,
        ],
    ];
}

?>

<link rel="stylesheet" href="modern-table-styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
  .calendar-inner {
    padding: 16px;
  }

  /* Schedules table header: gray background with readable text */
  .schedule-table.modern-table thead {
    background: #e5e7eb !important;
    background-image: none !important;
    color: #111827 !important;
  }
  .schedule-table.modern-table thead tr {
    background: #e5e7eb !important;
    background-image: none !important;
  }
  .schedule-table.modern-table thead th {
    background: #e5e7eb !important;
    background-image: none !important;
    color: #111827 !important;
    border-bottom-color: rgba(17,24,39,0.12) !important;
  }
  body.dark-mode .schedule-table.modern-table thead {
    background: #374151 !important;
    background-image: none !important;
    color: #f9fafb !important;
  }
  body.dark-mode .schedule-table.modern-table thead tr {
    background: #374151 !important;
    background-image: none !important;
  }
  body.dark-mode .schedule-table.modern-table thead th {
    background: #374151 !important;
    background-image: none !important;
    color: #f9fafb !important;
    border-bottom-color: rgba(255,255,255,0.14) !important;
  }

  /* Schedule details modal header: gray background with readable text */
  #scheduleDetailsModal .modal-header {
    background: #e5e7eb;
    border-bottom: 1px solid rgba(17,24,39,0.12);
  }
  #scheduleDetailsModal .modal-header .modal-title {
    color: #111827;
    font-weight: 700;
  }
  #scheduleDetailsModal .modal-header .btn-close {
    filter: none;
    opacity: 1;
  }
  body.dark-mode #scheduleDetailsModal .modal-header {
    background: #374151;
    border-bottom: 1px solid rgba(255,255,255,0.14);
  }
  body.dark-mode #scheduleDetailsModal .modal-header .modal-title {
    color: #f9fafb;
  }
  body.dark-mode #scheduleDetailsModal .modal-header .btn-close {
    filter: invert(1) grayscale(100%);
    opacity: 0.9;
  }

  .fc .fc-event.fc-event-completed,
  .fc .fc-event.fc-event-completed .fc-event-main {
    background-color: #bbf7d0 !important;
    border-color: #86efac !important;
    color: #065f46 !important;
  }
  .fc .fc-event.fc-event-completed .fc-event-time,
  .fc .fc-event.fc-event-completed .fc-event-title {
    color: #065f46 !important;
    font-weight: 700;
  }

  /* FullCalendar controls (match modern module look) */
  .fc .fc-toolbar-title { font-weight: 700; letter-spacing: .2px; }
  .fc .fc-button {
    border-radius: 999px !important;
    border: 1px solid rgba(15,23,42,0.12) !important;
    background: rgba(255,255,255,0.9) !important;
    color: #0f172a !important;
    box-shadow: 0 6px 14px rgba(2,6,23,0.08);
    padding: 0.4rem 0.75rem;
    font-weight: 600;
  }
  .fc .fc-button:hover {
    background: rgba(79,110,247,0.10) !important;
    border-color: rgba(79,110,247,0.30) !important;
  }
  .fc .fc-button.fc-button-active,
  .fc .fc-button:active {
    background: rgba(79,110,247,0.20) !important;
    border-color: rgba(79,110,247,0.45) !important;
  }
  .fc .fc-daygrid-day.fc-day-today {
    background: rgba(79,110,247,0.08) !important;
  }

  body.dark-mode .fc,
  body.dark-mode .fc .fc-toolbar-title,
  body.dark-mode .fc .fc-col-header-cell-cushion,
  body.dark-mode .fc .fc-daygrid-day-number {
    color: #e6edf3 !important;
  }
  body.dark-mode .fc .fc-button {
    background: rgba(15,23,42,0.55) !important;
    color: #e6edf3 !important;
    border-color: rgba(255,255,255,0.14) !important;
    box-shadow: none;
  }
  body.dark-mode .fc .fc-button:hover {
    background: rgba(148,163,184,0.18) !important;
    border-color: rgba(148,163,184,0.26) !important;
  }
  body.dark-mode .fc .fc-scrollgrid,
  body.dark-mode .fc .fc-scrollgrid td,
  body.dark-mode .fc .fc-scrollgrid th {
    border-color: rgba(255,255,255,0.10) !important;
  }
</style>

<div class="content p-4">
  <div class="modern-header d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="mb-1">Scheduling & Calendar Management</h3>
    </div>
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" id="toggleCompletedInCalendar" checked>
      <label class="form-check-label" for="toggleCompletedInCalendar">Show completed in calendar</label>
    </div>
  </div>

  <div class="modern-table-container mb-4">
    <div class="calendar-inner">
      <div id="scheduleCalendar"></div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Filter Deliveries</h5>
          <div class="row g-3">
            <div class="col-md-6 col-lg-4">
              <label class="form-label">Search</label>
              <input id="scheduleSearch" type="text" class="form-control" placeholder="Search schedule, route, provider, origin, destination">
            </div>
            <div class="col-md-6 col-lg-4">
              <label class="form-label">Status</label>
              <select id="statusFilter" class="form-control">
                <option value="">All statuses</option>
                <?php foreach ($allowedStatuses as $st): ?>
                  <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars(ucwords($st)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modern-table-container">
    <div class="table-responsive">
      <table class="table modern-table schedule-table">
      <thead>
        <tr>
          <th>Schedule ID</th>
          <th>Route</th>
          <th>Provider</th>
          <th>Origin</th>
          <th>Destination</th>
          <th>Mode</th>
          <th>SOP</th>
          <th>Date</th>
          <th>Time</th>
          <th>Total Rate</th>
          <th>Status</th>
          <th>View</th>
        </tr>
      </thead>
      <tbody id="scheduleTableBody">
        <?php if (count($schedules) === 0): ?>
          <tr><td colspan="12" class="text-center">No schedules found.</td></tr>
        <?php else: ?>
          <?php foreach ($schedules as $s): ?>
            <?php
              $stNorm = $s['status_norm'];
              $badge = badgeClass($stNorm);
              $scheduleId = (int)$s['schedule_id'];
              $routeId = $s['route_id'] !== null ? (int)$s['route_id'] : null;
            ?>
            <tr class="schedule-row" data-status="<?= htmlspecialchars($stNorm) ?>" data-search="<?= htmlspecialchars(strtolower(
                'schedule '.$scheduleId.' route '.($routeId ?? '').' '.($s['company_name'] ?? '').' '.($s['origin_name'] ?? '').' '.($s['destination_name'] ?? '')
              ), ENT_QUOTES) ?>">
              <td><strong>#<?= $scheduleId ?></strong></td>
              <td><?= $routeId ? ('#'.$routeId) : 'N/A' ?></td>
              <td><?= htmlspecialchars($s['company_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($s['origin_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($s['destination_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($s['carrier_type'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($s['sop_title'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($s['schedule_date'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($s['schedule_time'] ?? 'N/A') ?></td>
              <td><?= $s['total_rate'] !== null ? ('₱ ' . number_format((float)$s['total_rate'], 2)) : 'N/A' ?></td>
              <td><span class="badge <?= $badge ?>"><?= htmlspecialchars(ucwords($stNorm)) ?></span></td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary viewScheduleBtn"
                  data-schedule_id="<?= $scheduleId ?>"
                  data-status="<?= htmlspecialchars($stNorm, ENT_QUOTES) ?>"
                  data-route_id="<?= htmlspecialchars((string)($routeId ?? ''), ENT_QUOTES) ?>"
                  data-provider_id="<?= htmlspecialchars((string)($s['provider_id'] ?? ''), ENT_QUOTES) ?>"
                  data-provider="<?= htmlspecialchars((string)($s['company_name'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-origin="<?= htmlspecialchars((string)($s['origin_name'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-destination="<?= htmlspecialchars((string)($s['destination_name'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-mode="<?= htmlspecialchars((string)($s['carrier_type'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-sop="<?= htmlspecialchars((string)($s['sop_title'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-date="<?= htmlspecialchars((string)($s['schedule_date'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-time="<?= htmlspecialchars((string)($s['schedule_time'] ?? 'N/A'), ENT_QUOTES) ?>"
                  data-total_rate="<?= htmlspecialchars((string)($s['total_rate'] ?? ''), ENT_QUOTES) ?>">
                  Details
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="scheduleDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Schedule Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><div class="text-muted">Schedule</div><div id="mdScheduleId" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Status</div><div id="mdStatus" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Route</div><div id="mdRoute" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Provider</div><div id="mdProvider" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Origin</div><div id="mdOrigin" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Destination</div><div id="mdDestination" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Mode</div><div id="mdMode" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">SOP</div><div id="mdSop" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Date</div><div id="mdDate" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Time</div><div id="mdTime" class="fw-semibold"></div></div>
          <div class="col-md-6"><div class="text-muted">Total Rate</div><div id="mdRate" class="fw-semibold"></div></div>
        </div>

        <hr>
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="fw-semibold">Scheduling AI</div>
          <button type="button" class="btn btn-sm btn-secondary" id="runSchedulingAiBtn">
            <i class="fas fa-robot me-1"></i>AI Conflict Check
          </button>
        </div>
        <div id="schedulingAiResult"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
  const calendarEvents = <?php echo json_encode($calendarEvents, JSON_UNESCAPED_SLASHES); ?>;

  function normalize(s){ return (s || '').toString().trim().toLowerCase(); }

  function applyTableFilters() {
    const q = normalize(document.getElementById('scheduleSearch').value);
    const st = normalize(document.getElementById('statusFilter').value);

    document.querySelectorAll('#scheduleTableBody .schedule-row').forEach(row => {
      const rowSt = normalize(row.dataset.status);
      const rowText = normalize(row.dataset.search);

      const okStatus = !st || rowSt === st;
      const okSearch = !q || rowText.includes(q);

      row.classList.toggle('d-none', !(okStatus && okSearch));
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('scheduleDetailsModal');
    const modal = new bootstrap.Modal(modalEl);

    let currentScheduleDetails = null;

    function openDetails(details) {
      currentScheduleDetails = details;
      document.getElementById('mdScheduleId').textContent = '#' + (details.schedule_id || '');
      document.getElementById('mdStatus').textContent = (details.status || '').toString();
      document.getElementById('mdRoute').textContent = details.route_id ? ('#' + details.route_id) : 'N/A';
      document.getElementById('mdProvider').textContent = details.provider || 'N/A';
      document.getElementById('mdOrigin').textContent = details.origin || 'N/A';
      document.getElementById('mdDestination').textContent = details.destination || 'N/A';
      document.getElementById('mdMode').textContent = details.mode || 'N/A';
      document.getElementById('mdSop').textContent = details.sop || 'N/A';
      document.getElementById('mdDate').textContent = details.date || 'N/A';
      document.getElementById('mdTime').textContent = details.time || 'N/A';
      if (details.total_rate !== undefined && details.total_rate !== null && details.total_rate !== '') {
        const r = parseFloat(details.total_rate);
        document.getElementById('mdRate').textContent = isNaN(r) ? 'N/A' : ('₱ ' + r.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
      } else {
        document.getElementById('mdRate').textContent = 'N/A';
      }

      const aiEl = document.getElementById('schedulingAiResult');
      if (aiEl) aiEl.innerHTML = '';
      modal.show();
    }

    document.querySelectorAll('.viewScheduleBtn').forEach(btn => {
      btn.addEventListener('click', function() {
        openDetails({
          schedule_id: btn.dataset.schedule_id,
          status: btn.dataset.status,
          route_id: btn.dataset.route_id,
          provider_id: btn.dataset.provider_id,
          provider: btn.dataset.provider,
          origin: btn.dataset.origin,
          destination: btn.dataset.destination,
          mode: btn.dataset.mode,
          sop: btn.dataset.sop,
          date: btn.dataset.date,
          time: btn.dataset.time,
          total_rate: btn.dataset.total_rate,
        });
      });
    });

    const runAiBtn = document.getElementById('runSchedulingAiBtn');
    if (runAiBtn) {
      runAiBtn.addEventListener('click', async function() {
        const aiEl = document.getElementById('schedulingAiResult');
        if (aiEl) {
          aiEl.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div><div class="mt-2">Running AI conflict check...</div></div>';
        }

        const d = currentScheduleDetails || {};
        const providerId = parseInt(d.provider_id || '0', 10);
        const routeId = parseInt(d.route_id || '0', 10);
        const scheduleId = parseInt(d.schedule_id || '0', 10);
        const scheduleDate = (d.date || '').toString();
        const scheduleTime = (d.time || '').toString();

        if (!providerId || !routeId || !scheduleDate || !scheduleTime) {
          if (aiEl) aiEl.innerHTML = '<div class="alert alert-warning mb-0">Missing provider/route/date/time to run AI check.</div>';
          return;
        }

        try {
          const resp = await fetch('../api/ai/scheduling_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              provider_id: providerId,
              route_id: routeId,
              schedule_date: scheduleDate,
              schedule_time: scheduleTime,
              exclude_schedule_id: scheduleId
            })
          });

          const data = await resp.json().catch(() => null);
          if (!resp.ok || !data || !data.success) {
            const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'AI check failed.';
            if (aiEl) aiEl.innerHTML = `<div class="alert alert-danger mb-0">${msg}</div>`;
            return;
          }

          if (aiEl) {
            aiEl.innerHTML = `
              <div class="mb-2"><strong>Schedule Conflict:</strong> ${data.schedule_conflict ? 'YES' : 'NO'}</div>
              <div class="mb-2"><strong>SLA Risk:</strong> ${data.sla_risk}</div>
              <div class="mb-0"><strong>Optimization:</strong><div class="mt-1">${(data.optimization || '').toString()}</div></div>
            `;
          }
        } catch (e) {
          if (aiEl) aiEl.innerHTML = '<div class="alert alert-danger mb-0">Network error while calling AI check.</div>';
        }
      });
    }

    document.getElementById('scheduleSearch').addEventListener('input', applyTableFilters);
    document.getElementById('statusFilter').addEventListener('change', applyTableFilters);

    let showCompleted = true;

    const calendarEl = document.getElementById('scheduleCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      events: function(fetchInfo, successCallback) {
        const filtered = calendarEvents.filter(e => {
          const st = normalize(e.extendedProps && e.extendedProps.status);
          if (!showCompleted && st === 'completed') return false;
          return true;
        });
        successCallback(filtered);
      },
      eventClick: function(info) {
        const e = info.event;
        const p = e.extendedProps || {};
        openDetails({
          schedule_id: e.id,
          status: p.status,
          route_id: p.route_id,
          provider: p.provider,
          origin: p.origin,
          destination: p.destination,
          mode: p.mode,
          sop: p.sop,
          date: p.date,
          time: p.time,
          total_rate: p.total_rate,
        });
      },
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: true }
    });

    calendar.render();

    const toggle = document.getElementById('toggleCompletedInCalendar');
    toggle.addEventListener('change', function() {
      showCompleted = !!toggle.checked;
      calendar.refetchEvents();
    });
  });
</script>

<?php include('footer.php'); ?>
