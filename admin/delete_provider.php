<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';
require 'functions.php';

if (isset($_POST['delete_provider'])) {
    $id = intval($_POST['provider_id']);

    // Get provider name before deletion for notification
    $getProviderStmt = $conn->prepare("SELECT company_name FROM active_service_provider WHERE provider_id = ?");
    $getProviderStmt->bind_param("i", $id);
    $getProviderStmt->execute();
    $result = $getProviderStmt->get_result();
    $provider = $result->fetch_assoc();
    $providerName = $provider ? $provider['company_name'] : 'Unknown Provider';

    $stmt = $conn->prepare("DELETE FROM active_service_provider WHERE provider_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Add notification for provider deletion
        addNotification($conn, "Service provider '$providerName' has been deleted from the system.", 'warning', 'active_providers.php');
        header("Location: active_providers.php?success=deleted");
    } else {
        echo "Error deleting provider: " . $conn->error;
    }
}
?>
