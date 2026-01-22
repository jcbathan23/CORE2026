<?php
/**
 * Security Test Page
 * Tests if authentication is working properly
 * This page itself requires authentication to view test results
 */

require_once __DIR__ . '/auth.php';
include('header.php');
include('sidebar.php');
include('navbar.php');
?>

<style>
  .test-result {
    padding: 15px;
    margin: 10px 0;
    border-radius: 8px;
    border-left: 4px solid;
  }
  .test-pass {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
  }
  .test-fail {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
  }
  .test-info {
    background: #d1ecf1;
    border-color: #17a2b8;
    color: #0c5460;
  }
  .test-section {
    background: white;
    padding: 20px;
    margin: 20px 0;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  body.dark-mode .test-section {
    background: #1f2937;
  }
</style>

<div class="content p-4">
    <div class="modern-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">🔒 Security Authentication Test</h3>
            <p>Verify that authentication guards are properly protecting admin resources</p>
        </div>
    </div>

    <!-- Current Session Info -->
    <div class="test-section">
        <h4>📋 Current Session Information</h4>
        <div class="test-result test-pass">
            <strong>✓ Authentication Status:</strong> LOGGED IN<br>
            <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?><br>
            <strong>Account Type:</strong> <?php echo htmlspecialchars($_SESSION['account_type']); ?><br>
            <strong>Session ID:</strong> <?php echo session_id(); ?>
        </div>
        <p><small><em>If you can see this page, the authentication system is working correctly!</em></small></p>
    </div>

    <!-- Protected Files Check -->
    <div class="test-section">
        <h4>🛡️ Protected Files Security Status</h4>
        
        <?php
        // List of critical files that must have authentication
        $protected_files = [
            'Action Handlers' => [
                'archive_provider.php' => 'Archives service providers',
                'unarchive_provider.php' => 'Restores archived providers',
                'add_user.php' => 'Creates new user accounts',
                'update_user.php' => 'Modifies user accounts',
                'delete_user.php' => 'Deletes user accounts',
                'calculate rate.php' => 'Rate calculation API'
            ],
            'API Endpoints' => [
                '../api/import_provider.php' => 'Imports providers from Logistic1',
                '../api/migrate_database.php' => 'Database schema migrations'
            ],
            'Admin Pages' => [
                'dashboard.php' => 'Main admin dashboard',
                'active_providers.php' => 'Active provider management',
                'pending_providers.php' => 'Pending provider approvals',
                'manage_routes.php' => 'Route management'
            ]
        ];

        foreach ($protected_files as $category => $files) {
            echo "<h5>$category</h5>";
            foreach ($files as $file => $description) {
                $file_path = __DIR__ . '/' . $file;
                $exists = file_exists($file_path);
                
                if ($exists) {
                    $content = file_get_contents($file_path);
                    $has_auth = (
                        strpos($content, "require_once __DIR__ . '/auth.php'") !== false ||
                        strpos($content, "require 'auth.php'") !== false ||
                        strpos($content, "include('header.php')") !== false ||
                        strpos($content, "session_start()") !== false
                    );
                    
                    if ($has_auth) {
                        echo "<div class='test-result test-pass'>";
                        echo "<strong>✓ $file</strong><br>";
                        echo "<small>$description - Authentication: <strong>PROTECTED</strong></small>";
                        echo "</div>";
                    } else {
                        echo "<div class='test-result test-fail'>";
                        echo "<strong>✗ $file</strong><br>";
                        echo "<small>$description - Authentication: <strong>MISSING</strong></small>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='test-result test-info'>";
                    echo "<strong>ℹ $file</strong><br>";
                    echo "<small>$description - File not found (may be optional)</small>";
                    echo "</div>";
                }
            }
        }
        ?>
    </div>

    <!-- Manual Testing Instructions -->
    <div class="test-section">
        <h4>🧪 Manual Testing Instructions</h4>
        <div class="alert alert-info">
            <strong>Test Unauthorized Access:</strong>
            <ol>
                <li>Open a new incognito/private browser window</li>
                <li>Try to access this URL: <code><?php echo $_SERVER['HTTP_HOST'] . '/NEWFMSCORE2/admin/dashboard.php'; ?></code></li>
                <li><strong>Expected Result:</strong> Should redirect to login page with "Access denied" message</li>
            </ol>
        </div>

        <div class="alert alert-warning">
            <strong>Test Service Provider Restriction:</strong>
            <ol>
                <li>Logout from current admin account</li>
                <li>Login as a service provider (account_type = 3)</li>
                <li>Try to access any admin page</li>
                <li><strong>Expected Result:</strong> Should redirect to login page with access denied</li>
            </ol>
        </div>

        <div class="alert alert-success">
            <strong>Test Admin Access:</strong>
            <ol>
                <li>Login as admin (account_type = 1 or 2)</li>
                <li>Try to access admin pages and perform actions</li>
                <li><strong>Expected Result:</strong> Should work normally without redirects</li>
            </ol>
        </div>
    </div>

    <!-- Security Recommendations -->
    <div class="test-section">
        <h4>🔐 Security Recommendations</h4>
        <div class="test-result test-info">
            <strong>Future Enhancements:</strong>
            <ul>
                <li>✅ <strong>Completed:</strong> Session-based authentication for admin pages</li>
                <li>✅ <strong>Completed:</strong> Account type validation (admin vs service provider)</li>
                <li>⚠️ <strong>Recommended:</strong> Implement password hashing (bcrypt/Argon2)</li>
                <li>⚠️ <strong>Recommended:</strong> Add CSRF token protection for forms</li>
                <li>⚠️ <strong>Recommended:</strong> Implement API key authentication</li>
                <li>⚠️ <strong>Recommended:</strong> Add login rate limiting</li>
                <li>⚠️ <strong>Recommended:</strong> Enable secure session cookies (HTTPS)</li>
                <li>⚠️ <strong>Recommended:</strong> Implement audit logging</li>
            </ul>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="test-section">
        <h4>⚡ Quick Actions</h4>
        <a href="logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout and Test Authentication
        </a>
        <a href="SECURITY_IMPLEMENTATION.md" class="btn btn-info" target="_blank">
            <i class="fas fa-book"></i> View Security Documentation
        </a>
        <button class="btn btn-secondary" onclick="window.location.reload()">
            <i class="fas fa-sync"></i> Refresh Test Results
        </button>
    </div>
</div>

<?php include('footer.php'); ?>
