<?php
include('header.php');
include('sidebar.php');
include('navbar.php');
?>

<link rel="stylesheet" href="modern-table-styles.css">

<style>
  .content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
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

  /* Checklist Styles */
  .checklist-container {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
  }
  
  .checklist-container .form-check {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px 15px;
    transition: all 0.2s ease;
  }
  
  .checklist-container .form-check:hover {
    border-color: #28a745;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.1);
  }
  
  .checklist-container .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
  }
  
  .checklist-container .form-check-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    user-select: none;
  }
  
  .checklist-container .form-check-input:checked ~ .form-check-label {
    color: #28a745;
    font-weight: 600;
  }
</style>

<div class="content p-4">
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Pending Service Providers</h3>
            <p>Review and manage pending service provider registrations</p>
        </div>
    </div>

    <!-- Success/Failure Alerts -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'rejected_provider'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                Service Provider Rejected!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'approved_provider'): ?>
            <div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
                Service Provider Approved!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modern Pending Providers Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-building me-2"></i>Company Name</th>
                        <th><i class="fas fa-user me-2"></i>Contact Person</th>
                        <th><i class="fas fa-envelope me-2"></i>Email</th>
                        <th><i class="fas fa-phone me-2"></i>Phone</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Address</th>
                        <th><i class="fas fa-cogs me-2"></i>Services</th>
                        <th><i class="fas fa-calendar me-2"></i>Created</th>
                        <th class="text-center"><i class="fas fa-tools me-2"></i>Actions</th>
                    </tr>
                </thead>
            <tbody>
            <?php
            $query = "SELECT * FROM pending_service_provider";
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
                <tr class="modern-table-row">
                    <td><span class="fw-medium"><?= $row['registration_id'] ?></span></td>
                    <td>
                        <div class="company-name">
                            <i class="fas fa-building me-2 text-muted"></i>
                            <span class="fw-medium"><?= htmlspecialchars($row['company_name']) ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="contact-info">
                            <i class="fas fa-user-circle me-2 text-muted"></i>
                            <span><?= htmlspecialchars($row['contact_person']) ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="user-email">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            <span class="fw-medium"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['contact_number']) ?></td>
                    <td class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($row['address']) ?>">
                        <?= htmlspecialchars($row['address']) ?>
                    </td>
                    <td>
                        <?php
                            $services = array_filter(array_map('trim', preg_split('/[,&]|\n|\r/',$row['services'])));
                            foreach ($services as $svc) {
                                $label = strtoupper($svc);
                                $cls = 'service-badge ';
                                if ($label === 'LAND') $cls .= 'service-land';
                                elseif ($label === 'AIR') $cls .= 'service-air';
                                elseif ($label === 'SEA') $cls .= 'service-sea';
                                else $cls .= 'service-land';
                                echo '<span class="' . $cls . '"><i class="fas fa-cog"></i>' . htmlspecialchars($label) . '</span>';
                            }
                        ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($row['date_submitted'] ?? 'now')) ?></td>
                    <td class="text-center">
                        <div class="action-buttons">
                            <!-- View -->
                            <button class="btn btn-modern-view viewBtn" data-id="<?= $row['registration_id'] ?>" data-bs-toggle="modal" data-bs-target="#viewProviderModal" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>

                            <!-- Approve -->
                            <button class="btn btn-modern-success approve-btn" 
                                    data-id="<?= $row['registration_id'] ?>" 
                                    data-company="<?= htmlspecialchars($row['company_name']) ?>" 
                                    title="Approve Provider">
                                <i class="fas fa-check-circle"></i>
                            </button>

                            <!-- Reject -->
                            <button class="btn btn-modern-delete reject-btn" 
                                    data-id="<?= $row['registration_id'] ?>" 
                                    data-company="<?= htmlspecialchars($row['company_name']) ?>" 
                                    title="Reject Provider">
                                <i class="fas fa-times-circle"></i>
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
                            <i class="fas fa-inbox"></i>
                            <h5>No Pending Providers</h5>
                            <p>There are no pending service provider registrations at this time.</p>
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

    <!-- Logistic1 Service Providers Section -->
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">LOGISTIC1 SERVICE PROVIDERS</h3>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary" id="refreshLogistic1Btn">
                    <i class="fas fa-sync-alt"></i> Refresh Data
                </button>
                <button type="button" class="btn btn-info" id="searchLogistic1Btn" data-bs-toggle="modal" data-bs-target="#searchModal">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>

        <!-- Loading and Status Messages -->
        <div id="logistic1Status" class="mb-3" style="display: none;">
            <!-- Status messages will be displayed here -->
        </div>

        <!-- Statistics Cards -->
        <div id="logistic1Stats" class="row mb-4" style="display: none;">
            <!-- Statistics cards will be displayed here -->
        </div>

        <!-- Logistic1 Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="logistic1Table">
                <thead class="table-info">
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Hub Location</th>
                        <th>Service Areas</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="logistic1TableBody">
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            <div class="py-4">
                                <i class="fas fa-cloud-download-alt fa-2x mb-2"></i><br>
                                Click "Refresh Data" to load service providers from Logistic1
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Search Logistic1 Providers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="searchQuery" class="form-label">Search Query</label>
                        <input type="text" class="form-control" id="searchQuery" placeholder="Enter company name, location, service type, etc.">
                        <div class="form-text">Search across company names, locations, services, and contact information</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="performSearchBtn">Search</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logistic1 Provider Details Modal -->
    <div class="modal fade" id="logistic1ProviderModal" tabindex="-1" aria-labelledby="logistic1ProviderLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="logistic1ModalContent">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content">
                <form id="approveForm" action="#" method="POST">
                    <input type="hidden" name="registration_id" id="approveProviderId">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="approveModalLabel">Approve Service Provider</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center mb-3">Are you sure you want to approve <strong id="approveCompanyName"></strong>?</p>
                        
                        <h6 class="text-primary mb-3"><i class="fas fa-clipboard-check me-2"></i>Approval Checklist</h6>
                        
                        <div class="checklist-container">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkBusinessPermit" required>
                                <label class="form-check-label" for="checkBusinessPermit">
                                    <i class="fas fa-file-contract me-1 text-muted"></i>Business Permit Verified
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkIsoCertified">
                                <label class="form-check-label" for="checkIsoCertified">
                                    <i class="fas fa-certificate me-1 text-muted"></i>ISO Certificate Verified (if applicable)
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkCompanyProfile" required>
                                <label class="form-check-label" for="checkCompanyProfile">
                                    <i class="fas fa-building me-1 text-muted"></i>Company Profile Complete
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkContactInfo" required>
                                <label class="form-check-label" for="checkContactInfo">
                                    <i class="fas fa-phone me-1 text-muted"></i>Contact Information Verified
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkServices" required>
                                <label class="form-check-label" for="checkServices">
                                    <i class="fas fa-cogs me-1 text-muted"></i>Services Offered Reviewed
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="checkAddress" required>
                                <label class="form-check-label" for="checkAddress">
                                    <i class="fas fa-map-marker-alt me-1 text-muted"></i>Address Verified
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="checkCompliance" required>
                                <label class="form-check-label" for="checkCompliance">
                                    <i class="fas fa-shield-alt me-1 text-muted"></i>Compliance Requirements Met
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Please ensure all requirements are verified before approving this service provider.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm" id="approveSubmitBtn">
                            <i class="fas fa-check-circle me-1"></i>Approve Provider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content">
                <form id="rejectForm" action="#" method="POST">
                    <input type="hidden" name="registration_id" id="rejectProviderId">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Service Provider</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-center mb-3">Are you sure you want to reject <strong id="rejectCompanyName"></strong>?</p>
                        
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">
                                <i class="fas fa-exclamation-triangle me-1"></i>Rejection Reason
                            </label>
                            <select class="form-select" id="rejectionReason" name="rejection_reason" required>
                                <option value="">Select a reason...</option>
                                <option value="missing_requirements">Missing Requirements</option>
                                <option value="incomplete_documents">Incomplete Documents</option>
                                <option value="invalid_business_permit">Invalid Business Permit</option>
                                <option value="incomplete_profile">Incomplete Company Profile</option>
                                <option value="invalid_contact_info">Invalid Contact Information</option>
                                <option value="non_compliance">Non-Compliance with Standards</option>
                                <option value="duplicate_application">Duplicate Application</option>
                                <option value="other">Other (please specify)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rejectRemarks" class="form-label">
                                <i class="fas fa-comment me-1"></i>Detailed Remarks
                            </label>
                            <textarea class="form-control" id="rejectRemarks" name="remarks" rows="4" 
                                      placeholder="Provide detailed explanation for rejection..." required></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                This information will be sent to the service provider as notification.
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Important:</strong> The service provider will be notified with the rejection reason and remarks.
                            This action cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm" id="rejectSubmitBtn">
                            <i class="fas fa-times-circle me-1"></i>Reject Provider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewProviderModal" tabindex="-1" aria-labelledby="viewProviderLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="modalContent">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Simple view button functionality for local providers
    const buttons = document.querySelectorAll(".viewBtn");
    buttons.forEach(btn => {
        btn.addEventListener("click", function () {
            const providerId = this.getAttribute("data-id");
            fetch("get_provider_modal.php?id=" + providerId)
                .then(response => response.text())
                .then(html => { 
                    document.getElementById("modalContent").innerHTML = html; 
                })
                .catch(err => { 
                    document.getElementById("modalContent").innerHTML = "<div class='modal-body'><div class='alert alert-danger'>Failed to load details.</div></div>"; 
                });
        });
    });

    // Approve button functionality
    const approveButtons = document.querySelectorAll(".approve-btn");
    approveButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const providerId = this.getAttribute("data-id");
            const companyName = this.getAttribute("data-company");
            
            // Set the modal data
            document.getElementById("approveProviderId").value = providerId;
            document.getElementById("approveCompanyName").textContent = companyName;
            
            // Show the modal
            const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
            approveModal.show();
        });
    });

    // Reject button functionality
    const rejectButtons = document.querySelectorAll(".reject-btn");
    rejectButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const providerId = this.getAttribute("data-id");
            const companyName = this.getAttribute("data-company");
            
            // Set the modal data
            document.getElementById("rejectProviderId").value = providerId;
            document.getElementById("rejectCompanyName").textContent = companyName;
            
            // Show the modal
            const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
            rejectModal.show();
        });
    });

    // Form submission handling with loading states
    document.getElementById('approveForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Validate checklist
        const requiredCheckboxes = ['checkBusinessPermit', 'checkCompanyProfile', 'checkContactInfo', 'checkServices', 'checkAddress', 'checkCompliance'];
        const allChecked = requiredCheckboxes.every(id => document.getElementById(id).checked);
        
        if (!allChecked) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Checklist Incomplete',
                text: 'Please complete all required checklist items before approving.',
                confirmButtonColor: '#28a745'
            });
            return;
        }
        
        const submitBtn = document.getElementById('approveSubmitBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Approving...';
        submitBtn.disabled = true;

        const providerId = document.getElementById('approveProviderId').value;

        fetch(`../api/providers_api.php/pending/${providerId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Approval failed');
            }

            bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Approved',
                text: 'Service Provider Approved!',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(err => {
            submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Approve Provider';
            submitBtn.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'Approval Failed',
                text: err.message || 'Approval failed',
                confirmButtonColor: '#dc3545'
            });
        });
    });

    document.getElementById('rejectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const remarksEl = document.getElementById('rejectRemarks');
        const reasonEl = document.getElementById('rejectionReason');
        
        if (!reasonEl.value || reasonEl.value.trim().length === 0) {
            e.preventDefault();
            reasonEl.classList.add('is-invalid');
            reasonEl.focus();
            return;
        }
        
        if (!remarksEl.value || remarksEl.value.trim().length === 0) {
            e.preventDefault();
            remarksEl.classList.add('is-invalid');
            remarksEl.focus();
            return;
        }

        const submitBtn = document.getElementById('rejectSubmitBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Rejecting...';
        submitBtn.disabled = true;

        const providerId = document.getElementById('rejectProviderId').value;

        fetch(`../api/providers_api.php/pending/${providerId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                rejection_reason: reasonEl.value,
                remarks: remarksEl.value
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Rejection failed');
            }

            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Rejected',
                text: 'Service Provider Rejected!',
                confirmButtonColor: '#dc3545'
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(err => {
            submitBtn.innerHTML = '<i class="fas fa-times-circle me-1"></i>Reject Provider';
            submitBtn.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'Rejection Failed',
                text: err.message || 'Rejection failed',
                confirmButtonColor: '#dc3545'
            });
        });
    });

    // Reset modal states when they're hidden
    document.getElementById('approveModal').addEventListener('hidden.bs.modal', function () {
        const submitBtn = document.getElementById('approveSubmitBtn');
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Approve';
        submitBtn.disabled = false;
    });

    document.getElementById('rejectModal').addEventListener('hidden.bs.modal', function () {
        const submitBtn = document.getElementById('rejectSubmitBtn');
        submitBtn.innerHTML = '<i class="fas fa-times-circle me-1"></i>Reject';
        submitBtn.disabled = false;
    });

    // Logistic1 API Integration
    const logistic1API = {
        baseUrl: '../api/core2_pull_service_providers.php',
        
        showStatus: function(message, type = 'info') {
            const statusDiv = document.getElementById('logistic1Status');
            statusDiv.style.display = 'block';
            statusDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        },
        
        showLoading: function(message = 'Loading data from Logistic1...') {
            this.showStatus(`<i class="fas fa-spinner fa-spin"></i> ${message}`, 'info');
        },
        
        hideStatus: function() {
            const statusDiv = document.getElementById('logistic1Status');
            statusDiv.style.display = 'none';
        },
        
        loadProviders: function() {
            this.showLoading('Fetching service providers from Logistic1...');
            
            fetch(this.baseUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.displayProviders(data.data);
                        this.loadStats();
                        this.showStatus(`Successfully loaded ${data.count} service providers from Logistic1`, 'success');
                        setTimeout(() => this.hideStatus(), 3000);
                    } else {
                        this.showStatus(`Error: ${data.message || data.error}`, 'danger');
                        this.displayError(data.message || 'Failed to load providers');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showStatus('Network error: Unable to connect to pull script', 'danger');
                    this.displayError('Network connection failed');
                });
        },
        
        loadStats: function() {
            fetch(this.baseUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Calculate stats from the data
                        const providers = data.data;
                        const stats = {
                            total_service_providers: providers.length,
                            service_providers_only: providers.filter(p => p.type === 'service_provider').length,
                            both_types: providers.filter(p => p.type === 'both').length,
                            approved_count: providers.filter(p => p.status === 'active' || p.status === '1').length
                        };
                        this.displayStats(stats);
                    }
                })
                .catch(error => {
                    console.error('Stats error:', error);
                });
        }, 
        
        displayProviders: function(providers) {
            const tbody = document.getElementById('logistic1TableBody');
            
            if (!providers || providers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No service providers found in Logistic1</td></tr>';
                return;
            }
            
            tbody.innerHTML = providers.map(provider => `
                <tr>
                    <td>${provider.id}</td>
                    <td><strong class="text-primary">${this.escapeHtml(provider.name || provider.supplier_name || 'N/A')}</strong></td>
                    <td>${this.escapeHtml(provider.contact || provider.contact_person || 'N/A')}</td>
                    <td>${this.escapeHtml(provider.email || 'N/A')}</td>
                    <td>${this.escapeHtml(provider.phone || 'N/A')}</td>
                    <td>
                        <span class="badge ${(provider.type || provider.supplier_type) === 'service_provider' ? 'bg-success' : 'bg-secondary'}">
                            ${this.escapeHtml(provider.type || provider.supplier_type || 'N/A')}
                        </span>
                    </td>
                    <td>${this.escapeHtml(provider.hub_location || 'N/A')}</td>
                    <td>${this.escapeHtml(provider.service_areas || 'N/A')}</td>
                    <td><small class="text-muted">${this.formatDate(provider.created_at)}</small></td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm me-1 logistic1ViewBtn" data-id="${provider.id}" data-bs-toggle="modal" data-bs-target="#logistic1ProviderModal">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-success btn-sm logistic1ImportBtn" data-id="${provider.id}" title="Import to Local Database">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            // Add event listeners for the new buttons
            this.attachEventListeners();
        },
        
        displayError: function(message) {
            const tbody = document.getElementById('logistic1TableBody');
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                ${message}<br>
                <button class="btn btn-outline-primary btn-sm mt-2" onclick="logistic1API.loadProviders()">Try Again</button>
            </td></tr>`;
        },
        
        attachEventListeners: function() {
            // View buttons
            document.querySelectorAll('.logistic1ViewBtn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const providerId = e.currentTarget.getAttribute('data-id');
                    this.viewProvider(providerId);
                });
            });
            
            // Import buttons
            document.querySelectorAll('.logistic1ImportBtn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const providerId = e.currentTarget.getAttribute('data-id');
                    this.importProvider(providerId, e.currentTarget);
                });
            });
        },
        
        viewProvider: function(providerId) {
            this.showLoading('Loading provider details...');
            
            fetch(this.baseUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Find the provider by ID
                        const provider = data.data.find(p => p.id == providerId);
                        if (provider) {
                            this.displayProviderModal(provider);
                            this.hideStatus();
                        } else {
                            this.showStatus(`Provider with ID ${providerId} not found`, 'danger');
                        }
                    } else {
                        this.showStatus(`Error: ${data.message || data.error}`, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showStatus('Failed to load provider details', 'danger');
                });
        },
        
        displayProviderModal: function(provider) {
            const modalContent = document.getElementById('logistic1ModalContent');
            modalContent.innerHTML = `
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-building me-2"></i>${this.escapeHtml(provider.name || provider.supplier_name || 'Unknown Provider')}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-info-circle"></i> Basic Information</h6>
                            <table class="table table-sm">
                                <tr><th>ID:</th><td>${provider.id}</td></tr>
                                <tr><th>Company Name:</th><td>${this.escapeHtml(provider.name || provider.supplier_name || 'N/A')}</td></tr>
                                <tr><th>Contact Person:</th><td>${this.escapeHtml(provider.contact || provider.contact_person || 'N/A')}</td></tr>
                                <tr><th>Email:</th><td>${this.escapeHtml(provider.email || 'N/A')}</td></tr>
                                <tr><th>Phone:</th><td>${this.escapeHtml(provider.phone || 'N/A')}</td></tr>
                                <tr><th>Type:</th><td><span class="badge ${(provider.type || provider.supplier_type) === 'service_provider' ? 'bg-success' : 'bg-secondary'}">${this.escapeHtml(provider.type || provider.supplier_type || 'N/A')}</span></td></tr>
                                <tr><th>Status:</th><td><span class="badge ${(provider.status === 'active' || provider.status === '1') ? 'bg-success' : 'bg-warning'}">${this.escapeHtml(provider.status || 'Unknown')}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Location & Services</h6>
                            <table class="table table-sm">
                                <tr><th>Hub Location:</th><td>${this.escapeHtml(provider.hub_location || 'N/A')}</td></tr>
                                <tr><th>Service Areas:</th><td>${this.escapeHtml(provider.service_areas || 'N/A')}</td></tr>
                                <tr><th>Facility Type:</th><td>${this.escapeHtml(provider.facility_type || 'N/A')}</td></tr>
                                <tr><th>Service Capabilities:</th><td>${this.escapeHtml(provider.service_capabilities || 'N/A')}</td></tr>
                                <tr><th>Created:</th><td>${this.formatDate(provider.created_at)}</td></tr>
                            </table>
                        </div>
                    </div>
                    ${provider._raw ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary"><i class="fas fa-code"></i> Raw Data (Debug)</h6>
                            <details>
                                <summary class="btn btn-sm btn-outline-secondary">Show Raw API Data</summary>
                                <pre class="mt-2 bg-light p-2 rounded" style="font-size: 12px; max-height: 200px; overflow-y: auto;">${JSON.stringify(provider._raw, null, 2)}</pre>
                            </details>
                        </div>
                    </div>
                    ` : ''}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="logistic1API.importProvider(${provider.id}, this)">
                        <i class="fas fa-download"></i> Import to Local Database
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            `;
        },
        
        importProvider: function(providerId, buttonElement) {
            // Find the provider data first
            fetch(this.baseUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const provider = data.data.find(p => p.id == providerId);
                        if (provider) {
                            this.performImport(provider, buttonElement);
                        } else {
                            this.showStatus(`Provider with ID ${providerId} not found`, 'danger');
                        }
                    } else {
                        this.showStatus(`Error loading provider data: ${data.message || data.error}`, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showStatus('Failed to load provider data for import', 'danger');
                });
        },
        
        performImport: function(provider, buttonElement) {
            if (buttonElement) {
                const originalText = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
                buttonElement.disabled = true;
            }
            
            this.showStatus('Importing provider to local database...', 'info');
            
            fetch('../api/import_provider.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    provider: provider
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showStatus(`Successfully imported "${data.company_name}" to pending providers!`, 'success');
                    
                    if (buttonElement) {
                        buttonElement.innerHTML = '<i class="fas fa-check"></i> Imported';
                        buttonElement.classList.remove('btn-success');
                        buttonElement.classList.add('btn-secondary');
                        buttonElement.disabled = true;
                    }
                    
                    // Refresh the main pending providers table
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                } else {
                    this.showStatus(`Import failed: ${data.message || data.error}`, 'danger');
                    
                    if (buttonElement) {
                        buttonElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Failed';
                        buttonElement.classList.remove('btn-success');
                        buttonElement.classList.add('btn-danger');
                        buttonElement.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error('Import error:', error);
                this.showStatus('Network error during import', 'danger');
                
                if (buttonElement) {
                    buttonElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-danger');
                    buttonElement.disabled = false;
                }
            });
        },
        
        searchProviders: function(query) {
            this.showLoading(`Searching for "${query}"...`);
            
            fetch(this.baseUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Filter the data locally
                        const searchTerm = query.toLowerCase();
                        const matchingProviders = data.data.filter(provider => {
                            const searchFields = [
                                provider.name || '',
                                provider.contact || '',
                                provider.email || '',
                                provider.phone || '',
                                provider.hub_location || '',
                                provider.service_areas || '',
                                provider.service_capabilities || '',
                                provider.facility_type || '',
                                provider.type || ''
                            ];
                            
                            return searchFields.some(field => 
                                field.toLowerCase().includes(searchTerm)
                            );
                        });
                        
                        this.displayProviders(matchingProviders);
                        this.showStatus(`Found ${matchingProviders.length} providers matching "${query}"`, 'success');
                        setTimeout(() => this.hideStatus(), 3000);
                    } else {
                        this.showStatus(`Search error: ${data.message || data.error}`, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showStatus('Search failed: Network error', 'danger');
                });
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        formatDate: function(dateString) {
            if (!dateString) return 'N/A';
            try {
                return new Date(dateString).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (e) {
                return dateString;
            }
        }
    };
    
    // Event listeners for Logistic1 functionality
    document.getElementById('refreshLogistic1Btn').addEventListener('click', () => {
        logistic1API.loadProviders();
    });
    
    document.getElementById('performSearchBtn').addEventListener('click', () => {
        const query = document.getElementById('searchQuery').value.trim();
        if (query) {
            logistic1API.searchProviders(query);
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
            modal.hide();
        } else {
            alert('Please enter a search query');
        }
    });
    
    // Allow search on Enter key
    document.getElementById('searchQuery').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('performSearchBtn').click();
        }
    });
    
    // Make logistic1API globally accessible for onclick handlers
    window.logistic1API = logistic1API;
});
</script>

<style>
.table-hover tbody tr:hover {
    background-color: #f1f3f5;
    transition: background-color 0.2s;
}
.btn-sm {
    border-radius: 6px;
}
.badge {
    padding: 0.45em 0.75em;
    font-size: 0.85rem;
}

/* Modal improvements */
.modal-backdrop {
    z-index: 1040;
}

.modal {
    z-index: 1050;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px 12px 0 0;
}

.modal-footer {
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 12px 12px;
}

/* Button loading state */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Prevent modal conflicts */
#approveModal .modal-dialog,
#rejectModal .modal-dialog {
    margin: 1.75rem auto;
}
</style>

<?php include('footer.php'); ?>
