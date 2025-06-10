<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../views/header.php';
include '../configdb.php'; // Pastikan path ini benar

// DEBUG: Aktifkan pelaporan error untuk melihat detail
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<link rel="stylesheet" href="../Admin-HTML/css/Pembayaran.css">
<style>
    /* ... (CSS Anda sebelumnya) ... */
    .thank-you-container {
        text-align: center;
        padding: 50px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 50px auto;
    }
    .thank-you-container h2 {
        color: #28a745; /* Green for success */
        font-size: 2.5em;
        margin-bottom: 20px;
    }
    .thank-you-container p {
        font-size: 1.2em;
        color: #555;
        margin-bottom: 25px;
    }
    .thank-you-container .actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 30px;
    }
    .thank-you-container .actions button {
        padding: 12px 25px;
        font-size: 1.1em;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .thank-you-container .actions button.home {
        background-color: #007bff;
        color: white;
    }
    .thank-you-container .actions button.home:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }
    .thank-you-container .actions button.status {
        background-color: #6c757d;
        color: white;
    }
    .thank-you-container .actions button.status:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }

    /* Existing styles for messages */
    .message {
        padding: 10px;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        margin-bottom: 20px;
        transition: opacity 1s ease;
    }
    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    /* Styling untuk gambar bukti pembayaran (jika ingin ditampilkan setelah upload) */
    .proof-of-payment-preview {
        max-width: 200px;
        height: auto;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 10px;
        display: block;
    }
</style>

<?php
$uploadDir = '../photos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$message = '';
$payment_success = false;
$id_pesanan_display = null;

$id_pesanan_from_url = $_GET['order_id'] ?? null;
$id_pelanggan_from_session = $_SESSION['id_pelanggan'] ?? null;

$id_pesanan = $id_pesanan_from_url;
$id_pelanggan = $id_pelanggan_from_session;

// Debug: Tampilkan ID yang didapat
// echo "";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($id_pesanan === null || $id_pelanggan === null)) {
    $message = "ID Pesanan atau ID Pelanggan tidak ditemukan. Mohon ulangi proses checkout atau login.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id_pesanan === null || $id_pelanggan === null) {
        $message = "ID Pesanan atau ID Pelanggan tidak ditemukan saat mencoba mengirim pembayaran. Mohon ulangi proses checkout atau login.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!$name || !$phone || !$address) {
            $message = 'Mohon lengkapi semua kolom teks.';
        } elseif (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Mohon unggah bukti pembayaran.';
        } else {
            $fileInfo = $_FILES['payment_proof'];
            $fileExt = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];

            if (!in_array($fileExt, $allowedExt)) {
                $message = 'Format file bukti pembayaran harus JPG, PNG, atau PDF.';
            } else {
                $newFileName = uniqid('proof_') . '.' . $fileExt;
                $targetFile = $uploadDir . $newFileName;

                if (move_uploaded_file($fileInfo['tmp_name'], $targetFile)) {
                    $tanggal_bayar = date('Y-m-d H:i:s');
                    $bukti_bayar_path = $newFileName; // Simpan hanya nama file ke DB

                    // --- PERBAIKAN UTAMA: Cek apakah pembayaran untuk pesanan ini sudah ada ---
                    $stmt_check = $conn->prepare("SELECT id_pembayaran FROM pembayaran WHERE id_pesanan = ?");
                    if ($stmt_check === false) {
                        error_log("Failed to prepare check statement: " . $conn->error);
                        $message = 'Error preparing payment check. Please try again.';
                        unlink($targetFile);
                    } else {
                        $stmt_check->bind_param("i", $id_pesanan);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();
                        $existing_payment_count = $result_check->num_rows;
                        $stmt_check->close();

                        // DEBUG: Tampilkan hasil pemeriksaan
                        // echo "";

                        if ($existing_payment_count > 0) {
                            // Pembayaran sudah ada, lakukan UPDATE
                            $stmt = $conn->prepare("UPDATE pembayaran SET tanggal_bayar = ?, bukti_bayar = ? WHERE id_pesanan = ?");
                            if ($stmt === false) {
                                error_log("Failed to prepare UPDATE statement: " . $conn->error);
                                $message = 'Error preparing payment update. Please try again.';
                                unlink($targetFile);
                            } else {
                                $stmt->bind_param("ssi", $tanggal_bayar, $bukti_bayar_path, $id_pesanan);
                                if ($stmt->execute()) { // LINE INI BUKAN LINE 172
                                    $message = 'Bukti pembayaran berhasil diperbarui!';
                                    $payment_success = true;
                                    $id_pesanan_display = $id_pesanan;
                                } else {
                                    $message = 'Gagal memperbarui data pembayaran ke database: ' . $stmt->error;
                                    unlink($targetFile); // Hapus file jika gagal disimpan ke DB
                                }
                                $stmt->close();
                            }
                        } else {
                            // Pembayaran belum ada, lakukan INSERT
                            // Ini adalah bagian yang menyebabkan error di LINE 172 pada kode sebelumnya
                            $stmt = $conn->prepare("INSERT INTO pembayaran (id_pesanan, tanggal_bayar, id_pelanggan, bukti_bayar) VALUES (?, ?, ?, ?)");
                            if ($stmt === false) {
                                error_log("Failed to prepare INSERT statement: " . $conn->error);
                                $message = 'Error preparing payment insert. Please try again.';
                                unlink($targetFile);
                            } else {
                                $stmt->bind_param("isis", $id_pesanan, $tanggal_bayar, $id_pelanggan, $bukti_bayar_path);
                                if ($stmt->execute()) { // Ini adalah LINE 172 Anda
                                    $message = 'Pembayaran berhasil dikirim!';
                                    $payment_success = true; // Set flag sukses
                                    $id_pesanan_display = $id_pesanan; // Simpan ID untuk ditampilkan
                                } else {
                                    $message = 'Gagal menyimpan data pembayaran ke database: ' . $stmt->error;
                                    if (file_exists($targetFile)) {
                                        unlink($targetFile); // Hapus file jika gagal disimpan ke DB
                                    }
                                }
                                $stmt->close();
                            }
                        }
                    }

                    // Jika pembayaran sukses (baik INSERT atau UPDATE), update status pesanan
                    if ($payment_success) {
                        $new_status_name = 'Pembayaran Dikonfirmasi';
                        $status_id = null;

                        $stmt_get_status_id = $conn->prepare("SELECT id_status FROM status_pesanan WHERE nama_status = ?");
                        if ($stmt_get_status_id) {
                            $stmt_get_status_id->bind_param("s", $new_status_name);
                            $stmt_get_status_id->execute();
                            $result_status_id = $stmt_get_status_id->get_result();
                            if ($result_status_id->num_rows > 0) {
                                $row = $result_status_id->fetch_assoc();
                                $status_id = $row['id_status'];
                            } else {
                                error_log("Status '" . $new_status_name . "' tidak ditemukan di tabel status_pesanan.");
                            }
                            $stmt_get_status_id->close();
                        } else {
                            error_log("Gagal menyiapkan statement SELECT status_pesanan: " . $conn->error);
                        }

                        if ($status_id !== null) {
                            $stmt_update_status = $conn->prepare("UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
                            if ($stmt_update_status) {
                                $stmt_update_status->bind_param("ii", $status_id, $id_pesanan);
                                if (!$stmt_update_status->execute()) {
                                    error_log("Gagal mengupdate status pesanan: " . $stmt_update_status->error);
                                }
                                $stmt_update_status->close();
                            } else {
                                error_log("Gagal menyiapkan statement update status: " . $conn->error);
                            }
                        } else {
                            $message .= " <br>Namun, status pesanan tidak dapat diperbarui karena status '$new_status_name' tidak ditemukan di database.";
                        }
                    }

                } else {
                    $message = 'Gagal mengunggah file bukti pembayaran.';
                }
            }
        }
    }
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Form Pembayaran</title>
    <link rel="stylesheet" href="../Admin-HTML/css/Pembayaran.css" />
    </head>
<body>
    <?php if ($payment_success): ?>
        <div class="thank-you-container">
            <h2>Terima Kasih!</h2>
            <p>Pembayaran Anda berhasil dikirim.</p>
            <p>ID Pesanan Anda adalah: <strong><?php echo htmlspecialchars($id_pesanan_display); ?></strong></p>
            <p>Kami akan segera memproses pesanan Anda. Silakan cek status pesanan secara berkala.</p>
            <div class="actions">
                <button class="home" onclick="location.href='home.php'">Kembali ke Beranda</button>
                <button class="status" onclick="location.href='cekStatus.php?order_id=<?php echo urlencode($id_pesanan_display); ?>'">Cek Status Pesanan</button>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <h1>Form Pembayaran</h1>

            <?php if ($message): ?>
                <div class="message <?= (strpos($message, 'Gagal') !== false || strpos($message, 'tidak ditemukan') !== false || strpos($message, 'tidak dapat diperbarui') !== false) ? 'error' : '' ?>" id="messageBox"><?= $message ?></div>
            <?php endif; ?>

            <?php if ($id_pesanan === null || $id_pelanggan === null): ?>
                <p>Silakan kembali ke halaman checkout untuk memulai proses pesanan.</p>
                <button onclick="location.href='checkOut.php'">Kembali ke Checkout</button>
            <?php else: ?>
                <form id="paymentForm" method="post" enctype="multipart/form-data" autocomplete="off">
                    <center><h2>Scan QR Code</h2></center>
                    <center><img src="../Admin-HTML/images/Contoh QR.jpeg" alt="QR Code" width="200" height="200"></center>

                    <label for="name">Nama</label>
                    <input type="text" id="name" name="name" placeholder="Nama lengkap" value="<?= htmlspecialchars($name ?? '') ?>" required>

                    <label for="phone">No Telepon</label>
                    <input type="tel" id="phone" name="phone" placeholder="081234567890" value="<?= htmlspecialchars($phone ?? '') ?>" pattern="[0-9+ ]{6,20}" required>

                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" placeholder="Alamat lengkap" required><?= htmlspecialchars($address ?? '') ?></textarea>

                    <label for="payment_proof">Bukti Pembayaran (JPG, PNG, PDF, JPEG)</label>
                    <input type="file" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>

                    <button type="submit">Kirim Pembayaran</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php include '../views/footer.php'; ?>
    <script>
        // Opsional: Hilangkan pesan otomatis setelah beberapa detik jika ada
        document.addEventListener('DOMContentLoaded', function() {
            const messageBox = document.getElementById('messageBox');
            if (messageBox) {
                setTimeout(() => {
                    messageBox.style.opacity = '0';
                    setTimeout(() => {
                        messageBox.style.display = 'none'; // Sembunyikan setelah fade out
                    }, 1000); // Durasi fade out
                }, 5000); // Tampilkan selama 5 detik
            }
        });
    </script>
</body>
</html>