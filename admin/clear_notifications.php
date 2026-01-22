<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE is_read = 0");
echo 'success';
