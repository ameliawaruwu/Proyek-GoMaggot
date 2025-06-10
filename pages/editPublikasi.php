<?php

include '../configdb.php';

$message = '';
$message_type = '';

// Proses Update Artikel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id_artikel = (int)($_POST['id_artikel'] ?? 0);
    $judul = $_POST['judul'] ?? '';
    $penulis = $_POST['penulis'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $konten = $_POST['konten'] ?? '';
    $hak_cipta = $_POST['hak_cipta'] ?? '';
    $selected_galleries = $_POST['selected_galleries'] ?? [];

    if ($id_artikel > 0) {
        $stmt = $conn->prepare("UPDATE artikel SET judul = ?, penulis = ?, tanggal = ?, konten = ?, hak_cipta = ? WHERE id_artikel = ?");
        $stmt->bind_param("sssssi", $judul, $penulis, $tanggal, $konten, $hak_cipta, $id_artikel);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM artikel_galeri WHERE id_artikel = ?");
        $stmt->bind_param("i", $id_artikel);
        $stmt->execute();
        $stmt->close();

        if (!empty($selected_galleries) && is_array($selected_galleries)) {
            $stmt = $conn->prepare("INSERT INTO artikel_galeri (id_artikel, id_galeri) VALUES (?, ?)");
            foreach ($selected_galleries as $id_galeri) {
                $id_galeri = (int)$id_galeri;
                $stmt->bind_param("ii", $id_artikel, $id_galeri);
                $stmt->execute();
            }
            $stmt->close();
        }

        // --- Perbaikan untuk redirect setelah submit ---
        header("Location: publikasi.php?message=Artikel berhasil diperbarui.&type=success");
        exit(); // Penting: Pastikan untuk keluar setelah redirect
        // --- Akhir perbaikan redirect ---

    } else {
        $message = "ID artikel tidak valid.";
        $message_type = "error";
    }
}

// Ambil Data Artikel
$id_artikel = (int)($_GET['id'] ?? $_POST['id_artikel'] ?? 0);
$artikel_data = null;
$available_galleries = [];
$selected_gallery_ids = [];

if ($id_artikel > 0) {
    $result = $conn->query("SELECT * FROM artikel WHERE id_artikel = $id_artikel");
    if ($result && $result->num_rows > 0) {
        $artikel_data = $result->fetch_assoc();
    }

    $result = $conn->query("SELECT * FROM galeri");
    while ($row = $result->fetch_assoc()) {
        $available_galleries[] = $row;
    }

    $result = $conn->query("SELECT id_galeri FROM artikel_galeri WHERE id_artikel = $id_artikel");
    while ($row = $result->fetch_assoc()) {
        $selected_gallery_ids[] = $row['id_galeri'];
    }
}

include '../views/headeradmin.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
<style>
    .gallery-selection {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .gallery-item {
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

    .gallery-item input[type="checkbox"]:checked + img {
        border: 2px solid #007bff;
    }

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
            <h1>Edit Artikel</h1>
            <ul class="breadcrumb">
                <li><a href="">Artikel</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Edit Artikel</a></li>
            </ul>
        </div>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Form Edit Artikel</h3>
            </div>

            <?php if ($message): ?>
                <p class="form-message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <?php if ($artikel_data): ?>
                <div class="form-container">
                    <form action="editPublikasi.php?id=<?php echo htmlspecialchars($id_artikel); ?>" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_artikel" value="<?php echo htmlspecialchars($artikel_data['id_artikel']); ?>">

                        <div class="form-group">
                            <label for="judul">Judul Artikel</label>
                            <input type="text" id="judul" name="judul" required value="<?php echo htmlspecialchars($artikel_data['judul']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="penulis">Penulis</label>
                            <input type="text" id="penulis" name="penulis" value="<?php echo htmlspecialchars($artikel_data['penulis']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal" required value="<?php echo htmlspecialchars($artikel_data['tanggal']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="hak_cipta">Hak Cipta</label>
                            <input type="text" id="hak_cipta" name="hak_cipta" value="<?php echo htmlspecialchars($artikel_data['hak_cipta']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="konten">Isi Artikel</label>
                            <textarea id="konten" name="konten" rows="10" required><?php echo htmlspecialchars($artikel_data['konten']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Galeri Artikel</label>
                            <div class="gallery-selection">
                                <?php foreach ($available_galleries as $galeri): ?>
                                    <div class="gallery-item">
                                        <label>
                                            <input type="checkbox" name="selected_galleries[]" value="<?= $galeri['id_galeri'] ?>"
                                                <?= in_array($galeri['id_galeri'], $selected_gallery_ids) ? 'checked' : '' ?> />
                                            <img src="../photos/<?= $galeri['gambar'] ?>" alt="Gambar <?= $galeri['id_galeri'] ?>" />
                                            <span>ID: <?= $galeri['id_galeri'] ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="form-text text-muted">Pilih satu atau lebih gambar untuk artikel.</small>
                        </div>

                        <button type="submit" class="btn-submit">Perbarui Artikel</button>
                        <a href="editPublikasi.php" class="btn-cancel">Batal</a>
                    </form>
                </div>
            <?php else: ?>
                <p class="form-message error">Data artikel tidak dapat dimuat atau ID tidak valid.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const galleryItems = document.querySelectorAll('.gallery-item');

        galleryItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            const label = item.querySelector('label');

            // Inisialisasi status 'selected'
            if (checkbox.checked) {
                item.classList.add('selected');
            }

            // Event listener pada label
            label.addEventListener('click', function (event) {
                // Mencegah double-toggle jika checkbox diklik langsung
                if (event.target === checkbox) {
                    return;
                }
                // Toggle kelas 'selected' pada item induk berdasarkan status checkbox
                item.classList.toggle('selected', checkbox.checked);
            });

            // Pastikan perubahan checkbox juga memperbarui kelas visual
            checkbox.addEventListener('change', function () {
                item.classList.toggle('selected', this.checked);
            });
        });
    });
</script>

<?php
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
include '../views/footeradmin.php';
?>