<?php
// WAJIB: session_start() harus di baris pertama TANPA spasi, tab, atau baris kosong
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['role'])) {
    header("Location: ../pages/login.php");
    exit;
}

$role = $_SESSION['role']; //role sesuai session
$currentPage = basename($_SERVER['PHP_SELF']); // halaman yang sedang dibuka.


// Daftar halaman yang boleh diakses oleh role 'konsumen'
$allowedPagesKonsumen = [
    'home.php',
    'halgaleri.php',
    'about.php',
    'keranjang.php',
    'help.php',
    'feedback.php',
    'blog.php',
    'portofolios.php',
    'qna.php',
    'contact.php',
    'profile.php',
    'feedback_process.php',
    'Pembayaran.php',
    'checkOut.php',
    'cekStatus.php',
    'halajakan.php',
    'artikelsatu.php',
    'artikeldua.php',
    'artikeltiga.php',
    'kandang.php'


];

// Daftar halaman yang boleh diakses oleh role 'admin'
$allowedPagesAdmin = [
    'dashboard.php',
    'user.php',
    'produk.php',
    'halgaleri.php',
    'galery.php',
    'home.php',
    'about.php',
    'keranjang.php',
    'help.php',
    'feedback.php',
    'blog.php',
    'portofolios.php',
    'qna.php',
    'contact.php',
    'profile.php',
    'feedback_process.php',
    'Pembayaran.php',
    'checkOut.php',
    'cekStatus.php',
    'halajakan.php',
    'artikelsatu.php',
    'artikeldua.php',
    'artikeltiga.php',
    'addGaleri.php', 'addproduk.php', 'addPublikasi.php',
    'addReviews.php','addUser.php', 'DeleteGaleri.php', 'deleteproduk.php',
    'deletePublikasi.php', 'DeleteReviews.php', 'deleteUser.php', 'detailPesanan.php',
    'EditGaleri.php', 'editProduk.php', 'editPublikasi.php', 'edit Reviews.php', 'editUser.php',
    'galeri.php', 'publikasi.php', 'setting.php', 'user.php', 'reviewsadmin.php'


    // tambah halaman admin lainnya di sini
];

// Pengecekan akses untuk 'konsumen'
if ($role === 'konsumen' && !in_array($currentPage, $allowedPagesKonsumen)) {
    echo "❌ Anda tidak punya akses ke halaman ini.";
    exit;
}

// Pengecekan akses untuk 'admin'
if ($role === 'admin' && !in_array($currentPage, $allowedPagesAdmin)) {
    echo "❌ Akses ditolak. Halaman ini hanya untuk admin.";
    exit;
}
?>
