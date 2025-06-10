<?php
// Pastikan semua error dilaporkan untuk debugging selama pengembangan
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../views/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../Admin-HTML/css/detailProduk.css">
<?php include '../configdb.php'; ?>
<?php

// Ambil ID produk dari parameter URL dan validasi
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Pastikan ID adalah integer

$product = null; // Inisialisasi produk sebagai null
if ($productId > 0) {
    // Gunakan prepared statement untuk keamanan SQL Injection
    $query = "SELECT * FROM produk WHERE idproduk = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $productId); // "i" for integer
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
    } else {
        // Log error jika prepared statement gagal
        error_log("Failed to prepare statement: " . $conn->error);
    }
}

// Periksa apakah produk ditemukan
if (!$product) {
    echo '<section class="error-message"><div class="container flex">Produk tidak ditemukan atau ID tidak valid.</div></section>';
    // Atau bisa redirect ke halaman lain:
    // header("Location: daftar_produk.php");
    // exit();
    include '../views/footer.php';
    exit(); // Hentikan eksekusi skrip lebih lanjut
}

// Ambil data produk dan pastikan di-escape
$productName = htmlspecialchars($product['namaproduk']);
$productPrice = $product['harga']; // Harga akan diformat nanti
$productDescription = htmlspecialchars($product['deskripsi_produk']);
$productCategory = htmlspecialchars($product['kategori']);
$productStock = (int)$product['stok'];
$productBrand = htmlspecialchars($product['Merk']);
// Masa penyimpanan di tabel Anda adalah 'masapenyimpanan', tipe data DECIMAL(10,2) atau sejenisnya
// Jika ini adalah angka bulan, tampilkan dengan "Bulan"
$productSave = htmlspecialchars($product['masapenyimpanan']) . ' Bulan'; // Sesuaikan format output
$productWeight = htmlspecialchars($product['berat']) . 'g'; // Sesuaikan format output
$productImage = htmlspecialchars($product['gambar']);
$productPengiriman = "Bandung"; // Karena tidak ada di tabel, ini tetap statis atau tambahkan kolom 'pengiriman' jika perlu
?>

<section>
    <div class="container flex">
        <div class="left">
            <div class="main_image">
                <img src="../Admin-HTML/images/<?= $productImage; ?>" class="slide" width="360" height="300" alt="<?= $productName; ?>">
            </div>
            <div class="option flex">
                <img src="../Admin-HTML/images/kompos remove bg.png" onclick="img('image/p1.jpg')" alt="Thumbnail 1">
                <img src="../Admin-HTML/images/Bibit-remove bg.png" onclick="img('image/p2.jpg')" alt="Thumbnail 2">
                <img src="../Admin-HTML/images/Bundling Maggot.png" onclick="img('image/p3.jpg')" alt="Thumbnail 3">
                <img src="../Admin-HTML/images/maggot removebg.png" onclick="img('image/p4.jpg')" alt="Thumbnail 4">
            </div>
        </div>
        <div class="right">
            <h3><?= $productName; ?></h3>
            <div class="product-rating">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <span>(5.0)</span>
            </div>

            <div class="info">
                <span>900 Penilaian</span>
                <span>1RB+ Terjual</span>
            </div>
            <div class="button">
                <a href="laporan.html">Laporkan</a>
            </div>

            <h1><small>Rp</small><?= number_format($productPrice, 0, ',', '.'); ?> / pcs</h1>
            <p><?= $productDescription; ?></p>
            <div class="tombol">
                <a href="Keranjang.php">
                    <button>Kembali ke Produk</button>
                </a>
                </div>
        </div>
    </div>
</section>

<div class="bagianprofil">
    <div class="profile">
        <div class="profile-image">
            <img src="../Admin-HTML/images/SS LOGO.png" alt="GoMaggot Logo" width="100" height="70">
        </div>
        <div class="profile-info">
            <h2>GoMaggot</h2>
        </div>
    </div>
    <div class="stats">
        <div class="stat">
            <span class="stat-value">1,5RB</span>
            <span class="stat-label">Penilaian</span>
        </div>
        <div class="stat">
            <span class="stat-value">10</span>
            <span class="stat-label">Produk</span>
        </div>
        <div class="stat">
            <span class="stat-value">90%</span>
            <span class="stat-label">Persentase Chat Dibalas</span>
        </div>
        <div class="stat">
            <span class="stat-value">Hitungan Jam</span>
            <span class="stat-label">Waktu Chat Dibalas</span>
        </div>
        <div class="stat">
            <span class="stat-value">3 tahun lalu</span>
            <span class="stat-label">Memulai</span>
        </div>
        <div class="stat">
            <span class="stat-value">200RB</span>
            <span class="stat-label">Pengikut</span>
        </div>
    </div>
</div>

<div class="bagiandesk">
    <h2>Spesifikasi Produk</h2><br>

    <div class="spec-item">
        <span class="spec-label">Kategori:</span>
        <span class="spec-value"><?= $productCategory; ?></span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Stok:</span>
        <span class="spec-value">
            <?php
            if ($productStock > 0) {
                echo $productStock . ' pcs';
            } else {
                echo 'Habis';
            }
            ?>
        </span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Merek:</span>
        <span class="spec-value"><?= $productBrand; ?></span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Masa Penyimpanan:</span>
        <span class="spec-value"><?= $productSave; ?></span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Berat:</span>
        <span class="spec-value"><?= $productWeight; ?></span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Harga:</span>
        <span class="spec-value">Rp <?= number_format($productPrice, 0, ',', '.'); ?> / pcs</span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Dikirim Dari:</span>
        <span class="spec-value"><?= $productPengiriman; ?></span>
    </div>

    <div class="spec-item">
        <span class="spec-label">Deskripsi:</span>
        <span class="spec-value"><?= $productDescription; ?></span>
    </div>
</div>

<div class="bagianakhir">
    <h2>Penilaian Produk</h2><br>
    <div class="rating">
        <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
        </div>
        <div class="score">5.0 dari 5</div>
    </div><br><br>
    <div class="review">
        <div class="user">
            <img src="../Admin-HTML/images/billie.jpg" alt="Avatar" class="avatar">
            <div class="name">Billie Eilish</div>
        </div>
        <div class="text">Kandang nya ringan, saya kira akan berat woww!</div>
    </div>
    <div class="review">
        <div class="user">
            <img src="../Admin-HTML/images/jungkook.jpg" alt="Avatar" class="avatar">
            <div class="name">Jeon Jungkook</div>
        </div>
        <div class="text">Bentuk kandangnya sangat menarik, ayah saya sangat menyukainya..</div>
    </div>
    <div class="review">
        <div class="user">
            <img src="../Admin-HTML/images/cha eun woo.jpg" alt="Avatar" class="avatar">
            <div class="name">Cha Eun Woo</div>
        </div>
        <div class="text">Toko ini memang tidak pernah mengecewakan.</div>
    </div>

    <div class="tombol">
        <button>Lihat Semua Ulasan</button>
    </div>
</div>

<?php
// Tutup koneksi database
if ($conn) {
    mysqli_close($conn);
}
include '../views/footer.php';
?>