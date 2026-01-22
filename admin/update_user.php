<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if(isset($_POST['update_user']) || (isset($_POST['email']) && isset($_POST['password']))){
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $account_type = 'Admin'; // Always admin

    // Basic validation
    if(empty($email) || empty($password)) {
        header("Location: profile.php?error=missing_fields");
        exit;
    }

    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update the password for the admin account
        $stmt = $conn->prepare("UPDATE admin_list SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        
        if($stmt->execute() && $stmt->affected_rows > 0) {
            $conn->commit();
            header("Location: profile.php?success=updated");
        } else {
            $conn->rollback();
            error_log("SQL Error: " . $stmt->error);
            error_log("Email: " . $email);
            header("Location: profile.php?error=update_failed");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Update admin error: " . $e->getMessage());
        header("Location: profile.php?error=update_failed");
    }
    
    exit;
} else {
    // Redirect if accessed directly
    header("Location: profile.php");
    exit;
}
?>
