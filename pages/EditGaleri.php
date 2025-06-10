<?php
// editgaleri.php

require_once '../configdb.php'; // Menggunakan koneksi MySQLi Anda

$message = '';
$message_type = '';
$galeri = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT id_galeri, gambar, keterangan FROM galeri WHERE id_galeri = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $galeri = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$galeri) {
            $message = "Galeri tidak ditemukan.";
            $message_type = 'error';
        }
    } else {
        $message = "Error saat menyiapkan query: " . mysqli_error($conn);
        $message_type = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PERBAIKAN: Mengambil ID dari name="id_galeri" (hidden input)
    $id = $_POST['id_galeri'] ?? ''; 
    
    // PERBAIKAN KRITIS: Mengambil data dari name="deskripsi" (textarea)
    $keterangan = $_POST['deskripsi'] ?? ''; // <--- SEKARANG MENGAMBIL DARI name="deskripsi"
    
    $gambar_lama = $_POST['gambar_lama'] ?? '';
    $gambar_baru = $_FILES['gambar'] ?? null;

    $fileNameToSave = $gambar_lama;

    if ($gambar_baru && $gambar_baru['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../photos/';
        $newFileName = uniqid() . '_' . basename($gambar_baru['name']);
        $targetFilePath = $uploadDir . $newFileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $message = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $message_type = 'error';
        } elseif ($gambar_baru['size'] > 5000000) {
            $message = "Maaf, ukuran file Anda terlalu besar (max 5MB).";
            $message_type = 'error';
        } else {
            if (move_uploaded_file($gambar_baru['tmp_name'], $targetFilePath)) {
                if ($gambar_lama && file_exists($uploadDir . $gambar_lama) && $gambar_lama != 'default_image.png') {
                    unlink($uploadDir . $gambar_lama);
                }
                $fileNameToSave = $newFileName;
            } else {
                $message = "Maaf, ada kesalahan saat mengunggah file baru.";
                $message_type = 'error';
            }
        }
    }

    if ($message_type !== 'error') {
        // Kolom database adalah 'keterangan'
        $query = "UPDATE galeri SET gambar = ?, keterangan = ? WHERE id_galeri = ?"; 
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // PERBAIKAN: Mengikat variabel $keterangan yang baru
            mysqli_stmt_bind_param($stmt, "ssi", $fileNameToSave, $keterangan, $id); 
            if (mysqli_stmt_execute($stmt)) {
                $message = "Galeri berhasil diperbarui!";
                $message_type = 'success';
                mysqli_stmt_close($stmt);
                header('Location: galeri.php?status=success&msg=' . urlencode($message));
                exit();
            } else {
                $message = "Error saat memperbarui galeri di database: " . mysqli_stmt_error($stmt);
                $message_type = 'error';
            }
        } else {
            $message = "Error saat menyiapkan query: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}
include '../views/headeradmin.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/AdminFormGaleri.css">
<link rel="stylesheet" href="../Admin-HTML/css/editgaleri.css">

<main>
    <div class="head-title">
        <div class="left">
            <h1>Edit Galeri</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Edit</a></li>
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
                <h3>Form Edit Galeri</h3>
            </div>
            <?php if ($message): ?>
                <p class="form-message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($galeri): ?>
                <form action="editgaleri.php" method="post" enctype="multipart/form-data" class="galeri-form">
                    <input type="hidden" name="id_galeri" value="<?php echo htmlspecialchars($galeri['id_galeri']); ?>">
                    <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($galeri['gambar']); ?>">

                    <div class="form-row">
                        <div class="form-label">
                            <label for="deskripsi">Deskripsi:</label>
                        </div>
                        <div class="form-input">
                            <textarea name="deskripsi" id="deskripsi" rows="5"><?php echo htmlspecialchars($galeri['keterangan']); ?></textarea> 
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-label">
                            <label for="gambar">Gambar Baru :</label>
                        </div>
                        <div class="form-input">
                            <input type="file" name="gambar" id="gambar" accept="image/*">
                            <p class="gambar-saat-ini-text">Gambar saat ini:</p>
                            <div class="current-image-preview">
                                <img src="../photos/<?php echo htmlspecialchars($galeri['gambar']); ?>" alt="Gambar Galeri">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions-bottom">
                        <button type="submit" class="btn-submit">Simpan</button>
                        <a href="galeri.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            <?php elseif (!$message): ?>
                <p class="form-message error">Silakan pilih galeri untuk diedit dari halaman <a href="galeri.php">Galeri Foto</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
</section>

<script src="js/editgaleri.js"></script>

<?php
include '../views/footeradmin.php';
?>