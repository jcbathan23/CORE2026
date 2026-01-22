<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>
<?php include('navbar.php'); ?>

<!-- Main Content -->
<div class="content">
  <h3 class="mb-4"></h3>
</div>

<?php include('footer.php'); ?>



<!--Provider Template-->
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

// Get company name
$stmt = $conn->prepare("SELECT company_name FROM active_service_provider WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($company_name);
if ($stmt->fetch()) {
    $account_status = "active";
}
$stmt->close();

if (empty($company_name)) {
    $stmt = $conn->prepare("SELECT company_name FROM pending_service_provider WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($company_name);
    if ($stmt->fetch()) {
        $account_status = "pending";
    }
    $stmt->close();
}

if (empty($company_name)) {
    $company_name = "Service Provider";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Provider Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="providerstyles.css" rel="stylesheet">
</head>
<body>

<?php include('provider_sidebar.php'); ?>

<div class="main-content">
    <?php include('provider_navbar.php'); ?>

    <div class="content">
      <h3 class="mb-4">Rates</h3>
  
</div>

<?php include('provider_footer.php'); ?>
