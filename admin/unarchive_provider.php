<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if (isset($_POST['unarchive_provider'])) {
    $provider_id = $_POST['provider_id'];
    
    // Validate provider_id
    if (empty($provider_id) || !is_numeric($provider_id)) {
        header("Location: archived_providers.php?error=invalid_id");
        exit();
    }
    
    // Update the provider status back to 'Active'
    $query = "UPDATE active_service_provider SET status = 'Active' WHERE provider_id = ? AND status = 'Archived'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $provider_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: archived_providers.php?success=unarchived");
        } else {
            header("Location: archived_providers.php?error=no_rows_affected");
        }
        exit();
    } else {
        header("Location: archived_providers.php?error=unarchive_failed");
        exit();
    }
} else {
    header("Location: archived_providers.php");
    exit();
}

$conn->close();
?>
