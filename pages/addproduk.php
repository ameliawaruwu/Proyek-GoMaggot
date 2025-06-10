<?php 

include '../configdb.php';

// Ambil data kategori untuk dropdown
$sqlKategori = "SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL";
$queryKategori = mysqli_query($conn, $sqlKategori);
$arrKategori = mysqli_fetch_all($queryKategori, MYSQLI_ASSOC);



// Inisialisasi variabel untuk menampung nilai form jika terjadi error
$form_data = [
    'productName' => '',
    'productCategory' => '',
    'productPrice' => '',
    'productStock' => '',
    'stockUnit' => '',
    'productMerk' => 'GoMaggot',
    'productWeight' => '0',
    'weightUnit' => 'gr',
    'productShelfLife' => '1',
    'shelfLifeUnit' => 'hari',
    'productShippingOrigin' => 'Bandung',
    'productDescription' => ''
];

// Proses form jika disubmit
if (isset($_POST['btnSubmit'])) {
    // Ambil data dari form
    $nama_produk = $_POST['productName'];
    $kategori = $_POST['productCategory'];
    $harga = $_POST['productPrice'];
    $stok = $_POST['productStock'];
    $unit_stok = $_POST['stockUnit']; // Menangkap satuan stok (kg atau pcs)
    $Merk = $_POST['productMerk'];
    $berat_produk = $_POST['productWeight'];
    $unit_berat = $_POST['weightUnit'];
    $masa_simpan = $_POST['productShelfLife'];
    $asal_pengiriman = $_POST['productShippingOrigin'];
    $unit_simpan = $_POST['shelfLifeUnit'];

    $deskripsi = $_POST['productDescription'];
    
    // Simpan nilai form untuk ditampilkan kembali jika terjadi error
    $form_data = [
        'productName' => $nama_produk,
        'productCategory' => $kategori,
        'productPrice' => $harga,
        'productStock' => $stok,
        'stockUnit' => $unit_stok,
        'productMerk' => $Merk,
        'productWeight' => $berat_produk,
        'weightUnit' => $unit_berat,
        'productShelfLife' => $masa_simpan,
        'shelfLifeUnit' => $unit_simpan,
        'productShippingOrigin' => $asal_pengiriman,
        'productDescription' => $deskripsi
       
    ];
    
    // Format masa simpan
    $masa_penyimpanan = $masa_simpan . ' ' . $unit_simpan;
    
    // Stok
    $stok_total = $stok . ' ' . $unit_stok; // Menggabungkan stok dan satuan

    // Konversi berat ke kilogram untuk database
    if ($unit_berat === 'gr') {
        $berat_produk = $berat_produk / 1000; // Konversi gram ke kilogram
    }
    
    // Handle gambar
    $gambar = '';
    
    if (isset($_FILES['productImage']) && $_FILES['productImage']['name'] != '') {
        $fileFoto = $_FILES['productImage'];
        $uploadfile = '../photos/' . basename($fileFoto['name']);
        
        if (move_uploaded_file($fileFoto['tmp_name'], $uploadfile)) {
            $gambar = $fileFoto['name'];
        } else {
            $error = "Upload gambar gagal! Periksa file dan folder penyimpanan.";
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (!isset($error)) {
        // Buat query untuk menyimpan data
        $sqlInsert = "INSERT INTO produk (namaproduk, kategori, harga, stok, deskripsi_produk, masapenyimpanan, berat, pengiriman, gambar) 
        VALUES ('$nama_produk', '$kategori', '$harga', '$stok_total', '$deskripsi', '$masa_penyimpanan', '$berat_produk', '$asal_pengiriman', '$gambar')";

        $queryInsert = mysqli_query($conn, $sqlInsert);
        
        if (mysqli_affected_rows($conn) != 0) {
            // Redirect ke halaman produk dengan status sukses
            header("location:produk.php?status=added");
            exit;
        } else {
            $error = "Penambahan data produk gagal! " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
include '../views/headeradmin.php';
?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<div class="head-title">
    <div class="left">
        <h1>Tambah Produk Baru</h1>
        <ul class="breadcrumb">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a href="produk.php">Produk</a></li>
            <li><i class='bx bx-chevron-right'></i></li>
            <li><a class="active" href="#">Tambah Produk</a></li>
        </ul>
    </div>
    <a href="produk.php" class="btn-download">
        <i class='bx bx-arrow-back'></i>
        <span class="text">Kembali ke Daftar Produk</span>
    </a>
</div>

<!-- Toast notification for success message -->
<div id="toast-notification" class="toast-notification">
    <i id="toast-icon" class='bx bx-check-circle toast-icon'></i>
    <span id="toast-message">Pesan notifikasi</span>
    <span class="toast-close" onclick="closeToast()">&times;</span>
</div>

<div class="edit-form-container">
    <h2 class="form-title">Form Tambah Produk</h2>
    
    <?php if (isset($error)): ?>
    <div class="error-message">
        <i class='bx bx-error-circle'></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" id="addProductForm">
        <table class="form-table">
            <tr>
                <td><label for="productName" class="required-field">Nama Produk</label></td>
                <td>:</td>
                <td>
                    <input type="text" id="productName" name="productName" class="form-input" value="<?= htmlspecialchars($form_data['productName']) ?>" required>
                </td>
            </tr>
            <tr>
                <td><label for="productCategory" class="required-field">Kategori</label></td>
                <td>:</td>
                <td>
                    <select id="productCategory" name="productCategory" class="form-select" required>
                     <option value="">Pilih Kategori</option>
                    <?php 
                    // Daftar kategori tambahan
                    $additionalCategories = ['BSF', 'Kompos', 'Pupuk', 'Lainnya'];

                    // Gabungkan kategori tambahan dan yang sudah ada, pastikan tidak duplikat
                    $uniqueCategories = array_unique(array_merge(array_column($arrKategori, 'kategori'), $additionalCategories));

                    // Menampilkan kategori tanpa duplikasi
                    foreach ($uniqueCategories as $category) {
                    $selected = ($category == $form_data['productCategory']) ? 'selected' : '';
                    echo "<option value='{$category}' {$selected}>{$category}</option>";
                     }
                    ?>
                    </select>
                </td>
            </tr>

            <tr>
                <td><label for="productPrice" class="required-field">Harga (Rp)</label></td>
                <td>:</td>
                <td>
                    <input type="number" id="productPrice" name="productPrice" class="form-input" min="0" value="<?= $form_data['productPrice'] ?>" required>
                </td>
            </tr>
            <tr>
                <td><label for="productStock" class="required-field">Stok Produk</label></td>
                 <td>:</td>
                 <td>
                    <input type="number" id="productStock" name="productStock" class="form-control" value="<?= $form_data['productStock'] ?>" required>
                        <select id="stockUnit" name="stockUnit" class="form-select" required>
                        <option value="kg" <?= ($form_data['stockUnit'] == 'kg') ? 'selected' : '' ?>>kg</option>
                        <option value="pcs" <?= ($form_data['stockUnit'] == 'pcs') ? 'selected' : '' ?>>pcs</option>
                        </select>
                </td>
            </tr>

            <tr>
                <td><label for="productMerk" class="required-field">Merk</label></td>
                 <td>:</td>
                 <td>
                    <input type="text" id="productMerk" name="productMerk" class="form-input" value="<?= htmlspecialchars($form_data['productMerk']) ?>" required>
                </td>
            </tr>
            <tr>
                <td><label for="productWeight" class="required-field">Berat Produk</label></td>
                <td>:</td>
                <td>
                    <div class="input-group">
                        <input type="number" id="productWeight" name="productWeight" class="form-input" step="0.01" min="0" value="<?= $form_data['productWeight'] ?>" required>
                        <select id="weightUnit" name="weightUnit" class="form-select">
                            <option value="gr" <?= ($form_data['weightUnit'] == 'gr') ? 'selected' : '' ?>>gr</option>
                            <option value="kg" <?= ($form_data['weightUnit'] == 'kg') ? 'selected' : '' ?>>kg</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td><label for="productShelfLife" class="required-field">Masa Simpan</label></td>
                <td>:</td>
                <td>
                    <div class="input-group">
                        <input type="number" id="productShelfLife" name="productShelfLife" class="form-input" min="1" value="<?= $form_data['productShelfLife'] ?>" required>
                        <select id="shelfLifeUnit" name="shelfLifeUnit" class="form-select">
                            <option value="hari" <?= ($form_data['shelfLifeUnit'] == 'hari') ? 'selected' : '' ?>>hari</option>
                            <option value="minggu" <?= ($form_data['shelfLifeUnit'] == 'minggu') ? 'selected' : '' ?>>minggu</option>
                            <option value="bulan" <?= ($form_data['shelfLifeUnit'] == 'bulan') ? 'selected' : '' ?>>bulan</option>
                            <option value="tahun" <?= ($form_data['shelfLifeUnit'] == 'tahun') ? 'selected' : '' ?>>tahun</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                 <td><label for="productShippingOrigin" class="required-field">Asal Pengiriman</label></td>
                 <td>:</td>
                <td>
                     <select id="productShippingOrigin" name="productShippingOrigin" class="form-select" required>
                     <option value="">Pilih Asal Pengiriman</option>
                     <option value="Jakarta" <?= ($form_data['productShippingOrigin'] == 'Jakarta') ? 'selected' : '' ?>>Jakarta</option>
                     <option value="Bandung" <?= ($form_data['productShippingOrigin'] == 'Bandung') ? 'selected' : '' ?>>Bandung</option>
                     <option value="Surabaya" <?= ($form_data['productShippingOrigin'] == 'Surabaya') ? 'selected' : '' ?>>Surabaya</option>
                     <option value="Lainnya" <?= ($form_data['productShippingOrigin'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td valign="top"><label for="productDescription">Deskripsi</label></td>
                <td valign="top">:</td>
                <td>
                    <textarea id="productDescription" name="productDescription" class="form-textarea"><?= htmlspecialchars($form_data['productDescription']) ?></textarea>
                </td>
            </tr>
            <tr>
                <td valign="top"><label for="productImage">Gambar Produk</label></td>
                <td valign="top">:</td>
                <td>
                    <input type="file" id="productImage" name="productImage" class="input-file" accept="image/*">
                    <span class="form-helper">Upload gambar produk (jpg, png, atau gif)</span>
                    
                    <div id="imagePreview" class="preview-container">
                        <span class="preview-label">Preview Gambar:</span>
                        <img id="previewImage" class="preview-image">
                    </div>
                </td>
            </tr>
        </table>
        
        <div class="form-actions">
            <button type="button" id="btnCancel" class="btn btn-secondary">
                <i class='bx bx-x'></i> Batal
            </button>
            <button type="submit" name="btnSubmit" class="btn btn-primary">
                <i class='bx bx-save'></i> Simpan Produk
            </button>
        </div>
    </form>
</div>

<script>
// Function to show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-notification');
    const toastMessage = document.getElementById('toast-message');
    const toastIcon = document.getElementById('toast-icon');
    
    // Set message
    toastMessage.textContent = message;
    
    // Set toast type
    toast.className = 'toast-notification';
    if (type === 'success') {
        toast.classList.add('toast-success');
        toastIcon.className = 'bx bx-check-circle toast-icon';
    } else if (type === 'error') {
        toast.classList.add('toast-error');
        toastIcon.className = 'bx bx-error-circle toast-icon';
    }
    
    // Show toast
    toast.style.display = 'flex';
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        closeToast();
    }, 5000);
}

// Function to close toast
function closeToast() {
    document.getElementById('toast-notification').style.display = 'none';
}

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
document.getElementById('addProductForm').addEventListener('submit', function(e) {
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
window.addEventListener('aftereunload', function(e) {
    // Check if form has saved changes
    const form = document.getElementById('addProductForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    let formChanged = false;
    
    inputs.forEach(input => {
        if (input.type === 'file') {
            if (input.files.length > 0) formChanged = true;
        } else if (input.type === 'text' || input.type === 'number' || input.tagName === 'TEXTAREA') {
            if (input.value !== '') formChanged = true;
        } else if (input.type === 'select-one') {
            if (input.selectedIndex > 0) formChanged = true;
        }
    });
    
   
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
    color:rgb(4, 34, 21);
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

.preview-container {
    margin-top: 15px;
    display: none;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 4px;
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
    margin-top: 8px;
}

.preview-label {
    font-weight: 500;
    color:rgb(7, 45, 29);
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
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background-color: #36B37E;
    color: white;
}

.btn-primary:hover {
    background-color:rgb(5, 45, 26);
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

/* Responsiveness for mobile devices */
@media screen and (max-width: 768px) {
    .form-table td {
        display: block;
        width: 100%;
        padding: 5px 0;
    }
    
    .form-table td:first-child {
        width: 100%;
        padding-top: 15px;
    }
    
    .form-table td:nth-child(2) {
        display: none;
    }
    
    .input-group {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .input-group .form-input,
    .input-group .form-select {
        width: 100%;
        max-width: 100%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Toast notification for success message */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    min-width: 300px;
    z-index: 9999;
    padding: 15px 20px;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    animation: slideIn 0.3s, fadeOut 0.5s 4.5s forwards;
    display: none;
}

.toast-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.toast-error {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.toast-icon {
    margin-right: 10px;
    font-size: 20px;
}

.toast-close {
    margin-left: auto;
    cursor: pointer;
    font-size: 20px;
    opacity: 0.7;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>

<?php include '../views/footeradmin.php'; ?>