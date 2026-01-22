<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if(isset($_POST['delete_user']) || isset($_POST['email'])){
    $email = $conn->real_escape_string($_POST['email']);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete from admin_list only
        $stmt = $conn->prepare("DELETE FROM admin_list WHERE email = ?");
        $stmt->bind_param("s", $email);
        
        if($stmt->execute() && $stmt->affected_rows > 0) {
            // Commit the transaction
            $conn->commit();
            $stmt->close();
            header("Location: profile.php?success=deleted");
        } else {
            // Rollback if no admin was found
            $conn->rollback();
            $stmt->close();
            header("Location: profile.php?error=user_not_found");
        }
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Delete admin error: " . $e->getMessage());
        error_log("Email: " . $email);
        header("Location: profile.php?error=delete_failed");
    }
    
    exit;
} else {
    // Redirect if accessed directly
    header("Location: profile.php");
    exit;
}
?>
