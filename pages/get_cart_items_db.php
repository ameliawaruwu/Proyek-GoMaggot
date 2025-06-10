<?php
session_start();
header('Content-Type: application/json');

include '../configdb.php'; // Sesuaikan path jika perlu. Pastikan ini menghasilkan variabel $conn.

// Pastikan $conn terdefinisi dan koneksi berhasil.
// Idealnya, configdb.php sudah menangani ini dengan die() jika gagal.
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . ($conn->connect_error ?? 'Variabel $conn tidak terdefinisi.')]);
    exit();
}

$id_pelanggan = isset($_SESSION['id_pelanggan']) ? (int)$_SESSION['id_pelanggan'] : 0;

// Jika id_pelanggan tidak ada (pengguna belum login), kembalikan keranjang kosong.
// Ini penting agar tidak ada error ketika user belum login dan mencoba melihat keranjang.
if ($id_pelanggan === 0) {
    echo json_encode(['success' => true, 'message' => 'Pengguna belum login.', 'cart' => []]);
    $conn->close();
    exit();
}

$cartItems = [];

// PERHATIKAN NAMA KOLOM:
// K.idproduk -> asumsi ini id_produk di tabel keranjang
// p.namaproduk -> asumsi ini nama_produk di tabel produk
// p.harga -> asumsi ini harga_produk di tabel produk
// p.gambar -> asumsi ini gambar_url di tabel produk
// k.jumlah -> asumsi ini jumlah di tabel keranjang
// k.tanggal_ditambahkan -> asumsi ini nama kolom untuk waktu penambahan

$query = "
    SELECT
        k.id_produk, -- Kolom id produk di tabel keranjang (sesuaikan jika namanya idproduk)
        p.nama_produk AS namaproduk, -- Nama kolom produk di tabel produk
        p.harga_produk AS harga, -- Harga produk di tabel produk
        p.gambar_url AS gambar, -- Path/nama file gambar di tabel produk
        k.jumlah -- Jumlah produk di tabel keranjang
    FROM
        keranjang k
    JOIN
        produk p ON k.id_produk = p.id_produk -- Sesuaikan nama tabel 'produk' dan kolom JOIN jika berbeda
    WHERE
        k.id_pelanggan = ?
    ORDER BY
        k.tanggal_ditambahkan DESC -- Sesuaikan nama kolom jika berbeda
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    // Log error untuk debugging di server
    error_log("Error preparing get_cart_items_db.php statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement: ' . $conn->error]);
    $conn->close();
    exit();
}

$stmt->bind_param("i", $id_pelanggan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Penting: Sesuaikan path gambar jika diperlukan.
        // Jika kolom 'gambar_url' hanya menyimpan 'nama_gambar.jpg' dan gambar ada di '../Admin-HTML/images/',
        // maka tambahkan path di sini. Jika kolom sudah menyimpan path lengkap, baris ini TIDAK DIBUTUHKAN.
        // Contoh: $row['gambar'] = 'path/ke/folder/images/' . $row['gambar'];
        // Jika path gambar Anda sudah lengkap di database (misal: /images/product-1.jpg),
        // maka Anda tidak perlu menambahkan prefix path lagi.
        // Asumsi gambar Anda berada di '../Admin-HTML/images/' relatif terhadap halaman yang memanggil ini.
        if (!empty($row['gambar']) && strpos($row['gambar'], '../') === false && strpos($row['gambar'], 'http') === false) {
             // Jika gambar hanya nama file, tambahkan path relatif
            $row['gambar'] = '../Admin-HTML/images/' . $row['gambar'];
        }
        $cartItems[] = $row;
    }
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'cart' => $cartItems
]);
?>