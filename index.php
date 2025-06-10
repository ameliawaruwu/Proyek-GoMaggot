<?php
session_start();
include "views/headeradmin.php";

// Tentukan daftar halaman yang diizinkan
$allowed_pages = [
    'dashboard','produk','galeri','user','login',
    'publikasi','setting','chat','addproduk','editproduk','deleteproduk'
];

// Ambil parameter 'page', default ke 'dashboard'
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Jika halaman diizinkan dan file-nya ada, maka include
if (in_array($page, $allowed_pages) && file_exists("pages/$page.php")) {
    include "pages/$page.php";
} else {
    echo "<h2 style='padding: 20px;'>404 - Halaman <em>$page</em> tidak ditemukan</h2>";
}

include "views/footeradmin.php";
?>
