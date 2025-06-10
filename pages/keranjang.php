<?php include '../logic/update/auth.php'; ?>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include header Anda
// Asumsi headerkeranjang.php berisi tag <head> dan awal <body>
include '../views/headerkeranjang.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/keranjang.css">

<div class="main-content-wrapper">
    <div class="tulisan">
        <h2>Produk Kami</h2>
    </div>

    <div class="container">
        <div class="listProduct">
            <?php
            // Sertakan file koneksi database
            include '../configdb.php'; // Pastikan file ini berisi koneksi $conn

            // Pastikan koneksi berhasil sebelum query
            if (!isset($conn) || $conn->connect_error) {
                // Tampilkan pesan error user-friendly
                echo "<p style='color: red; text-align: center;'>Terjadi masalah dengan koneksi database saat memuat produk.</p>";
                // Log error lebih detail untuk debugging server
                error_log("Koneksi database gagal di keranjang.php (produk display): " . ($conn->connect_error ?? 'Objek koneksi tidak tersedia'));
            } else {
                // Query ambil semua produk yang stoknya lebih dari 0
                // Pastikan nama kolom di tabel 'produk' sesuai: idproduk, namaproduk, harga, gambar, stok
                $query = "SELECT idproduk, namaproduk, harga, gambar, stok FROM produk WHERE stok > 0 ORDER BY idproduk ASC";
                $result = $conn->query($query);

                if ($result && $result->num_rows > 0) {
                    while ($product = $result->fetch_assoc()) {
                        $productId = $product['idproduk'];
                        $productName = $product['namaproduk'];
                        $productPrice = $product['harga'];
                        $productImage = $product['gambar'];
                        $productStock = $product['stok'];

                        // Sesuaikan path detailPage jika ada
                        $detailPage = 'kandang.php?id=' . $productId; // Asumsi ada detail_produk.php

                        // Siapkan data produk dalam format JSON untuk JavaScript
                        // Menggunakan 'idproduk' agar konsisten dengan PHP backend dan JavaScript frontend
                        $productData = json_encode([
                            'idproduk' => $productId,
                            'namaproduk' => $productName,
                            'harga' => (float)$productPrice, // Pastikan harga adalah float
                            'gambar' => "../Admin-HTML/images/" . $productImage, // Pastikan path gambar benar
                            'stok' => (int)$productStock
                        ]);
            ?>
                        <div class="item">
                            <img src="../Admin-HTML/images/<?= htmlspecialchars($productImage); ?>" width="250px" height="150px" alt="<?= htmlspecialchars($productName); ?>">
                            <h2><?= htmlspecialchars($productName); ?></h2>
                            <div class="harga">Rp.<?= number_format($productPrice, 0, ',', '.'); ?></div>
                            <div class="stok">Stok: <?= htmlspecialchars($productStock); ?></div>
                            <a href="<?= $detailPage ?>">
                                <button class="detail-product-btn">Detail Produk</button>
                            </a>
                            <button class="add-to-cart-btn" data-product-data='<?= htmlspecialchars($productData, ENT_QUOTES, 'UTF-8'); ?>'>Masukan Keranjang</button>
                        </div>
            <?php
                    }
                } else {
                    echo "<p>Tidak ada produk yang tersedia saat ini.</p>";
                }
            }
            // Tutup koneksi database setelah semua query selesai
            if (isset($conn) && $conn instanceof mysqli) {
                $conn->close();
            }
            ?>
        </div>
    </div>

    <div class="cartTab">
        <h1>Keranjang Saya</h1>
        <div class="ListCart">
            <p id="loadingCartMessage" style="text-align: center; color: gray;">Memuat keranjang...</p>
        </div>

        <p id="emptyCartMessage" style="display: none; text-align: center;">Keranjang Anda kosong.</p>

        <div class="btn">
            <button class="close">Tutup</button>
            <button class="checkOut">Check Out</button>
        </div>
        <div class="total-price-cart">
            <span>Total Harga:</span>
            <span id="totalPriceDisplay">Rp.0</span>
        </div>
    </div>
</div>
<?php
include '../views/footer.php';
?>
<script src="../Admin-HTML/js/keranjang.js"></script>
<script src="../Admin-HTML/js/script.js"></script>
