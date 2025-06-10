<?php
// === HEADER AND CSS INCLUDES ===
include '../views/header.php';
include '../configdb.php'; // Koneksi ke database
?>

<link rel="stylesheet" href="../Admin-HTML/css/artikeldua.css">
<link rel="stylesheet" href="../Admin-HTML/css/artikeltiga.css">
<div class="main-article-wrapper">
<?php
// === MENGAMBIL ID ARTIKEL: KITA AKAN SECARA PAKSA MENAMPILKAN ARTIKEL DENGAN ID 2 ===
$article_id = 2; // Mengatur ID artikel menjadi 2 secara statis

// Mengambil data artikel dari tabel 'artikel'
$article = null;
// Hapus 'subtitle' dari SELECT jika Anda tidak punya kolom itu di database!
// Jika Anda punya kolom 'subtitle', pastikan itu ada di tabel Anda.
$stmt_article = $conn->prepare("SELECT id_artikel, judul, penulis, tanggal, hak_cipta, konten FROM artikel WHERE id_artikel = ?");
$stmt_article->bind_param("i", $article_id);
$stmt_article->execute();
$result_article = $stmt_article->get_result();

if ($result_article->num_rows > 0) {
    $article = $result_article->fetch_assoc();
} else {
    // Artikel dengan ID 2 tidak ditemukan di database
    echo "<div style='text-align: center; padding: 50px;'><h1>Artikel dengan ID " . htmlspecialchars($article_id) . " tidak ditemukan.</h1><p>Pastikan ada artikel dengan ID 2 di database Anda.</p><p>Error: " . $conn->error . "</p></div>";
    $stmt_article->close();
    $conn->close();
    include '../partials/footer.php';
    exit();
}
$stmt_article->close();

?>
    <div class="artikel-atas">
        <div class="artikel-isi">
            <h1><?php echo htmlspecialchars($article['judul']); ?></h1>
            <?php /*
            if (isset($article['subtitle']) && !empty($article['subtitle'])):
                <h2><?php echo htmlspecialchars($article['subtitle']); ?></h2>
            endif;
            */ ?>
            <p class="artikel-penulis">ditulis oleh <a href="#"><?php echo htmlspecialchars($article['penulis']); ?></a> pada <?php echo date("d F Y", strtotime($article['tanggal'])); ?></p>
        </div>

        <?php echo $article['konten']; ?>

        <?php if (!empty($article['hak_cipta'])): ?>
            <div class="artikel-kaki">
                <?php echo htmlspecialchars($article['hak_cipta']); ?>
            </div>
        <?php else: ?>
            <div class="artikel-kaki">
                Copyright &copy; <?php echo date("Y"); ?> GoMaggot
            </div>
        <?php endif; ?>

    </div> <?php // Closes artikel-atas ?>

</div> <?php // Closes main-article-wrapper ?>

<?php
// === CLOSE DATABASE CONNECTION ===
$conn->close();

// === FOOTER INCLUDE ===
include '../partials/footer.php';
?>