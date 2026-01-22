<?php
// Default values
$emailErr = $passwordErr = '';
$email = $password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup_type'])) {
    $signupType = $_POST['signup_type'];
    include("../connect.php");

    if ($signupType === "user") {
        // === USER SIGNUP ===
        if (empty($_POST['email'])) {
            $emailErr = 'Email is required';
        } else {
            $email = trim($_POST['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = 'Invalid email format';
            }
        }

        if (empty($_POST['password'])) {
            $passwordErr = 'Password is required';
        } else {
            $password = trim($_POST['password']);
        }

        if ($emailErr === '' && $passwordErr === '') {
            if (strtolower($email) === 'admin' || strtolower($password) === 'admin') {
                echo '<script>alert("Registration with \'admin\' is not allowed.");</script>';
            } else {
                $accountType = 2; // Normal User

                $stmt = $conn->prepare("INSERT INTO newaccounts (email, password, account_type) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $email, $password, $accountType);

                if ($stmt->execute()) {
                    // ✅ Add notification for admin
                    $notifMsg = "New User Registered: " . $email;
                    $notifLink = "user_management.php"; // Admin page to view users
                    $notifSql = "INSERT INTO notifications (message, link, is_read, created_at) VALUES (?, ?, 0, NOW())";
                    $notifStmt = $conn->prepare($notifSql);
                    $notifStmt->bind_param("ss", $notifMsg, $notifLink);
                    $notifStmt->execute();
                    $notifStmt->close();

                    echo '<script>alert("User sign up successful!"); window.location="loginpage.php";</script>';
                } else {
                    echo '<script>alert("Error: Unable to create user account.");</script>';
                }
                $stmt->close();
            }
        }
    } elseif ($signupType === "provider") {
        // === SERVICE PROVIDER SIGNUP ===
        $company_name = $_POST['company_name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $contact_person = $_POST['contact_person'] ?? '';
        $contact_number = $_POST['contact_number'] ?? '';
        $address = $_POST['address'];
        $services = isset($_POST['services']) ? implode(',', $_POST['services']) : '';
        $iso_certified = $_POST['iso_certified'] ?? 'no';

        if (empty($password)) {
            echo '<script>alert("Password is required for Service Provider signup!");</script>';
        } else {
            // Handle uploads
            $business_permit = $_FILES['business_permit']['name'];
            $company_profile = $_FILES['company_profile']['name'] ?? null;

            move_uploaded_file($_FILES['business_permit']['tmp_name'], "uploads/" . $business_permit);
            if ($company_profile) {
                move_uploaded_file($_FILES['company_profile']['tmp_name'], "uploads/" . $company_profile);
            }

            // Save provider info to pending_service_provider ONLY
            $stmt = $conn->prepare("INSERT INTO pending_service_provider 
                (company_name, email, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $company_name, $email, $contact_person, $contact_number, $address, $services, $iso_certified, $business_permit, $company_profile, $password);

            if ($stmt->execute()) {
                // ✅ Add notification for admin
                $notifMsg = "New Service Provider Registered: " . $company_name;
                $notifLink = "pending_providers.php"; // Admin can click to view
                $notifSql = "INSERT INTO notifications (message, link, is_read, created_at) VALUES (?, ?, 0, NOW())";
                $notifStmt = $conn->prepare($notifSql);
                $notifStmt->bind_param("ss", $notifMsg, $notifLink);
                $notifStmt->execute();
                $notifStmt->close();

                echo '<script>alert("Service Provider registration submitted successfully!"); window.location="signup.php?success=registered_provider";</script>';
            } else {
                echo '<script>alert("Error: Unable to register service provider.");</script>';
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>



  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLATE SIGN UP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        color: #e2e8f0;
      }
      .signup-card {
        background: #0f172a; /* dark navy */
        color: #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.35);
        width: 100%;
        max-width: 650px;
        padding: 28px;
        animation: fadeIn 0.5s ease;
      }
      @keyframes fadeIn {
        from {opacity: 0; transform: translateY(-10px);}
        to {opacity: 1; transform: translateY(0);}
      }
      .nav-pills .nav-link {
        border-radius: 30px;
        font-weight: 600;
        color: #cbd5e1;
        background: #1f2937;
        border: 1px solid #273449;
      }
      .nav-pills .nav-link.active {
        color: #fff;
        background: linear-gradient(90deg, #0ea5ea 0%, #2563eb 100%);
        border: none;
      }
      .form-section { margin-top: 20px; }
      label { font-weight: 500; color:#e2e8f0; }

      /* Dark inputs */
      .form-control, .form-select {
        background: #1f2937;
        border: 1px solid #273449;
        color: #e5e7eb;
      }
      .form-control::placeholder { color:#94a3b8; }

      /* Buttons */
      .btn-primary, .btn-success {
        background: linear-gradient(90deg, #0ea5ea 0%, #2563eb 100%);
        border: none;
        box-shadow: 0 6px 16px rgba(37,99,235,0.35);
      }
      .btn-primary:hover, .btn-success:hover { filter: brightness(1.05); }
      button { border-radius: 10px; }
    </style>
  </head>
  <body>
    <div class="signup-card">
      <h2 class="text-center mb-4">Create Your Account</h2>

      <!-- Toggle Buttons -->
      <ul class="nav nav-pills justify-content-center mb-3" id="signupTabs">
        <li class="nav-item">
          <button class="nav-link active" id="user-tab" onclick="showForm('user')">User</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" id="provider-tab" onclick="showForm('provider')">Service Provider</button>
        </li>
      </ul>

      <!-- User Signup Form -->
      <form id="userForm" class="form-section" action="" method="POST">
        <input type="hidden" name="signup_type" value="user">
        <div class="mb-3">
          <label>Email</label>
          <input type="text" class="form-control" name="email" placeholder="Enter Email" required>
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign Up as User</button>
        <p class="mt-3 text-center">Already have an account? <a href="loginpage.php"><br>Login here</a></p>
      </form>

      <!-- Service Provider Signup Form -->
      <form id="providerForm" class="form-section" action="" method="POST" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="signup_type" value="provider">

        <div class="mb-3">
          <label>Company Name *</label>
          <input type="text" name="company_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email *</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Password *</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Contact Person</label>
            <input type="text" name="contact_person" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label>Contact Number</label>
            <input type="text" name="contact_number" class="form-control">
          </div>
        </div>
        <div class="mb-3">
          <label>Business Address</label>
          <textarea name="address" class="form-control" rows="2" required></textarea>
        </div>
        <div class="mb-3">
          <label>Services Offered</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="services[]" value="land">
            <label class="form-check-label">Land Transport</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="services[]" value="air">
            <label class="form-check-label">Air Freight</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="services[]" value="sea">
            <label class="form-check-label">Sea Freight</label>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>ISO Certified?</label>
            <select name="iso_certified" class="form-select">
              <option value="yes">Yes</option>
              <option value="no" selected>No</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label>Upload Business Permit *</label>
            <input type="file" name="business_permit" class="form-control" required>
          </div>
          <div class="col-12 mb-3">
            <label>Upload Company Profile (Optional)</label>
            <input type="file" name="company_profile" class="form-control">
          </div>
        </div>
        <button type="submit" class="btn btn-success w-100">Submit Provider Registration</button>
        <p class="mt-3 text-center">Already have an account?<a href="loginpage.php"><br>Login here</a></p>
      </form>
    </div>

    <script>
      function showForm(type) {
        document.getElementById('userForm').style.display = (type === 'user') ? 'block' : 'none';
        document.getElementById('providerForm').style.display = (type === 'provider') ? 'block' : 'none';
        document.getElementById('user-tab').classList.toggle('active', type === 'user');
        document.getElementById('provider-tab').classList.toggle('active', type === 'provider');
      }
    </script>
  </body>
  </html>
