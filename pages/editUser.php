<?php
include '../views/headeradmin.php';
include '../configdb.php'; // Include file koneksi database

$userId = null;
$user = null;
$message = ''; // Untuk menampilkan pesan sukses atau error


// Cek apakah ID user ada di URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    // DEBUG: Pastikan ID yang diterima adalah integer yang valid
    if (!filter_var($userId, FILTER_VALIDATE_INT)) {
        echo "<script>alert('ID User tidak valid!'); window.location.href = 'user.php';</script>";
        exit();
    }
    error_log("ID User diterima: " . $userId);

    // Ambil data user dari database berdasarkan ID
    $sql = "SELECT id_pelanggan, username, email, role, nomor_telepon, alamat, foto_profil FROM pengguna WHERE id_pelanggan = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $userId); // 'i' karena id_pelanggan adalah integer
    $stmt->execute();


    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("User dengan ID " . $userId . " ditemukan.");
    } else {
        // DEBUG: Log jika user tidak ditemukan
        error_log("User dengan ID " . $userId . " TIDAK ditemukan di database.");
        echo "<script>alert('User tidak ditemukan!'); window.location.href = 'user.php';</script>";
        exit(); // Hentikan eksekusi jika user tidak ditemukan
    }
    $stmt->close();
} 


// Cek apakah form disubmit untuk update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateUser'])) {
    error_log("Form update user disubmit.");
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Jika diisi, akan diupdate
    $role = $_POST['role'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];

    $foto_profil = $user['foto_profil']; // Default ke nama file foto lama
    $uploadOk = 1; // Flag untuk status upload foto
    $targetDir = "../photos/"; // Direktori target untuk upload foto profil

    // Buat direktori target jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
        error_log("Direktori target " . $targetDir . " dibuat.");
    }

    // Penanganan upload foto baru
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
        error_log("File foto profil diunggah.");
        $fileExtension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $fileExtension;
        $targetFilePath = $targetDir . $newFileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validasi gambar
        $check = getimagesize($_FILES["foto_profil"]["tmp_name"]);
        if($check === false) {
            $message = "File bukan gambar.";
            $uploadOk = 0;
            error_log("Upload foto: File bukan gambar.");
        }

        // Batasi ukuran file
        if ($_FILES["foto_profil"]["size"] > 500000) { // Max 500KB
            $message = "Ukuran file terlalu besar. Maksimal 500KB.";
            $uploadOk = 0;
            error_log("Upload foto: Ukuran file terlalu besar.");
        }

        // Izinkan format file tertentu
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = 0;
            error_log("Upload foto: Format file tidak diizinkan.");
        }

        // Jika semua validasi lolos, coba upload file
        if ($uploadOk == 1) {
            // Hapus foto lama jika ada dan bukan placeholder default
            if (!empty($user['foto_profil']) && file_exists($targetDir . $user['foto_profil'])) {
                unlink($targetDir . $user['foto_profil']);
                error_log("Foto lama " . $user['foto_profil'] . " dihapus.");
            }
            if (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $targetFilePath)) {
                $foto_profil = $newFileName; // Update nama file foto
                error_log("Foto baru " . $newFileName . " berhasil diunggah.");
            } else {
                $message = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
                $uploadOk = 0;
                error_log("Upload foto: Gagal memindahkan file yang diunggah.");
            }
        }
    } else if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] != UPLOAD_ERR_NO_FILE) {
        // Menangani error upload selain 'no file'
        $phpFileUploadErrors = array(
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (php.ini).',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (FORM).',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian.',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori sementara tidak ada.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Ekstensi PHP menghentikan upload file.',
        );
        $message = "Kesalahan upload file: " . (isset($phpFileUploadErrors[$_FILES['foto_profil']['error']]) ? $phpFileUploadErrors[$_FILES['foto_profil']['error']] : 'Error tidak diketahui.');
        $uploadOk = 0;
        error_log("Kesalahan upload file: " . $_FILES['foto_profil']['error'] . " - " . $message);
    }

    // Lanjutkan ke penyimpanan database hanya jika upload foto (jika ada) berhasil
    if ($uploadOk) {
        // Bagian query UPDATE yang akan dibangun secara dinamis
        $updateFields = "username = ?, email = ?, role = ?, nomor_telepon = ?, alamat = ?, foto_profil = ?";
        $paramTypes = "ssssss"; // Tipe data untuk kolom yang selalu diupdate (6 string)
        $paramValues = [$username, $email, $role, $nomor_telepon, $alamat, $foto_profil];

        // Jika password diisi di form, hash dan tambahkan ke query update
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateFields .= ", password = ?"; // Tambahkan kolom password
            $paramTypes .= "s"; // Tambahkan tipe data string untuk password
            $paramValues[] = $hashedPassword; // Tambahkan nilai hashed password
        }

        // Bangun query UPDATE lengkap
        $sql = "UPDATE pengguna SET " . $updateFields . " WHERE id_pelanggan = ?";
        $paramTypes .= "i"; // Tambahkan tipe data integer untuk id_pelanggan
        $paramValues[] = $userId; // Tambahkan nilai id_pelanggan

        error_log("Query UPDATE disiapkan: " . $sql);
        error_log("Parameter types: " . $paramTypes);
        error_log("Parameter values: " . print_r($paramValues, true));

        $stmt = $conn->prepare($sql);
        // DEBUG: Cek apakah prepare statement berhasil
        if ($stmt === false) {
            error_log("Prepare statement gagal untuk UPDATE: " . $conn-a>error);
            $message = "Gagal memperbarui user: Terjadi kesalahan internal.";
        } else {
            // Penting: Gunakan operator splat (...) untuk meneruskan array paramValues sebagai argumen terpisah
            $stmt->bind_param($paramTypes, ...$paramValues);

            if ($stmt->execute()) {
                error_log("User dengan ID " . $userId . " berhasil diperbarui.");
                echo "<script>alert('User berhasil diperbarui!'); window.location.href = 'user.php';</script>";
            } else {
                $message = "Gagal memperbarui user: " . $stmt->error;
                error_log("Execute statement gagal saat UPDATE user: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminUser.css">
<main>
    <div class="head-title">
        <div class="left">
            <h1>User Management</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="user.php">User</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Edit User</a></li>
            </ul>
        </div>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Edit User</h3>
            </div>
            <?php if (!empty($message)) : ?>
                <div class="alert" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form action="editUser.php?id=<?php echo $userId; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="updateUser" value="1">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan alamat email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                    <small>Biarkan kosong jika tidak ingin mengubah password.</small>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="konsumen" <?php echo (($user['role'] ?? '') == 'konsumen') ? 'selected' : ''; ?>>Konsumen</option>
                        <option value="admin" <?php echo (($user['role'] ?? '') == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nomor_telepon">Nomor Telepon</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon" value="<?php echo htmlspecialchars($user['nomor_telepon'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" placeholder="Masukkan alamat"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>
                    <?php
                    // Pastikan $targetDir sudah terdefinisi.
                    // Ini penting agar tidak ada error undefined variable jika ada masalah di awal skrip.
                    if (!isset($targetDir)) {
                        $targetDir = "../photos/";
                    }
                    // Path foto profil saat ini, diambil dari folder '../photos/'
                    $currentPhotoPath = (!empty($user['foto_profil'] ?? '') && file_exists($targetDir . $user['foto_profil']))
                        ? $targetDir . htmlspecialchars($user['foto_profil'])
                        : "../Admin-HTML/img/no-avatar.png"; // Placeholder jika tidak ada foto
                    ?>
                    <img src="<?php echo $currentPhotoPath; ?>" alt="User Photo" width="70" style="vertical-align: middle; margin-right: 10px; border-radius: 50%; object-fit: cover;">
                    <br>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                    <small>Maks. 500KB (JPG, JPEG, PNG, GIF). Biarkan kosong jika tidak ingin mengubah foto.</small>
                </div>
                <div class="modal-footer">
                    <a href="user.php" class="btn btn-secondary close-btn">Batal</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
$conn->close(); // Tutup koneksi database
include '../views/footeradmin.php';
?>