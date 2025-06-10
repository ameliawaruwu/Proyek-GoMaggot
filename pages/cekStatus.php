<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../views/header.php';
require_once '../configdb.php'; // Sesuaikan path jika perlu

// Inisialisasi variabel
$order_id = $_GET['order_id'] ?? null;
$pesanan_data = null;
$error_message = '';
$status_id_from_db = null; // Variabel untuk menyimpan ID status numerik dari DB
$status_name_from_db = 'Tidak Diketahui'; // Variabel untuk menyimpan nama status dari DB

// Pastikan koneksi database berhasil
if (!$conn) {
    $error_message = "Koneksi database gagal.";
} else {
    if ($order_id) {
        // Query untuk mengambil data pesanan DAN nama status dari tabel status_pesanan
        // p.status AS current_status_id (untuk menyimpan ID status numerik dari tabel pesanan)
        // sp.nama_status AS current_status_name (untuk menyimpan nama status dari tabel status_pesanan)
        $stmt = $conn->prepare("SELECT p.id_pesanan, p.id_pelanggan, p.nama_penerima, p.alamat_pengiriman, p.tanggal_pesanan, p.total_harga, p.metode_pembayaran, p.status AS current_status_id, sp.nama_status AS current_status_name
                                FROM pesanan p
                                JOIN status_pesanan sp ON p.status = sp.id_status
                                WHERE p.id_pesanan = ?");

        if (!$stmt) {
            $error_message = "Gagal menyiapkan statement: " . $conn->error;
        } else {
            $stmt->bind_param("i", $order_id); // 'i' karena id_pesanan adalah integer
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $pesanan_data = $result->fetch_assoc();
                $status_id_from_db = $pesanan_data['current_status_id']; // Dapatkan ID status numerik
                $status_name_from_db = $pesanan_data['current_status_name']; // Dapatkan nama status dari JOIN
            } else {
                // Fallback jika pesanan tidak ditemukan dengan JOIN (misalnya, status_id di pesanan tidak ada di status_pesanan)
                // Ini mungkin menandakan data tidak konsisten, tapi tetap ambil data pesanan dasarnya.
                $stmt_fallback = $conn->prepare("SELECT id_pesanan, id_pelanggan, nama_penerima, alamat_pengiriman, tanggal_pesanan, status, total_harga, metode_pembayaran FROM pesanan WHERE id_pesanan = ?");
                if ($stmt_fallback) {
                    $stmt_fallback->bind_param("i", $order_id);
                    $stmt_fallback->execute();
                    $result_fallback = $stmt_fallback->get_result();
                    if ($result_fallback->num_rows > 0) {
                        $pesanan_data = $result_fallback->fetch_assoc();
                        $status_id_from_db = $pesanan_data['status']; // Ambil ID status numerik
                        // Jika nama status tidak ditemukan, beri pesan informatif
                        $status_name_from_db = "ID Status: " . htmlspecialchars($status_id_from_db) . " (Nama status tidak valid)"; 
                        $error_message = "Status pesanan tidak dikenali. Mohon hubungi admin.";
                    } else {
                        $error_message = "Pesanan dengan ID " . htmlspecialchars($order_id) . " tidak ditemukan.";
                    }
                    $stmt_fallback->close();
                } else {
                    $error_message = "Gagal menyiapkan statement fallback: " . $conn->error;
                }
            }
            $stmt->close();
        }
    } else {
        $error_message = "ID Pesanan tidak ditemukan dalam URL.";
    }
    // Tutup koneksi di sini setelah semua operasi database selesai
    $conn->close();
}


// Definisikan ID status yang Anda miliki di tabel status_pesanan
// PASTIKAN NILAI INI SESUAI DENGAN id_status DI DATABASE ANDA!
// BERDASARKAN asumsi dari pertanyaan sebelumnya:
$status_id_menunggu_pembayaran = 1;
$status_id_pembayaran_dikonfirmasi = 2;
$status_id_dikemas = 3;    // ID untuk 'Sedang Dikemas'
$status_id_dikirim = 4;    // ID untuk 'Sedang Dikirim'
$status_id_sampai = 5;     // ID untuk 'Sudah Sampai'
// Tambahkan ID status lain jika ada dan ingin direpresentasikan dalam UI
// $status_id_dibatalkan = 6;


// Tentukan kelas CSS berdasarkan status ID
$dikemas_class = '';
$dikirim_class = '';
$sampai_class = '';

if ($status_id_from_db !== null) {
    // 'Sedang Dikemas' harus aktif jika status Pembayaran Dikonfirmasi (2) atau lebih tinggi
    // Ini berarti pesanan sudah siap untuk proses pengemasan
    if ($status_id_from_db >= $status_id_pembayaran_dikonfirmasi) {
        $dikemas_class = 'active';
    }
    
    // 'Sedang Dikirim' harus aktif jika status Sedang Dikirim (4) atau lebih tinggi
    if ($status_id_from_db >= $status_id_dikirim) {
        $dikirim_class = 'active';
    }
    
    // 'Sudah Sampai' harus aktif jika status Sudah Sampai (5) atau lebih tinggi
    if ($status_id_from_db >= $status_id_sampai) {
        $sampai_class = 'active';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pesanan - GoMomaggot</title>
    <link rel="stylesheet" href="../Admin-HTML/css/cekStatus.css">
</head>
<body>
    <div class="container">
        <h1>Status Pesanan Anda</h1>

        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php elseif ($pesanan_data): ?>
            <div class="order-summary">
                <p><strong>ID Pesanan:</strong> <?php echo htmlspecialchars($pesanan_data['id_pesanan']); ?></p>
                <p><strong>Nama Penerima:</strong> <?php echo htmlspecialchars($pesanan_data['nama_penerima']); ?></p>
                <p><strong>Alamat Pengiriman:</strong> <?php echo htmlspecialchars($pesanan_data['alamat_pengiriman']); ?></p>
                <p><strong>Tanggal Pesanan:</strong> <?php echo htmlspecialchars($pesanan_data['tanggal_pesanan']); ?></p>
                <p><strong>Total Harga:</strong> Rp <?php echo number_format($pesanan_data['total_harga'], 0, ',', '.'); ?></p>
                <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($pesanan_data['metode_pembayaran']); ?></p>
            </div>

            <div class="status-tracker">
                <div class="step <?php echo $dikemas_class; ?>">
                    <span class="icon">&#10003;</span>
                    <p>Sedang Dikemas</p>
                </div>
                <div class="step <?php echo $dikirim_class; ?>">
                    <span class="icon">&#10003;</span>
                    <p>Sedang Dikirim</p>
                </div>
                <div class="step <?php echo $sampai_class; ?>">
                    <span class="icon">&#10003;</span>
                    <p>Sudah Sampai</p>
                </div>
            </div>

            <p class="current-status"><strong>Status Saat Ini:</strong> <span class="status-text"><?php echo htmlspecialchars($status_name_from_db); ?></span></p>

        <?php endif; ?>

        <div class="actions">
            <button onclick="location.href='home.php'">Kembali ke Beranda</button>
            <?php if ($pesanan_data): // Hanya tampilkan jika ada data pesanan ?>
            <button onclick="location.href='cekStatus.php?order_id=<?php echo htmlspecialchars($pesanan_data['id_pesanan']); ?>'">Refresh Status</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php include '../views/footer.php'; ?>
<script src="cekStatus.js"></script>