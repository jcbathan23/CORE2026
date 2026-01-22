<?php
// Test database connection
$conn = mysqli_connect('localhost','root','','newcore2');

if (mysqli_connect_error()) {
    die('Connection failed: ' . mysqli_connect_error());
} else {
    echo 'Successfully connected to MySQL server!';
    
    // Test if database exists and is accessible
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        echo "<br>Successfully connected to database 'newcore2'. Found tables: <br>";
        while ($row = mysqli_fetch_row($result)) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "<br>Connected to MySQL but could not access database 'newcore2'. Error: " . mysqli_error($conn);
    }
    
    mysqli_close($conn);
}
