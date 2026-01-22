<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if (isset($_POST['archive_provider'])) {
    $provider_id = $_POST['provider_id'];
    
    // Validate provider_id
    if (empty($provider_id) || !is_numeric($provider_id)) {
        header("Location: active_providers.php?error=invalid_id");
        exit();
    }
    
    // Update the provider status to 'Archived'
    $query = "UPDATE active_service_provider SET status = 'Archived' WHERE provider_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $provider_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: active_providers.php?success=archived");
        } else {
            header("Location: active_providers.php?error=no_rows_affected");
        }
        exit();
    } else {
        header("Location: active_providers.php?error=archive_failed");
        exit();
    }
} else {
    header("Location: active_providers.php");
    exit();
}

$conn->close();
?>
