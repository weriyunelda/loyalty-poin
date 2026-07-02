<?php
include 'config.php';

// Handle search
$search = '';
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $data = mysqli_query($conn,"SELECT * FROM customers WHERE nama LIKE '%$search%' OR id_customer LIKE '%$search%' ORDER BY id_customer ASC");
} else {
    $data = mysqli_query($conn,"SELECT * FROM customers ORDER BY id_customer ASC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Loyalty Poin</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal {
            display:none;
            position:fixed;
            z-index:1000;
            left:0;
            top:0;
            width:100%;
            height:100%;
            background-color:rgba(0,0,0,0.5);
            animation:fadeIn 0.3s ease;
            align-items:center;
            justify-content:center;
        }

        .modal[style*="display: flex"] {
            display:flex !important;
        }

        .modal-content {
            background:white;
            padding:30px;
            border-radius:12px;
            box-shadow:0 10px 40px rgba(0,0,0,0.3);
            max-width:450px;
            text-align:center;
            animation:slideUp 0.3s ease;
        }

        .modal-content i {
            font-size:3em;
            color:#e74c3c;
            margin-bottom:15px;
            display:block;
        }

        .modal-content h2 {
            color:#333;
            margin-bottom:10px;
            margin-top:0;
        }

        .modal-content p {
            color:#666;
            margin-bottom:10px;
            line-height:1.6;
        }

        .modal-buttons {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
            margin-top:20px;
        }

        .btn-confirm {
            background:linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color:white;
            border:none;
            padding:12px 20px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            transition:all 0.3s ease;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }

        .btn-confirm:hover {
            transform:translateY(-2px);
            box-shadow:0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-cancel {
            background:white;
            color:#e74c3c;
            border:2px solid #e74c3c;
            padding:12px 20px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            transition:all 0.3s ease;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }

        .btn-cancel:hover {
            background:#f5f5f5;
        }

        @keyframes fadeIn {
            from { opacity:0; }
            to { opacity:1; }
        }

        @keyframes slideUp {
            from { transform:translateY(20px); opacity:0; }
            to { transform:translateY(0); opacity:1; }
        }

        .action-cell {
            display:flex;
            gap:5px;
            flex-wrap:wrap;
            align-items:center;
        }
    </style>
</head>
<body>

<?php if (isset($_GET['deleted'])): ?>
<div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; margin:20px; border-radius:6px; text-align:center;">
    <i class="fas fa-check-circle"></i> Customer berhasil dihapus!
</div>
<?php endif; ?>

<?php if (isset($_GET['added'])): ?>
<div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; margin:20px; border-radius:6px; text-align:center;">
    <i class="fas fa-check-circle"></i> Customer berhasil ditambahkan!
</div>
<?php endif; ?>

<?php if (isset($_GET['edited'])): ?>
<div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; margin:20px; border-radius:6px; text-align:center;">
    <i class="fas fa-check-circle"></i> Customer berhasil diubah!
</div>
<?php endif; ?>

<div class="header">
    <div class="container">
        <h1><i class="fas fa-crown"></i> Loyalty Poin</h1>
        <p>Kelola Program Loyalitas Pelanggan Anda</p>
    </div>
</div>

<div class="container">
    <div class="card">
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;">
            <form method="GET" style="display: flex; gap: 10px; flex: 1; min-width: 250px;">
                <input type="text" name="search" placeholder="Cari nama atau ID customer..." value="<?= htmlspecialchars($search); ?>" 
                    style="flex: 1; padding: 10px 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; transition: border 0.3s ease;"
                    onfocus="this.style.borderColor='#667eea'"
                    onblur="this.style.borderColor='#e0e0e0'">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if ($search): ?>
                <a href="loyalty.php" class="btn btn-primary" style="padding: 10px 20px; background: #999; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-times"></i> Reset
                </a>
                <?php endif; ?>
            </form>
            <button type="button" class="btn btn-primary" onclick="showAddCustomerModal()">
                <i class="fas fa-plus-circle"></i> Tambah Customer Baru
            </button>
        </div>

        <?php if ($search && mysqli_num_rows($data) > 0): ?>
        <div style="background:#e3f2fd; border:1px solid #90caf9; color:#1565c0; padding:10px 15px; margin-bottom:15px; border-radius:6px;">
            <i class="fas fa-info-circle"></i> Hasil pencarian untuk "<strong><?= htmlspecialchars($search); ?></strong>" (<?= mysqli_num_rows($data); ?> customer ditemukan)
        </div>
        <?php elseif ($search && mysqli_num_rows($data) === 0): ?>
        <div style="background:#fff3cd; border:1px solid #ffc107; color:#856404; padding:15px; margin-bottom:15px; border-radius:6px; text-align:center;">
            <i class="fas fa-search"></i> Tidak ada customer ditemukan untuk "<strong><?= htmlspecialchars($search); ?></strong>"
        </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th><i class="fas fa-id-card"></i> ID Customer</th>
                    <th><i class="fas fa-user"></i> Nama</th>
                    <th><i class="fas fa-coins"></i> Total Poin</th>
                    <th><i class="fas fa-medal"></i> Level</th>
                    <th><i class="fas fa-clock"></i> Transaksi Terakhir</th>
                    <th><i class="fas fa-cogs"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row=mysqli_fetch_assoc($data)){ 
                    $customerId = $row['id_customer'];
                    $customerName = $row['nama'];
                ?>
                <tr>
                    <td><strong><?= $customerId; ?></strong></td>
                    <td><?= $customerName; ?></td>
                    <td><span class="poin-badge"><?= $row['total_poin']; ?></span></td>
                    <td>
                        <span class="badge <?= strtolower($row['level']) ?>">
                            <?= $row['level']; ?>
                        </span>
                    </td>
                    <td><?= $row['terakhir_transaksi']; ?></td>
                    <td class="action-cell">
                        <a href="detail-loyalty.php?id=<?= $customerId; ?>" class="btn btn-primary" style="margin-right:5px;">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <button type="button" class="btn btn-sm" onclick="showEditCustomerModal('<?= $customerId; ?>', '<?= $customerName; ?>')" style="background:linear-gradient(135deg, #3498db 0%, #2980b9 100%); color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; transition:all 0.3s ease;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-delete" onclick="showDeleteCustomerModal('<?= $customerId; ?>', '<?= $customerName; ?>')">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit/Tambah Customer -->
<div id="customerModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h2 id="customerModalTitle"><i class="fas fa-user-plus"></i> Tambah Customer Baru</h2>
        <form id="customerForm" action="edit-customer.php" method="POST">
            <input type="hidden" id="action" name="action" value="add">
            <input type="hidden" id="id_customer" name="id_customer" value="">
            
            <div style="margin-bottom: 20px; text-align: left;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">
                    <i class="fas fa-user"></i> Nama Customer
                </label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama customer" required 
                    style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px; transition: border 0.3s ease;"
                    onfocus="this.style.borderColor='#667eea'"
                    onblur="this.style.borderColor='#e0e0e0'">
            </div>
            
            <div class="modal-buttons" style="grid-template-columns: 1fr 1fr; gap: 10px;">
                <button type="button" class="btn-cancel" onclick="closeCustomerModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" class="btn-confirm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-save"></i> <span id="submitBtnText">Tambah</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Customer -->
<div id="deleteCustomerModal" class="modal">
    <div class="modal-content">
        <i class="fas fa-exclamation-triangle"></i>
        <h2>Hapus Customer?</h2>
        <p>Yakin ingin menghapus customer <strong id="deleteCustomerName"></strong> (ID: <strong id="deleteCustomerId"></strong>)?</p>
        <p style="color:#e74c3c; font-size:0.9em; margin-top:15px;">⚠️ Data riwayat poin dan reward juga akan dihapus!</p>
        <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closeDeleteCustomerModal()">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn-confirm" onclick="confirmDeleteCustomer()">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

<script>
// Fungsi untuk modal tambah/edit customer
function showAddCustomerModal() {
    document.getElementById('action').value = 'add';
    document.getElementById('id_customer').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('customerModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Tambah Customer Baru';
    document.getElementById('submitBtnText').textContent = 'Tambah';
    document.getElementById('customerModal').style.display = 'flex';
}

function showEditCustomerModal(customerId, customerName) {
    document.getElementById('action').value = 'edit';
    document.getElementById('id_customer').value = customerId;
    document.getElementById('nama').value = customerName;
    document.getElementById('customerModalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit Customer';
    document.getElementById('submitBtnText').textContent = 'Simpan Perubahan';
    document.getElementById('customerModal').style.display = 'flex';
}

function closeCustomerModal() {
    document.getElementById('customerModal').style.display = 'none';
}

// Fungsi untuk modal delete customer
let deleteCustomerInfo = {};

function showDeleteCustomerModal(customerId, customerName) {
    deleteCustomerInfo = {
        customerId: customerId
    };
    
    document.getElementById('deleteCustomerId').textContent = customerId;
    document.getElementById('deleteCustomerName').textContent = customerName;
    document.getElementById('deleteCustomerModal').style.display = 'flex';
}

function closeDeleteCustomerModal() {
    document.getElementById('deleteCustomerModal').style.display = 'none';
}

function confirmDeleteCustomer() {
    if (!deleteCustomerInfo.customerId) return;
    
    const url = 'hapus-customer.php?id=' + deleteCustomerInfo.customerId;
    window.location.href = url;
}

// Close modal saat klik di luar
window.addEventListener('click', function(event) {
    const customerModal = document.getElementById('customerModal');
    const deleteModal = document.getElementById('deleteCustomerModal');
    
    if (event.target === customerModal) {
        closeCustomerModal();
    }
    
    if (event.target === deleteModal) {
        closeDeleteCustomerModal();
    }
});
</script>

</body>
</html>