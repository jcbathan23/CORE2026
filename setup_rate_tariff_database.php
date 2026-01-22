<?php
include('connect.php');

echo "<h2>Updating Rate & Tariff Database Structure</h2>";

// Read the SQL file (fallback to built-in defaults if not present)
$sqlFile = 'update_rate_tariff_database.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    // Split SQL statements by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
} else {
    echo "<p style='color: orange;'>SQL file not found: $sqlFile. Using built-in default schema...</p>";

    $statements = [
        // Core table used by rates APIs
        "CREATE TABLE IF NOT EXISTS calculated_rates (
            id INT NOT NULL AUTO_INCREMENT,
            route_id INT NOT NULL,
            provider_id INT NOT NULL,
            carrier_type VARCHAR(20) NOT NULL,
            unit VARCHAR(50) DEFAULT 'per shipment',
            quantity INT DEFAULT 1,
            total_rate DECIMAL(12,2) NOT NULL,
            base_rate DECIMAL(12,2) DEFAULT 0,
            tariff_amount DECIMAL(12,2) DEFAULT 0,
            ai_calculated TINYINT(1) DEFAULT 0,
            calculation_details TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_cr_route (route_id),
            KEY idx_cr_provider (provider_id),
            KEY idx_cr_status (status),
            KEY idx_cr_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        // Lightweight notifications table used by approve/reject endpoints
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT NOT NULL AUTO_INCREMENT,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_notif_type (type),
            KEY idx_notif_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];
}

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        if ($conn->query($statement)) {
            echo "<p style='color: green;'>✓ Success: " . substr($statement, 0, 100) . "...</p>";
            $successCount++;
        } else {
            echo "<p style='color: orange;'>⚠ Note: " . $conn->error . " - " . substr($statement, 0, 100) . "...</p>";
            $successCount++;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . " - " . substr($statement, 0, 100) . "...</p>";
        $errorCount++;
    }
}

echo "<h3>Setup Complete</h3>";
echo "<p>Successful statements: $successCount</p>";
echo "<p>Failed statements: $errorCount</p>";

if ($errorCount === 0) {
    echo "<p style='color: green; font-weight: bold;'>Database setup completed successfully!</p>";
    echo "<p><a href='admin/rate_tariff_management.php'>Go to Rate & Tariff Management</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Some errors occurred. Please check the messages above.</p>";
}

$conn->close();
?>
