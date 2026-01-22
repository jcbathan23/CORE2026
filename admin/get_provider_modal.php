<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM pending_service_provider WHERE registration_id = $id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "<div class='modal-body'><div class='alert alert-danger'>Provider not found.</div></div>";
        exit;
    }
} else {
    echo "<div class='modal-body'><div class='alert alert-warning'>No ID provided.</div></div>";
    exit;
}
?>

<div class="modal-header bg-info text-black">
  <h5 class="modal-title">Service Provider Details</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
  <style>
    .service-badge{display:inline-flex;align-items:center;gap:6px;padding:0.35rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:600;letter-spacing:0.3px;margin:2px 4px 2px 0}
    .service-badge i{font-size:.8rem}
    .service-land{background:linear-gradient(135deg,#198754,#20c997);color:#fff}
    .service-air{background:linear-gradient(135deg,#0d6efd,#6ea8fe);color:#fff}
    .service-sea{background:linear-gradient(135deg,#0aa2c0,#20c997);color:#fff}
  </style>
  <table class="table table-bordered">
    <tr><th>Company Name</th><td><?= htmlspecialchars($row['company_name'] ?? '') ?></td></tr>
    <tr><th>Email</th><td><?= htmlspecialchars($row['email'] ?? '') ?></td></tr>
    <tr><th>Contact Person</th><td><?= htmlspecialchars($row['contact_person'] ?? '') ?></td></tr>
    <tr><th>Contact Number</th><td><?= htmlspecialchars($row['contact_number'] ?? '') ?></td></tr>
    <tr><th>Address</th><td><?= htmlspecialchars($row['address'] ?? '') ?></td></tr>
    <tr><th>Services Offered</th><td>
      <?php
        $sv = $row['services'] ?? '';
        $services = array_filter(array_map('trim', preg_split('/[,&]|\n|\r/',$sv)));
        if (!$services) echo '<span class="text-muted">N/A</span>';
        foreach ($services as $svc) {
          $label = strtoupper($svc);
          $cls = 'service-badge ';
          if ($label === 'LAND') $cls .= 'service-land';
          elseif ($label === 'AIR') $cls .= 'service-air';
          elseif ($label === 'SEA') $cls .= 'service-sea';
          else $cls .= 'service-land';
          echo '<span class="'.$cls.'"><i class="fas fa-cog"></i>'.htmlspecialchars($label).'</span>';
        }
      ?>
    </td></tr>
    <tr><th>ISO Certified</th><td><?= htmlspecialchars($row['iso_certified'] ?? 'N/A') ?></td></tr>
    <tr>
  <th>Business Permit</th>
  <td>
    <?php
      $permit = $row['business_permit'] ?? '';
      if (!empty($permit)) {
        $ext = pathinfo($permit, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
          echo "<img src='uploads/$permit' style='max-width:100%; height:auto; display:block; margin:auto;'>";
        } elseif ($ext === 'pdf') {
          echo "<embed src='uploads/$permit' type='application/pdf' width='100%' height='600' style='display:block;'>";
        } else {
          echo "No valid file.";
        }
      } else {
        echo "No file uploaded.";
      }
    ?>
  </td>
</tr>
<tr>
  <th>Company Profile</th>
  <td>
    <?php
      $profile = $row['company_profile'] ?? '';
      if (!empty($profile)) {
        $ext = pathinfo($profile, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
          echo "<img src='uploads/$profile' style='max-width:100%; height:auto; display:block; margin:auto;'>";
        } elseif ($ext === 'pdf') {
          echo "<embed src='uploads/$profile' type='application/pdf' width='100%' height='600' style='display:block;'>";
        } else {
          echo "No valid file.";
        }
      } else {
        echo "No file uploaded.";
      }
    ?>
  </td>
</tr>

  </table>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
          