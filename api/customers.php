<?php
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../config.php';

function respond($payload, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchSafe = mysqli_real_escape_string($conn, $search);

    $sql = "SELECT id_customer, nama, total_poin, level, terakhir_transaksi FROM customers";
    if ($searchSafe !== '') {
        $sql .= " WHERE nama LIKE '%$searchSafe%' OR id_customer LIKE '%$searchSafe%'";
    }
    $sql .= " ORDER BY id_customer ASC";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        respond(['success' => false, 'message' => 'Gagal mengambil data customer'], 500);
    }

    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = [
            'id' => $row['id_customer'],
            'nama' => $row['nama'],
            'totalPoin' => (int) $row['total_poin'],
            'level' => $row['level'],
            'terakhirTransaksi' => $row['terakhir_transaksi']
        ];
    }

    respond(['success' => true, 'data' => $customers]);
}

if ($method === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';

    if ($nama === '') {
        respond(['success' => false, 'message' => 'Nama customer tidak boleh kosong'], 400);
    }

    if ($action === 'edit') {
        $id_customer = isset($_POST['id_customer']) ? mysqli_real_escape_string($conn, $_POST['id_customer']) : '';
        if ($id_customer === '') {
            respond(['success' => false, 'message' => 'ID customer tidak valid'], 400);
        }

        $check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE id_customer='$id_customer'");
        if (mysqli_num_rows($check) === 0) {
            respond(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
        }

        $checkName = mysqli_query($conn, "SELECT id_customer FROM customers WHERE nama='$nama' AND id_customer != '$id_customer'");
        if (mysqli_num_rows($checkName) > 0) {
            respond(['success' => false, 'message' => 'Nama customer sudah terdaftar customer lain'], 409);
        }

        $update = mysqli_query($conn, "UPDATE customers SET nama='$nama' WHERE id_customer='$id_customer'");
        if (!$update) {
            respond(['success' => false, 'message' => 'Gagal mengubah customer'], 500);
        }

        respond(['success' => true, 'message' => 'Customer berhasil diubah']);
    }

    $check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE nama='$nama'");
    if (mysqli_num_rows($check) > 0) {
        respond(['success' => false, 'message' => 'Nama customer sudah terdaftar'], 409);
    }

    $insert = mysqli_query($conn, "INSERT INTO customers (nama, total_poin, level, terakhir_transaksi) VALUES ('$nama', 0, 'Bronze', CURDATE())");
    if (!$insert) {
        respond(['success' => false, 'message' => 'Gagal menambah customer'], 500);
    }

    respond(['success' => true, 'message' => 'Customer berhasil ditambahkan']);
}

if ($method === 'DELETE') {
    $id_customer = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
    if ($id_customer === '') {
        respond(['success' => false, 'message' => 'ID customer tidak ditemukan'], 400);
    }

    $check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE id_customer='$id_customer'");
    if (mysqli_num_rows($check) === 0) {
        respond(['success' => false, 'message' => 'Customer tidak ditemukan'], 404);
    }

    mysqli_query($conn, "DELETE FROM point_history WHERE id_customer='$id_customer'");
    mysqli_query($conn, "DELETE FROM reward_history WHERE id_customer='$id_customer'");
    $delete = mysqli_query($conn, "DELETE FROM customers WHERE id_customer='$id_customer'");

    if (!$delete) {
        respond(['success' => false, 'message' => 'Gagal menghapus customer'], 500);
    }

    respond(['success' => true, 'message' => 'Customer berhasil dihapus']);
}

respond(['success' => false, 'message' => 'Method tidak didukung'], 405);
