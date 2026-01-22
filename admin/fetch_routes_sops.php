<?php
include '../connect.php'; // adjust path
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Get routes + provider info
$routes = [];
$sql = "SELECT r.route_id, r.provider_id, a.company_name, r.origin, r.destination 
        FROM routes r 
        JOIN active_provider a ON r.provider_id = a.id";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    $routes[] = $row;
}

// Get SOPs
$sops = [];
$sql2 = "SELECT sop_id, title AS sop_name FROM sop_documents";
$res2 = $conn->query($sql2);
while($row2 = $res2->fetch_assoc()) {
    $sops[] = $row2;
}

echo json_encode(['routes'=>$routes, 'sops'=>$sops]);
