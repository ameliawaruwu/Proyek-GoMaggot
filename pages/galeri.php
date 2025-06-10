<?php

include '../views/headeradmin.php';
include '../configdb.php';

$galleries = [];
$message = '';
$message_type = '';

if (isset($_GET['status']) && isset($_GET['msg'])) {
    $message_type = htmlspecialchars($_GET['status']);
    $message = htmlspecialchars($_GET['msg']);
}
// Tambahkan juga penanganan status dari deletegaleri.php
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'deleted_success') {
        $message = "Galeri berhasil dihapus!";
        $message_type = 'success';
    } elseif ($_GET['status'] == 'deleted_error') {
        $message = "Gagal menghapus galeri.";
        if (isset($_GET['error'])) {
            $message .= " Error: " . htmlspecialchars($_GET['error']);
        }
        $message_type = 'error';
    }
}


if ($conn) {
    $query = "SELECT id_galeri, gambar, keterangan FROM galeri ORDER BY id_galeri ASC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $galleries[] = $row;
        }
        mysqli_free_result($result);
    } else {
        $message = "Error saat mengambil data galeri: " . mysqli_error($conn);
        $message_type = 'error';
    }
} else {
    $message = "Koneksi database gagal. Silakan hubungi administrator.";
    $message_type = 'error';
}
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/AdminGaleri.css">
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Galeri Foto</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Galeri</a></li>
            </ul>
        </div>
        <a href="addgaleri.php" class="btn-download">
            <i class='bx bxs-plus-circle'></i>
            <span class="text">Tambah Galeri Baru</span>
        </a>
    </div>

    <?php if ($message): ?>
        <p class="form-message <?php echo $message_type; ?>"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="table-data">
        <div class="galeri-management-container">
            <div class="head galeri-management-header">
                <h3>Daftar Galeri</h3>
                <div class="search-box">
                    <input type="text" placeholder="Cari galeri..." class="search-input">
                    <button type="button" class="search-button"><i class='bx bx-search'></i></button>
                </div>
            </div>
            <?php if (empty($galleries) && $message_type != 'error'): ?>
                <p class="no-data">Tidak ada galeri yang ditemukan.</p>
            <?php elseif ($message_type == 'error'): ?>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($galleries as $galeri): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($galeri['id_galeri']); ?></td>
                                <td data-label="Gambar" class="gallery-image-cell">
                                    <img src="../photos/<?php echo htmlspecialchars($galeri['gambar']); ?>" alt="Gambar Galeri" class="product-image">
                                </td>
                                <td data-label="Keterangan"><?php echo htmlspecialchars($galeri['keterangan']); ?></td>
                                <td data-label="Aksi" class="action-buttons">
                                    <a href="EditGaleri.php?id=<?php echo htmlspecialchars($galeri['id_galeri']); ?>" class="icon-button edit action-icon">
                                        <i class='bx bxs-edit-alt'></i>
                                    </a>
                                    <a href="DeleteGaleri.php?id=<?php echo htmlspecialchars($galeri['id_galeri']); ?>" class="icon-button delete action-icon">
                                        <i class='bx bxs-trash'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>
</section>

<script src="../Admin-HTML/js/galeri.js"></script>

<?php
include '../views/footeradmin.php';
?>