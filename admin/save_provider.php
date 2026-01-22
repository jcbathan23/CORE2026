<?php
require '../connect.php'; // Your DB connection file
require_once __DIR__ . '/auth.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $companyName   = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $password      = mysqli_real_escape_string($conn, $_POST['password']); // <-- fixed semicolon
    $contactPerson = mysqli_real_escape_string($conn, $_POST['contact_person']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $address       = mysqli_real_escape_string($conn, $_POST['address']);
    $services      = isset($_POST['services']) ? mysqli_real_escape_string($conn, implode(', ', $_POST['services'])) : '';
    $isoCertified  = $_POST['iso_certified'] ?? 'no';

    // File uploads
    $permitName = '';
    $profileName = '';

    // Business Permit upload
    if (isset($_FILES['business_permit']) && $_FILES['business_permit']['error'] === UPLOAD_ERR_OK) {
        $permitTmp  = $_FILES['business_permit']['tmp_name'];
        $permitName = time() . '_' . basename($_FILES['business_permit']['name']);
        move_uploaded_file($permitTmp, 'uploads/' . $permitName);
    }

    // Company Profile upload (optional)
    if (isset($_FILES['company_profile']) && $_FILES['company_profile']['error'] === UPLOAD_ERR_OK) {
        $profileTmp  = $_FILES['company_profile']['tmp_name'];
        $profileName = time() . '_' . basename($_FILES['company_profile']['name']);
        move_uploaded_file($profileTmp, 'uploads/' . $profileName);
    }

    // Insert to database
    $sql = "INSERT INTO pending_service_provider 
            (company_name, email, password, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssss",
        $companyName,
        $email,
        $password,
        $contactPerson,
        $contactNumber,
        $address,
        $services,
        $isoCertified,
        $permitName,
        $profileName
    );

    if ($stmt->execute()) {
        // Add notification for new provider registration
        addNotification($conn, "New service provider '$companyName' has been registered and is pending approval.", 'info', 'pending_providers.php');
        header("Location: register_provider.php?success=registered_provider"); // redirect back
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
