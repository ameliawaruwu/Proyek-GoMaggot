<?php
session_start();
header('Content-Type: application/json');

include '../configdb.php'; // Sesuaikan path jika perlu

// Ambil data JSON dari body request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Pastikan data valid dan tidak ada error JSON
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON or empty data provided.']);
    exit();
}

// Validasi keberadaan properti yang diharapkan
$required_fields = ['idproduk', 'quantity', 'namaproduk', 'harga', 'gambar'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
        exit();
    }
}

$productId = (int)$data['idproduk'];
$quantity = (int)$data['quantity'];
// $productName = $data['namaproduk']; // Tidak disimpan di tabel keranjang, hanya untuk referensi/validasi
// $productPrice = (float)$data['harga']; // Tidak disimpan di tabel keranjang, hanya untuk referensi/validasi
// $productImage = $data['gambar']; // Tidak disimpan di tabel keranjang

// Dapatkan id_pelanggan. Sesuaikan ini sesuai sistem login Anda.
// Jika user belum login, Anda bisa menggunakan ID sementara (misal 0) atau ID sesi.
// Untuk POC (Proof of Concept) ini, kita gunakan 0 atau Anda bisa membuatnya tergantung sesi login.
// Asumsi: Jika user login, $_SESSION['user_id'] berisi ID pelanggan.
$id_pelanggan = isset($_SESSION['id_pelanggan']) ? (int)$_SESSION['id_pelanggan'] : 0; // Ganti 0 dengan ID pelanggan yang sebenarnya jika user login.

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit();
}

// Cek stok produk dari tabel 'produk'
$stmt_stock = $conn->prepare("SELECT stok FROM produk WHERE idproduk = ?");
if (!$stmt_stock) {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
    exit();
}
$stmt_stock->bind_param("i", $productId);
$stmt_stock->execute();
$result_stock = $stmt_stock->get_result();
$product_info = $result_stock->fetch_assoc();
$stmt_stock->close();

if (!$product_info) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan dalam database produk.']);
    exit();
}

$available_stock = (int)$product_info['stok'];

// Cek apakah produk sudah ada di keranjang untuk pelanggan ini
$stmt_check = $conn->prepare("SELECT id_keranjang, jumlah FROM keranjang WHERE id_pelanggan = ? AND idproduk = ?");
if (!$stmt_check) {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement (check): ' . $conn->error]);
    exit();
}
$stmt_check->bind_param("ii", $id_pelanggan, $productId);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$existing_item = $result_check->fetch_assoc();
$stmt_check->close();

if ($existing_item) {
    // Produk sudah ada di keranjang, update jumlahnya
    $newQuantity = $existing_item['jumlah'] + $quantity;

    // Validasi stok sebelum update
    if ($newQuantity > $available_stock) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak cukup untuk menambah kuantitas. Stok tersedia: ' . $available_stock]);
        exit();
    }

    $stmt_update = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ?");
    if (!$stmt_update) {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement (update): ' . $conn->error]);
        exit();
    }
    $stmt_update->bind_param("ii", $newQuantity, $existing_item['id_keranjang']);
    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Kuantitas produk berhasil diupdate.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupdate kuantitas: ' . $conn->error]);
    }
    $stmt_update->close();
} else {
    // Produk belum ada di keranjang, tambahkan baru
    // Validasi stok sebelum insert
    if ($quantity > $available_stock) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak cukup. Stok tersedia: ' . $available_stock]);
        exit();
    }

    $stmt_insert = $conn->prepare("INSERT INTO keranjang (id_pelanggan, idproduk, jumlah) VALUES (?, ?, ?)");
    if (!$stmt_insert) {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement (insert): ' . $conn->error]);
        exit();
    }
    $stmt_insert->bind_param("iii", $id_pelanggan, $productId, $quantity);
    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan produk ke keranjang: ' . $conn->error]);
    }
    $stmt_insert->close();
}

$conn->close();
?>