<?php

include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id_customer']) ? mysqli_real_escape_string($conn, $_POST['id_customer']) : '';
    $belanja = isset($_POST['belanja']) ? (int)$_POST['belanja'] : 0;

    if (empty($id) || empty($belanja) || $belanja < 0) {
        $error = 'ID Customer dan Jumlah Belanja harus diisi dengan benar!';
    } else {
        // Cek apakah customer exists
        $check = mysqli_query($conn, "SELECT id_customer FROM customers WHERE id_customer='$id'");
        if (mysqli_num_rows($check) === 0) {
            $error = 'Customer tidak ditemukan!';
        } else {
            $poin = floor($belanja / 10000);

            // Update customer poin
            mysqli_query($conn, "
                UPDATE customers
                SET total_poin = total_poin + $poin,
                    terakhir_transaksi = CURDATE()
                WHERE id_customer='$id'
            ");

            // Insert ke point history
            mysqli_query($conn, "
                INSERT INTO point_history
                (id_customer, tanggal, transaksi, total_belanja, poin_didapat)
                VALUES ('$id', CURDATE(), 'Pembelian', '$belanja', '$poin')
            ");

            // Get total poin terbaru
            $data = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT total_poin FROM customers WHERE id_customer='$id'
            "));

            $total = $data['total_poin'];

            // Tentukan level
            if ($total >= 1000) {
                $level = "Gold";
            } elseif ($total >= 500) {
                $level = "Silver";
            } else {
                $level = "Bronze";
            }

            // Update level
            mysqli_query($conn, "UPDATE customers SET level='$level' WHERE id_customer='$id'");

            $success = 'Poin berhasil ditambahkan! Redirecting...';
            header("refresh:2;url=detail-loyalty.php?id=$id");
        }
    }
}

// Jika bukan POST, tampilkan form
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($error)) {
    $customers = mysqli_query($conn, "SELECT id_customer, nama FROM customers ORDER BY nama");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Poin Pelanggan</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-wrapper {
            max-width: 500px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95em;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
        }
        .form-group input::placeholder {
            color: #999;
        }
        .info-text {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
            display: block;
        }
        .form-actions {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-back {
            background: white !important;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #f5f5f5 !important;
        }
        .error-alert {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <h1><i class="fas fa-plus-circle"></i> Tambah Poin</h1>
        <p>Tambahkan poin untuk pelanggan Anda</p>
    </div>
</div>

<div class="container">
    <div class="card form-wrapper">
        
        <?php if (!empty($error)): ?>
            <div class="error-alert">
                <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i> Pilih Pelanggan
                </label>
                <select name="id_customer" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php while($row = mysqli_fetch_assoc($customers)): ?>
                        <option value="<?= $row['id_customer']; ?>"><?= $row['id_customer']; ?> - <?= $row['nama']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-money-bill"></i> Jumlah Belanja (Rp)
                </label>
                <input type="number" name="belanja" min="0" required placeholder="Contoh: 100000">
                <span class="info-text">Setiap Rp 10.000 = 1 Poin</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Tambah Poin
                </button>
                <a href="loyalty.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

<?php
} else if (!empty($success)) {
    echo '<div style="text-align:center; padding:50px;">';
    echo '<h2 style="color:green;"><i class="fas fa-check-circle"></i> ' . $success . '</h2>';
    echo '</div>';
}
?>