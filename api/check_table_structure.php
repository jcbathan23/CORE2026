<?php
// Check the current table structure
require_once '../connect.php';

echo "<h2>Pending Service Provider Table Structure</h2>";

// Get table structure
$result = $conn->query("DESCRIBE pending_service_provider");

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Check if external_id and website columns exist
$columns_to_check = ['external_id', 'website'];
$existing_columns = [];

$result = $conn->query("SHOW COLUMNS FROM pending_service_provider");
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

echo "<h3>Column Check:</h3>";
foreach ($columns_to_check as $column) {
    if (in_array($column, $existing_columns)) {
        echo "<p style='color: green;'>✅ Column '$column' exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Column '$column' missing</p>";
        echo "<p>SQL to add: <code>ALTER TABLE pending_service_provider ADD COLUMN $column VARCHAR(255);</code></p>";
    }
}

$conn->close();
?>
