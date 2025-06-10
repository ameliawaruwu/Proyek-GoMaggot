<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../configdb.php'; // Pastikan path ini benar

// Periksa apakah request method adalah POST dan header Content-Type adalah application/json
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method or content type.']);
    exit();
}

// Ambil input JSON dari request body
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit();
}

// Ambil data yang dikirim dari JavaScript
$nama_penerima        = $input['nama_penerima'] ?? '';
$nomor_telepon        = $input['nomor_telepon'] ?? '';
$alamat_lengkap_form= $input['alamat_lengkap'] ?? '';
$kota                 = $input['kota'] ?? '';
$metode_pembayaran    = $input['metode_pembayaran'] ?? '';
$cartItems            = $input['cartItems'] ?? []; // Ini adalah data keranjang dari localStorage di JS

// Menggabungkan alamat_lengkap_form dan kota untuk kolom alamat_pengiriman di DB
$alamat_pengiriman_db = $alamat_lengkap_form . ', ' . $kota;

// Hitung ulang total harga dari data keranjang yang diterima dari JavaScript
$final_total_harga_calculated = 0;
// $final_total_produk_calculated = 0; // Tidak ada di tabel pesanan, jadi tidak perlu dihitung atau disimpan

if (!empty($cartItems)) {
    foreach ($cartItems as $item) {
        $itemJumlah = (int)($item['jumlah'] ?? 0);
        $itemHarga = (float)($item['harga'] ?? 0);
        if ($itemJumlah > 0 && $itemHarga >= 0) {
            $final_total_harga_calculated += ($itemJumlah * $itemHarga);
        }
    }
}

// Validasi komprehensif setelah perhitungan ulang
// Menghapus $kota dari validasi empty() karena kota digabungkan ke alamat_pengiriman_db
// dan validasi alamat_lengkap_form sudah cukup.
if (empty($nama_penerima) || empty($nomor_telepon) || empty($alamat_lengkap_form) || empty($metode_pembayaran) || empty($cartItems) || $final_total_harga_calculated <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Mohon lengkapi semua data pengiriman dan pembayaran, dan pastikan keranjang tidak kosong dan total harga lebih dari nol.']);
    exit();
}

// Pastikan koneksi database berhasil
if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal.']);
    exit();
}

try {
    $conn->begin_transaction();

    // Penanganan id_pelanggan
    $id_pelanggan = $_SESSION['id_pelanggan'] ?? null;
    if ($id_pelanggan === null) {
        // Jika id_pelanggan tidak ada di sesi, Anda punya beberapa opsi:
        // 1. Paksa login (uncomment baris di bawah ini jika ingin memaksa login)
        // throw new Exception("ID Pelanggan tidak ditemukan. Mohon login terlebih dahulu.");
        // 2. Gunakan nilai default 'guest' (jika kolom id_pelanggan di DB menerima string 'guest')
        $id_pelanggan = 'guest'; // Misalnya, untuk pelanggan tanpa login
        // 3. Gunakan NULL (jika kolom id_pelanggan di DB adalah NULLABLE)
        // $id_pelanggan = null;
        // PENTING: Pilih salah satu sesuai konfigurasi DB Anda untuk kolom id_pelanggan (NOT NULL vs NULLABLE)
    }
    
    // --- PERUBAHAN UTAMA DI SINI ---
    // Dapatkan ID dari 'Menunggu Pembayaran' dari tabel status_pesanan
    $status_awal_text = 'Menunggu Pembayaran';
    $stmt_get_status_id = $conn->prepare("SELECT id_status FROM status_pesanan WHERE nama_status = ?");
    if (!$stmt_get_status_id) {
        error_log("Prepare statement untuk mendapatkan status ID gagal: " . $conn->error);
        throw new Exception("Gagal menyiapkan statement untuk mendapatkan ID status.");
    }
    $stmt_get_status_id->bind_param("s", $status_awal_text);
    $stmt_get_status_id->execute();
    $result_status_id = $stmt_get_status_id->get_result();
    $status_row = $result_status_id->fetch_assoc();

    if (!$status_row) {
        // Ini seharusnya tidak terjadi jika tabel status_pesanan sudah diisi dengan benar
        error_log("Status '" . $status_awal_text . "' tidak ditemukan di tabel status_pesanan.");
        throw new Exception("Konfigurasi status pesanan tidak valid. Mohon hubungi administrator.");
    }
    $id_status_pesanan = $status_row['id_status'];
    // --- AKHIR PERUBAHAN UTAMA ---

    $tanggal_pesanan = date('Y-m-d H:i:s'); // Mengambil tanggal saat ini

    // --- Bagian INSERT ke tabel 'pesanan' ---
    // Kolom disesuaikan 100% dengan Screenshot 2025-06-03 200356.jpg:
    // id_pelanggan, nama_penerima, nomor_telepon, alamat_pengiriman,
    // tanggal_pesanan, status, total_harga, metode_pembayaran
    $stmt_pesanan = $conn->prepare("INSERT INTO pesanan (id_pelanggan, nama_penerima, nomor_telepon, alamat_pengiriman, tanggal_pesanan, status, total_harga, metode_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt_pesanan) {
        error_log("Prepare statement pesanan failed: " . $conn->error);
        throw new Exception("Gagal menyiapkan statement pesanan: " . $conn->error);
    }

    // Perbaikan PENTING: string tipe data "sssssids" untuk 8 parameter
    // 1. id_pelanggan (s)
    // 2. nama_penerima (s)
    // 3. nomor_telepon (s)
    // 4. alamat_pengiriman_db (s)
    // 5. tanggal_pesanan (s)
    // 6. id_status_pesanan (i) - INI PERUBAHANNYA, sekarang integer
    // 7. final_total_harga_calculated (d)
    // 8. metode_pembayaran (s)
    $stmt_pesanan->bind_param(
        "sssssids", // INI PERUBAHANNYA: 's' untuk status_pesanan menjadi 'i' (integer)
        $id_pelanggan,
        $nama_penerima,
        $nomor_telepon,
        $alamat_pengiriman_db, // Ini adalah gabungan alamat_lengkap_form dan kota
        $tanggal_pesanan,
        $id_status_pesanan, // INI PERUBAHANNYA: Menggunakan ID status
        $final_total_harga_calculated,
        $metode_pembayaran
    );
    
    if (!$stmt_pesanan->execute()) {
        error_log("Database INSERT Pesanan Error: " . $stmt_pesanan->error);
        throw new Exception("Gagal menyimpan pesanan ke database: " . $stmt_pesanan->error);
    }

    $id_pesanan_baru = $conn->insert_id; // Ambil ID pesanan yang baru saja dibuat

    // --- DEBUGGING UNTUK id_pesanan_baru ---
    error_log("SUCCESS: Pesanan utama dibuat dengan ID: " . $id_pesanan_baru);
    // --- AKHIR DEBUGGING ---

    // --- Bagian INSERT ke tabel 'detail_pesanan' dan PENGURANGAN STOK PRODUK ---
    $stmt_detail_pesanan = $conn->prepare("INSERT INTO detail_pesanan (id_pesanan, idproduk, jumlah, harga_saat_pembelian) VALUES (?, ?, ?, ?)");
    $stmt_update_stok = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE idproduk = ? AND stok >= ?");

    if (!$stmt_detail_pesanan) {
        error_log("Prepare statement detail_pesanan failed: " . $conn->error);
        throw new Exception("Gagal menyiapkan statement detail pesanan: " . $conn->error);
    }
    if (!$stmt_update_stok) {
        error_log("Prepare statement update stok failed: " . $conn->error);
        throw new Exception("Gagal menyiapkan statement update stok: " . $conn->error);
    }

    foreach ($cartItems as $item) {
        $id_produk = $item['idproduk'] ?? null;
        $jumlah_produk = (int)($item['jumlah'] ?? 0);
        $harga_produk_saat_pembelian = (float)($item['harga'] ?? 0);

        if ($id_produk && $jumlah_produk > 0 && $harga_produk_saat_pembelian >= 0) {
            // Masukkan detail pesanan
            $stmt_detail_pesanan->bind_param("iiid", $id_pesanan_baru, $id_produk, $jumlah_produk, $harga_produk_saat_pembelian);
            if (!$stmt_detail_pesanan->execute()) {
                error_log("Database INSERT Detail Pesanan Error: " . $stmt_detail_pesanan->error);
                throw new Exception("Gagal menyimpan detail pesanan untuk produk ID " . $id_produk . ": " . $stmt_detail_pesanan->error);
            }

            // --- PENGURANGAN STOK PRODUK ---
            $stmt_update_stok->bind_param("iii", $jumlah_produk, $id_produk, $jumlah_produk);
            
            if (!$stmt_update_stok->execute()) {
                error_log("Database UPDATE Stok Error: " . $stmt_update_stok->error);
                throw new Exception("Gagal mengurangi stok untuk produk ID " . $id_produk . ": " . $stmt_update_stok->error);
            }

            // Periksa apakah ada baris yang terpengaruh (stok berhasil dikurangi)
            if ($stmt_update_stok->affected_rows === 0) {
                error_log("Stok tidak mencukupi atau produk tidak ditemukan untuk ID: " . $id_produk . " (Jumlah diminta: " . $jumlah_produk . ")");
                throw new Exception("Stok tidak mencukupi untuk produk: " . htmlspecialchars($item['namaproduk'] ?? 'ID Produk ' . $id_produk));
            }
        } else {
            error_log("Skipping invalid cart item (ID: " . ($id_produk ?? 'N/A') . ", Jumlah: " . $jumlah_produk . ", Harga: " . $harga_produk_saat_pembelian . ")");
        }
    }

    $conn->commit(); // Commit transaksi jika semua berhasil

    // Hapus item keranjang dari sesi (jika masih ada sisa-sisa dari alur lama)
    unset($_SESSION['checkout_items']);
    unset($_SESSION['checkout_total_harga']);

    // Kirim respons sukses dalam format JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan Anda berhasil ditempatkan! ID Pesanan: ' . $id_pesanan_baru,
        'redirect_url' => 'pembayaran.php?order_id=' . $id_pesanan_baru
    ]);
    exit();

} catch (Exception $e) {
    $conn->rollback(); // Rollback transaksi jika ada kesalahan
    // Kirim respons error dalam format JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage()]);
    error_log("General Checkout Exception: " . $e->getMessage()); // Log error untuk debugging
    exit(); // Penting: Hentikan eksekusi setelah mengirim JSON
} finally {
    if (isset($stmt_pesanan) && $stmt_pesanan instanceof mysqli_stmt) {
        $stmt_pesanan->close();
    }
    if (isset($stmt_detail_pesanan) && $stmt_detail_pesanan instanceof mysqli_stmt) {
        $stmt_detail_pesanan->close();
    }
    if (isset($stmt_update_stok) && $stmt_update_stok instanceof mysqli_stmt) {
        $stmt_update_stok->close();
    }
    // Tutup statement untuk mendapatkan status ID
    if (isset($stmt_get_status_id) && $stmt_get_status_id instanceof mysqli_stmt) {
        $stmt_get_status_id->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>