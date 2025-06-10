<?php
require_once __DIR__ . '/../logic/update/koneksi.php'; // sambungkan ke database

// Pastikan koneksi berhasil
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ambil data dari form
$review = $_POST['review'];
$condition = $_POST['condition'];
$quality = $_POST['quality'];
$tampilkan_username = isset($_POST['username-toggle']) ? 1 : 0;
$rating_produk = $_POST['rating_produk']; // Rating Produk
$rating_seller = $_POST['rating_seller']; // Rating Seller
// Ambil id_pelanggan dari session
session_start();
$id_pelanggan = $_SESSION['id_pelanggan']; // ID pelanggan yang login
$idproduk = 123;   // misalnya dari halaman produk

// Upload foto
$fotoName = '';
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $fotoName = time() . '_' . $_FILES['photo']['name'];
    $uploadPath = __DIR__ . '/../photos/' . $fotoName; // Gunakan path relatif yang benar
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
        echo "Error uploading photo.";
    }
}

// Upload video
$videoName = '';
if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
    $videoName = time() . '_' . $_FILES['video']['name'];
    $uploadPath = __DIR__ . '/../photos/' . $videoName;
    if (!move_uploaded_file($_FILES['video']['tmp_name'], $uploadPath)) {
        echo "Error uploading video.";
    }
}

// Simpan ke database
$query = "INSERT INTO review (id_pelanggan, idproduk, rating_produk, komentar, foto, video, fitur, kegunaan, tampilkan_username, rating_seller, tanggal_review)
             VALUES ('$id_pelanggan', '$idproduk', '$rating_produk', '$review', '$fotoName', '$videoName', '$condition', '$quality', '$tampilkan_username', '$rating_seller', NOW())";

if (mysqli_query($koneksi, $query)) {
    // Redirect ke halaman terima kasih atau halaman lain setelah berhasil
    header("Location: home.php");
    exit;
} else {
    echo "Error: " . $query . "<br>" . mysqli_error($koneksi);
}
?>
