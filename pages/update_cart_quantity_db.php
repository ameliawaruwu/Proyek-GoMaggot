<?php
session_start();
header('Content-Type: application/json');

include '../configdb.php'; // Sesuaikan path jika perlu

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || !isset($data['idproduk']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit();
}

$productId = (int)$data['idproduk'];
$newQuantity = (int)$data['quantity'];

if ($newQuantity < 1) { // Kuantitas tidak boleh kurang dari 1, untuk hapus gunakan remove_from_cart_db.php
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1. Use remove button to delete item.']);
    exit();
}

$id_pelanggan = isset($_SESSION['id_pelanggan']) ? (int)$_SESSION['id_pelanggan'] : 0; // Ganti 0 dengan ID pelanggan yang sebenarnya jika user login.

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit();
}

// Cek stok produk terlebih dahulu
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
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
    exit();
}

$available_stock = (int)$product_info['stok'];

if ($newQuantity > $available_stock) {
    echo json_encode(['success' => false, 'message' => 'Stok tidak cukup. Stok tersedia: ' . $available_stock]);
    exit();
}

// Update kuantitas di tabel keranjang
$stmt_update = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id_pelanggan = ? AND idproduk = ?");
if (!$stmt_update) {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
    exit();
}
$stmt_update->bind_param("iii", $newQuantity, $id_pelanggan, $productId);
if ($stmt_update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Kuantitas berhasil diupdate.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate kuantitas: ' . $conn->error]);
}
$stmt_update->close();
$conn->close();
?>