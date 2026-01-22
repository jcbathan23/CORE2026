<?php
include('../connect.php');

if (isset($_GET['id'])) {
    $sop_id = intval($_GET['id']);
    $sql = "SELECT * FROM sop_documents WHERE sop_id = $sop_id";
    $result = $conn->query($sql);
    $sop = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Print SOP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Arial', sans-serif;
      line-height: 1.6;
      color: #333;
    }
    .sop-header {
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
      margin-bottom: 20px;
      text-align: center;
    }
    .sop-header h2 {
      margin: 0;
      font-size: 28px;
      font-weight: bold;
    }
    .sop-header p {
      margin: 2px 0;
      font-size: 14px;
    }
    .sop-section {
      border: 1px solid #ccc;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      background-color: #f9f9f9;
    }
    .sop-section h5 {
      font-weight: bold;
      margin-bottom: 10px;
      color: #2C3E50;
    }
    .sop-footer {
      text-align: center;
      font-size: 12px;
      color: #555;
      margin-top: 40px;
      border-top: 1px solid #ccc;
      padding-top: 10px;
    }
    @media print {
      .no-print { display: none; }
      body { padding: 0; }
      .sop-section { page-break-inside: avoid; }
    }
  </style>
</head>
<body class="p-5">

  <!-- Header -->
  <div class="sop-header">
    <!-- Replace with your logo if available -->
    <img src="logo.png" alt="SLATE FREIGHT INC Logo" style="height:80px; margin-bottom:10px;">
    <h2>SLATE FREIGHT INC</h2>
    <hr>
  </div>

  <!-- SOP Info Section -->
  <div class="sop-section">
    <h5>SOP Information</h5>
    <p><strong>Title:</strong> <?php echo htmlspecialchars($sop['title']); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($sop['category']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($sop['status']); ?></p>
    <p><strong>Created:</strong> <?php echo htmlspecialchars($sop['created_at']); ?></p>
  </div>

  <!-- SOP Content Section -->
  <div class="sop-section">
    <h5>Procedure Details</h5>
    <p><?php echo nl2br(htmlspecialchars($sop['content'])); ?></p>
  </div>

  <!-- Attached File -->
  <?php if (!empty($sop['file_path'])): ?>
  <div class="sop-section">
    <h5>Attached File</h5>
    <a href="../uploads/<?php echo $sop['file_path']; ?>" target="_blank">Download File</a>
  </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="sop-footer">
    SLATE FREIGHT INC | Email: slatefreight@gmail.com | FB Page: slatefreight
  </div>

  <!-- Print Button -->
  <div class="no-print text-center mt-4">
    <button onclick="window.print()" class="btn btn-primary">🖨 Print / Save as PDF</button>
  </div>

</body>
</html>
