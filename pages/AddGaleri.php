<?php
// addgaleri.php
include '../configdb.php'; // Menggunakan koneksi MySQLi Anda

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deskripsi = $_POST['deskripsi'] ?? '';
    $gambar = $_FILES['gambar'] ?? null;

    if ($gambar && $gambar['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../photos/'; // Path ke folder photos
        // Pastikan direktori ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($gambar['name']); // Nama file unik
        $targetFilePath = $uploadDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $message = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $message_type = 'error';
        } elseif ($gambar['size'] > 5000000) { // Batas 5MB
            $message = "Maaf, ukuran file Anda terlalu besar (max 5MB).";
            $message_type = 'error';
        } else {
            // Pindahkan file yang diunggah
            if (move_uploaded_file($gambar['tmp_name'], $targetFilePath)) {
                // Perhatikan: di query Anda menggunakan 'keterangan' bukan 'deskripsi'.
                // Pastikan nama kolom di database Anda adalah 'keterangan' atau ganti sesuai jika 'deskripsi'.
                $query = "INSERT INTO galeri (gambar, keterangan) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $query);

                if ($stmt) {
                    // "ss" = dua string
                    mysqli_stmt_bind_param($stmt, "ss", $fileName, $deskripsi);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Galeri berhasil ditambahkan!";
                        $message_type = 'success';
                        mysqli_stmt_close($stmt);
                        // Redirect kembali ke halaman galeri setelah berhasil menambahkan
                        header('Location: galeri.php?status=success&msg=' . urlencode($message));
                        exit();
                    } else {
                        $message = "Error saat menyimpan data ke database: " . mysqli_stmt_error($stmt);
                        $message_type = 'error';
                    }
                } else {
                    $message = "Error saat menyiapkan query: " . mysqli_error($conn);
                    $message_type = 'error';
                }
            } else {
                $message = "Maaf, ada kesalahan saat mengunggah file Anda.";
                $message_type = 'error';
            }
        }
    } else {
        $message = "Silakan pilih gambar untuk diunggah.";
        $message_type = 'error';
    }
}
include '../views/headeradmin.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/AdminFormGaleri.css">

<main>
    <div class="head-title">
        <div class="left">
            <h1>Tambah Galeri Baru</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="galeri.php">Galeri</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Tambah</a></li>
            </ul>
        </div>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Form Tambah Galeri</h3>
            </div>
            <?php if ($message): ?>
                <p class="form-message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="addgaleri.php" method="post" enctype="multipart/form-data" class="galeri-form">
                <div class="form-group">
                    <label for="gambar">Pilih Gambar:</label>
                    <input type="file" name="gambar" id="gambar" accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea name="deskripsi" id="deskripsi" rows="5"></textarea>
                </div>

                <div class="form-actions">
                    <a href="galeri.php" class="btn-cancel">Batal</a>
                     <button type="submit" class="btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</main>
</section>

<script src="../Admin-HTML/js/AdminGaleri.js"></script>

<?php
include '../views/footeradmin.php';
?>