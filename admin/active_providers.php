<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<?php
// Function to get count of archived providers
function getArchivedProvidersCount($conn) {
    $query = "SELECT COUNT(*) as count FROM active_service_provider WHERE status = 'Archived'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Function to check if provider is available today
function isProviderAvailableToday($conn, $provider_id) {
    // Get current date and time in Philippines timezone
    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    $current_datetime = date('Y-m-d H:i:s');
    
    // Check if provider is active
    $provider_query = "SELECT status FROM active_service_provider WHERE provider_id = ? AND status = 'Active'";
    $provider_stmt = $conn->prepare($provider_query);
    $provider_stmt->bind_param("i", $provider_id);
    $provider_stmt->execute();
    $provider_result = $provider_stmt->get_result();
    
    if ($provider_result->num_rows === 0) {
        return [
            'available' => false,
            'reason' => 'Provider is not active',
            'status' => 'inactive'
        ];
    }
    
    // Check for scheduled deliveries today
    $schedule_query = "
        SELECT s.schedule_id, s.schedule_date, s.schedule_time, s.status, r.carrier_type
        FROM schedules s
        JOIN routes r ON s.route_id = r.route_id
        WHERE s.provider_id = ? 
        AND s.schedule_date = ?
        AND s.status IN ('scheduled', 'in_progress')
        ORDER BY s.schedule_time ASC
    ";
    
    $schedule_stmt = $conn->prepare($schedule_query);
    $schedule_stmt->bind_param("is", $provider_id, $today);
    $schedule_stmt->execute();
    $schedule_result = $schedule_stmt->get_result();
    
    $active_schedules = [];
    while ($schedule = $schedule_result->fetch_assoc()) {
        $active_schedules[] = $schedule;
    }
    
    // Check for ongoing routes (in progress)
    $ongoing_query = "
        SELECT COUNT(*) as ongoing_count
        FROM schedules s
        WHERE s.provider_id = ?
        AND s.status = 'in_progress'
    ";
    
    $ongoing_stmt = $conn->prepare($ongoing_query);
    $ongoing_stmt->bind_param("i", $provider_id);
    $ongoing_stmt->execute();
    $ongoing_result = $ongoing_stmt->get_result();
    $ongoing_data = $ongoing_result->fetch_assoc();
    
    // Determine availability status
    if ($ongoing_data['ongoing_count'] > 0) {
        return [
            'available' => false,
            'reason' => 'Currently on delivery',
            'status' => 'busy',
            'schedules' => $active_schedules
        ];
    }
    
    if (count($active_schedules) > 0) {
        // Check if current time conflicts with scheduled times
        $has_conflict = false;
        $next_schedule = null;
        
        foreach ($active_schedules as $schedule) {
            $schedule_time = $schedule['schedule_time'];
            $schedule_datetime = $today . ' ' . $schedule_time;
            
            // Consider a 2-hour window around scheduled time as busy
            $start_busy = date('H:i:s', strtotime($schedule_time . ' -1 hour'));
            $end_busy = date('H:i:s', strtotime($schedule_time . ' +1 hour'));
            
            if ($current_time >= $start_busy && $current_time <= $end_busy) {
                $has_conflict = true;
                break;
            }
            
            // Find next upcoming schedule
            if ($schedule_time > $current_time && ($next_schedule === null || $schedule_time < $next_schedule['schedule_time'])) {
                $next_schedule = $schedule;
            }
        }
        
        if ($has_conflict) {
            return [
                'available' => false,
                'reason' => 'Scheduled delivery in progress',
                'status' => 'scheduled',
                'schedules' => $active_schedules
            ];
        }
        
        return [
            'available' => true,
            'reason' => 'Available with scheduled deliveries later',
            'status' => 'available_scheduled',
            'schedules' => $active_schedules,
            'next_schedule' => $next_schedule
        ];
    }
    
    // No schedules today - fully available
    return [
        'available' => true,
        'reason' => 'Fully available today',
        'status' => 'available',
        'schedules' => []
    ];
}

// Function to get availability badge HTML
function getAvailabilityBadge($availability) {
    $status = $availability['status'];
    $reason = $availability['reason'];
    
    switch ($status) {
        case 'available':
            return '<span class="modern-badge badge-available" title="' . htmlspecialchars($reason) . '">
                        <i class="fas fa-check-circle me-1"></i>Available
                    </span>';
        
        case 'available_scheduled':
            $next_time = isset($availability['next_schedule']) ? date('g:i A', strtotime($availability['next_schedule']['schedule_time'])) : '';
            $tooltip = $reason . ($next_time ? ' (Next: ' . $next_time . ')' : '');
            return '<span class="modern-badge badge-available-scheduled" title="' . htmlspecialchars($tooltip) . '">
                        <i class="fas fa-clock me-1"></i>Available
                    </span>';
        
        case 'scheduled':
            return '<span class="modern-badge badge-scheduled" title="' . htmlspecialchars($reason) . '">
                        <i class="fas fa-truck me-1"></i>On Delivery
                    </span>';
        
        case 'busy':
            return '<span class="modern-badge badge-busy" title="' . htmlspecialchars($reason) . '">
                        <i class="fas fa-shipping-fast me-1"></i>Busy
                    </span>';
        
        case 'inactive':
        default:
            return '<span class="modern-badge badge-inactive" title="' . htmlspecialchars($reason) . '">
                        <i class="fas fa-pause-circle me-1"></i>Inactive
                    </span>';
    }
}
?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
  /* Page-specific override: remove background from the H3 title */
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important; /* keep text color consistent with theme */
  }
  
  /* Availability Badge Styles */
  .badge-available {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
  }
  
  .badge-available-scheduled {
    background: linear-gradient(135deg, #17a2b8, #20c997);
    color: white;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
  }
  
  .badge-scheduled {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: #212529;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
  }
  
  .badge-busy {
    background: linear-gradient(135deg, #dc3545, #e83e8c);
    color: white;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
  }
  
  .availability-column {
    min-width: 120px;
  }

  /* Service Type Badges */
  .service-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    margin: 2px 4px 2px 0;
  }
  .service-badge i { font-size: 0.8rem; }
  .service-land { background: linear-gradient(135deg, #198754, #20c997); color: #fff; }
  .service-air  { background: linear-gradient(135deg, #0d6efd, #6ea8fe); color: #fff; }
  .service-sea  { background: linear-gradient(135deg, #0aa2c0, #20c997); color: #fff; }
</style>

<div class="content p-4">
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Active Service Providers</h3>
            <p>Manage approved and active service providers in the system</p>
        </div>
        <a href="archived_providers.php" class="btn btn-outline-dark btn-sm d-flex align-items-center">
            <i class="fas fa-archive me-4"></i>Archived Providers
            <span class="badge bg-dark ms-2"><?= getArchivedProvidersCount($conn) ?></span>
        </a>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
                Service Provider Details Updated!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'archived'): ?>
            <div class="alert alert-warning alert-dismissible fade show auto-fade" role="alert">
                Service Provider Archived Successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Service Provider Deleted!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'archive_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Failed to archive service provider. Please try again.
                <?php if (isset($_GET['sql_error'])): ?>
                    <br><small>SQL Error: <?= htmlspecialchars($_GET['sql_error']) ?></small>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'invalid_id'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Invalid provider ID. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'db_connection'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Database connection error. Please contact administrator.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'prepare_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Database query preparation failed. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'no_rows_affected'): ?>
            <div class="alert alert-warning alert-dismissible fade show auto-fade" role="alert">
                Provider not found or already archived. Please refresh the page.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'no_post_data'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Invalid request. Please use the archive button.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modern Active Providers Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-building me-2"></i>Company Name</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Address</th>
                        <th><i class="fas fa-user me-2"></i>Contact Person</th>
                        <th><i class="fas fa-phone me-2"></i>Contact Number</th>
                        <th><i class="fas fa-cogs me-2"></i>Services Offered</th>
                        <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                        <th class="availability-column"><i class="fas fa-calendar-check me-2"></i>Available Today</th>
                        <th class="text-center"><i class="fas fa-tools me-2"></i>Actions</th>
                    </tr>
                </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM active_service_provider WHERE status != 'Archived'";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        // Get availability status for this provider
                        $availability = isProviderAvailableToday($conn, $row['provider_id']);
                ?>
                    <tr class="modern-table-row">
                        <td><span class="fw-medium"><?= $row['provider_id'] ?></span></td>
                        <td>
                            <div class="company-name">
                                <i class="fas fa-building me-2 text-muted"></i>
                                <span class="fw-medium"><?= htmlspecialchars($row['company_name']) ?></span>
                            </div>
                        </td>
                        <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['address']) ?>">
                            <?= htmlspecialchars($row['address']) ?>
                        </td>
                        <td>
                            <div class="contact-info">
                                <i class="fas fa-user-circle me-2 text-muted"></i>
                                <span><?= htmlspecialchars($row['contact_person']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['contact_number']) ?></td>
                        <td>
                            <?php
                                $services = array_filter(array_map('trim', preg_split('/[,&]|\n|\r/',$row['services'])));
                                foreach ($services as $svc) {
                                    $label = strtoupper($svc);
                                    $cls = 'service-badge ';
                                    if ($label === 'LAND') $cls .= 'service-land';
                                    elseif ($label === 'AIR') $cls .= 'service-air';
                                    elseif ($label === 'SEA') $cls .= 'service-sea';
                                    else $cls .= 'badge-service-provider';
                                    echo '<span class="' . $cls . '"><i class="fas fa-cog"></i>' . htmlspecialchars($label) . '</span>';
                                }
                            ?>
                        </td>
                        <td>
                            <span class="modern-badge <?= $row['status'] === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                                <i class="fas fa-<?= $row['status'] === 'Active' ? 'check-circle' : 'pause-circle' ?> me-1"></i>
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?= getAvailabilityBadge($availability) ?>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <!-- View Button -->
                                <button class="btn btn-modern-view viewBtn" data-id="<?= $row['provider_id'] ?>" data-bs-toggle="modal" data-bs-target="#viewProviderModal" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <button class="btn btn-sm btn-secondary aiProviderBtn"
                                        data-provider_id="<?= $row['provider_id'] ?>"
                                        data-company="<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>"
                                        title="AI Review">
                                    <i class="fas fa-robot"></i>
                                </button>

                                <!-- Archive Button -->
                                <button class="btn btn-sm btn-warning archiveBtn" 
                                        data-id="<?= $row['provider_id'] ?>"
                                        data-company="<?= htmlspecialchars($row['company_name'])?>"
                                        onclick="archiveProvider(<?= $row['provider_id'] ?>, '<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>')"
                                        title="Archive Provider"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="empty-state">
                                <i class="fas fa-building"></i>
                                <h5>No Active Providers</h5>
                                <p>There are no active service providers in the system at this time.</p>
                            </div>
                        </td>
                    </tr>
                <?php
                endif;
                $conn->close();
                ?>
            </tbody>
            </table>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewProviderModal" tabindex="-1" aria-labelledby="viewProviderLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="modalContent">
                <!-- Dynamic content loaded via fetch -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="providerAiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="providerAiTitle">Provider AI Review</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="providerAiBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- JS for dynamic modal content -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // View Button
    document.querySelectorAll(".viewBtn").forEach(btn => {
        btn.addEventListener("click", function () {
            const providerId = this.getAttribute("data-id");
            fetch("get_provider_modal1.php?id=" + providerId)
                .then(res => res.text())
                .then(html => {
                    document.getElementById("modalContent").innerHTML = html;
                })
                .catch(() => {
                    document.getElementById("modalContent").innerHTML = "<div class='modal-body'><div class='alert alert-danger'>Failed to load details.</div></div>";
                });
        });
    });

    document.querySelectorAll('.aiProviderBtn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const providerId = parseInt(this.dataset.provider_id || '0', 10);
            const company = this.dataset.company || '';
            const modalEl = document.getElementById('providerAiModal');
            const modal = new bootstrap.Modal(modalEl);

            const titleEl = document.getElementById('providerAiTitle');
            if (titleEl) titleEl.textContent = company ? ('Provider AI Review - ' + company) : 'Provider AI Review';

            const bodyEl = document.getElementById('providerAiBody');
            if (bodyEl) {
                bodyEl.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div><div class="mt-2">Running AI review...</div></div>';
            }
            modal.show();

            if (!providerId) {
                if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Missing provider ID.</div>';
                return;
            }

            try {
                const resp = await fetch('../api/ai/provider_ai.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ provider_id: providerId })
                });

                const data = await resp.json().catch(() => null);
                if (!resp.ok || !data || !data.success) {
                    const msg = (data && (data.message || data.error)) ? (data.message || data.error) : 'AI review failed.';
                    if (bodyEl) bodyEl.innerHTML = `<div class="alert alert-danger mb-0">${msg}</div>`;
                    return;
                }

                const redFlags = Array.isArray(data.red_flags) ? data.red_flags : [];
                if (bodyEl) {
                    bodyEl.innerHTML = `
                        <div class="mb-2"><strong>Provider Score:</strong> ${data.provider_score}</div>
                        <div class="mb-2"><strong>Recommendation:</strong> ${data.recommendation}</div>
                        <div class="mb-2"><strong>Risk Level:</strong> ${data.risk_level}</div>
                        <div class="mb-3"><strong>Notes:</strong><div class="mt-1">${(data.notes || '').toString()}</div></div>
                        <div><strong>Red Flags:</strong>
                            ${redFlags.length ? `<ul class="mb-0">${redFlags.map(x => `<li>${(x || '').toString()}</li>`).join('')}</ul>` : '<div class="text-muted">None</div>'}
                        </div>
                    `;
                }
            } catch (e) {
                if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Network error while calling AI review.</div>';
            }
        });
    });
});

// Archive Provider Function using SweetAlert
function archiveProvider(providerId, companyName) {
    Swal.fire({
        title: 'Archive Service Provider',
        html: `
            <div class="text-center mb-3">
                <i class="fas fa-archive fa-3x text-warning mb-3"></i>
                <h5>Archive Service Provider</h5>
            </div>
            <p>Are you sure you want to archive <strong>${companyName}</strong>?</p>
            <div class="alert alert-info text-start">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Archived providers will be moved to the archived section and will no longer appear in the active providers list. This action can be reversed if needed.
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fd7e14',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-archive me-1"></i>Yes, Archive',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
        customClass: {
            popup: 'swal2-popup-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'archive_provider.php';
            
            const providerIdInput = document.createElement('input');
            providerIdInput.type = 'hidden';
            providerIdInput.name = 'provider_id';
            providerIdInput.value = providerId;
            
            const archiveInput = document.createElement('input');
            archiveInput.type = 'hidden';
            archiveInput.name = 'archive_provider';
            archiveInput.value = '1';
            
            form.appendChild(providerIdInput);
            form.appendChild(archiveInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include('footer.php'); ?>
  