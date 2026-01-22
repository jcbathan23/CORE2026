<?php
session_start();
include("../connect.php");

// Only allow logged-in providers
if (!isset($_SESSION['email']) || $_SESSION['account_type'] != 3) {
    header("Location: ../admin/loginpage.php");
    exit();
}

$email = $_SESSION['email'];
$account_status = "";

// Fetch provider info
$stmt = $conn->prepare("
    SELECT company_name, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, status 
    FROM active_service_provider WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($company_name, $contact_person, $contact_number, $address, $services, $iso_certified, $business_permit, $company_profile, $status);
if ($stmt->fetch()) $account_status = "active";
$stmt->close();

if (empty($account_status)) {
    $stmt = $conn->prepare("
        SELECT company_name, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, date_submitted, status 
        FROM pending_service_provider WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($company_name, $contact_person, $contact_number, $address, $services, $iso_certified, $business_permit, $company_profile, $date_submitted, $status);
    if ($stmt->fetch()) $account_status = "pending";
    $stmt->close();
}

// Default values
if (empty($company_name)) $company_name = "Service Provider";
if (empty($date_submitted)) $date_submitted = "N/A";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Provider Profile</title>
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

body.dark-mode .profile-card,
body.dark-mode .profile-header {
  background: rgba(30, 41, 59, 0.8) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #e2e8f0 !important;
}

body.dark-mode .form-control {
  background: rgba(15, 23, 42, 0.8) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  color: #e2e8f0 !important;
}

body.dark-mode .form-label {
  color: #94a3b8 !important;
}
</style>
</head>
<body>

<?php include('provider_sidebar.php'); ?>

<div class="main-content">
    <?php include('provider_navbar.php'); ?>

    <div class="content">
        <!-- Profile Header -->
        <div class="profile-header">
            <h2><?= htmlspecialchars($company_name) ?></h2>
            <p><?= htmlspecialchars($contact_person) ?></p>
            <img class="avatar" src="../uploads/<?= !empty($company_profile) ? htmlspecialchars($company_profile) : 'default-avatar.png' ?>" alt="Avatar">
        </div>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-stats">
                <div class="stat">
                    <h5><?= $account_status == 'active' ? 'Active' : 'Pending' ?></h5>
                    <p>Account Status</p>
                </div>
                <div class="stat">
                    <h5><?= htmlspecialchars($contact_number) ?></h5>
                    <p>Contact Number</p>
                </div>
                <div class="stat">
                    <h5><?= htmlspecialchars($iso_certified) ?></h5>
                    <p>ISO Certified</p>
                </div>
            </div>

            <form>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($address) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Services</label>
                        <textarea class="form-control" rows="2" readonly><?= htmlspecialchars($services) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Permit</label>
                        <?php if (!empty($business_permit)): ?>
                            <a href="../uploads/<?= htmlspecialchars($business_permit) ?>" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                        <?php else: ?>
                            <p class="text-muted">No file uploaded.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Profile</label>
                        <?php if (!empty($company_profile)): ?>
                            <a href="../uploads/<?= htmlspecialchars($company_profile) ?>" target="_blank" class="btn btn-outline-primary btn-sm">View</a>
                        <?php else: ?>
                            <p class="text-muted">No file uploaded.</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Submitted</label>
                        <input type="text" class="form-control" value="<?= $date_submitted ?>" readonly>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
