<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';

$currentYear = date('Y');
$months = [];
$monthLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = date('m', strtotime("-$i month"));
    $monthLabels[] = date('M', strtotime("-$i month"));
}

$spPendingData = $spActiveData = $spInactiveData = [];
$tariffsData = $sopData = [];

foreach ($months as $month) {
    $spPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pending_service_provider WHERE MONTH(date_submitted)='$month' AND YEAR(date_submitted)='$currentYear'"))['total'];
    $spActive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Active' AND MONTH(date_approved)='$month' AND YEAR(date_approved)='$currentYear'"))['total'];
    $spInactive = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Inactive' AND MONTH(date_approved)='$month' AND YEAR(date_approved)='$currentYear'"))['total'];

    $spPendingData[] = $spPending;
    $spActiveData[] = $spActive;
    $spInactiveData[] = $spInactive;

    $tariffsData[] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM freight_rates WHERE MONTH(created_at)='$month' AND YEAR(created_at)='$currentYear'"))['total'];
    $sopData[] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sop_documents WHERE MONTH(created_at)='$month' AND YEAR(created_at)='$currentYear'"))['total'];
}

$totalPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pending_service_provider"))['total'];
$totalActiveSP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Active'"))['total'];
$totalInactiveSP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Inactive'"))['total'];

$totalRoutes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM routes"))['total'];
$activeRoutes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM routes WHERE status != 'completed'"))['total'];

$totalPoints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM network_points"))['total'];
$activePoints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM network_points WHERE status='Active'"))['total'];

$totalSchedules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM schedules"))['total'];
$activeSchedules = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM schedules WHERE status='scheduled'"))['total'];

$totalSOP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sop_documents"))['total'];
$activeSOP = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM sop_documents WHERE status='Active'"))['total'];

$totalTariffs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status IN ('Active','Inactive')"))['total'];
$activeTariffs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM active_service_provider WHERE status='Active'"))['total'];

$summaryTiles = [
    ['Service Providers', $totalActiveSP + $totalInactiveSP, $totalActiveSP, $totalInactiveSP],
    ['Routes', $totalRoutes, $activeRoutes, $totalRoutes - $activeRoutes],
    ['Schedules', $totalSchedules, $activeSchedules, $totalSchedules - $activeSchedules],
    ['Service Points', $totalPoints, $activePoints, $totalPoints - $activePoints],
    ['SOPs', $totalSOP, $activeSOP, $totalSOP - $activeSOP],
    ['Tariffs', $totalTariffs, $activeTariffs, $totalTariffs - $activeTariffs],
];

$filename = 'dashboard_summary_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

fputcsv($out, ['Dashboard Summary Export']);
fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
fputcsv($out, []);

fputcsv($out, ['Module Summary']);
fputcsv($out, ['Module', 'Total', 'Active', 'Inactive']);
foreach ($summaryTiles as $row) {
    fputcsv($out, $row);
}

fputcsv($out, []);

fputcsv($out, ['Service Providers (Last 6 Months)']);
fputcsv($out, array_merge(['Month'], $monthLabels));
fputcsv($out, array_merge(['Pending'], $spPendingData));
fputcsv($out, array_merge(['Active'], $spActiveData));
fputcsv($out, array_merge(['Inactive'], $spInactiveData));

fputcsv($out, []);

fputcsv($out, ['Tariffs & SOPs (Last 6 Months)']);
fputcsv($out, array_merge(['Month'], $monthLabels));
fputcsv($out, array_merge(['Tariffs'], $tariffsData));
fputcsv($out, array_merge(['SOPs'], $sopData));

fputcsv($out, []);

fputcsv($out, ['Routes, Points, Schedules Totals']);
fputcsv($out, ['Metric', 'Count']);
fputcsv($out, ['Total Routes', $totalRoutes]);
fputcsv($out, ['Active Routes', $activeRoutes]);
fputcsv($out, ['Total Points', $totalPoints]);
fputcsv($out, ['Active Points', $activePoints]);
fputcsv($out, ['Total Schedules', $totalSchedules]);
fputcsv($out, ['Active Schedules', $activeSchedules]);

fputcsv($out, []);

fputcsv($out, ['Users & Admins']);
fputcsv($out, ['Metric', 'Count']);
$totalAdmin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(email) AS total FROM admin_list"))['total'];
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(email) AS total FROM newaccounts"))['total'];
fputcsv($out, ['Admins', $totalAdmin]);
fputcsv($out, ['Users', $totalUsers]);

fclose($out);
exit;
