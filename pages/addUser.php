<?php
include '../views/headeradmin.php';
include '../configdb.php'; // Include file koneksi database

$message = ''; // Untuk menampilkan pesan sukses atau error

// Cek apakah form disubmit (metode POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Akan di-hash
    $role = $_POST['role'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];
    // $tanggal_daftar = $_POST['tanggal_daftar']; // Baris ini dihapus karena tidak ada di form lagi

    $foto_profil = ''; // Default kosong, akan diisi jika ada upload foto
    $uploadOk = 1; // Flag untuk status upload foto
    $targetDir = "../photos/"; // Direktori target untuk upload foto profil

    // Buat direktori target jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Pastikan izin yang sesuai untuk produksi
    }

    // Penanganan upload file foto_profil
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $fileExtension; // Buat nama file unik
        $targetFilePath = $targetDir . $newFileName;
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validasi gambar
        $check = getimagesize($_FILES["foto_profil"]["tmp_name"]);
        if($check === false) {
            $message = "File bukan gambar.";
            $uploadOk = 0;
        }

        // Batasi ukuran file (contoh: 500KB)
        if ($_FILES["foto_profil"]["size"] > 500000) {
            $message = "Ukuran file terlalu besar. Maksimal 500KB.";
            $uploadOk = 0;
        }

        // Izinkan hanya format gambar tertentu
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $message = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = 0;
        }

        // Jika semua validasi lolos, coba upload file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $targetFilePath)) {
                $foto_profil = $newFileName; // Simpan nama file ke variabel untuk database
            } else {
                $message = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
                $uploadOk = 0; // Set uploadOk menjadi 0 jika gagal upload
            }
        }
    }

    // Lanjutkan ke penyimpanan database hanya jika upload foto (jika ada) berhasil
    if ($uploadOk) {
        // Hashing password sebelum disimpan ke database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Query INSERT ke tabel 'pengguna'
        // PENTING: Pastikan urutan kolom sesuai dengan parameter bind_param
        // Kolom 'tanggal_daftar' dihapus dari sini
        $sql = "INSERT INTO pengguna (username, email, password, role, nomor_telepon, alamat, foto_profil) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // String format 'sssssss' (7 string) sesuai dengan 7 kolom di atas
        // Variabel '$tanggal_daftar' dihapus dari sini
        $stmt->bind_param("sssssss", $username, $email, $hashedPassword, $role, $nomor_telepon, $alamat, $foto_profil);

        // Eksekusi query
        if ($stmt->execute()) {
            echo "<script>alert('User berhasil ditambahkan!'); window.location.href = 'user.php';</script>";
        } else {
            $message = "Gagal menambahkan user: " . $stmt->error;
        }

        $stmt->close(); // Tutup statement
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
                <li><a class="active" href="#">Add User</a></li>
            </ul>
        </div>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Add New User</h3>
            </div>
            <?php if (!empty($message)) : ?>
                <div class="alert" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form action="addUser.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan alamat email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="konsumen">Konsumen</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nomor_telepon">Nomor Telepon</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" placeholder="Masukkan nomor telepon">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" placeholder="Masukkan alamat"></textarea>
                </div>
                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                    <small>Maks. 500KB (JPG, JPEG, PNG, GIF). Opsional.</small>
                </div>
                <div class="modal-footer">
                    <a href="user.php" class="btn btn-secondary close-btn">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
$conn->close(); // Tutup koneksi database
include '../views/footeradmin.php';
?>