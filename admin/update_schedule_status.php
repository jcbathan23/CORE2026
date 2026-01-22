<?php
session_start();
include("../connect.php");

// Only allow logged-in providers
if (!isset($_SESSION['email']) || $_SESSION['account_type'] != 3) {
    header("Location: ../admin/loginpage.php");
    exit();
}

$email = $_SESSION['email'];

// Get provider_id for the logged-in provider
$stmt = $conn->prepare("SELECT provider_id FROM active_service_provider WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($provider_id);
$stmt->fetch();
$stmt->close();

// Allowed statuses
$allowed_status = ['pending', 'scheduled', 'in progress', 'delayed', 'completed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($schedule_id && $status && in_array(strtolower($status), $allowed_status)) {

        // Check schedule ownership
        $stmt = $conn->prepare("SELECT schedule_id, route_id, rate_id, status FROM schedules WHERE schedule_id = ? AND provider_id = ?");
        $stmt->bind_param("ii", $schedule_id, $provider_id);
        $stmt->execute();
        $stmt->bind_result($sched_id, $route_id, $rate_id, $current_status);

        if ($stmt->fetch()) {
            $stmt->close();

            // Prevent moving completed back to in progress/delayed
            if ($current_status === 'completed' && in_array(strtolower($status), ['in progress', 'delayed'])) {
                $_SESSION['error'] = "Cannot change a completed schedule back to In Progress or Delayed.";
            } else {
                $conn->begin_transaction();
                try {
                    // Update schedules
                    $stmt = $conn->prepare("UPDATE schedules SET status = ? WHERE schedule_id = ?");
                    $stmt->bind_param("si", $status, $schedule_id);
                    $stmt->execute();
                    $stmt->close();

                    // Update routes
                    $stmt = $conn->prepare("UPDATE routes SET status = ? WHERE route_id = ?");
                    $stmt->bind_param("si", $status, $route_id);
                    $stmt->execute();
                    $stmt->close();

                    // Update calculated_rates
                    if ($rate_id) {
                        $stmt = $conn->prepare("UPDATE calculated_rates SET status = ? WHERE id = ?");
                        $stmt->bind_param("si", $status, $rate_id);
                        $stmt->execute();
                        $stmt->close();
                    }

                    // Add notification for delayed or completed
                    if (in_array(strtolower($status), ['delayed', 'completed'])) {
                        $message = ($status === 'delayed') 
                            ? "Schedule ID $schedule_id has been delayed."
                            : "Schedule ID $schedule_id has been completed.";
                        $type = 'info';
                        $link = "provider_schedules.php";
                        $is_read = 0;

                        $stmt = $conn->prepare("INSERT INTO notifications (message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->bind_param("sssi", $message, $type, $link, $is_read);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $conn->commit();
                    $_SESSION['message'] = "Schedule and rate updated successfully!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['error'] = "Failed to update schedule: " . $e->getMessage();
                }
            }
        } else {
            $_SESSION['error'] = "Schedule not found or does not belong to you.";
        }

    } else {
        $_SESSION['error'] = "Invalid input.";
    }

    header("Location: provider_schedules.php");
    exit();
} else {
    header("Location: provider_schedules.php");
    exit();
}
?>
