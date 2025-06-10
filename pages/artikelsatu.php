<?php
include '../views/header.php';
include '../configdb.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/artikeldua.css">
<link rel="stylesheet" href="../Admin-HTML/css/artikeltiga.css">

<div class="main-article-wrapper">
<?php
$article_id = 1;

$article = null;
$stmt_article = $conn->prepare("SELECT id_artikel, judul, penulis, tanggal, hak_cipta, konten FROM artikel WHERE id_artikel = ?");
$stmt_article->bind_param("i", $article_id);
$stmt_article->execute();
$result_article = $stmt_article->get_result();

if ($result_article->num_rows > 0) {
    $article = $result_article->fetch_assoc();
} else {
    echo "<div style='text-align: center; padding: 50px;'><h1>Artikel dengan ID " . htmlspecialchars($article_id) . " tidak ditemukan.</h1><p>Pastikan ada artikel dengan ID 1 di database Anda.</p></div>";
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

    </div>

</div>

<?php
$conn->close();
include '../partials/footer.php';
?>