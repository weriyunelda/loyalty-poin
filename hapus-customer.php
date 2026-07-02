<?php

include 'config.php';

if (!isset($_GET['id'])) {
    die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> ID Customer tidak ditemukan!</div>');
}

$id_customer = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah customer exists
$check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE id_customer='$id_customer'");
if (mysqli_num_rows($check) === 0) {
    die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Customer tidak ditemukan!</div>');
}

// Hapus dari point_history
mysqli_query($conn, "DELETE FROM point_history WHERE id_customer='$id_customer'");

// Hapus dari reward_history
mysqli_query($conn, "DELETE FROM reward_history WHERE id_customer='$id_customer'");

// Hapus customer
$delete = mysqli_query($conn, "DELETE FROM customers WHERE id_customer='$id_customer'");

if ($delete) {
    header("Location: loyalty.php?deleted=1");
} else {
    die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Gagal menghapus customer!</div>');
}
?>
