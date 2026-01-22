<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';

if (isset($_POST['update_provider'])) {
    $id = intval($_POST['provider_id']);
    $company_name = $_POST['company_name'];
    $address = $_POST['address'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $services = $_POST['services'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE active_service_provider 
        SET company_name = ?, address = ?, contact_person = ?, contact_number = ?, services = ?, status = ?
        WHERE provider_id = ?");
    $stmt->bind_param("ssssssi", $company_name, $address, $contact_person, $contact_number, $services, $status, $id);

    if ($stmt->execute()) {
        header("Location: active_providers.php?success=updated");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
