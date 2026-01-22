<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';
require 'functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];
    $id = intval($_POST['id']);

    if($table && $id) {
        // Get point name before deletion for notification
        $getPointStmt = $conn->prepare("SELECT point_name, city FROM `$table` WHERE point_id = ?");
        $getPointStmt->bind_param("i", $id);
        $getPointStmt->execute();
        $result = $getPointStmt->get_result();
        $point = $result->fetch_assoc();
        $pointInfo = $point ? $point['point_name'] . ' (' . $point['city'] . ')' : 'Point ID: ' . $id;

        $stmt = $conn->prepare("DELETE FROM `$table` WHERE point_id = ?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()) {
            // Add notification for point deletion
            addNotification($conn, "Network point deleted: $pointInfo", 'warning', 'network_manage.php');
            header("Location: network_manage.php?success=point_deleted");
            exit;
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}
?>
