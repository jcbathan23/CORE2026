<?php
require_once __DIR__ . '/auth.php';
if (!isset($_GET['address'])) {
    echo json_encode(['error' => 'No address provided']);
    exit;
}

$address = urlencode($_GET['address']);
$url = "https://nominatim.openstreetmap.org/search?format=json&q={$address}&limit=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0'); // Required by Nominatim
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!$data || count($data) === 0) {
    echo json_encode(['error' => 'Address not found']);
} else {
    echo json_encode([
        'lat' => $data[0]['lat'],
        'lon' => $data[0]['lon'],
        'display_name' => $data[0]['display_name']
    ]);
}
