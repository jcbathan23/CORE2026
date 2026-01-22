<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';
require 'functions.php';

// Get form inputs
$name   = $_POST['point_name'] ?? '';
$type   = $_POST['point_type'] ?? '';
$city   = $_POST['city'] ?? '';
$status = $_POST['status'] ?? 'Active';

// Default coordinates
$latitude = null;
$longitude = null;

// If latitude/longitude already provided (from map), use them
if (!empty($_POST['latitude']) && !empty($_POST['longitude'])) {
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
} else if (!empty($city)) {
    // Otherwise, geocode the city using Nominatim
    $encodedCity = urlencode($city);
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=$encodedCity";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "your-app-name/1.0 (your_email@example.com)"); // REQUIRED by Nominatim
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("cURL error: " . curl_error($ch));
    }
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data)) {
            $latitude = floatval($data[0]['lat']);
            $longitude = floatval($data[0]['lon']);
        }
    }
}

// Prepare and insert into DB
$stmt = $conn->prepare("INSERT INTO network_points 
    (point_name, point_type, city, latitude, longitude, status) 
    VALUES (?, ?, ?, ?, ?, ?)");

// Correct bind_param types: s=string, d=double
$stmt->bind_param("sssdss", $name, $type, $city, $latitude, $longitude, $status);

if ($stmt->execute()) {
    // Add notification for new network point
    addNotification($conn, "New network point added: '$name' in $city ($type)", 'success', 'network_manage.php');
    header("Location: network_manage.php?success=point_saved");
    exit();
} else {
    die("DB error: " . $stmt->error);
}
