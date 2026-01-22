<?php
require '../connect.php';
require 'functions.php'; // Make sure this contains addNotification() and sendApprovalEmail()
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_id'])) {
    $id = intval($_POST['registration_id']);

    // Fetch provider from pending_service_provider
    $stmt = $conn->prepare("SELECT * FROM pending_service_provider WHERE registration_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $provider = $result->fetch_assoc();

        // Use the existing password
        $password = $provider['password'];

        // Insert into active_service_provider
        $insert = $conn->prepare("INSERT INTO active_service_provider 
            (company_name, email, contact_person, contact_number, address, services, iso_certified, business_permit, company_profile, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $insert->bind_param(
            "ssssssssss",
            $provider['company_name'],
            $provider['email'],
            $provider['contact_person'],
            $provider['contact_number'],
            $provider['address'],
            $provider['services'],
            $provider['iso_certified'],
            $provider['business_permit'],
            $provider['company_profile'],
            $password
        );

        if ($insert->execute()) {
            // Delete from pending_service_provider
            $delete = $conn->prepare("DELETE FROM pending_service_provider WHERE registration_id = ?");
            $delete->bind_param("i", $id);
            $delete->execute();

            // Add notification
            addNotification(
                $conn,
                "Service Provider approved: " . $provider['company_name'] . " | Contact: " . $provider['contact_person'],
                "service_provider",
                "active_providers.php"
            );

            // Send approval email notification (if email function exists)
            if (function_exists('sendApprovalEmail') && $provider['email']) {
                try {
                    sendApprovalEmail(
                        $provider['email'], 
                        $provider['contact_person'], 
                        $provider['company_name'],
                        $provider['email'],
                        $password
                    );
                } catch (Exception $e) {
                    // Log email error but don't fail the approval
                    error_log("Failed to send approval email: " . $e->getMessage());
                }
            }

            // Redirect with success
            header("Location: pending_providers.php?success=approved_provider");
            exit();
        } else {
            echo "Failed to approve provider.";
        }
    } else {
        echo "Provider not found.";
    }
} else {
    echo "Invalid request.";
}
?>
