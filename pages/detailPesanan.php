<?php
// include '../Logic/update/auth.php'; // Uncomment jika authentication dibutuhkan dan sudah ada
// Sertakan header admin
include '../configdb.php'; // Sertakan file koneksi database

// Inisialisasi variabel untuk menghindari error undefined
$detail_pesanan = null;
$produk_pesanan = [];
$status_options = []; // Untuk menyimpan pilihan status dari database

// Pastikan ada ID pesanan yang diterima dari URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_pesanan = mysqli_real_escape_string($conn, $_GET['id']);

    // Query untuk mengambil detail pesanan utama
    // Memastikan mengambil status_id (jika status pesanan disimpan sebagai ID)
    $sql_pesanan = "SELECT p.*, u.username, u.email, u.alamat, sp.nama_status AS current_status_name
                    FROM pesanan p 
                    JOIN pengguna u ON p.id_pelanggan = u.id_pelanggan 
                    JOIN status_pesanan sp ON p.status = sp.id_status -- JOIN ke tabel status_pesanan
                    WHERE p.id_pesanan = '$id_pesanan'";
    $result_pesanan = mysqli_query($conn, $sql_pesanan);

    if ($result_pesanan && mysqli_num_rows($result_pesanan) > 0) {
        $detail_pesanan = mysqli_fetch_assoc($result_pesanan);

        // Query untuk mengambil produk-produk dalam pesanan (detail_pesanan)
        $sql_detail_produk = "SELECT dp.jumlah, dp.harga_saat_pembelian, pr.namaproduk, pr.gambar 
                              FROM detail_pesanan dp 
                              JOIN produk pr ON dp.idproduk = pr.idproduk 
                              WHERE dp.id_pesanan = '$id_pesanan'";
        $result_detail_produk = mysqli_query($conn, $sql_detail_produk);

        if ($result_detail_produk && mysqli_num_rows($result_detail_produk) > 0) {
            while ($row_produk = mysqli_fetch_assoc($result_detail_produk)) {
                $produk_pesanan[] = $row_produk;
            }
        }
        
        // Ambil semua status dari tabel status_pesanan untuk dropdown
        $sql_status = "SELECT id_status, nama_status FROM status_pesanan ORDER BY id_status ASC";
        $result_status = mysqli_query($conn, $sql_status);
        if ($result_status) {
            while ($row_status = mysqli_fetch_assoc($result_status)) {
                $status_options[] = $row_status;
            }
        }

    } else {
        echo "<p>Pesanan tidak ditemukan.</p>";
        include '../views/footeradmin.php';
        mysqli_close($conn);
        exit();
    }
} else {
    echo "<p>ID Pesanan tidak valid.</p>";
    include '../views/footeradmin.php';
    mysqli_close($conn);
    exit();
}

// Handle update status request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status_btn'])) {
    $new_status_id = mysqli_real_escape_string($conn, $_POST['new_status']);
    $order_id_to_update = mysqli_real_escape_string($conn, $_POST['order_id_to_update']);

    // Validasi apakah status baru valid (ada di $status_options)
    $valid_status_id = false;
    foreach ($status_options as $status_opt) {
        if ($status_opt['id_status'] == $new_status_id) {
            $valid_status_id = true;
            break;
        }
    }

    if ($valid_status_id) {
        $stmt_update = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("ii", $new_status_id, $order_id_to_update);
            if ($stmt_update->execute()) {
                // Redirect untuk mencegah resubmission form
                header("Location: dashboard.php?id=" . urlencode($order_id_to_update) . "&status_updated=true");
                exit();
            } else {
                echo "<p class='message error'>Gagal mengupdate status pesanan: " . $stmt_update->error . "</p>";
            }
            $stmt_update->close();
        } else {
            echo "<p class='message error'>Error preparing update statement: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='message error'>Status yang dipilih tidak valid.</p>";
    }
}

// Tampilkan pesan jika status berhasil diupdate
if (isset($_GET['status_updated']) && $_GET['status_updated'] === 'true') {
    echo "<p class='message'>Status pesanan berhasil diperbarui!</p>";
}

include '../views/headeradmin.php'; 
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<style>
    /* Styling tambahan untuk halaman detail */
    .detail-container {
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 900px;
        margin: 20px auto;
    }
    .detail-section {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .detail-section h2, .detail-section h3 {
        color: #333;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .detail-info p {
        margin-bottom: 8px;
        line-height: 1.6;
    }
    .detail-info strong {
        color: #555;
        display: inline-block;
        width: 150px; /* Lebar untuk label */
    }
    .product-list {
        list-style: none;
        padding: 0;
    }
    .product-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #eee;
        border-radius: 5px;
    }
    .product-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        margin-right: 15px;
        border-radius: 4px;
    }
    .product-details {
        flex-grow: 1;
    }
    .product-details h4 {
        margin: 0 0 5px 0;
        color: #007bff;
    }
    .product-details p {
        margin: 0;
        font-size: 0.9em;
        color: #666;
    }
    .total-summary {
        text-align: right;
        font-size: 1.2em;
        font-weight: bold;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        color: #333;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        text-transform: capitalize;
    }
    .status-pending { background-color: #ffc107; color: #333; }
    .status-process { background-color: #007bff; }
    .status-completed { background-color: #28a745; }
    .status-cancelled { background-color: #dc3545; }
    .status-shipped { background-color: #6f42c1; } /* Added for 'Sedang Dikirim' if needed */
    .back-button {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 20px;
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }
    .back-button:hover {
        background-color: #5a6268;
    }
    /* Styles for status update form */
    .status-update-form {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        align-items: center;
    }
    .status-update-form select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1em;
        flex-grow: 1;
    }
    .status-update-form button {
        padding: 8px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.3s ease;
    }
    .status-update-form button:hover {
        background-color: #0056b3;
    }
</style>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Detail Pesanan</h1>
            <ul class="breadcrumb">
                <li><a href="admin.php">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="admin.php">Recent Orders</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Detail Pesanan #<?php echo htmlspecialchars($detail_pesanan['id_pesanan'] ?? 'N/A'); ?></a></li>
            </ul>
        </div>
    </div>

    <div class="detail-container">
        <a href="javascript:history.back()" class="back-button">Kembali</a>

        <?php if ($detail_pesanan): ?>
            <div class="detail-section">
                <h2>Informasi Pesanan</h2>
                <div class="detail-info">
                    <p><strong>ID Pesanan:</strong> #<?php echo htmlspecialchars($detail_pesanan['id_pesanan']); ?></p>
                    <p><strong>Tanggal Pesanan:</strong> <?php echo date('d M Y H:i', strtotime($detail_pesanan['tanggal_pesanan'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $detail_pesanan['current_status_name'])); ?>">
                            <?php echo htmlspecialchars(ucfirst($detail_pesanan['current_status_name'])); ?>
                        </span>
                    </p>
                    <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($detail_pesanan['metode_pembayaran']); ?></p>
                    <p><strong>Total Harga:</strong> Rp <?php echo number_format($detail_pesanan['total_harga'], 0, ',', '.'); ?></p>
                </div>

                <form action="" method="post" class="status-update-form">
                    <input type="hidden" name="order_id_to_update" value="<?php echo htmlspecialchars($detail_pesanan['id_pesanan']); ?>">
                    <label for="new_status">Ubah Status:</label>
                    <select name="new_status" id="new_status">
                        <?php foreach ($status_options as $status_opt): ?>
                            <option value="<?php echo htmlspecialchars($status_opt['id_status']); ?>"
                                <?php echo ($status_opt['id_status'] == $detail_pesanan['status']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($status_opt['nama_status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_status_btn">Update Status</button>
                </form>
            </div>

            <div class="detail-section">
                <h2>Informasi Pelanggan</h2>
                <div class="detail-info">
                    <p><strong>Nama Pengguna:</strong> <?php echo htmlspecialchars($detail_pesanan['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($detail_pesanan['email']); ?></p>
                    <p><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($detail_pesanan['alamat']); ?></p>
                </div>
            </div>

            <div class="detail-section">
                <h2>Produk yang Dipesan</h2>
                <?php if (!empty($produk_pesanan)): ?>
                    <ul class="product-list">
                        <?php foreach ($produk_pesanan as $produk): ?>
                            <li class="product-item">
                                <?php if (!empty($produk['gambar'])): ?>
                                    <img src="../photos/<?php echo htmlspecialchars($produk['gambar']); ?>" alt="<?php echo htmlspecialchars($produk['namaproduk']); ?>">
                                <?php else: ?>
                                    <img src="../placeholder.png" alt="No Image"> 
                                <?php endif; ?>
                                <div class="product-details">
                                    <h4><?php echo htmlspecialchars($produk['namaproduk']); ?></h4>
                                    <p>Jumlah: <?php echo htmlspecialchars($produk['jumlah']); ?></p>
                                    <p>Harga Satuan: Rp <?php echo number_format($produk['harga_saat_pembelian'], 0, ',', '.'); ?></p>
                                    <p>Subtotal: Rp <?php echo number_format($produk['jumlah'] * $produk['harga_saat_pembelian'], 0, ',', '.'); ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Tidak ada produk dalam pesanan ini.</p>
                <?php endif; ?>
            </div>

            <div class="total-summary">
                Total Pembayaran Akhir: Rp <?php echo number_format($detail_pesanan['total_harga'], 0, ',', '.'); ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script src="../Admin-HTML/js/scriptadmin.js"></script>
<?php
include '../views/footeradmin.php';
mysqli_close($conn);
?>