<?php
// update_point.php
require '../connect.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Grab POST values
    $point_id = $_POST['point_id'] ?? '';
    $point_name = $_POST['point_name'] ?? '';
    $point_type = $_POST['point_type'] ?? '';
    $city = $_POST['city'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $status = $_POST['status'] ?? '';

    // Simple validation
    if (!$point_id || !$point_name || !$city || !$latitude || !$longitude) {
        header("Location: network_manage.php?error=missing_fields");
        exit;
    }

    // Prepare update statement
    $stmt = $conn->prepare("UPDATE network_points SET point_name=?, point_type=?, city=?, latitude=?, longitude=?, status=? WHERE point_id=?");
    $stmt->bind_param("sssdssi", $point_name, $point_type, $city, $latitude, $longitude, $status, $point_id);

    if ($stmt->execute()) {
        // Success
        header("Location: network_manage.php?success=point_updated");
    } else {
        // Error
        header("Location: network_manage.php?error=update_failed");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: network_manage.php");
    exit;
}
?>
