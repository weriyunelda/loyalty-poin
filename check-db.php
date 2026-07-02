<?php
include 'config.php';

if (!$conn) {
    echo "NO_CONNECTION\n";
    exit;
}

echo "CONNECTED\n";
echo "SERVER: " . mysqli_get_server_info($conn) . "\n";

$result = mysqli_query($conn, 'SHOW TABLES');
if (!$result) {
    echo 'SHOW_TABLES_FAILED: ' . mysqli_error($conn) . "\n";
    exit;
}

while ($row = mysqli_fetch_array($result)) {
    echo $row[0] . "\n";
}
