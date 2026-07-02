<?php

include 'config.php';

$error = '';
$success = '';
$data = null;

// Get ID from URL
if (!isset($_GET['record_id'])) {
    die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> ID Record tidak ditemukan!</div>');
}

$record_id = (int)$_GET['record_id'];
$id_customer = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// Get current data
$query = mysqli_query($conn, "SELECT * FROM point_history WHERE id = $record_id AND id_customer = '$id_customer'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die('<div style="padding: 20px; color: red;"><i class="fas fa-exclamation-circle"></i> Record tidak ditemukan!</div>');
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $belanja = isset($_POST['belanja']) ? (int)$_POST['belanja'] : 0;
    $poin_baru = floor($belanja / 10000);
    $poin_lama = $data['poin_didapat'];
    $poin_diff = $poin_baru - $poin_lama;

    if (empty($belanja) || $belanja < 0) {
        $error = 'Jumlah belanja harus diisi dengan benar!';
    } else {
        // Update point_history
        $update = mysqli_query($conn, "UPDATE point_history SET total_belanja = $belanja, poin_didapat = $poin_baru WHERE id = $record_id");

        if ($update) {
            // Update total poin customer
            mysqli_query($conn, "UPDATE customers SET total_poin = total_poin + $poin_diff WHERE id_customer = '$id_customer'");

            // Hitung ulang level
            $result = mysqli_fetch_assoc(mysqli_query($conn, "SELECT total_poin FROM customers WHERE id_customer = '$id_customer'"));
            $total = $result['total_poin'];

            if ($total >= 1000) {
                $level = "Gold";
            } elseif ($total >= 500) {
                $level = "Silver";
            } else {
                $level = "Bronze";
            }

            mysqli_query($conn, "UPDATE customers SET level = '$level' WHERE id_customer = '$id_customer'");

            $success = 'Poin berhasil diperbarui! Redirecting...';
            header("refresh:2;url=detail-loyalty.php?id=$id_customer");
        } else {
            $error = 'Gagal memperbarui poin!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($error)) {
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Poin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-form {
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95em;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
        }
        .form-group input:read-only {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        .form-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .form-actions a {
            text-align: center;
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
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-cancel {
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
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background: #f5f5f5 !important;
        }
        .info-text {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .success-alert {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <h1><i class="fas fa-edit"></i> Edit Poin</h1>
        <p>Perbarui data perolehan poin pelanggan</p>
    </div>
</div>

<div class="container">
    <div class="card edit-form">
        
        <?php if (!empty($error)): ?>
            <div class="success-alert" style="background:#f8d7da; border:1px solid #f5c6cb; color:#721c24;">
                <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i> ID Customer
                </label>
                <input type="text" readonly value="<?= $id_customer; ?>">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-calendar"></i> Tanggal
                </label>
                <input type="text" readonly value="<?= $data['tanggal']; ?>">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-money-bill"></i> Jumlah Belanja (Rp)
                </label>
                <input type="number" name="belanja" min="0" required value="<?= $data['total_belanja']; ?>" placeholder="Contoh: 100000">
                <p class="info-text">Poin Lama: <strong><?= $data['poin_didapat']; ?></strong> | Setiap Rp 10.000 = 1 Poin</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="detail-loyalty.php?id=<?= $id_customer; ?>" class="btn-cancel">
                    <i class="fas fa-times"></i> Batal
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
