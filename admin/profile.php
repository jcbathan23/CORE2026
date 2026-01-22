<?php 
include('header.php'); 
include('sidebar.php'); 
include('navbar.php'); 
include('../connect.php'); 
?>

<style>
.card-dashboard-admin {
    background: linear-gradient(135deg, #2b3f4e 0%, #1f3442 100%);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(43, 63, 78, 0.25);
    padding: 2rem 2.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}
.card-dashboard-admin::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%);
    pointer-events: none;
}
.card-dashboard-admin::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.card-dashboard-admin:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 48px rgba(43, 63, 78, 0.35);
    border-color: rgba(255, 255, 255, 0.2);
}
.icon-container-modern {
    width: 90px;
    height: 90px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}
.icon-container-modern:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: scale(1.05);
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: 0.9; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-dashboard-admin {
        padding: 1.5rem 1.25rem;
    }
    .card-dashboard-admin .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1.5rem !important;
    }
    .icon-container-modern {
        width: 70px;
        height: 70px;
    }
    .icon-container-modern i {
        font-size: 2rem !important;
    }
    .card-dashboard-admin h2 {
        font-size: 2rem !important;
    }
    .btn-light-custom {
        width: 100%;
    }
}

.content h3.mb-4 {
    background: transparent !important;
    color: inherit !important;
}

.auto-fade {
    animation: fadeOut 5s ease-in-out forwards;
}

@keyframes fadeOut {
    0% { opacity: 1; }
    70% { opacity: 1; }
    100% { opacity: 0; display: none; }
}

/* Modern Table Styles */
.modern-table-container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(43, 63, 78, 0.08);
    overflow: hidden;
    border: 1px solid rgba(43, 63, 78, 0.06);
    transition: all 0.3s ease;
}
.modern-table-container:hover {
    box-shadow: 0 8px 32px rgba(43, 63, 78, 0.12);
}

.modern-table {
    margin-bottom: 0;
    border: none;
}

.modern-table thead {
    background: linear-gradient(90deg, #2b3f4e 0%, #1f3442 100%);
    color: #fff;
    position: relative;
}
.modern-table thead::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 100%);
}

.modern-table thead th {
    border: none;
    padding: 1.1rem 1.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-bottom: none;
    background: transparent;
    color: rgba(255, 255, 255, 0.95);
}

.modern-table tbody tr {
    border-bottom: 1px solid rgba(43, 63, 78, 0.05);
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background: rgba(43, 63, 78, 0.03);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(43, 63, 78, 0.08);
}

.modern-table tbody td {
    padding: 1.1rem 1.5rem;
    vertical-align: middle;
    border: none;
    font-size: 0.9rem;
}

.user-email {
    display: flex;
    align-items: center;
}

.user-email .fw-medium {
    color: #2b3f4e;
    font-weight: 600;
    font-size: 0.95rem;
}

.password-display {
    background: #f8f9fa;
    color: #495057;
    padding: 0.45rem 0.875rem;
    border-radius: 8px;
    font-size: 0.85rem;
    border: 1px solid #e9ecef;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.3px;
}

.modern-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
}

.badge-admin {
    background: #2b3f4e;
    color: white;
    box-shadow: 0 2px 8px rgba(43, 63, 78, 0.15);
}

.badge-user {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.badge-service-provider {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #2b3f4e, #1f3442);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    padding: 0.85rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(43, 63, 78, 0.3);
    letter-spacing: 0.5px;
}

.btn-modern-primary:hover {
    background: linear-gradient(135deg, #1f3442, #152834);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(43, 63, 78, 0.4);
    color: white;
    border-color: rgba(255, 255, 255, 0.2);
}

.btn-modern-edit {
    background: #2b3f4e;
    border: none;
    color: white;
    padding: 0.5rem 0.875rem;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.25s ease;
    margin-right: 0.25rem;
    font-weight: 500;
    box-shadow: 0 2px 6px rgba(43, 63, 78, 0.2);
}

.btn-modern-edit:hover {
    background: #1f3442;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(43, 63, 78, 0.3);
    color: white;
}

.btn-modern-delete {
    background: #dc3545;
    border: none;
    color: white;
    padding: 0.5rem 0.875rem;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.25s ease;
    font-weight: 500;
    box-shadow: 0 2px 6px rgba(220, 53, 69, 0.2);
}

.btn-modern-delete:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    color: white;
}

.table-responsive {
    border-radius: 16px;
    max-height: 600px;
    overflow-y: auto;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: rgba(43, 63, 78, 0.05);
    border-radius: 10px;
    margin: 8px 0;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #2b3f4e, #1f3442);
    border-radius: 10px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #1f3442, #152834);
    background-clip: padding-box;
}

/* Dark mode overrides for profile section */
body.dark-mode .modern-table-container {
    background: #0b1220;
    border-color: rgba(255, 255, 255, 0.06);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}
body.dark-mode .modern-table-container h5 {
    color: #cbd5e1;
}
body.dark-mode .form-label {
    color: #cbd5e1;
}
body.dark-mode .form-control {
    background-color: #0f1a2b;
    color: #e2e8f0;
    border-color: #1f2a3a;
}
body.dark-mode .form-control::placeholder {
    color: #94a3b8;
}
body.dark-mode .btn-modern-primary {
    background: linear-gradient(135deg, #1e2b3a, #162130);
    border-color: rgba(255,255,255,0.12);
}
body.dark-mode .btn-modern-primary:hover {
    background: linear-gradient(135deg, #162130, #0f1a28);
}
body.dark-mode .avatar-box {
    background: #0f1a2b !important;
    border-color: #1f2a3a !important;
}
body.dark-mode .modern-table-container .text-muted {
    color: #94a3b8 !important;
}

</style>



<div class="content p-4">
    <?php
    // Count total admins only
    $totalAdmins = $conn->query("SELECT COUNT(*) as cnt FROM admin_list")->fetch_assoc()['cnt'];
    ?>

    <!-- My Profile Section -->
    <?php
        $adminEmail = $_SESSION['email'] ?? null;
        $profile = ['name' => '', 'phone' => '', 'avatar' => null];
        if ($adminEmail) {
            $tbl = $conn->query("SHOW TABLES LIKE 'admin_profiles'");
            if ($tbl && $tbl->num_rows > 0) {
                $stmt = $conn->prepare('SELECT name, phone, avatar FROM admin_profiles WHERE email = ? LIMIT 1');
                $stmt->bind_param('s', $adminEmail);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $profile = $row;
                    }
                }
                $stmt->close();
            }
        }
        $avatarPath = !empty($profile['avatar']) ? ('uploads/' . $profile['avatar']) : null;
    ?>
    <div class="modern-table-container mb-4">
        <div class="p-3 p-md-4">
            <h5 class="mb-3"><i class="fas fa-user-circle me-2"></i>My Profile</h5>
            <?php if (isset($_GET['profile']) && $_GET['profile'] === 'updated'): ?>
                <div class="alert alert-success py-2 mb-3"><i class="fas fa-check-circle me-2"></i>Profile updated successfully.</div>
            <?php elseif (isset($_GET['profile']) && $_GET['profile'] === 'error'): ?>
                <div class="alert alert-danger py-2 mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Failed to update profile. Please try again.</div>
            <?php endif; ?>
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-auto text-center">
                    <div class="avatar-box" style="width: 120px; height: 120px; border-radius: 16px; background: #f1f5f9; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px solid #e2e8f0;">
                        <?php if ($avatarPath && file_exists(__DIR__ . '/' . $avatarPath)): ?>
                            <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user fa-3x" style="color:#64748b;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted mt-2">Current Avatar</div>
                </div>
                <div class="col-12 col-md">
                    <form action="save_admin_profile.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold"><i class="fas fa-signature me-2"></i>Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" placeholder="Your full name">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold"><i class="fas fa-phone me-2"></i>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" placeholder="e.g. 09XXXXXXXXX">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold"><i class="fas fa-image me-2"></i>Avatar</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF, WEBP. Max 5MB.</small>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-modern-primary w-100">
                                    <i class="fas fa-save me-2"></i>Save Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Card -->
    <div class="row g-3 mb-5">
        <div class="col-12">
            <div class="card-dashboard-admin">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-4">
                        <div class="icon-container-modern">
                            <i class="fas fa-users-cog fa-3x" style="color: rgba(255, 255, 255, 0.95);"></i>
                        </div>
                        <div>
                            <h5 class="text-uppercase fw-bold mb-2" style="color: rgba(255, 255, 255, 0.75); font-size: 0.875rem; letter-spacing: 2px;">Admin Management</h5>
                            <h2 class="fw-bold mb-0" style="color: #ffffff; font-size: 2.5rem;"><?= $totalAdmins ?> <span style="font-size: 1.25rem; color: rgba(255, 255, 255, 0.7);">Administrators</span></h2>
                        </div>
                    </div>
                    <button class="btn btn-light-custom" data-bs-toggle="modal" data-bs-target="#addUserModal" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); color: white; backdrop-filter: blur(10px); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255, 255, 255, 0.25)'; this.style.borderColor='rgba(255, 255, 255, 0.35)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.borderColor='rgba(255, 255, 255, 0.25)';">
                        <i class="fas fa-user-plus me-2"></i>Add New Admin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-check-circle me-2"></i>Administrator Details Updated Successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'deleted'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-check-circle me-2"></i>Administrator Deleted Successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['success'] === 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-check-circle me-2"></i>Administrator Added Successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'update_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>Failed to update administrator. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'delete_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>Failed to delete administrator. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'user_not_found'): ?>
            <div class="alert alert-warning alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Administrator not found in the system.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'missing_fields'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>Please fill in all required fields.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'invalid_email'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>Please enter a valid email address.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'email_exists'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>This email address is already registered as an administrator.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['error'] === 'add_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show auto-fade" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>Failed to add administrator. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Modern Users Table -->
    <div class="modern-table-container">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-envelope me-2"></i>Email Address</th>
                        <th><i class="fas fa-key me-2"></i>Password</th>
                        <th><i class="fas fa-user-tag me-2"></i>Account Type</th>
                        <th class="text-center"><i class="fas fa-cogs me-2"></i>Actions</th>
                    </tr>
                </thead>
            <tbody>
                <?php
                // Only fetch admin accounts
                $sql = "SELECT email, password FROM admin_list ORDER BY email ASC";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $email = $row['email'];
                        $password = $row['password'];
                ?>
                    <tr class="modern-table-row">
                        <td>
                            <div class="user-email">
                                <i class="fas fa-user-shield me-2 text-danger"></i>
                                <span class="fw-medium"><?= htmlspecialchars($email) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="password-field">
                                <code class="password-display"><?= str_repeat('*', strlen($password)) ?></code>
                            </div>
                        </td>
                        <td>
                            <span class="modern-badge badge-admin">
                                <i class="fas fa-shield-alt me-1"></i>
                                ADMINISTRATOR
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <!-- Update Button -->
                                <button class="btn btn-modern-edit updateBtn"
                                        data-email="<?= htmlspecialchars($email) ?>"
                                        data-password="<?= htmlspecialchars($password) ?>"
                                        data-bs-toggle="modal" data-bs-target="#updateModal"
                                        title="Edit Admin">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Delete Button -->
                                <button class="btn btn-modern-delete deleteBtn"
                                        data-email="<?= htmlspecialchars($email) ?>"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        title="Delete Admin">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle me-2"></i>No administrator accounts found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="update_user.php" method="POST" class="modal-content" style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(43, 63, 78, 0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, #2b3f4e, #1f3442); color: white; border: none;">
                <h5 class="modal-title" id="updateLabel"><i class="fas fa-user-edit me-2"></i>Update Administrator</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="email" id="updateEmail">
                <input type="hidden" name="account_type" value="Admin">

                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    <input type="text" class="form-control" id="displayEmail" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-key me-2"></i>Password <span class="text-danger">*</span></label>
                    <input type="text" name="password" class="form-control" id="updatePassword" required placeholder="Enter new password">
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid rgba(43, 63, 78, 0.1);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" name="update_user" class="btn" style="background: linear-gradient(135deg, #2b3f4e, #1f3442); color: white; border-radius: 10px; padding: 0.5rem 1.5rem; font-weight: 600;">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="delete_user.php" method="POST" class="modal-content" style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(220, 53, 69, 0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; border: none;">
                <h5 class="modal-title" id="deleteLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="email" id="deleteEmail">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <p class="mb-0">Are you sure you want to delete administrator account: <strong id="deleteUserName" class="text-danger"></strong>?</p>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid rgba(220, 53, 69, 0.1);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" name="delete_user" class="btn" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; border-radius: 10px; padding: 0.5rem 1.5rem; font-weight: 600;">
                    <i class="fas fa-trash-alt me-2"></i>Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="add_user.php" method="POST" class="modal-content" style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(43, 63, 78, 0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, #2b3f4e, #1f3442); color: white; border: none;">
                <h5 class="modal-title" id="addUserLabel"><i class="fas fa-user-plus me-2"></i>Add New Administrator</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="account_type" value="Admin">
                
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-envelope me-2"></i>Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" id="addEmail" required placeholder="admin@example.com">
                    <small class="text-muted">This will be used for login</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-key me-2"></i>Password <span class="text-danger">*</span></label>
                    <input type="text" name="password" class="form-control" id="addPassword" required placeholder="Enter secure password">
                    <small class="text-muted">Minimum 6 characters recommended</small>
                </div>

                <div class="alert mb-0" style="background: linear-gradient(135deg, rgba(43, 63, 78, 0.1), rgba(31, 52, 66, 0.05)); border: 1px solid rgba(43, 63, 78, 0.2); color: #2b3f4e;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This account will have full administrative privileges.
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid rgba(43, 63, 78, 0.1);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px;">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" name="add_user" class="btn" style="background: linear-gradient(135deg, #2b3f4e, #1f3442); color: white; border-radius: 10px; padding: 0.5rem 1.5rem; font-weight: 600;">
                    <i class="fas fa-user-plus me-2"></i>Add Administrator
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Fill update modal
    document.querySelectorAll(".updateBtn").forEach(btn => {
        btn.addEventListener("click", function () {
            document.getElementById("updateEmail").value = this.dataset.email;
            document.getElementById("displayEmail").value = this.dataset.email;
            document.getElementById("updatePassword").value = this.dataset.password;
        });
    });

    // Fill delete modal
    document.querySelectorAll(".deleteBtn").forEach(btn => {
        btn.addEventListener("click", function () {
            document.getElementById("deleteEmail").value = this.dataset.email;
            document.getElementById("deleteUserName").textContent = this.dataset.email;
        });
    });
    
    // Form validation for update
    document.querySelector('form[action="update_user.php"]').addEventListener('submit', function(e) {
        const password = document.getElementById('updatePassword').value;
        
        if (!password.trim()) {
            e.preventDefault();
            alert('Password is required');
            return false;
        }
    });

    // Form validation for add admin
    document.querySelector('form[action="add_user.php"]').addEventListener('submit', function(e) {
        const email = document.getElementById('addEmail').value;
        const password = document.getElementById('addPassword').value;
        
        if (!email.trim()) {
            e.preventDefault();
            alert('Email is required');
            return false;
        }
        
        if (!password.trim()) {
            e.preventDefault();
            alert('Password is required');
            return false;
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.auto-fade');
        alerts.forEach(alert => {
            if (alert) {
                alert.style.display = 'none';
            }
        });
    }, 5000);
});
</script>

<?php include('footer.php'); ?>
