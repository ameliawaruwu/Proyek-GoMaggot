<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan file koneksi database
include '../configdb.php'; // PASTIKAN PATH INI BENAR!

// Periksa apakah koneksi berhasil
if (!isset($conn) || $conn->connect_error) {
    // Ini akan menampilkan error jika koneksi database gagal
    echo "<script>alert('Koneksi database gagal: " . ($conn->connect_error ?? 'Objek koneksi tidak tersedia') . "'); window.history.back();</script>";
    exit; // Hentikan eksekusi script
}

// Pastikan request adalah POST dan ada data id_produk
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_produk'])) {
    $id_produk = (int)$_POST['id_produk'];
    $jumlah_diminta = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1; // Default jumlah 1

    // Debugging: Cek data POST yang diterima
    // error_log("Debug: Data POST diterima - id_produk: " . $id_produk . ", jumlah: " . $jumlah_diminta);

    // Asumsi: Anda sudah punya id_pelanggan dari sesi login
    // SESUAIKAN DENGAN NAMA VARIABEL SESSION ID PENGGUNA ANDA
    $id_pelanggan = $_SESSION['id_pengguna'] ?? null; 

    if (!$id_pelanggan) {
        echo "<script>alert('Anda harus login untuk melakukan pembelian.'); window.location.href='../auth/login.php';</script>";
        exit;
    }

    // 1. Ambil detail produk (harga, stok)
    $stmt_produk = $conn->prepare("SELECT namaproduk, harga, stok FROM produk WHERE idproduk = ?");
    $stmt_produk->bind_param("i", $id_produk);
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();

    if ($result_produk->num_rows > 0) {
        $produk = $result_produk->fetch_assoc();
        $harga_satuan = $produk['harga'];
        $stok_tersedia = $produk['stok'];

        // Debugging: Cek detail produk yang ditemukan
        // error_log("Debug: Produk ditemukan - Nama: " . $produk['namaproduk'] . ", Harga: " . $harga_satuan . ", Stok: " . $stok_tersedia);

        // Validasi jumlah dan stok
        if ($jumlah_diminta <= 0 || $jumlah_diminta > $stok_tersedia) {
            echo "<script>alert('Jumlah tidak valid atau stok tidak mencukupi. Stok tersedia: " . $stok_tersedia . "'); window.history.back();</script>";
            exit;
        }

        // Mulai transaksi database untuk memastikan atomisitas
        $conn->begin_transaction();

        try {
            $id_pesanan_aktif = null;

            // Logika: Cari pesanan aktif untuk pelanggan dengan status 'Menunggu Pembayaran' (atau 'Draft')
            // Jika ada, tambahkan item ke pesanan itu. Jika tidak, buat pesanan baru.
            $stmt_cek_pesanan = $conn->prepare("SELECT id_pesanan FROM pesanan WHERE id_pelanggan = ? AND status = 'Menunggu Pembayaran' LIMIT 1");
            $stmt_cek_pesanan->bind_param("i", $id_pelanggan);
            $stmt_cek_pesanan->execute();
            $result_cek_pesanan = $stmt_cek_pesanan->get_result();

            if ($result_cek_pesanan->num_rows > 0) {
                $row_pesanan = $result_cek_pesanan->fetch_assoc();
                $id_pesanan_aktif = $row_pesanan['id_pesanan'];
                // error_log("Debug: Pesanan aktif ditemukan: " . $id_pesanan_aktif);
            }

            if (!$id_pesanan_aktif) {
                // Jika tidak ada pesanan aktif, buat pesanan baru
                // Anda perlu data nama_penerima, alamat_pengiriman, nomor_telepon.
                // Ini bisa diambil dari profil user atau user diminta mengisi di halaman selanjutnya
                // Untuk sementara, gunakan placeholder atau ambil dari sesi/profil jika ada
                $nama_penerima = $_SESSION['user_nama'] ?? "Nama Pelanggan"; // Ganti dengan data aktual
                $alamat_pengiriman = $_SESSION['user_alamat'] ?? "Alamat Pelanggan Default"; // Ganti
                $nomor_telepon = $_SESSION['user_telepon'] ?? "08123456789"; // Ganti
                $metode_pembayaran = "Belum Dipilih"; // Default awal

                $initial_total_harga = 0; // Akan diupdate setelah detail pesanan ditambahkan
                $status_awal_pesanan = 'Menunggu Pembayaran'; 

                $stmt_insert_pesanan = $conn->prepare("INSERT INTO pesanan (id_pelanggan, nama_penerima, alamat_pengiriman, tanggal_pesanan, status, total_harga, metode_pembayaran, nomor_telepon) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
                // Pastikan tipe data binding sesuai: i (int), s (string), d (double)
                $stmt_insert_pesanan->bind_param("isssdds", $id_pelanggan, $nama_penerima, $alamat_pengiriman, $status_awal_pesanan, $initial_total_harga, $metode_pembayaran, $nomor_telepon);
                $stmt_insert_pesanan->execute();
                $id_pesanan_aktif = $conn->insert_id; // Dapatkan ID pesanan yang baru dibuat

                if (!$id_pesanan_aktif) {
                    throw new Exception("Gagal membuat pesanan baru di tabel 'pesanan'.");
                }
                // error_log("Debug: Pesanan baru dibuat: " . $id_pesanan_aktif);
            }

            // 2. Masukkan item ke tabel `detail_pesanan`
            // Cek apakah produk sudah ada di detail_pesanan untuk pesanan aktif ini
            $stmt_cek_detail = $conn->prepare("SELECT id_detail, jumlah FROM detail_pesanan WHERE id_pesanan = ? AND id_produk = ?");
            $stmt_cek_detail->bind_param("ii", $id_pesanan_aktif, $id_produk);
            $stmt_cek_detail->execute();
            $result_cek_detail = $stmt_cek_detail->get_result();

            if ($result_cek_detail->num_rows > 0) {
                // Produk sudah ada, update jumlahnya
                $detail_existing = $result_cek_detail->fetch_assoc();
                $new_jumlah = $detail_existing['jumlah'] + $jumlah_diminta;

                if ($new_jumlah > $stok_tersedia) {
                    throw new Exception("Penambahan melebihi stok tersedia. Hanya " . $stok_tersedia . " unit yang tersedia.");
                }

                $stmt_update_detail = $conn->prepare("UPDATE detail_pesanan SET jumlah = ? WHERE id_detail = ?");
                $stmt_update_detail->bind_param("ii", $new_jumlah, $detail_existing['id_detail']);
                $stmt_update_detail->execute();

                if ($stmt_update_detail->affected_rows === 0) {
                    throw new Exception("Gagal memperbarui jumlah detail pesanan (tidak ada baris yang terpengaruh).");
                }
                // error_log("Debug: Jumlah detail pesanan diperbarui untuk id_detail: " . $detail_existing['id_detail'] . ", jumlah baru: " . $new_jumlah);
            } else {
                // Produk belum ada, masukkan sebagai item baru
                $stmt_insert_detail = $conn->prepare("INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_saat_pembelian) VALUES (?, ?, ?, ?)");
                $stmt_insert_detail->bind_param("iiid", $id_pesanan_aktif, $id_produk, $jumlah_diminta, $harga_satuan);
                $stmt_insert_detail->execute();

                if ($stmt_insert_detail->affected_rows === 0) {
                    throw new Exception("Gagal menambahkan detail pesanan baru.");
                }
                // error_log("Debug: Detail pesanan baru ditambahkan untuk id_pesanan: " . $id_pesanan_aktif . ", id_produk: " . $id_produk);
            }

            // 3. Update stok produk
            $stmt_update_stok = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE idproduk = ?");
            $stmt_update_stok->bind_param("ii", $jumlah_diminta, $id_produk);
            $stmt_update_stok->execute();

            if ($stmt_update_stok->affected_rows === 0) {
                // Ini bisa terjadi jika stok sudah 0 atau id_produk salah
                throw new Exception("Gagal mengurangi stok produk atau produk tidak ditemukan.");
            }
            // error_log("Debug: Stok produk diupdate untuk id_produk: " . $id_produk . ", dikurangi: " . $jumlah_diminta);

            // 4. Update total_harga di tabel `pesanan`
            $stmt_update_total = $conn->prepare("UPDATE pesanan SET total_harga = (SELECT SUM(jumlah * harga_saat_pembelian) FROM detail_pesanan WHERE id_pesanan = ?) WHERE id_pesanan = ?");
            $stmt_update_total->bind_param("ii", $id_pesanan_aktif, $id_pesanan_aktif);
            $stmt_update_total->execute();

            if ($stmt_update_total->affected_rows === 0) {
                throw new Exception("Gagal memperbarui total harga pesanan.");
            }
            // error_log("Debug: Total harga pesanan diperbarui untuk id_pesanan: " . $id_pesanan_aktif);

            $conn->commit(); // Commit transaksi jika semua berhasil
            echo "<script>alert('Produk berhasil ditambahkan ke pesanan Anda! Lanjutkan pembayaran.'); window.location.href='halaman_pesanan_saya.php?id_pesanan=" . $id_pesanan_aktif . "';</script>";
            // Sesuaikan 'halaman_pesanan_saya.php' dengan halaman yang benar untuk menampilkan detail pesanan
            exit;

        } catch (Exception $e) {
            $conn->rollback(); // Rollback jika ada kesalahan
            // error_log("Error in proses_pesanan_langsung.php: " . $e->getMessage()); // Log error ke server
            echo "<script>alert('Terjadi kesalahan saat memproses pesanan: " . $e->getMessage() . "'); window.history.back();</script>";
            exit;
        } finally {
            // Tutup semua statement yang mungkin dibuka
            if (isset($stmt_produk)) $stmt_produk->close();
            if (isset($stmt_cek_pesanan)) $stmt_cek_pesanan->close();
            if (isset($stmt_insert_pesanan)) $stmt_insert_pesanan->close();
            if (isset($stmt_cek_detail)) $stmt_cek_detail->close();
            if (isset($stmt_update_detail)) $stmt_update_detail->close();
            if (isset($stmt_insert_detail)) $stmt_insert_detail->close();
            if (isset($stmt_update_stok)) $stmt_update_stok->close();
            if (isset($stmt_update_total)) $stmt_update_total->close();
        }
    } else {
        echo "<script>alert('Produk tidak ditemukan atau sudah tidak tersedia di database.'); window.history.back();</script>";
        exit;
    }
} else {
    // Jika akses bukan POST atau data tidak lengkap
    echo "<script>alert('Akses tidak sah atau data tidak lengkap.'); window.history.back();</script>";
    exit;
}

// Tutup koneksi database di akhir (jika masih terbuka)
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>