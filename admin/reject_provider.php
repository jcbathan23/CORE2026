<?php
require '../connect.php';
require 'functions.php'; // Make sure addNotification() is available
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_id'])) {
    $id = intval($_POST['registration_id']);
    $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
    $rejection_reason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : '';

    // Optional: fetch provider details before deleting for notification and email
    $stmtFetch = $conn->prepare("SELECT company_name, email, contact_person FROM pending_service_provider WHERE registration_id = ?");
    $stmtFetch->bind_param("i", $id);
    $stmtFetch->execute();
    $result = $stmtFetch->get_result();
    
    if ($result && $result->num_rows > 0) {
        $provider = $result->fetch_assoc();
        $providerName = $provider['company_name'];
        $providerEmail = $provider['email'];
        $contactPerson = $provider['contact_person'];
        
        // Format rejection reason for display
        $reasonText = '';
        $reasons = [
            'missing_requirements' => 'Missing Requirements',
            'incomplete_documents' => 'Incomplete Documents',
            'invalid_business_permit' => 'Invalid Business Permit',
            'incomplete_profile' => 'Incomplete Company Profile',
            'invalid_contact_info' => 'Invalid Contact Information',
            'non_compliance' => 'Non-Compliance with Standards',
            'duplicate_application' => 'Duplicate Application',
            'other' => 'Other'
        ];
        
        if (isset($reasons[$rejection_reason])) {
            $reasonText = $reasons[$rejection_reason];
        } else {
            $reasonText = 'Unknown Reason';
        }
        
        // Delete provider
        $stmt = $conn->prepare("DELETE FROM pending_service_provider WHERE registration_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Add comprehensive notification
            $notificationMessage = "Service Provider rejected: " . $providerName . 
                                 " | Reason: " . $reasonText . 
                                 ($remarks !== '' ? " | Remarks: " . $remarks : '') .
                                 " | Contact: " . $contactPerson;
            
            addNotification(
                $conn,
                $notificationMessage,
                "service_provider",
                "pending_providers.php"
            );

            // Send email notification to provider (if email function exists)
            if (function_exists('sendRejectionEmail') && $providerEmail) {
                try {
                    sendRejectionEmail($providerEmail, $contactPerson, $providerName, $reasonText, $remarks);
                } catch (Exception $e) {
                    // Log email error but don't fail the rejection
                    error_log("Failed to send rejection email: " . $e->getMessage());
                }
            }

            header("Location: pending_providers.php?success=rejected_provider");
            exit();
        } else {
            echo "Error deleting provider.";
        }
    } else {
        echo "Provider not found.";
    }
} else {
    echo "Invalid request.";
}
?>
