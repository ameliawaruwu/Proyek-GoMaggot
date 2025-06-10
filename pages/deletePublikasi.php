<?php
// Pastikan tidak ada karakter, spasi, atau baris baru di sini
ob_start();

require_once '../configdb.php';
include '../views/headeradmin.php';

$message = '';
$message_type = '';
$artikel = null;
$id_artikel_to_process = null;

function sanitize_input_delete_artikel($conn, $data) {
    if (!$conn) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

// Bagian ini hanya dieksekusi jika form konfirmasi penghapusan disubmit (metode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_artikel'])) {
    if (!$conn) {
        $message = "Koneksi database tidak valid. Tidak dapat memproses penghapusan.";
        $message_type = 'error';
        ob_clean();
        header("Location: publikasi.php?status=" . urlencode($message_type) . "&msg=" . urlencode($message));
        exit();
    }

    $id_artikel_to_process = (int)sanitize_input_delete_artikel($conn, $_POST['id_artikel']);

    if ($id_artikel_to_process <= 0) {
        ob_clean();
        header("Location: publikasi.php?status=error&msg=" . urlencode("ID Artikel tidak valid."));
        exit();
    }

    // --- LANGKAH 1: Hapus entri terkait di tabel 'artikel_galeri' terlebih dahulu ---
    $query_delete_galeri = "DELETE FROM artikel_galeri WHERE id_artikel = ?";
    $stmt_delete_galeri = mysqli_prepare($conn, $query_delete_galeri);

    if ($stmt_delete_galeri) {
        mysqli_stmt_bind_param($stmt_delete_galeri, "i", $id_artikel_to_process);
        if (!mysqli_stmt_execute($stmt_delete_galeri)) {
            $message = "Error saat menghapus relasi galeri: " . mysqli_error($conn);
            $message_type = 'error';
            mysqli_stmt_close($stmt_delete_galeri);
            ob_clean();
            header("Location: publikasi.php?status=" . urlencode($message_type) . "&msg=" . urlencode($message));
            exit();
        }
        mysqli_stmt_close($stmt_delete_galeri);
    } else {
        $message = "Error menyiapkan query hapus relasi galeri: " . mysqli_error($conn);
        $message_type = 'error';
        ob_clean();
        header("Location: publikasi.php?status=" . urlencode($message_type) . "&msg=" . urlencode($message));
        exit();
    }
    // --- AKHIR LANGKAH 1 ---


    // --- LANGKAH 2: Hapus artikel dari tabel 'artikel' ---
    $query_delete = "DELETE FROM artikel WHERE id_artikel = ?";
    $stmt_delete = mysqli_prepare($conn, $query_delete); // Baris 37 yang error sebelumnya

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_artikel_to_process);
        if (mysqli_stmt_execute($stmt_delete)) {
            $message = "Artikel berhasil dihapus.";
            $message_type = 'success';
        } else {
            $message = "Error database saat menghapus artikel: " . mysqli_error($conn);
            $message_type = 'error';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        $message = "Error menyiapkan query hapus artikel: " . mysqli_error($conn);
        $message_type = 'error';
    }

    ob_clean();
    header("Location: publikasi.php?status=" . urlencode($message_type) . "&msg=" . urlencode($message));
    exit();

} else if (isset($_GET['id'])) {
    if (!$conn) {
        $message = "Koneksi database tidak valid. Tidak dapat menampilkan konfirmasi.";
        $message_type = 'error';
        ob_clean();
        header("Location: publikasi.php?status=" . urlencode($message_type) . "&msg=" . urlencode($message));
        exit();
    }

    $id_artikel_to_process = (int)sanitize_input_delete_artikel($conn, $_GET['id']);

    if ($id_artikel_to_process <= 0) {
        ob_clean();
        header("Location: publikasi.php?status=error&msg=" . urlencode("ID Artikel tidak valid untuk konfirmasi."));
        exit();
    }

    $query_select = "SELECT id_artikel, judul, penulis, tanggal, konten, hak_cipta FROM artikel WHERE id_artikel = ?";
    $stmt_select = mysqli_prepare($conn, $query_select);

    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $id_artikel_to_process);
        mysqli_stmt_execute($stmt_select);
        $result_select = mysqli_stmt_get_result($stmt_select);
        $artikel = mysqli_fetch_assoc($result_select);
        mysqli_stmt_close($stmt_select);

        if (!$artikel) {
            ob_clean();
            header("Location: publikasi.php?status=error&msg=" . urlencode("Artikel tidak ditemukan."));
            exit();
        }
    } else {
        $message = "Error menyiapkan query detail artikel: " . mysqli_error($conn);
        $message_type = 'error';
    }
} else {
    ob_clean();
    header("Location: publikasi.php?status=error&msg=" . urlencode("Akses tidak sah."));
    exit();
}

if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Konfirmasi Hapus Artikel</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="publikasi.php">Artikel</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Konfirmasi Hapus</a></li>
            </ul>
        </div>
        <a href="publikasi.php" class="btn-download">
            <i class='bx bx-arrow-back'></i>
            <span class="text">Kembali ke Daftar Artikel</span>
        </a>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Artikel yang Akan Dihapus</h3>
            </div>
            <?php if ($message): ?>
                <p class="form-message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($artikel): ?>
                <div class="confirmation-box">
                    <h3>Anda yakin ingin menghapus artikel ini?</h3>
                    <div class="confirmation-details">
                        <div>
                            <p><strong>ID Artikel:</strong> <?php echo htmlspecialchars($artikel['id_artikel']); ?></p>
                            <p><strong>Judul:</strong> <?php echo htmlspecialchars($artikel['judul']); ?></p>
                            <p><strong>Penulis:</strong> <?php echo htmlspecialchars($artikel['penulis']); ?></p>
                            <p><strong>Tanggal:</strong> <?php echo htmlspecialchars($artikel['tanggal']); ?></p>
                            <p><strong>Konten (Preview):</strong> <?php echo htmlspecialchars(mb_strimwidth($artikel['konten'], 0, 150, "...")); ?></p>
                            <p><strong>Hak Cipta:</strong> <?php echo htmlspecialchars($artikel['hak_cipta']); ?></p>
                        </div>
                    </div>
                    <div class="confirmation-actions">
                        <form action="deletePublikasi.php" method="post" id="deleteArtikelForm" style="display:inline;">
                            <input type="hidden" name="id_artikel" value="<?php echo htmlspecialchars($artikel['id_artikel']); ?>">
                            <button type="submit" name="btnConfirm" class="btn-confirm-delete">
                                Hapus
                            </button>
                        </form>
                        <a href="publikasi.php" class="btn-cancel">Batal</a>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!$message): ?>
                    <p class="form-message error">Artikel tidak dapat ditampilkan untuk konfirmasi penghapusan. Mohon kembali ke halaman sebelumnya.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForm = document.getElementById('deleteArtikelForm');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus artikel ini secara permanen? Tindakan ini tidak dapat dibatalkan.')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>

<?php
ob_end_flush();
include '../views/footeradmin.php';
?>