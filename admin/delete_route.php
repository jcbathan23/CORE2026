<?php
include('../connect.php'); // adjust path if needed
require_once __DIR__ . '/auth.php';
require 'functions.php';

if (isset($_GET['table']) && isset($_GET['id'])) {
    $table = $_GET['table'];
    $id = intval($_GET['id']); // force integer for security

    // allowlist table names to prevent SQL injection
    $allowed_tables = ['routes']; 

    if (in_array($table, $allowed_tables)) {
        // Get route details before deletion for notification
        $getRouteStmt = $conn->prepare("
            SELECT r.*, 
                   o.point_name as origin_name, 
                   d.point_name as destination_name 
            FROM routes r 
            LEFT JOIN network_points o ON r.origin_id = o.point_id 
            LEFT JOIN network_points d ON r.destination_id = d.point_id 
            WHERE r.route_id = ?
        ");
        $getRouteStmt->bind_param("i", $id);
        $getRouteStmt->execute();
        $result = $getRouteStmt->get_result();
        $route = $result->fetch_assoc();
        
        $routeInfo = $route ? 
            $route['origin_name'] . ' to ' . $route['destination_name'] . ' (' . $route['carrier_type'] . ')' : 
            'Route ID: ' . $id;

        $sql = "DELETE FROM $table WHERE route_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Add notification for route deletion
            addNotification($conn, "Route deleted: $routeInfo", 'warning', 'manage_routes.php');
            header("Location: manage_routes.php?success=deleted");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        echo "Invalid table.";
    }
} else {
    echo "Invalid request.";
}
?>
