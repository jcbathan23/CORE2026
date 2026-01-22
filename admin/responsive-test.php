<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<div class="content p-4">
    <!-- Header Section -->
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Responsive Design Test</h3>
            <p>Testing mobile responsiveness across different screen sizes</p>
        </div>
    </div>

    <!-- Responsive Grid Test -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="modern-card p-3">
                <h5><i class="fas fa-mobile-alt me-2"></i>Mobile</h5>
                <p class="mb-0">≤ 576px</p>
                <small class="text-muted">Portrait phones</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="modern-card p-3">
                <h5><i class="fas fa-tablet-alt me-2"></i>Tablet</h5>
                <p class="mb-0">577px - 768px</p>
                <small class="text-muted">Landscape phones, tablets</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="modern-card p-3">
                <h5><i class="fas fa-laptop me-2"></i>Desktop</h5>
                <p class="mb-0">769px - 1200px</p>
                <small class="text-muted">Small desktops</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="modern-card p-3">
                <h5><i class="fas fa-desktop me-2"></i>Large</h5>
                <p class="mb-0">≥ 1201px</p>
                <small class="text-muted">Large desktops</small>
            </div>
        </div>
    </div>

    <!-- Responsive Table Test -->
    <div class="modern-table-container mb-4">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                        <th><i class="fas fa-user me-2"></i>Name</th>
                        <th><i class="fas fa-envelope me-2"></i>Email</th>
                        <th><i class="fas fa-phone me-2"></i>Phone</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Location</th>
                        <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                        <th class="text-center"><i class="fas fa-tools me-2"></i>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="modern-table-row">
                        <td data-label="ID"><span class="fw-medium">001</span></td>
                        <td data-label="Name">
                            <div class="contact-info">
                                <i class="fas fa-user-circle me-2 text-muted"></i>
                                <span>John Doe</span>
                            </div>
                        </td>
                        <td data-label="Email">john.doe@example.com</td>
                        <td data-label="Phone">+1 (555) 123-4567</td>
                        <td data-label="Location">New York, NY</td>
                        <td data-label="Status">
                            <span class="modern-badge badge-active">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="action-buttons">
                                <button class="btn btn-modern-view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-modern-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-modern-delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="modern-table-row">
                        <td data-label="ID"><span class="fw-medium">002</span></td>
                        <td data-label="Name">
                            <div class="contact-info">
                                <i class="fas fa-user-circle me-2 text-muted"></i>
                                <span>Jane Smith</span>
                            </div>
                        </td>
                        <td data-label="Email">jane.smith@example.com</td>
                        <td data-label="Phone">+1 (555) 987-6543</td>
                        <td data-label="Location">Los Angeles, CA</td>
                        <td data-label="Status">
                            <span class="modern-badge badge-pending">
                                <i class="fas fa-clock me-1"></i>Pending
                            </span>
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="action-buttons">
                                <button class="btn btn-modern-view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-modern-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-modern-delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="modern-table-row">
                        <td data-label="ID"><span class="fw-medium">003</span></td>
                        <td data-label="Name">
                            <div class="contact-info">
                                <i class="fas fa-user-circle me-2 text-muted"></i>
                                <span>Mike Johnson</span>
                            </div>
                        </td>
                        <td data-label="Email">mike.johnson@example.com</td>
                        <td data-label="Phone">+1 (555) 456-7890</td>
                        <td data-label="Location">Chicago, IL</td>
                        <td data-label="Status">
                            <span class="modern-badge badge-inactive">
                                <i class="fas fa-pause-circle me-1"></i>Inactive
                            </span>
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="action-buttons">
                                <button class="btn btn-modern-view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-modern-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-modern-delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Responsive Form Test -->
    <div class="modern-card p-4 mb-4">
        <h4 class="mb-3">Responsive Form Test</h4>
        <form>
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" class="form-control" placeholder="Enter first name">
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" class="form-control" placeholder="Enter last name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" class="form-control" placeholder="Enter phone number">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" class="form-control" rows="3" placeholder="Enter full address"></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" class="form-control" placeholder="Enter city">
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <select id="state" class="form-control">
                        <option value="">Select State</option>
                        <option value="NY">New York</option>
                        <option value="CA">California</option>
                        <option value="IL">Illinois</option>
                    </select>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-modern-primary">
                    <i class="fas fa-save me-2"></i>Save Information
                </button>
            </div>
        </form>
    </div>

    <!-- Responsive Button Test -->
    <div class="modern-card p-4 mb-4">
        <h4 class="mb-3">Responsive Buttons</h4>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-modern-primary">
                <i class="fas fa-plus me-2"></i>Primary
            </button>
            <button class="btn btn-modern-edit">
                <i class="fas fa-edit me-2"></i>Edit
            </button>
            <button class="btn btn-modern-view">
                <i class="fas fa-eye me-2"></i>View
            </button>
            <button class="btn btn-modern-success">
                <i class="fas fa-check me-2"></i>Success
            </button>
            <button class="btn btn-modern-delete">
                <i class="fas fa-trash me-2"></i>Delete
            </button>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <span class="modern-badge badge-active">Active</span>
            <span class="modern-badge badge-pending">Pending</span>
            <span class="modern-badge badge-inactive">Inactive</span>
            <span class="modern-badge badge-service-provider">Service Provider</span>
        </div>
    </div>

    <!-- Screen Size Indicator -->
    <div class="modern-card p-4">
        <h4 class="mb-3">Current Screen Size</h4>
        <div id="screenInfo" class="d-flex flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-expand-arrows-alt me-2 text-primary"></i>
                <span>Width: <strong id="screenWidth">-</strong>px</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-arrows-alt-v me-2 text-success"></i>
                <span>Height: <strong id="screenHeight">-</strong>px</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-mobile-alt me-2 text-info"></i>
                <span>Breakpoint: <strong id="breakpoint">-</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Test Modal -->
<div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testModalLabel">Responsive Modal Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This modal adapts to different screen sizes:</p>
                <ul>
                    <li><strong>Mobile:</strong> Full screen on small devices</li>
                    <li><strong>Tablet:</strong> Responsive margins</li>
                    <li><strong>Desktop:</strong> Standard modal size</li>
                </ul>
                <form>
                    <div class="mb-3">
                        <label for="modalInput" class="form-label">Test Input</label>
                        <input type="text" class="form-control" id="modalInput" placeholder="Type something...">
                    </div>
                    <div class="mb-3">
                        <label for="modalSelect" class="form-label">Test Select</label>
                        <select class="form-control" id="modalSelect">
                            <option>Option 1</option>
                            <option>Option 2</option>
                            <option>Option 3</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-modern-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button for Mobile -->
<button class="btn btn-modern-primary position-fixed" 
        style="bottom: 20px; right: 20px; border-radius: 50%; width: 56px; height: 56px; z-index: 1000;"
        data-bs-toggle="modal" data-bs-target="#testModal"
        title="Open Test Modal">
    <i class="fas fa-plus"></i>
</button>

<script>
// Update screen size information
function updateScreenInfo() {
    const width = window.innerWidth;
    const height = window.innerHeight;
    let breakpoint = '';
    
    if (width <= 576) {
        breakpoint = 'Mobile (≤576px)';
    } else if (width <= 768) {
        breakpoint = 'Tablet (577-768px)';
    } else if (width <= 1200) {
        breakpoint = 'Desktop (769-1200px)';
    } else {
        breakpoint = 'Large Desktop (≥1201px)';
    }
    
    document.getElementById('screenWidth').textContent = width;
    document.getElementById('screenHeight').textContent = height;
    document.getElementById('breakpoint').textContent = breakpoint;
}

// Update on load and resize
updateScreenInfo();
window.addEventListener('resize', updateScreenInfo);
window.addEventListener('orientationchange', function() {
    setTimeout(updateScreenInfo, 100);
});
</script>

<?php include('footer.php'); ?>
