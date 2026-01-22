<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if(isset($_POST['add_user']) || (isset($_POST['email']) && isset($_POST['password']))){
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $account_type = 'Admin'; // Always admin

    // Basic validation
    if(empty($email) || empty($password)) {
        header("Location: profile.php?error=missing_fields");
        exit;
    }

    // Email validation
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: profile.php?error=invalid_email");
        exit;
    }

    try {
        // Check if email already exists in admin_list
        $stmt = $conn->prepare("SELECT email FROM admin_list WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            $stmt->close();
            header("Location: profile.php?error=email_exists");
            exit;
        }
        $stmt->close();

        // Start transaction
        $conn->begin_transaction();

        // Insert into admin_list only
        $stmt = $conn->prepare("INSERT INTO admin_list (email, password, account_type) VALUES (?, ?, 1)");
        $stmt->bind_param("ss", $email, $password);
        
        if($stmt->execute()) {
            $conn->commit();
            header("Location: profile.php?success=added");
        } else {
            $conn->rollback();
            error_log("SQL Error: " . $stmt->error);
            error_log("Email: " . $email);
            header("Location: profile.php?error=add_failed");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Add admin error: " . $e->getMessage());
        header("Location: profile.php?error=add_failed");
    }
    
    exit;
} else {
    // Redirect if accessed directly
    header("Location: profile.php");
    exit;
}
?>
