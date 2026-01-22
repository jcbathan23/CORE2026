<?php
include('../connect.php');
require_once __DIR__ . '/auth.php';

if (isset($_POST['sop_id'])) {
    $sop_id = intval($_POST['sop_id']);

    // ✅ Set status back to Active
    $sql = "UPDATE sop_documents SET status='Active' WHERE sop_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sop_id);

    if ($stmt->execute()) {
        header('Location: archived_sop.php?unarchived=1');
        exit;
    } else {
        $msg = urlencode('Error unarchiving SOP.');
        header('Location: archived_sop.php?error=' . $msg);
        exit;
    }

    $stmt->close();
}

$conn->close();
?>
