<?php
function addNotification($conn, $message, $type='info', $link=null) {
    $message = mysqli_real_escape_string($conn, $message);
    $link = $link ? mysqli_real_escape_string($conn, $link) : null;
    $sql = "INSERT INTO notifications (message, type, link) VALUES ('$message', '$type', '$link')";
    mysqli_query($conn, $sql);
}

function sendRejectionEmail($email, $contactPerson, $companyName, $reason, $remarks) {
    // Email configuration
    $to = $email;
    $subject = "Service Provider Application Status - " . $companyName;
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@newfmscore.com" . "\r\n";
    $headers .= "Reply-To: support@newfmscore.com" . "\r\n";
    
    // Email body
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Service Provider Application Status</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; border-radius: 0 0 8px 8px; }
            .reason-box { background: white; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; }
            .remarks-box { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #6c757d; }
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Application Status Update</h1>
                <p>Service Provider Registration</p>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($contactPerson) . ",</p>
                
                <p>We regret to inform you that your service provider application for <strong>" . htmlspecialchars($companyName) . "</strong> has been carefully reviewed and could not be approved at this time.</p>
                
                <div class='reason-box'>
                    <h3>Rejection Reason:</h3>
                    <p><strong>" . htmlspecialchars($reason) . "</strong></p>
                </div>";
    
    if (!empty($remarks)) {
        $emailBody .= "
                <div class='remarks-box'>
                    <h3>Additional Remarks:</h3>
                    <p>" . nl2br(htmlspecialchars($remarks)) . "</p>
                </div>";
    }
    
    $emailBody .= "
                <h3>Next Steps:</h3>
                <ul>
                    <li>Review the rejection reason and remarks provided above</li>
                    <li>Address the identified issues in your application</li>
                    <li>You may submit a new application once all requirements are met</li>
                </ul>
                
                <p>If you believe this decision was made in error or need clarification, please contact our support team.</p>
                
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " NewFMSCore. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    // Send email
    return mail($to, $subject, $emailBody, $headers);
}

function sendApprovalEmail($email, $contactPerson, $companyName, $username, $password) {
    // Email configuration
    $to = $email;
    $subject = "Welcome! Your Service Provider Application Has Been Approved - " . $companyName;
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@newfmscore.com" . "\r\n";
    $headers .= "Reply-To: support@newfmscore.com" . "\r\n";
    
    // Email body
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Service Provider Application Approved</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; border-radius: 0 0 8px 8px; }
            .credentials-box { background: white; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 5px; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #6c757d; }
            .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            .highlight { background: #d4edda; padding: 10px; border-radius: 5px; border: 1px solid #c3e6cb; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Congratulations!</h1>
                <p>Your Application Has Been Approved</p>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($contactPerson) . ",</p>
                
                <div class='highlight'>
                    <p>We are pleased to inform you that your service provider application for <strong>" . htmlspecialchars($companyName) . "</strong> has been successfully reviewed and approved!</p>
                </div>
                
                <p>You are now part of our trusted network of service providers. You can start managing your services and schedules through our platform.</p>
                
                <div class='credentials-box'>
                    <h3>🔐 Your Login Credentials:</h3>
                    <p><strong>Email/Username:</strong> " . htmlspecialchars($username) . "</p>
                    <p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>
                    <p class='text-muted'><small>Please keep these credentials secure and do not share them with anyone.</small></p>
                </div>
                
                <h3>Next Steps:</h3>
                <ul>
                    <li>Log in to your provider dashboard using the credentials above</li>
                    <li>Complete your company profile and service details</li>
                    <li>Set up your service areas and availability</li>
                    <li>Start receiving delivery requests and managing schedules</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://newfmscore.com/admin/provider_dashboard.php' class='btn'>
                        Go to Provider Dashboard
                    </a>
                </div>
                
                <p>If you have any questions or need assistance getting started, please contact our support team.</p>
                
                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " NewFMSCore. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    // Send email
    return mail($to, $subject, $emailBody, $headers);
}
?>
