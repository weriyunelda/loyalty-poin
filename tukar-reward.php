<?php

include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id_customer']) ? mysqli_real_escape_string($conn, $_POST['id_customer']) : '';
    $reward = isset($_POST['reward']) ? mysqli_real_escape_string($conn, $_POST['reward']) : '';
    $poin = isset($_POST['poin']) ? (int)$_POST['poin'] : 0;

    if (empty($id) || empty($reward) || empty($poin) || $poin < 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        // Cek apakah customer exists
        $check = mysqli_query($conn, "SELECT id_customer, total_poin FROM customers WHERE id_customer='$id'");
        $customer = mysqli_fetch_assoc($check);

        if (!$customer) {
            $error = 'Customer tidak ditemukan!';
        } elseif ($customer['total_poin'] < $poin) {
            $error = 'Poin tidak cukup! Total poin: ' . $customer['total_poin'];
        } else {
            // Update customer poin
            mysqli_query($conn, "UPDATE customers SET total_poin = total_poin - $poin WHERE id_customer='$id'");

            // Insert ke reward history
            mysqli_query($conn, "INSERT INTO reward_history (id_customer, tanggal, nama_reward, poin_ditukar) VALUES ('$id', CURDATE(), '$reward', '$poin')");

            $success = 'Reward berhasil ditukar! Redirecting...';
            header("refresh:2;url=detail-loyalty.php?id=$id");
        }
    }
}

// Tampilkan form jika ada error atau GET request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($error)) {
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tukar Reward</title>
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
        .form-group input::placeholder {
            color: #999;
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
        <h1><i class="fas fa-gift"></i> Tukar Reward</h1>
        <p>Tukarkan poin dengan reward menarik</p>
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
                    <i class="fas fa-user"></i> ID Customer
                </label>
                <input type="text" name="id_customer" readonly value="<?= isset($_GET['id']) ? $_GET['id'] : ''; ?>">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-gift"></i> Nama Reward
                </label>
                <input type="text" name="reward" required placeholder="Contoh: Voucher 100rb">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-coins"></i> Poin Ditukar
                </label>
                <input type="number" name="poin" min="0" required placeholder="Contoh: 500">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Tukar Reward
                </button>
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