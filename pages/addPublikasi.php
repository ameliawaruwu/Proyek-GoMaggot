<?php
// artikel/add_artikel.php - Form Tambah Artikel (Create)


include '../configdb.php';

$message = '';
$message_type = '';
$available_galleries = [];

if (!isset($conn) || $conn->connect_error) {
    die("Koneksi database gagal: " . (isset($conn) ? $conn->connect_error : "Variabel \$conn tidak didefinisikan."));
}

// === Proses Form Saat POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tanggal = $_POST['tanggal'];
    $konten = $_POST['konten'];
    $hak_cipta = $_POST['hak_cipta'];
    $selected_galleries = $_POST['selected_galleries'] ?? [];

    $stmt = $conn->prepare("INSERT INTO artikel (judul, penulis, tanggal, konten, hak_cipta) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssss", $judul, $penulis, $tanggal, $konten, $hak_cipta);
        if ($stmt->execute()) {
            $id_artikel = $stmt->insert_id;

            if (!empty($selected_galleries)) {
                $stmt_galeri = $conn->prepare("INSERT INTO artikel_galeri (id_artikel, id_galeri) VALUES (?, ?)");
                foreach ($selected_galleries as $galeri_id) {
                    $stmt_galeri->bind_param("ii", $id_artikel, $galeri_id);
                    $stmt_galeri->execute();
                }
                $stmt_galeri->close();
            }

            $stmt->close();
            $conn->close();
            header("Location: publikasi.php?status=sukses&msg=Artikel berhasil disimpan");
            exit;
        } else {
            $message = "Gagal menyimpan artikel: " . $stmt->error;
            $message_type = 'error';
        }
    } else {
        $message = "Query error: " . $conn->error;
        $message_type = 'error';
    }
}

// Ambil daftar galeri yang tersedia
$query_galleries = "SELECT id_galeri, gambar, keterangan FROM galeri ORDER BY id_galeri DESC";
$result_galleries = mysqli_query($conn, $query_galleries);

if ($result_galleries) {
    while ($row = mysqli_fetch_assoc($result_galleries)) {
        $available_galleries[] = $row;
    }
    mysqli_free_result($result_galleries);
} else {
    $message = "Error saat mengambil daftar galeri: " . mysqli_error($conn);
    $message_type = 'error';
}

// Isi ulang data form jika terjadi error
$old_judul = htmlspecialchars($_POST['judul'] ?? '');
$old_penulis = htmlspecialchars($_POST['penulis'] ?? '');
$old_tanggal = htmlspecialchars($_POST['tanggal'] ?? '');
$old_konten = htmlspecialchars($_POST['konten'] ?? '');
$old_hak_cipta = htmlspecialchars($_POST['hak_cipta'] ?? '');
$old_selected_galleries = isset($_POST['selected_galleries']) ? $_POST['selected_galleries'] : [];

include '../views/headeradmin.php';
?>


<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<style>
    /* CSS Tambahan untuk pemilihan gambar */
    .gallery-selection {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .gallery-item {
        display: inline-block;
        width: 140px;
        padding: 10px;
        border: 2px solid transparent;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
        border-radius: 6px;
    }

    .gallery-item input[type="checkbox"] {
        display: none;
    }

    .gallery-item img {
        width: 100%;
        height: auto;
        border-radius: 4px;
    }

    /* Saat dicentang, beri border biru */
    .gallery-item input[type="checkbox"]:checked + img {
        border: 2px solid #007bff;
    }

    /* Saat dicentang, beri latar belakang */
    .gallery-item input[type="checkbox"]:checked ~ span {
        font-weight: bold;
        color: #007bff;
    }

    .gallery-item.selected {
        border: 2px solid #007bff;
        background-color: #eaf4ff;
    }

</style>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Tambah Artikel Baru</h1>
            <ul class="breadcrumb">
                <li><a href="index.php">Artikel</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Tambah Baru</a></li>
            </ul>
        </div>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Form Artikel</h3>
            </div>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <p class="form-message error"><?php echo htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.'); ?></p>
            <?php endif; ?>

            <div class="form-container">
                <form action="addPublikasi.php" method="POST">
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="judul">Judul Artikel</label>
                        <input type="text" id="judul" name="judul" required value="<?php echo $old_judul; ?>">
                    </div>
                    <div class="form-group">
                        <label for="penulis">Penulis</label>
                        <input type="text" id="penulis" name="penulis" value="<?php echo $old_penulis; ?>">
                    </div>
                    <div class="form-group">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" required value="<?php echo $old_tanggal; ?>">
                    </div>
                    <div class="form-group">
                        <label for="hak_cipta">Hak Cipta</label>
                        <input type="text" id="hak_cipta" name="hak_cipta" value="<?php echo $old_hak_cipta; ?>">
                    </div>
                    <div class="form-group">
                        <label for="konten">Isi Artikel</label>
                        <textarea id="konten" name="konten" rows="10" required><?php echo $old_konten; ?></textarea>
                    </div>

                   <div class="form-group">
                        <label>Pilih Gambar untuk Artikel (Bisa Pilih Banyak):</label>
                        <div class="gallery-selection">
                            <?php if (empty($available_galleries)): ?>
                                <p>Tidak ada gambar di galeri. Silakan tambahkan gambar di bagian Galeri terlebih dahulu.</p>
                            <?php else: ?>
                                <?php foreach ($available_galleries as $gallery_item): ?>
                                    <label class="gallery-item">
                                        <input type="checkbox" name="selected_galleries[]" value="<?php echo htmlspecialchars($gallery_item['id_galeri']); ?>"
                                            <?php echo in_array($gallery_item['id_galeri'], $old_selected_galleries) ? 'checked' : ''; ?>>
                                        <img src="../photos/<?php echo htmlspecialchars($gallery_item['gambar']); ?>" alt="<?php echo htmlspecialchars($gallery_item['keterangan']); ?>">
                                        <span style="font-size:0.8em;"><?php echo htmlspecialchars(mb_strimwidth($gallery_item['keterangan'], 0, 15, "...")); ?> (ID:<?php echo htmlspecialchars($gallery_item['id_galeri']); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <small>Pilih satu atau lebih gambar yang akan ditampilkan di artikel ini.</small>
                    </div>
                    <div class="form-actions">
                    <a href="addPublikasi.php" class="btn-cancel">Batal</a>
                     <button type="submit" class="btn-submit">Simpan</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</main>
</section>

<script>
   document.addEventListener('DOMContentLoaded', function () {
        const galleryItems = document.querySelectorAll('.gallery-item');

        galleryItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');

            item.addEventListener('click', function (event) {
                // Cegah toggle ganda saat klik langsung checkbox
                if (event.target.tagName.toLowerCase() === 'input') {
                    return;
                }

                // Toggle checked state
                checkbox.checked = !checkbox.checked;

                // Tambahkan / hapus class 'selected' sesuai status checkbox
                this.classList.toggle('selected', checkbox.checked);
            });

            // Set class awal saat halaman dimuat
            if (checkbox.checked) {
                item.classList.add('selected');
            }
        });
    });

</script>

<?php
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
include '../views/footeradmin.php';
?>