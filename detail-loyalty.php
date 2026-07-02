<?php

include 'config.php';

if (!isset($_GET['id'])) {
    die('<div style="padding: 20px; color: red;">ID Customer tidak ditemukan!</div>');
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM customers WHERE id_customer='$id'");
$customer = mysqli_fetch_assoc($result);

if (!$customer) {
    die('<div style="padding: 20px; color: red;">Data Customer tidak ditemukan!</div>');
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Pelanggan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="header">
    <div class="container">
        <h1><i class="fas fa-user-circle"></i> Detail Loyalty Pelanggan</h1>
        <p>Kelola poin dan reward pelanggan</p>
    </div>
</div>

<div class="container">

<div class="card">
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div>
        <h2 style="color:#667eea; margin-bottom:10px;"><?= $customer['nama']; ?></h2>
        <p style="margin: 8px 0;"><strong>ID Customer:</strong> <?= $customer['id_customer']; ?></p>
        <p style="margin: 8px 0;"><strong>Terakhir Transaksi:</strong> <?= $customer['terakhir_transaksi']; ?></p>
    </div>
    <div style="text-align:right;">
        <p style="font-size:0.9em; color:#666; margin-bottom:5px;">Total Poin</p>
        <p style="font-size:2.5em; color:#667eea; font-weight:bold; margin:0;"><?= $customer['total_poin']; ?></p>
        <p style="margin-top:10px;">
            <span class="badge <?= strtolower($customer['level']) ?>">
                <i class="fas fa-medal"></i> <?= $customer['level']; ?>
            </span>
        </p>
    </div>
</div>
</div>

<h2>Riwayat Perolehan Poin</h2>

<div class="card">
<table>
<thead>
<tr>
<th><i class="fas fa-calendar"></i> Tanggal</th>
<th><i class="fas fa-exchange-alt"></i> Transaksi</th>
<th><i class="fas fa-money-bill"></i> Total Belanja</th>
<th><i class="fas fa-coins"></i> Poin</th>
</tr>
</thead>
<tbody>
<?php
$poin = mysqli_query($conn,"SELECT * FROM point_history WHERE id_customer='$id' ORDER BY tanggal DESC");
while($row=mysqli_fetch_assoc($poin)){
  $recordPoin = $row['poin_didapat'];
?>
<tr>
<td><?= $row['tanggal']; ?></td>
<td><?= $row['transaksi']; ?></td>
<td>Rp <?= number_format($row['total_belanja']); ?></td>
<td><span class="poin-badge"><?= $recordPoin; ?></span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<br>

<h2>Reward Ditukarkan</h2>

<div class="card">
<table>
<thead>
<tr>
<th><i class="fas fa-calendar"></i> Tanggal</th>
<th><i class="fas fa-gift"></i> Reward</th>
<th><i class="fas fa-coins"></i> Poin Ditukar</th>
</tr>
</thead>
<tbody>
<?php
$reward = mysqli_query($conn,"SELECT * FROM reward_history WHERE id_customer='$id' ORDER BY tanggal DESC");
while($row=mysqli_fetch_assoc($reward)){
  $rewardPoin = $row['poin_ditukar'];
?>
<tr>
<td><?= $row['tanggal']; ?></td>
<td><?= $row['nama_reward']; ?></td>
<td><span class="poin-badge"><?= $rewardPoin; ?></span></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

<br>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
    <div class="card">
        <h2><i class="fas fa-plus-circle"></i> Tambah Poin</h2>
        <form action="tambah-point.php" method="POST">
            <input type="hidden"
            name="id_customer"
            value="<?= $id ?>">

            <input type="number"
            name="belanja"
            placeholder="Total Belanja (Rp)"
            style="width:100%; padding:10px; border:2px solid #e0e0e0; border-radius:6px; margin-bottom:10px;">

            <button type="submit" style="width:100%;">
                <i class="fas fa-check"></i> Tambah Poin
            </button>
        </form>
    </div>

    <div class="card">
        <h2><i class="fas fa-gift"></i> Tukar Reward</h2>
        <form action="tukar-reward.php" method="POST">
            <input type="hidden"
            name="id_customer"
            value="<?= $id ?>">

            <input type="text"
            name="reward"
            placeholder="Nama Reward"
            style="width:100%; padding:10px; border:2px solid #e0e0e0; border-radius:6px; margin-bottom:10px;">

            <input type="number"
            name="poin"
            placeholder="Poin Ditukar"
            style="width:100%; padding:10px; border:2px solid #e0e0e0; border-radius:6px; margin-bottom:10px;">

            <button type="submit" style="width:100%;">
                <i class="fas fa-check"></i> Tukar Reward
            </button>
        </form>
    </div>
</div>

<a href="loyalty.php" style="display:inline-block; margin-top:20px; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:bold;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
</a>

</div>

</body>

</html>