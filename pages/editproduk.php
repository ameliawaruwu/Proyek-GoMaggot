<?php 

include '../configdb.php'; // Koneksi database

// Cek apakah ID produk tersedia
if (!isset($_GET['id'])) {
    header("location:produk.php");
    exit;
}

$id = $_GET['id'];

// Ambil data produk yang akan diedit
$sqlProduk = "SELECT * FROM produk WHERE idproduk = '$id'";
$queryProduk = mysqli_query($conn, $sqlProduk);

// Pastikan query berhasil
if (!$queryProduk) {
    die("Query gagal: " . mysqli_error($conn));
}

$produk = mysqli_fetch_assoc($queryProduk);

// Jika produk tidak ditemukan
if (!$produk) {
    header("location:produk.php");
    exit;
}

// Parse shelf life (masa simpan)
$masaSimpanParts = explode(' ', $produk['masapenyimpanan']);
$masaSimpanValue = $masaSimpanParts[0];
$masaSimpanUnit = isset($masaSimpanParts[1]) ? $masaSimpanParts[1] : 'hari';

// Parse weight (berat)
$berat = $produk['berat'];
$beratUnit = 'kg';
if ($berat < 1) {
    $berat = $berat * 1000; // Konversi kg ke gr
    $beratUnit = 'gr';
}

// Ambil data kategori untuk dropdown
$categoryQuery = "SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = mysqli_fetch_all($categoryResult, MYSQLI_ASSOC);

// Proses form jika disubmit
if (isset($_POST['btnSubmit'])) {
    $nama_produk = $_POST['productName'];
    $kategori = $_POST['productCategory'];
    $harga = $_POST['productPrice'];
    $stok = $_POST['productStock'];
    $unit_stok = $_POST['stockUnit'];
    $stokBerubah = (int) $_POST['productStockChange'];
    $merk = $_POST['productMerk'];
    $berat_produk = $_POST['productWeight'];
    $unit_berat = $_POST['weightUnit'];
    $masa_simpan = $_POST['productShelfLife'];
    $unit_simpan = $_POST['shelfLifeUnit'];
    $asal_pengiriman = $_POST['productShippingOrigin'];
    $deskripsi = $_POST['productDescription'];
    $gambar = $_POST['productImage'];
    
    // Format masa simpan
    $masa_penyimpanan = $masa_simpan . ' ' . $unit_simpan;

    // Gabungan stok dan satuannya
    $stok_final = $stok . ' ' . $stokBerubah . ''. $unit_stok;
    //Perubahan Stok
    $stoklama = (int) $produk['stok']; // Stok database
    $stok = $stoklama + $stokBerubah;
    
    // Konversi berat ke kilogram untuk database
    if ($unit_berat === 'gr') {
        $berat_produk = $berat_produk / 1000; // Konversi gram ke kilogram
    }
    
    // Handle gambar
    $gambar = $produk['gambar']; // default: gambar lama

    if (isset($_FILES['productImage']) && $_FILES['productImage']['name'] != '') {
        $fileFoto = $_FILES['productImage'];
        $uploadDir = '../photos/';
        $uploadFile = $uploadDir . basename($fileFoto['name']);
        
        if (move_uploaded_file($fileFoto['tmp_name'], $uploadFile)) {
            $gambar = $fileFoto['name']; // replace only if upload success
        } else {
            echo "Upload gambar gagal!";
        }
    }

    // Update data produk
    $sqlUpdate = "UPDATE produk SET 
                  namaproduk = '$nama_produk', 
                  kategori = '$kategori', 
                  harga = '$harga', 
                  stok = '$stok', 
                  merk = '$merk',
                  deskripsi_produk = '$deskripsi', 
                  masapenyimpanan = '$masa_penyimpanan', 
                  berat = '$berat_produk',
                  pengiriman = '$asal_pengiriman',
                  gambar = '$gambar'
                  WHERE idproduk = '$id'";
    
    $queryUpdate = mysqli_query($conn, $sqlUpdate);
    
    if ($queryUpdate) {
        // Redirect ke halaman produk dengan parameter status
        header("location:produk.php?status=updated");
        exit;
    } else {
        $error = "Update data produk gagal! " . mysqli_error($conn);
    }
}

// Tutup koneksi database jika tidak ada proses form
if (!isset($_POST['btnSubmit'])) {
    mysqli_close($conn);
}

include '../views/headeradmin.php';
?>


<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<div class="head-title">
    <div class="left">
        <h1>Edit Produk</h1>
        <ul class="breadcrumb">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a href="produk.php">Produk</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Edit Produk</a></li>
        </ul>
    </div>
    <a href="produk.php" class="btn-download">
        <i class='bx bx-arrow-back'></i>
        <span class="text">Kembali ke Daftar Produk</span>
    </a>
</div>

<div class="edit-form-container">
    <h2 class="form-title">Form Edit Produk</h2>
    
    <?php if (isset($error)): ?>
    <div class="error-message">
        <i class='bx bx-error-circle'></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" id="editProductForm">
        <table class="form-table">
            <tr>
                <td><label for="productName" class="required-field">Nama Produk</label></td>
                <td>:</td>
                <td>
                    <input type="text" id="productName" name="productName" class="form-input" value="<?= htmlspecialchars($produk['namaproduk']) ?>" required>
                </td>
            </tr>
            <tr>
                <td><label for="productCategory" class="required-field">Kategori</label></td>
                <td>:</td>
                <td>
                    <select id="productCategory" name="productCategory" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <?php 
                        foreach ($arrKategori as $kategori) {
                            $selected = ($kategori['kategori'] == $produk['kategori']) ? 'selected' : '';
                        ?>
                        <option value="<?= $kategori['kategori'] ?>" <?= $selected ?>><?= $kategori['kategori'] ?></option>
                        <?php } ?>
                        <option value="BSF" <?= ($produk['kategori'] == 'BSF') ? 'selected' : '' ?>>BSF</option>
                        <option value="Kompos" <?= ($produk['kategori'] == 'Kompos') ? 'selected' : '' ?>>Kompos</option>
                        <option value="Pupuk" <?= ($produk['kategori'] == 'Pupuk') ? 'selected' : '' ?>>Pupuk</option>
                        <option value="Lainnya" <?= ($produk['kategori'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="productPrice" class="required-field">Harga (Rp)</label></td>
                <td>:</td>
                <td>
                    <input type="number" id="productPrice" name="productPrice" class="form-input" min="0" value="<?= $produk['harga'] ?>" required>
                </td>
            </tr>
          <tr>
                <td><label for="productStockChange" class="required-field">Stok</label></td>
                <td>:</td>
                <td>
                    <input type="number" id="productStockChange" name="productStockChange" class="form-control" value="0" required>
                    <select id="stockUnitSelect" name="stockUnit" class="form-select" required>
                        <option value="kg" <?= ($produk['stockUnit'] ?? '') == 'kg' ? 'selected' : '' ?>>kg</option>
                        <option value="pcs" <?= ($produk['stockUnit'] ?? '') == 'pcs' ? 'selected' : '' ?>>pcs</option>
                    </select>
                </td>
            </tr>



            <tr>
                <td><label for="productMerk" class="required-field">Merk</label></td>
                <td>:</td>
                <td>
                    <input type="text" id="productMerk" name="productMerk" class="form-input" value="<?= isset($produk['merk']) ? htmlspecialchars($produk['merk']) : '' ?>" required>
                </td>
            </tr>

                <td><label for="productWeight" class="required-field">Berat Produk</label></td>
                <td>:</td>
                <td>
                    <div class="input-group">
                        <input type="number" id="productWeight" name="productWeight" class="form-input" step="0.01" min="0" value="<?= $berat ?>" required>
                        <select id="weightUnit" name="weightUnit" class="form-select">
                            <option value="gr" <?= ($beratUnit == 'gr') ? 'selected' : '' ?>>gr</option>
                            <option value="kg" <?= ($beratUnit == 'kg') ? 'selected' : '' ?>>kg</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="productShelfLife" class="required-field">Masa Simpan</label></td>
                <td>:</td>
                <td>
                    <div class="input-group">
                        <input type="number" id="productShelfLife" name="productShelfLife" class="form-input" min="1" value="<?= $masaSimpanValue ?>" required>
                        <select id="shelfLifeUnit" name="shelfLifeUnit" class="form-select">
                            <option value="hari" <?= ($masaSimpanUnit == 'hari') ? 'selected' : '' ?>>hari</option>
                            <option value="minggu" <?= ($masaSimpanUnit == 'minggu') ? 'selected' : '' ?>>minggu</option>
                            <option value="bulan" <?= ($masaSimpanUnit == 'bulan') ? 'selected' : '' ?>>bulan</option>
                            <option value="tahun" <?= ($masaSimpanUnit == 'tahun') ? 'selected' : '' ?>>tahun</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="productShippingOrigin" class="required-field">Asal Pengiriman</label></td>
                <td>:</td>
                <td>
                    <select id="productShippingOrigin" name="productShippingOrigin" class="form-select" required>
                        <option value="Jakarta" <?= (isset($produk['pengiriman']) && $produk['pengiriman'] == 'Jakarta') ? 'selected' : '' ?>>Jakarta</option>
                        <option value="Bandung" <?= (isset($produk['pengiriman']) && $produk['pengiriman'] == 'Bandung') ? 'selected' : '' ?>>Bandung</option>
                        <option value="Surabaya" <?= (isset($produk['pengiriman']) && $produk['pengiriman'] == 'Surabaya') ? 'selected' : '' ?>>Surabaya</option>
                        <option value="Lainnya" <?= (isset($produk['pengiriman']) && $produk['pengiriman'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </td>
            </tr>
     

            <tr>
                <td><label for="productDescription" class="required-field">Deskripsi Produk</label></td>
                <td>:</td>
                <td><textarea name="productDescription" id="productDescription" cols="30" rows="10" class="form-textarea"><?= htmlspecialchars($produk['deskripsi_produk']) ?></textarea></td>
            </tr>
            <tr>
                <td><label for="productImage">Gambar Produk</label></td>
                <td>:</td>
                <td>
                    <input type="file" id="productImage" name="productImage" accept="image/*">
                    <?php if (!empty($produk['gambar'])): ?>
                        <br><small>Gambar saat ini:</small><br>
                        <img src="../photos/<?= htmlspecialchars($produk['gambar']) ?>" alt="Gambar Produk" width="100">
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="form-actions">
                    <button type="button" id="btnCancel" class="btn btn-secondary">Batal</button>
                    <button type="submit" name="btnSubmit" class="btn btn-primary">Simpan</button>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
// Preview image before upload
document.getElementById('productImage').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.classList.add('active');
        }
        
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.classList.remove('active');
    }
});

// Format currency
document.getElementById('productPrice').addEventListener('blur', function() {
    const value = this.value;
    if (value) {
        // Format only when displaying, not for submission
        const formatted = new Intl.NumberFormat('id-ID').format(value);
        this.setAttribute('data-formatted', formatted);
    }
});

// Form validation
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    const productName = document.getElementById('productName').value;
    const productCategory = document.getElementById('productCategory').value;
    const productPrice = document.getElementById('productPrice').value;
    const productStock = document.getElementById('productStock').value;
    const productWeight = document.getElementById('productWeight').value;
    
    let isValid = true;
    let errorMessage = '';
    
    if (!productName.trim()) {
        errorMessage = 'Nama produk tidak boleh kosong';
        isValid = false;
    } else if (!productCategory) {
        errorMessage = 'Kategori harus dipilih';
        isValid = false;
    } else if (!productPrice || productPrice <= 0) {
        errorMessage = 'Harga harus lebih dari 0';
        isValid = false;
    } else if (!productStock || productStock < 0) {
        errorMessage = 'Stok tidak boleh negatif';
        isValid = false;
    } else if (!productWeight || productWeight <= 0) {
        errorMessage = 'Berat produk harus lebih dari 0';
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        
        // Create or update error message
        let errorDiv = document.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            
            const formTitle = document.querySelector('.form-title');
            formTitle.insertAdjacentElement('afterend', errorDiv);
        }
        
        errorDiv.innerHTML = `<i class='bx bx-error-circle'></i> ${errorMessage}`;
        
        // Scroll to error message
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Cancel button event
document.getElementById('btnCancel').addEventListener('click', function() {
    window.location.href = 'produk.php';
});

// Confirm before leaving with saved changes
window.addEventListener('afterunload', function(e) {
    // Check if form has saved changes
    const formChanged = document.getElementById('editProductForm').querySelector(':invalid, :focus');
    
   
});
</script>

<style>
.edit-form-container {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 30px;
}

.form-title {
    font-size: 24px;
    color: #36B37E;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.error-message {
    background-color: #fff0f0;
    color: #e74c3c;
    padding: 12px 15px;
    border-left: 4px solid #e74c3c;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 14px;
}

.form-table {
    width: 100%;
    border-collapse: collapse;
}

.form-table td {
    padding: 10px 5px;
    vertical-align: middle;
}

.form-table td:first-child {
    width: 140px;
    font-weight: 500;
    color: #333;
}

.form-table td:nth-child(2) {
    width: 15px;
    text-align: center;
}

.form-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-input:focus {
    border-color: #36B37E;
    outline: none;
    box-shadow: 0 0 0 2px rgba(54, 179, 126, 0.1);
}

.form-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: white;
    min-width: 120px;
}

.form-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
    font-size: 14px;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.input-group .form-input {
    width: auto;
    flex: 1;
    max-width: 150px;
}

.input-file {
    padding: 8px 0;
}

.form-helper {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
    display: block;
}

.current-image-container {
    margin-top: 15px;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 4px;
    display: inline-block;
}

.current-image {
    max-width: 150px;
    max-height: 150px;
    border-radius: 4px;
    border: 1px solid #ddd;
    display: block;
}

.current-image-label {
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
}

.preview-container {
    margin-top: 10px;
    display: none;
}

.preview-container.active {
    display: block;
}

.preview-image {
    max-width: 150px;
    max-height: 150px;
    border-radius: 4px;
    border: 1px solid #ddd;
    display: block;
}

.preview-label {
    font-weight: 500;
    margin-bottom: 8px;
    display: block;
    color: #36B37E;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #36B37E;
    color: white;
}

.btn-primary:hover {
    background-color: #2E9E6A;
    transform: translateY(-1px);
}

.btn-secondary {
    background-color: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background-color: #e3e3e3;
    transform: translateY(-1px);
}

.required-field::after {
    content: " *";
    color: #e74c3c;
}
</style>

<?php include '../views/footeradmin.php'; ?>