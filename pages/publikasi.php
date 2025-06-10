<?php
include '../logic/update/auth.php';
include '../views/headeradmin.php';
include '../configdb.php';

$articles = [];
$message = '';
$message_type = '';
$search_query = '';// Variabel baru untuk menyimpan keyword pencarian

// Handle untuk notifikasi status (dari add/edit/delete)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success_create') {
        $message = "Artikel berhasil ditambahkan!";
        $message_type = 'success';
    } elseif ($_GET['status'] == 'success_update') {
        $message = "Artikel berhasil diperbarui!";
        $message_type = 'success';
    } elseif ($_GET['status'] == 'success_delete') {
        $message = "Artikel berhasil dihapus!";
        $message_type = 'success';
    } elseif (strpos($_GET['status'], 'error') !== false) {
        $message = "Terjadi kesalahan.";
        $message_type = 'error';
        if (isset($_GET['msg'])) {
            $message .= " Error: " . htmlspecialchars($_GET['msg']);
        }
    }
}

if ($conn) {
    // Tangkap input pencarian jika ada
    if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
        $search_query = htmlspecialchars(trim($_GET['search_query']));
        // Menggunakan prepared statement untuk keamanan
        $search_param = "%" . $search_query . "%";
        $query = "SELECT id_artikel, judul, penulis, tanggal, konten, hak_cipta FROM artikel 
                  WHERE judul LIKE ? OR penulis LIKE ? OR konten LIKE ? OR hak_cipta LIKE ?
                  ORDER BY id_artikel ASC";
        
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $search_param, $search_param, $search_param, $search_param);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $message = "Error saat menyiapkan query pencarian: " . mysqli_error($conn);
            $message_type = 'error';
            $result = false; // Set result ke false agar tidak diproses
        }

    } else {
        // Query default jika tidak ada pencarian
        $query = "SELECT id_artikel, judul, penulis, tanggal, konten, hak_cipta FROM artikel ORDER BY id_artikel ASC";
        $result = mysqli_query($conn, $query);
    }

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $articles[] = $row;
        }
        mysqli_free_result($result);
    } else {
        // Hanya tambahkan error jika $result false dan belum ada message dari prepared statement
        if (empty($message)) { 
            $message = "Error saat mengambil data artikel: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
} else {
    $message = "Koneksi database gagal. Silakan hubungi administrator.";
    $message_type = 'error';
}
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminPublikasi.css">

<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Artikel</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Artikel</a></li>
            </ul>
        </div>
        <a href="addPublikasi.php" class="btn-download">
            <i class='bx bxs-plus-circle'></i>
            <span class="text">Add New Artikel</span>
        </a>
    </div>

    <?php if ($message): ?>
        <p class="form-message <?php echo $message_type; ?>"><?php echo $message; ?></p>
    <?php endif; ?>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Daftar Artikel</h3>
                <form action="" method="GET" class="search-form">
                    <div class="search-box">
                        <input type="text" name="search_query" placeholder="Cari artikel..." class="search-input" value="<?= htmlspecialchars($search_query) ?>">
                        <button type="submit" class="search-button"><i class='bx bx-search'></i></button>
                    </div>
                </form>
            </div>
            <?php if (empty($articles) && $message_type != 'error'): ?>
                <p class="no-data">Tidak ada artikel yang ditemukan.</p>
            <?php elseif ($message_type == 'error'): ?>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Tanggal</th>
                            <th>Konten</th>
                            <th>Hak Cipta</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($article['id_artikel']); ?></td>
                                <td data-label="Judul">
                                    <?php echo htmlspecialchars($article['judul']); ?>
                                </td>
                                <td data-label="Penulis"><?php echo htmlspecialchars($article['penulis']); ?></td>
                                <td data-label="Tanggal"><?php echo htmlspecialchars($article['tanggal']); ?></td>
                                <td data-label="Konten"><?php echo htmlspecialchars(mb_strimwidth($article['konten'], 0, 100, "...")); ?></td>
                                <td data-label="Hak Cipta"><?php echo htmlspecialchars($article['hak_cipta']); ?></td>
                                <td data-label="Aksi" class="action-buttons">
                                    <a href="editPublikasi.php?id=<?php echo htmlspecialchars($article['id_artikel']); ?>" class="icon-button edit action-icon">
                                        <i class='bx bxs-edit-alt'></i>
                                    </a>
                                    <a href="deletePublikasi.php?id=<?php echo htmlspecialchars($article['id_artikel']); ?>" class="icon-button delete action-icon" onclick="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');">
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

<?php
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
include '../views/footeradmin.php';
?>