<?php
include 'config.php';

// Handle tambah customer
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    
    // Validasi
    if (empty($nama)) {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Nama customer tidak boleh kosong!</div>');
    }
    
    // Cek apakah nama sudah ada
    $check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE nama='$nama'");
    if (mysqli_num_rows($check) > 0) {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Nama customer sudah terdaftar!</div>');
    }
    
    // Insert customer baru
    $insert = mysqli_query($conn, "INSERT INTO customers (nama, total_poin, level, terakhir_transaksi) VALUES ('$nama', 0, 'Bronze', CURDATE())");
    
    if ($insert) {
        header("Location: loyalty.php?added=1");
    } else {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Gagal menambah customer!</div>');
    }
}

// Handle edit customer
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_customer = mysqli_real_escape_string($conn, $_POST['id_customer']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    
    // Validasi
    if (empty($nama)) {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Nama customer tidak boleh kosong!</div>');
    }
    
    // Cek apakah customer exists
    $check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE id_customer='$id_customer'");
    if (mysqli_num_rows($check) === 0) {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Customer tidak ditemukan!</div>');
    }
    
    // Cek apakah nama sudah digunakan customer lain
    $checkName = mysqli_query($conn, "SELECT id_customer FROM customers WHERE nama='$nama' AND id_customer != '$id_customer'");
    if (mysqli_num_rows($checkName) > 0) {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Nama customer sudah terdaftar customer lain!</div>');
    }
    
    // Update customer
    $update = mysqli_query($conn, "UPDATE customers SET nama='$nama' WHERE id_customer='$id_customer'");
    
    if ($update) {
        header("Location: loyalty.php?edited=1");
    } else {
        die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Gagal mengubah customer!</div>');
    }
}
?>
