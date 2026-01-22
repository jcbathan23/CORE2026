<?php
/**
 * Database Migration Script
 * Adds necessary columns for Logistic1 integration
 * CRITICAL: This script requires admin authentication
 */

// Authentication check - must be logged in as admin
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['account_type']) || $_SESSION['account_type'] === 3) {
    die('<h2>Access Denied</h2><p>You must be logged in as an administrator to run database migrations.</p>');
}

require_once '../connect.php';

echo "<h2>Database Migration for Logistic1 Integration</h2>";

// Check if columns exist
$result = $conn->query("SHOW COLUMNS FROM pending_service_provider");
$existing_columns = [];

while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

$migrations = [
    [
        'column' => 'external_id',
        'sql' => "ALTER TABLE pending_service_provider ADD COLUMN external_id VARCHAR(255) NULL",
        'description' => 'External ID for tracking imported providers'
    ],
    [
        'column' => 'website',
        'sql' => "ALTER TABLE pending_service_provider ADD COLUMN website VARCHAR(255) NULL",
        'description' => 'Website URL field'
    ]
];

$migrations_run = 0;

foreach ($migrations as $migration) {
    if (!in_array($migration['column'], $existing_columns)) {
        echo "<p>Adding column '{$migration['column']}'...</p>";
        
        if ($conn->query($migration['sql'])) {
            echo "<p style='color: green;'>✅ Successfully added column '{$migration['column']}'</p>";
            $migrations_run++;
        } else {
            echo "<p style='color: red;'>❌ Failed to add column '{$migration['column']}': " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column '{$migration['column']}' already exists</p>";
    }
}

if ($migrations_run > 0) {
    echo "<h3 style='color: green;'>Migration completed! $migrations_run columns added.</h3>";
} else {
    echo "<h3 style='color: blue;'>No migrations needed. Database is up to date.</h3>";
}

echo "<h3>Current Table Structure:</h3>";
$result = $conn->query("DESCRIBE pending_service_provider");

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

while ($row = $result->fetch_assoc()) {
    $highlight = in_array($row['Field'], ['external_id', 'website']) ? 'background: #e8f5e8;' : '';
    echo "<tr style='$highlight'>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>

<p><strong>Next Steps:</strong></p>
<ol>
    <li>The database is now ready for Logistic1 imports</li>
    <li>Go to the pending providers page</li>
    <li>Load Logistic1 data and click "Import" on any provider</li>
    <li>The imported provider will appear in the "PENDING SERVICE PROVIDERS" table</li>
</ol>
