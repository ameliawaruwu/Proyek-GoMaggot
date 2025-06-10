<?php
include '../Logic/update/auth.php';
include '../configdb.php'; // 
$message = '';
$message_type = ''; 
$galeri = null;
$id_galeri = null;


// Cek apakah form konfirmasi penghapusan sudah disubmit (via POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_galeri'])) {
    $id_galeri = (int)$_POST['id_galeri']; // Ambil ID dari POST dan sanitasi

    if ($id_galeri <= 0) {
        // ID tidak valid dari POST, redirect dengan error
        header("Location: galeri.php?msg=" . urlencode("ID Galeri untuk penghapusan tidak valid.") . "&type=error");
        exit(); // PENTING: Selalu exit() setelah header redirect
    }

    // 1. Ambil nama file gambar dari database sebelum menghapus record
    $query_get_image = "SELECT gambar FROM galeri WHERE id_galeri = ?";
    $stmt_get_image = mysqli_prepare($conn, $query_get_image);
    $gambar_untuk_dihapus = null;

    if ($stmt_get_image) {
        mysqli_stmt_bind_param($stmt_get_image, "i", $id_galeri);
        mysqli_stmt_execute($stmt_get_image);
        $result_get_image = mysqli_stmt_get_result($stmt_get_image);
        $row_image = mysqli_fetch_assoc($result_get_image);
        mysqli_stmt_close($stmt_get_image);

        if ($row_image) {
            $gambar_untuk_dihapus = $row_image['gambar'];
        }
    } else {
        // Jika ada masalah di query SELECT gambar, catat di log tapi tetap coba hapus DB
        error_log("Error preparing image select query for delete: " . mysqli_error($conn));
    }

    // 2. Hapus data galeri dari database
    $query_delete = "DELETE FROM galeri WHERE id_galeri = ?";
    $stmt_delete = mysqli_prepare($conn, $query_delete);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_galeri);
        if (mysqli_stmt_execute($stmt_delete)) {
            // 3. Jika penghapusan dari database berhasil, coba hapus file fisik
            if ($gambar_untuk_dihapus && !empty($gambar_untuk_dihapus)) {
                $gambar_path = '../photos/' . $gambar_untuk_dihapus; // Sesuaikan path folder 'photos'
                if (file_exists($gambar_path) && is_file($gambar_path)) {
                    if (unlink($gambar_path)) {
                        $message = "Galeri berhasil dihapus.";
                        $message_type = 'success';
                    } else {
                        $message = "Galeri berhasil dihapus, tetapi gagal menghapus file gambar fisik. Cek izin folder 'photos'.";
                        $message_type = 'warning';
                    }
                } else {
                    $message = "Galeri berhasil dihapus, tetapi file gambar tidak ditemukan di server.";
                    $message_type = 'warning';
                }
            } else {
                $message = "Galeri berhasil dihapus."; // Jika tidak ada gambar atau gambar kosong di DB
                $message_type = 'success';
            }
        } else {
            $message = "Error saat menghapus galeri dari database: " . mysqli_error($conn);
            $message_type = 'error';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        $message = "Error saat menyiapkan query DELETE: " . mysqli_error($conn);
        $message_type = 'error';
    }

    // Setelah proses POST selesai (baik berhasil atau gagal), redirect selalu
    header("Location: galeri.php?msg=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit(); // PENTING: Selalu exit() setelah header redirect

} else if (isset($_GET['id'])) { // Jika diakses dengan GET (untuk menampilkan konfirmasi)
    $id_galeri = (int)$_GET['id']; // Ambil ID dari GET dan sanitasi

    if ($id_galeri <= 0) {
        // ID tidak valid dari GET, redirect dengan error
        header("Location: galeri.php?msg=" . urlencode("ID Galeri untuk konfirmasi tidak valid.") . "&type=error");
        exit(); // PENTING: Selalu exit() setelah header redirect
    }

    // Dapatkan detail galeri untuk ditampilkan di halaman konfirmasi
    $query_select = "SELECT id_galeri, gambar, keterangan FROM galeri WHERE id_galeri = ?";
    $stmt_select = mysqli_prepare($conn, $query_select);

    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $id_galeri);
        mysqli_stmt_execute($stmt_select);
        $result_select = mysqli_stmt_get_result($stmt_select);
        $galeri = mysqli_fetch_assoc($result_select);
        mysqli_stmt_close($stmt_select);

        if (!$galeri) {
            // Galeri tidak ditemukan, redirect dengan error
            header("location:galeri.php?msg=" . urlencode("Galeri tidak ditemukan untuk dikonfirmasi.") . "&type=error");
            exit(); // PENTING: Selalu exit() setelah header redirect
        }
    } else {
        $message = "Error saat menyiapkan query SELECT detail galeri: " . mysqli_error($conn);
        $message_type = 'error';
        // Meskipun ada error, kita tetap akan menampilkan HTML untuk error ini
        // Jika Anda ingin redirect juga, tambahkan header() dan exit() di sini
    }
} else {
    // Jika tidak ada ID di GET maupun POST, redirect kembali ke halaman daftar galeri
    header("location:galeri.php?msg=" . urlencode("Akses tidak sah. ID Galeri tidak diberikan.") . "&type=error");
    exit(); // PENTING: Selalu exit() setelah header redirect
}

mysqli_close($conn); // Tutup koneksi database setelah semua operasi PHP selesai


include '../views/headeradmin.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/AdminDeleteGaleri.css">
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<main>
    <div class="head-title">
        <div class="left">
            <h1>Konfirmasi Hapus Galeri</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Konfirmasi Hapus</a></li>
            </ul>
        </div>
        <a href="galeri.php" class="btn-download">
             <i class='bx bx-arrow-back'></i>
             <span class="text">Kembali ke Daftar Galeri</span>
        </a>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Detail Galeri yang Akan Dihapus</h3>
            </div>
            <?php if ($message): // Menampilkan pesan dari proses PHP awal (misal: error select) ?>
                <p class="form-message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($galeri): // Tampilkan detail galeri jika berhasil diambil via GET ?>
                <div class="confirmation-box">
                    <h3>Anda yakin ingin menghapus galeri ini?</h3>
                    <div class="confirmation-details">
                        <img src="../photos/<?php echo htmlspecialchars($galeri['gambar']); ?>" alt="Gambar Galeri">
                        <div>
                            <p><strong>ID Galeri:</strong> <?php echo htmlspecialchars($galeri['id_galeri']); ?></p>
                            <p><strong>Keterangan:</strong> <?php echo htmlspecialchars($galeri['keterangan']); ?></p>
                        </div>
                    </div>
                    <div class="confirmation-actions">
                        <form action="DeleteGaleri.php" method="post" id="deleteGaleriForm" style="display:inline;">
                            <input type="hidden" name="id_galeri" value="<?php echo htmlspecialchars($galeri['id_galeri']); ?>">
                            <button type="submit" name="btnConfirm" class="btn-confirm-delete">
                                Hapus Permanen
                            </button>
                        </form>
                        <a href="galeri.php" class="btn-cancel">Batal</a>
                    </div>
                </div>
            <?php else: // Jika galeri tidak ditemukan atau ada error saat select, dan belum redirect ?>
                <p class="form-message error">Galeri tidak dapat ditampilkan untuk konfirmasi penghapusan. Mohon kembali ke halaman sebelumnya.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForm = document.getElementById('deleteGaleriForm');
        if (deleteForm) { // Pastikan form ada sebelum menambahkan event listener
            deleteForm.addEventListener('submit', function(e) {
                // Konfirmasi terakhir sebelum benar-benar submit form untuk penghapusan
                if (!confirm('Apakah Anda yakin ingin menghapus galeri ini secara permanen? Tindakan ini tidak dapat dibatalkan.')) {
                    e.preventDefault(); // Mencegah form disubmit jika user klik 'Cancel'
                }
            });
        }
    });
</script>

<?php
include '../views/footeradmin.php';
?>