<?php
include '../Logic/update/auth.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan pengguna sudah login
if (!isset($_SESSION['id_pelanggan'])) {
    http_response_code(403); // Forbidden
    echo "Anda harus login untuk melihat detail pesanan.";
    exit();
}

include '../configdb.php'; // Pastikan path ini benar

header('Content-Type: text/html'); // Pastikan respons adalah HTML

$order_id = $_GET['order_id'] ?? null;
$id_pelanggan = $_SESSION['id_pelanggan'];

// Periksa koneksi database di awal
if (!$conn) {
    echo '<p style="color: red;">Koneksi database gagal. Tidak dapat memuat detail pesanan.</p>';
    exit();
}

if (!$order_id) {
    echo '<p style="color: red;">ID Pesanan tidak valid.</p>';
    $conn->close(); // Pastikan koneksi ditutup jika ada error awal
    exit();
}

// Query untuk mengambil detail produk dari pesanan tertentu
// Penting: Pastikan id_pesanan yang diminta milik id_pelanggan yang sedang login untuk keamanan
$sql = "
    SELECT
        dp.jumlah,
        dp.harga_saat_pembelian,
        p.namaproduk,
        p.foto_produk
    FROM
        detail_pesanan dp
    JOIN
        produk p ON dp.idproduk = p.idproduk
    JOIN
        pesanan ps ON dp.id_pesanan = ps.id_pesanan
    WHERE
        dp.id_pesanan = ? AND ps.id_pelanggan = ?
";

$stmt = $conn->prepare($sql);

// --- PERBAIKAN DI SINI: Periksa apakah prepare() berhasil ---
if ($stmt === false) {
    error_log("Prepare statement failed: " . $conn->error); // Log error untuk debugging
    echo '<p style="color: red;">Terjadi kesalahan sistem saat memuat detail pesanan. (Error preparing statement)</p>';
    $conn->close();
    exit();
}

$stmt->bind_param("ii", $order_id, $id_pelanggan);

if (!$stmt->execute()) {
    error_log("Execute statement failed: " . $stmt->error); // Log error eksekusi
    echo '<p style="color: red;">Terjadi kesalahan sistem saat memuat detail pesanan. (Error executing statement)</p>';
    $stmt->close();
    $conn->close();
    exit();
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<table>';
    echo '<thead><tr><th>Produk</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['jumlah'] * $row['harga_saat_pembelian'];
        echo '<tr>';
        echo '<td>';
        // Asumsi folder gambar produk ada di '../Admin-HTML/images/product_images/' atau sejenisnya
        // Sesuaikan path 'src' sesuai struktur folder Anda
        // Anda bisa mengaktifkan baris di bawah ini jika ingin menampilkan gambar
        // echo '<img src="../Admin-HTML/images/product_images/' . htmlspecialchars($row['foto_produk']) . '" alt="' . htmlspecialchars($row['namaproduk']) . '" width="50" height="50" style="vertical-align: middle; margin-right: 10px;">';
        echo htmlspecialchars($row['namaproduk']);
        echo '</td>';
        echo '<td>' . htmlspecialchars($row['jumlah']) . '</td>';
        echo '<td>Rp' . number_format($row['harga_saat_pembelian'], 0, ',', '.') . '</td>';
        echo '<td>Rp' . number_format($subtotal, 0, ',', '.') . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p style="text-align: center;">Tidak ada detail produk untuk pesanan ini atau pesanan tidak ditemukan.</p>';
}

$stmt->close();
$conn->close();
?>