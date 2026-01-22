<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    $schedule_id = intval($_POST['schedule_id']);

    // Cancel schedule
    $sql = "UPDATE schedules SET status='cancelled' WHERE schedule_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        // ✅ Get the related route_id from schedules
        $getRoute = $conn->prepare("SELECT route_id FROM schedules WHERE schedule_id=?");
        $getRoute->bind_param("i", $schedule_id);
        $getRoute->execute();
        $result = $getRoute->get_result();
        if ($row = $result->fetch_assoc()) {
            $route_id = $row['route_id'];

            // ✅ Cancel the route
            $sql2 = "UPDATE routes SET status='cancelled' WHERE route_id=?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $route_id);
            $stmt2->execute();
            $stmt2->close();
        }
        $getRoute->close();

        // Add notification for cancelled schedule
        addNotification($conn, "Schedule #$schedule_id has been cancelled", 'warning', 'confirmed_timetables.php');

        echo "<script>alert('Schedule and its related route cancelled successfully.'); window.location='confirmed_timetables.php';</script>";
    } else {
        echo "<script>alert('Error cancelling schedule.'); window.location='scheduled_shipments.php';</script>";
    }

    $stmt->close();
}
$conn->close();
?>
