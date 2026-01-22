<?php
include('../connect.php'); // make sure this path is correct
require_once __DIR__ . '/auth.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize input
    $rate_id = intval($_POST['rate_id']);
    $route_id = intval($_POST['route_id']);
    $sop_id = intval($_POST['sop_id']);
    $schedule_date = $_POST['schedule_date'];
    $schedule_time = $_POST['schedule_time'];
    $provider_id = intval($_POST['provider_id']);
    $total_rate = floatval($_POST['total_rate']);

    // Basic validation
    if (empty($rate_id) || empty($route_id) || empty($sop_id) || empty($schedule_date) || empty($schedule_time)) {
        die("All fields are required.");
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1️⃣ Insert into schedules
        $stmt = $conn->prepare("INSERT INTO schedules (rate_id, route_id, provider_id, sop_id, schedule_date, schedule_time, total_rate) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiissd", $rate_id, $route_id, $provider_id, $sop_id, $schedule_date, $schedule_time, $total_rate);
        $stmt->execute();
        $stmt->close();

        // 2️⃣ Update calculated_rates status
        $status = "scheduled";
        $updateRate = $conn->prepare("UPDATE calculated_rates SET status = ? WHERE id = ?");
        $updateRate->bind_param("si", $status, $rate_id);
        $updateRate->execute();
        $updateRate->close();

        // 3️⃣ Optional: Update routes status if you want
        /*
        $updateRoute = $conn->prepare("UPDATE routes SET status = ? WHERE route_id = ?");
        $updateRoute->bind_param("si", $status, $route_id);
        $updateRoute->execute();
        $updateRoute->close();
        */

        // Commit transaction
        $conn->commit();

        // Add notification for new schedule
        addNotification($conn, "New schedule created for $schedule_date at $schedule_time (Rate: $$total_rate)", 'success', 'confirmed_timetables.php');

        // Redirect back with success flag
        header("Location: schedule_routes.php?success=1");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }

    $conn->close();

} else {
    die("Invalid request method.");
}
?>
