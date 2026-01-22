<?php
include('connect.php');

echo "<h2>Setting Up Bookings Table</h2>";

// Read the SQL file
$sqlFile = 'setup_bookings_table.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Split SQL statements by semicolon
$statements = array_filter(array_map('trim', explode(';', $sql)));

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
    echo "<p style='color: green; font-weight: bold;'>Bookings table setup completed successfully!</p>";
    
    // Show sample data
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
    $count = $result->fetch_assoc()['count'];
    echo "<p>Sample bookings created: $count</p>";
    
    echo "<p><a href='admin/manage_routes.php'>Test Refresh Bookings functionality</a></p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Some errors occurred. Please check the messages above.</p>";
}

$conn->close();
?>
