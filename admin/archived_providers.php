<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
  /* Page-specific override: remove background from the H3 title */
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important; /* keep text color consistent with theme */
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
            <h3 class="mb-1">Archived Service Providers</h3>
            <p>View and manage archived service providers</p>
        </div>
        <a href="active_providers.php" class="btn btn-modern-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to Active Providers
        </a>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'unarchived'): ?>
            <div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
                Service Provider Restored Successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'unarchive_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Failed to restore service provider. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'invalid_id'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Invalid provider ID. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'no_rows_affected'): ?>
            <div class="alert alert-warning alert-dismissible fade show auto-fade" role="alert">
                Provider not found or already active. Please refresh the page.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modern Archived Providers Table -->
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
                        <th><i class="fas fa-calendar me-2"></i>Date Approved</th>
                        <th class="text-center"><i class="fas fa-tools me-2"></i>Actions</th>
                    </tr>
                </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM active_service_provider WHERE status = 'Archived' ORDER BY date_approved DESC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
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
                            <span class="modern-badge badge-archived">
                                <i class="fas fa-archive me-1"></i>
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['date_approved'])) ?></td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <!-- View Button -->
                                <button class="btn btn-modern-view viewBtn" data-id="<?= $row['provider_id'] ?>" data-bs-toggle="modal" data-bs-target="#viewProviderModal" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <!-- Unarchive Button -->
                                <button class="btn btn-modern-success unarchiveBtn" 
                                        onclick="unarchiveProvider(<?= $row['provider_id'] ?>, '<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>')"
                                        title="Restore Provider">
                                    <i class="fas fa-undo"></i>
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
                                <i class="fas fa-archive"></i>
                                <h5>No Archived Providers</h5>
                                <p>There are no archived service providers at this time.</p>
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
});

// Unarchive Provider Function using SweetAlert
function unarchiveProvider(providerId, companyName) {
    Swal.fire({
        title: 'Restore Service Provider',
        html: `
            <div class="text-center mb-3">
                <i class="fas fa-undo fa-3x text-success mb-3"></i>
                <h5>Restore Service Provider</h5>
            </div>
            <p>Are you sure you want to restore <strong>${companyName}</strong>?</p>
            <div class="alert alert-info text-start">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Restored providers will be moved back to the active providers list and will be available for scheduling again.
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-undo me-1"></i>Yes, Restore',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
        customClass: {
            popup: 'swal2-popup-custom'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'unarchive_provider.php';
            
            const providerIdInput = document.createElement('input');
            providerIdInput.type = 'hidden';
            providerIdInput.name = 'provider_id';
            providerIdInput.value = providerId;
            
            const unarchiveInput = document.createElement('input');
            unarchiveInput.type = 'hidden';
            unarchiveInput.name = 'unarchive_provider';
            unarchiveInput.value = '1';
            
            form.appendChild(providerIdInput);
            form.appendChild(unarchiveInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<?php include('footer.php'); ?>
