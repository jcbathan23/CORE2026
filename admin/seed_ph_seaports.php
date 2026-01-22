<?php
require_once __DIR__ . '/auth.php';
require '../connect.php';

$ok = @set_time_limit(0);
@ini_set('max_execution_time', '0');

$doGeocode = isset($_GET['geocode']) && $_GET['geocode'] === '1';
$onlyMissing = isset($_GET['only_missing']) && $_GET['only_missing'] === '1';

function contains_str($haystack, $needle) {
    if ($needle === '') return true;
    return strpos($haystack, $needle) !== false;
}

function normalize_port_name($name) {
    $n = trim($name);
    if ($n === '') return $n;
    if (preg_match('/\bport\b/i', $n)) return $n;
    return $n . ' Port';
}

function geocode_one($query) {
    $q = urlencode($query);
    $url = "https://nominatim.openstreetmap.org/search?format=json&q={$q}&countrycodes=ph&addressdetails=1&limit=5";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0');
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data || count($data) === 0) return [null, null];

    $best = null;
    $bestScore = -1;
    foreach ($data as $row) {
        $score = 0;
        $class = strtolower($row['class'] ?? '');
        $type = strtolower($row['type'] ?? '');
        $display = strtolower($row['display_name'] ?? '');

        if (contains_str($type, 'harbour') || contains_str($type, 'port')) $score += 3;
        if (contains_str($class, 'amenity')) $score += 1;
        if (contains_str($display, 'port')) $score += 1;

        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $row;
        }
    }

    if (!$best) return [null, null];
    return [floatval($best['lat']), floatval($best['lon'])];
}

function geocode_port($baseName, $pointName, $city) {
    $nameCandidates = [];
    $base = trim($baseName);
    $nameCandidates[] = $base;
    if (contains_str($base, '/')) {
        $parts = array_values(array_filter(array_map('trim', explode('/', $base))));
        foreach ($parts as $p) $nameCandidates[] = $p;
    }
    $nameCandidates = array_values(array_unique($nameCandidates));

    $cityCandidates = [];
    $c = trim($city);
    $cityCandidates[] = $c;
    if (stripos($c, ' and ') !== false) {
        $cityCandidates[] = trim(str_ireplace(' and ', ', ', $c));
        $first = trim(explode(' and ', $c, 2)[0]);
        if ($first !== '') $cityCandidates[] = $first;
    }
    if (contains_str($c, '/')) {
        $cityCandidates[] = trim(explode('/', $c, 2)[0]);
    }
    $cityCandidates = array_values(array_unique(array_filter($cityCandidates)));

    foreach ($cityCandidates as $cityTry) {
        foreach ($nameCandidates as $nameTry) {
            $queries = [
                $nameTry . ' Port, ' . $cityTry . ', Philippines',
                'Port of ' . $nameTry . ', ' . $cityTry . ', Philippines',
                $nameTry . ', ' . $cityTry . ', Philippines',
                $pointName . ', ' . $cityTry . ', Philippines',
                $nameTry . ', Philippines',
            ];

            foreach ($queries as $q) {
                [$lat, $lng] = geocode_one($q);
                if (!is_null($lat) && !is_null($lng)) {
                    return [$lat, $lng];
                }
                usleep(250000);
            }
        }
    }

    return [null, null];
}

$seaports = [
    ['Manila', 'Port Area, Manila', 14.5869, 120.9650],
    ['Subic Bay', 'Olongapo, Zambales and Morong, Bataan', 14.8294, 120.2828],
    ['Abra de Ilog', 'Abra de Ilog, Occidental Mindoro', 13.4386, 120.7215],
    ['Ambulong', 'Magdiwang, Romblon', 12.5792, 122.5386],
    ['Batangas', 'Batangas City', 13.7565, 121.0583],
    ['Balancan', 'Mogpog, Marinduque', 13.4767, 121.9189],
    ['Calapan', 'Calapan City', 13.4108, 121.1806],
    ['Cawit', 'Boac, Marinduque', 13.4453, 121.8392],
    ['Currimao', 'Currimao, Ilocos Norte', 18.0194, 120.4864],
    ['Legazpi', 'Legazpi, Albay', 13.1391, 123.7350],
    ['Lucena', 'Lucena City', 13.9316, 121.6171],
    ['Odiongan/Pocoy', 'Odiongan, Romblon', 12.4039, 121.9847],
    ['Puerto Princesa', 'Puerto Princesa', 9.7392, 118.7353],
    ['Romblon', 'Romblon, Romblon', 12.5753, 122.2706],
    ['Dangay', 'Roxas, Oriental Mindoro', 12.5854, 121.5042],
    ['Caminawit', 'San Jose, Occidental Mindoro', 12.3546, 121.0572],
    ['Sual', 'Sual, Pangasinan', 16.0639, 119.9508],

    ['Banago', 'Bacolod', 10.6874, 122.9455],
    ['Banate', 'Banate, Iloilo', 11.0398, 122.7775],
    ['Bato', 'Samboan, Cebu', 9.4606, 123.3047],
    ['Baybay', 'Baybay, Leyte', 10.6782, 124.8013],
    ['BREDCO', 'Bacolod'],
    ['Calbayog', 'Calbayog, Samar'],
    ['Catbalogan', 'Catbalogan, Samar'],
    ['Caticlan', 'Malay, Aklan'],
    ['Cebu', 'Cebu City'],
    ['Danao', 'Danao, Cebu'],
    ['Dumaguete', 'Dumaguete, Negros Oriental'],
    ['Dumagit', 'New Washington, Aklan'],
    ['Dumangas', 'Dumangas, Iloilo'],
    ['Escalante', 'Escalante, Negros Occidental'],
    ['Getafe', 'Getafe, Bohol'],
    ['Guihulngan', 'Guihulngan, Negros Oriental'],
    ['Hagnaya', 'San Remigio, Cebu'],
    ['Hilongos', 'Hilongos, Leyte'],
    ['Iloilo', 'Iloilo City'],
    ['Jagna', 'Jagna, Bohol'],
    ['Jordan', 'Jordan, Guimaras'],
    ['Larena', 'Larena, Siquijor'],
    ['Liloan/Santander', 'Santander, Cebu'],
    ['Liloan', 'Liloan, Southern Leyte'],
    ['Maasin', 'Maasin, Southern Leyte'],
    ['Naval', 'Naval, Biliran'],
    ['Ormoc', 'Ormoc'],
    ['Palompon', 'Palompon, Leyte'],
    ['Pulupandan', 'Pulupandan, Negros Occidental'],
    ['Roxas City/Culasi', 'Roxas, Capiz'],
    ['San Carlos', 'San Carlos, Negros Occidental'],
    ['Sibulan', 'Sibulan, Negros Oriental'],
    ['Tacloban', 'Tacloban'],
    ['Tabuelan', 'Tabuelan, Cebu'],
    ['Talibon', 'Talibon, Bohol'],
    ['Tagbilaran', 'Tagbilaran, Bohol'],
    ['Tampi', 'San Jose, Negros Oriental'],
    ['Tandaya', 'Amlan, Negros Oriental'],
    ['Toledo', 'Toledo, Cebu'],
    ['Tubigon', 'Tubigon, Bohol'],
    ['Ubay', 'Ubay, Bohol'],

    ['Benoni', 'Mahinog, Camiguin'],
    ['Davao', 'Davao City'],
    ['Dipolog', 'Dipolog, Zamboanga del Norte'],
    ['Zamboanga', 'Zamboanga City'],
    ['Cagayan de Oro', 'Cagayan de Oro'],
    ['General Santos', 'General Santos'],
    ['Iligan', 'Iligan'],
    ['Jimenez', 'Jimenez, Misamis Occidental'],
    ['Mukas', 'Kalumbugan, Lanao del Norte'],
    ['Nasipit/Butuan', 'Nasipit, Agusan del Norte'],
    ['Oroquieta', 'Oroquieta, Misamis Occidental'],
    ['Ozamiz', 'Ozamiz City'],
    ['Pagadian', 'Pagadian, Zamboanga del Sur'],
    ['Plaridel', 'Plaridel, Misamis Occidental'],
    ['Dapitan', 'Dapitan, Zamboanga del Norte'],
    ['San Jose', 'San Jose, Dinagat Islands'],
    ['Surigao', 'Surigao City'],
    ['Dapa', 'Dapa, Siargao'],
    ['Jubang', 'Dapa, Siargao'],
];

// Warehouses with exact coordinates
$warehouses = [
    ['NXLP NLI – G Warehouse (LIMA Tech Center)', 'Lipa / Malvar, Batangas', 13.9511, 121.1636],
    ['NXLP NLI – C & D Warehouse (LIMA Tech Center)', 'Malvar, Batangas', 13.9385, 121.1610],
    ['NXLP CEZ – 1 Warehouse (Cavite Economic Zone)', 'Rosario, Cavite', 14.3973, 120.8856],
    ['NXLP PTC Warehouse (People\'s Tech Complex)', 'Carmona, Cavite', 14.2932, 120.9370],
    ['NXLP MEZ – 1 Main Warehouse', 'Lapu-Lapu City, Cebu (Mactan Econ Zone)', 10.3157, 123.9628],
    ['NXLP MEZ – 2 Warehouse', 'Lapu-Lapu City, Cebu', 10.3135, 123.9620],
    ['LTI Phase 2 Orient Warehouse', 'Biñan, Laguna (Laguna Technopark)', 14.3306, 121.1364],
    ['NXLP LTI Main Warehouse', 'Biñan, Laguna', 14.3310, 121.1360],
    ['NXLP LTI Annex Warehouse', 'Biñan, Laguna', 14.3320, 121.1370],
    ['NXLP LTI Phase 6A Warehouse', 'Biñan, Laguna', 14.3330, 121.1380],
    ['Air Cargo Division / Ocean Cargo Division (FTI Warehouses)', 'Taguig City, Metro Manila', 14.5176, 121.0493],
    ['Logistics Division (FT12)', 'Taguig / Paranaque, Metro Manila', 14.4750, 121.0200],
    ['Clark Warehouse Satellite Office', 'Clark Freeport Zone, Pampanga', 15.1819, 120.5583],
    ['ISLA Logistics Warehouse – Manila', 'Manila, Luzon', 14.5995, 120.9842],
    ['ISLA Logistics Warehouse – Clark', 'Clark, Pampanga', 15.1859, 120.5600],
    ['ISLA Logistics Warehouse – Cebu', 'Cebu City / Mandaue', 10.3157, 123.9175],
];

$inserted = 0;
$updated = 0;
$skipped = 0;
$geocoded = 0;
$failed = [];

// Prepare statements for ports
$selectPortStmt = $conn->prepare("SELECT point_id, latitude, longitude FROM network_points WHERE point_type='Port' AND city = ? AND (point_name = ? OR point_name = ?) LIMIT 1");
$insertPortNoCoordsStmt = $conn->prepare("INSERT INTO network_points (point_name, point_type, country, city, latitude, longitude, status) VALUES (?, 'Port', 'Philippines', ?, NULL, NULL, 'Active')");
$insertPortWithCoordsStmt = $conn->prepare("INSERT INTO network_points (point_name, point_type, country, city, latitude, longitude, status) VALUES (?, 'Port', 'Philippines', ?, ?, ?, 'Active')");
$updatePortNoCoordsStmt = $conn->prepare("UPDATE network_points SET country='Philippines', city=? WHERE point_id=?");
$updatePortWithCoordsStmt = $conn->prepare("UPDATE network_points SET country='Philippines', city=?, latitude=COALESCE(latitude, ?), longitude=COALESCE(longitude, ?) WHERE point_id=?");

// Prepare statements for warehouses
$selectWarehouseStmt = $conn->prepare("SELECT point_id, latitude, longitude FROM network_points WHERE point_type='Warehouse' AND city = ? AND point_name = ? LIMIT 1");
$insertWarehouseStmt = $conn->prepare("INSERT INTO network_points (point_name, point_type, country, city, latitude, longitude, status) VALUES (?, 'Warehouse', 'Philippines', ?, ?, ?, 'Active')");
$updateWarehouseStmt = $conn->prepare("UPDATE network_points SET country='Philippines', city=?, latitude=COALESCE(latitude, ?), longitude=COALESCE(longitude, ?) WHERE point_id=?");

if (!$selectPortStmt || !$insertPortNoCoordsStmt || !$insertPortWithCoordsStmt || !$updatePortNoCoordsStmt || !$updatePortWithCoordsStmt || !$selectWarehouseStmt || !$insertWarehouseStmt || !$updateWarehouseStmt) {
    http_response_code(500);
    echo 'Prepare failed: ' . htmlspecialchars($conn->error);
    exit;
}

// Process seaports
foreach ($seaports as $sp) {
    $baseName = $sp[0];
    $city = $sp[1];
    $pointName = normalize_port_name($baseName);
    $providedLat = isset($sp[2]) ? $sp[2] : null;
    $providedLng = isset($sp[3]) ? $sp[3] : null;

    $altName = trim($baseName);
    $selectPortStmt->bind_param('sss', $city, $pointName, $altName);
    $selectPortStmt->execute();
    $res = $selectPortStmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;

    $existingLat = ($row && array_key_exists('latitude', $row)) ? $row['latitude'] : null;
    $existingLng = ($row && array_key_exists('longitude', $row)) ? $row['longitude'] : null;
    $hasCoords = !is_null($existingLat) && $existingLat !== '' && !is_null($existingLng) && $existingLng !== '';

    $lat = null;
    $lng = null;

    if ($doGeocode && (!$onlyMissing || !$hasCoords)) {
        // Prefer provided coordinates over geocoding
        if (!is_null($providedLat) && !is_null($providedLng)) {
            $lat = $providedLat;
            $lng = $providedLng;
            $geocoded++;
        } else {
            [$lat, $lng] = geocode_port($baseName, $pointName, $city);
            if (!is_null($lat) && !is_null($lng)) {
                $geocoded++;
            } else {
                $failed[] = $pointName . ' — ' . $city;
            }
            usleep(800000);
        }
    }

    if ($row && isset($row['point_id'])) {
        $pid = intval($row['point_id']);
        if (!is_null($lat) && !is_null($lng)) {
            $updatePortWithCoordsStmt->bind_param('sddi', $city, $lat, $lng, $pid);
            if ($updatePortWithCoordsStmt->execute()) {
                $updated++;
            } else {
                $skipped++;
            }
        } else {
            $updatePortNoCoordsStmt->bind_param('si', $city, $pid);
            if ($updatePortNoCoordsStmt->execute()) {
                $updated++;
            } else {
                $skipped++;
            }
        }
    } else {
        if (!is_null($lat) && !is_null($lng)) {
            $insertPortWithCoordsStmt->bind_param('ssdd', $pointName, $city, $lat, $lng);
            if ($insertPortWithCoordsStmt->execute()) {
                $inserted++;
            } else {
                $skipped++;
            }
        } else {
            $insertPortNoCoordsStmt->bind_param('ss', $pointName, $city);
            if ($insertPortNoCoordsStmt->execute()) {
                $inserted++;
            } else {
                $skipped++;
            }
        }
    }
}

// Process warehouses (all have exact coordinates)
foreach ($warehouses as $wh) {
    $name = $wh[0];
    $city = $wh[1];
    $lat = $wh[2];
    $lng = $wh[3];

    $selectWarehouseStmt->bind_param('ss', $city, $name);
    $selectWarehouseStmt->execute();
    $res = $selectWarehouseStmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;

    if ($row && isset($row['point_id'])) {
        $pid = intval($row['point_id']);
        $updateWarehouseStmt->bind_param('sddi', $city, $lat, $lng, $pid);
        if ($updateWarehouseStmt->execute()) {
            $updated++;
        } else {
            $skipped++;
        }
    } else {
        $insertWarehouseStmt->bind_param('ssdd', $name, $city, $lat, $lng);
        if ($insertWarehouseStmt->execute()) {
            $inserted++;
            $geocoded++; // Count as geocoded since we have exact coords
        } else {
            $skipped++;
        }
    }
}

$selectPortStmt->close();
$insertPortNoCoordsStmt->close();
$insertPortWithCoordsStmt->close();
$updatePortNoCoordsStmt->close();
$updatePortWithCoordsStmt->close();
$selectWarehouseStmt->close();
$insertWarehouseStmt->close();
$updateWarehouseStmt->close();

header('Content-Type: text/html; charset=utf-8');

echo '<div style="font-family:Arial,sans-serif; padding:16px;">';
echo '<h3>Seaports Seed Result</h3>';
echo '<div>Inserted: ' . intval($inserted) . '</div>';
echo '<div>Updated: ' . intval($updated) . '</div>';
echo '<div>Geocoded: ' . intval($geocoded) . '</div>';
echo '<div>Skipped: ' . intval($skipped) . '</div>';
if ($doGeocode) {
    $failedUnique = array_values(array_unique($failed));
    echo '<div>Failed geocodes: ' . intval(count($failedUnique)) . '</div>';
    if (count($failedUnique)) {
        echo '<div style="margin-top:10px;"><strong>Still missing coordinates:</strong></div>';
        echo '<ul>';
        foreach ($failedUnique as $f) {
            echo '<li>' . htmlspecialchars($f) . '</li>';
        }
        echo '</ul>';
    }
}
echo '<div style="margin-top:12px;">Done. You can now go back to <a href="manage_routes.php">Manage Routes</a>.</div>';
echo '</div>';
