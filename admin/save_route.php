<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../connect.php'; // Adjust path if needed
require_once __DIR__ . '/auth.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Grab POST values and sanitize
    $originId = isset($_POST['origin_id']) ? (int)$_POST['origin_id'] : 0;
    $destinationId = isset($_POST['destination_id']) ? (int)$_POST['destination_id'] : 0;
    $carrierType = isset($_POST['carrier_type']) ? trim($_POST['carrier_type']) : '';
    $providerId = isset($_POST['provider_id']) ? (int)$_POST['provider_id'] : 0;
    $distance = isset($_POST['distance']) ? (float)$_POST['distance'] : 0;
    $eta = isset($_POST['eta']) ? (int)$_POST['eta'] : 0;

    // Validate required fields
    if (!$originId || !$destinationId || !$carrierType || !$providerId || !$distance || !$eta) {
        die("All fields are required.");
    }

    // Prepare insert statement
    $stmt = $conn->prepare("
        INSERT INTO routes 
        (origin_id, destination_id, carrier_type, provider_id, distance_km, eta_min)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters with correct types: i=int, s=string, d=double
    $stmt->bind_param("iisidi", $originId, $destinationId, $carrierType, $providerId, $distance, $eta);

    // Execute statement
    if ($stmt->execute()) {
        // Get location names for notification
        $originQuery = $conn->prepare("SELECT point_name FROM network_points WHERE point_id = ?");
        $originQuery->bind_param("i", $originId);
        $originQuery->execute();
        $originResult = $originQuery->get_result();
        $originName = $originResult->fetch_assoc()['point_name'] ?? 'Unknown Origin';

        $destQuery = $conn->prepare("SELECT point_name FROM network_points WHERE point_id = ?");
        $destQuery->bind_param("i", $destinationId);
        $destQuery->execute();
        $destResult = $destQuery->get_result();
        $destName = $destResult->fetch_assoc()['point_name'] ?? 'Unknown Destination';

        // Add notification for new route
        addNotification($conn, "New route created: $originName to $destName ($carrierType, {$distance}km)", 'success', 'manage_routes.php');
        header("Location: manage_routes.php?success=route_saved"); // redirect back
    } else {
        echo "Error saving route: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    die("Invalid request method.");
}
?>
