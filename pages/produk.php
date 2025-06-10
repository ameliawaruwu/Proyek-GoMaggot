<?php 
include '../logic/update/auth.php';
include '../views/headeradmin.php';
include '../configdb.php'; // Koneksi ke dalam database

// Mengirimkan query ke MySQL untuk ambil produk
$sqlStatement = "SELECT * FROM produk ORDER BY idproduk ASC";
$query = mysqli_query($conn, $sqlStatement);
// Menangkap record hasil query
$dataProduk = mysqli_fetch_all($query, MYSQLI_ASSOC);

// Query untuk mendapatkan kategori unik
$categoryQuery = "SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = mysqli_fetch_all($categoryResult, MYSQLI_ASSOC);

// Menutup koneksi ke MySQL
mysqli_close($conn);
?> 


<!-- Toast notification untuk pesan sukses -->
<div id="toast-notification" class="toast-notification">
    <i id="toast-icon" class='bx bx-check-circle toast-icon'></i>
    <span id="toast-message">Pesan notifikasi</span>
    <span class="toast-close" onclick="closeToast()">&times;</span>
</div>

<!-- MAIN -->
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<main>
    <div class="head-title">
        <div class="left">
            <h1>Manajemen Produk</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Produk</a></li>
            </ul>
        </div>
        <a href="addproduk.php" class="btn-download" id="addProductBtn">
             <i class='bx bxs-plus-circle'></i>
             <span class="text">Add New Product</span>
        </a>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Product List</h3>
                <div class="search-container">
                    <input type="text" id="searchProduct" placeholder="Search products...">
                    <i class='bx bx-search'></i>
                </div>
                <div class="filter-container">
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php
                        // Daftar kategori tetap yang ingin ditampilkan
                        $fixedCategories = ['BSF', 'Kompos', 'Pupuk', 'Lainnya'];
                        
                        // Tambahkan kategori dari database yang belum ada di daftar tetap
                        foreach ($categories as $category) {
                            // Hanya tampilkan jika tidak ada dalam daftar kategori tetap
                            if (!in_array($category['kategori'], $fixedCategories)) {
                                echo "<option value='" . htmlspecialchars($category['kategori']) . "'>" . htmlspecialchars($category['kategori']) . "</option>";
                            }
                        }
                        
                        // Tambahkan kategori tetap
                        foreach ($fixedCategories as $fixedCategory) {
                            echo "<option value='" . htmlspecialchars($fixedCategory) . "'>" . htmlspecialchars($fixedCategory) . "</option>";
                        }
                        ?>
                    </select>
                    <i class='bx bx-filter'></i>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Merk</th>
                        <th>Berat</th>
                        <th>Masa Simpan</th>
                        <th>Pengiriman</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($dataProduk)) {
                        foreach ($dataProduk as $row) {
                            // Determine product status based on stock
                            $status = "Available";
                            $statusClass = "completed";
                            
                            if ($row['stok'] <= 0) {
                                $status = "Out of Stock";
                                $statusClass = "process";
                            } elseif ($row['stok'] <= 10) {
                                $status = "Low Stock";
                                $statusClass = "pending";
                            }
                            
                            // Image path handling
                            $imagePath = !empty($row['gambar']) ? "../photos/" . $row['gambar'] : '../img/default-product.jpg';
                            
                            // Format price
                            $formattedPrice = number_format($row['harga'], 0, ',', '.');
                            
                            // Get description (or default text if empty)
                            $description = !empty($row['deskripsi_produk']) ? htmlspecialchars($row['deskripsi_produk']) : "No description available";
                            
                            
                            echo "<tr>
                                <td>PRD" . str_pad($row['idproduk'], 3, '0', STR_PAD_LEFT) . "</td>
                                <td>
                                    <img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($row['namaproduk']) . "'>
                                    <p>" . htmlspecialchars($row['namaproduk']) . "</p>
                                </td>
                                <td>" . htmlspecialchars($row['kategori']) . "</td>
                                <td>Rp " . $formattedPrice . "</td>
                                <td>" . htmlspecialchars($row['stok']) . "</td>
                                <td>" . (isset($row['merk']) ? htmlspecialchars($row['merk']) : 'GoMaggot') . "</td>
                                <td>" . htmlspecialchars($row['berat'] . ($row['berat'] >= 1 ? ' kg' : ' gr')) . "</td>
                                <td>" . htmlspecialchars($row['masapenyimpanan']) . "</td>
                                <td>" . (isset($row['pengiriman']) ? htmlspecialchars($row['pengiriman']) : 'Tidak diketahui') . "</td>
                                <td class='product-description'>" . $description . "</td>
                                <td><span class='status " . $statusClass . "'>" . $status . "</span></td>
                                <td>
                                    <a href='editproduk.php?id=" . $row['idproduk'] . "' class='btn-edit'><i class='bx bxs-edit'></i></a>
                                    <a href='deleteproduk.php?id=" . $row['idproduk'] . "' class='btn-delete'><i class='bx bxs-trash'></i></a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12' style='text-align: center;'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</section>

<script>
// Search functionality
document.getElementById('searchProduct').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table-data table tbody tr');
    
    tableRows.forEach(row => {
        const productId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const productName = row.querySelector('td:nth-child(2) p').textContent.toLowerCase();
        const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const productDescription = row.querySelector('td:nth-child(9)').textContent.toLowerCase();
        
        if (productId.includes(searchTerm) || 
            productName.includes(searchTerm) || 
            productCategory.includes(searchTerm) ||
            productDescription.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const selectedCategory = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('.table-data table tbody tr');
    
    tableRows.forEach(row => {
        const productCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        
        if (selectedCategory === '' || productCategory === selectedCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

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

// Check for success parameter in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        
        if (status === 'deleted') {
            showToast('Produk berhasil dihapus!', 'success');
        } else if (status === 'updated') {
            showToast('Produk berhasil diperbarui!', 'success');
        } else if (status === 'added') {
            showToast('Produk baru berhasil ditambahkan!', 'success');
        } else if (status === 'error') {
            showToast('Terjadi kesalahan dalam operasi!', 'error');
        }
        
        // Remove the status parameter from URL
        const url = new URL(window.location);
        url.searchParams.delete('status');
        window.history.replaceState({}, '', url);
    }

    // Add tooltips to description cells to show full text on hover
    const descriptionCells = document.querySelectorAll('.product-description');
    descriptionCells.forEach(cell => {
        cell.setAttribute('title', cell.textContent);
    });
});
</script>


<style>
   
    /* Toast notification untuk pesan sukses */
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
    
    .toast-close:hover {
        opacity: 1;
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
    
    /* Peningkatan tampilan tombol */
    .btn-edit, .btn-delete {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        margin: 0 3px;
        transition: all 0.2s;
    }
    
    .btn-edit {
        background-color: #f0ad4e;
        color: white;
    }
    
    .btn-edit:hover {
        background-color: #ec971f;
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background-color: #d9534f;
        color: white;
    }
    
    .btn-delete:hover {
        background-color: #c9302c;
        transform: translateY(-2px);
    }
    
    /* Peningkatan tampilan tabel */
    .table-data .order {
        overflow-x: auto; /* Menambahkan horizontal scroll jika tabel terlalu lebar */
        max-width: 100%;
    }
    
    .table-data .order table {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        min-width: 1100px; /* Lebar minimum untuk memastikan semua kolom terlihat */
        width: 100%;
    }
    
    .table-data .order table thead tr {
        background-color: #f8f9fa;
    }
    
    .table-data .order table th {
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
    }
    
    .table-data .order table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .table-data .order table tr:last-child td {
        border-bottom: none;
    }
    
    .table-data .order table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Status styling */
    .status {
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status.completed {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status.pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status.process {
        background-color: #f8d7da;
        color: #721c24;
    }

    /* Description column styling */
    .product-description {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: help;
    }
    
    /* Ensure the colspan is updated */
    .table-data .order table tbody tr td[colspan="10"] {
        colspan: 11;
    }

    /* flex box*/
    .head {
    display: flex;
    justify-content: space-between; /* Bagi ruang antara judul dan kontrol */
    align-items: right;
    flex-wrap: wrap; /* Agar responsif saat layar kecil */
    gap: 10px;
    }

    .search-container,
    .filter-container {
    display: flex;
    align-items: center;
    gap: 5px;
    }

    .head > .search-container,
    .head > .filter-container {
    margin-left: 10px;
    }

    .right-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: aouto; /* Dorong ke kanan */
    flex-wrap: wrap;    /* Agar responsif di layar kecil */
    }

    .right-controls input,
    .right-controls select {
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    }

    .order {
    padding: 20px;
    box-sizing: border-box;
    }

        /* Tambahkan CSS ini ke dalam file CSS Anda */
    .header-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    }

    .header-left {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    }

    .page-title {
    margin-bottom: 15px;
    }

    .search-filter-container {
    display: flex;
    gap: 10px;
    margin-top: 5px;
    }

    .search-box {
    display: flex;
    position: relative;
    }

    .search-box input {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 220px;
    }

    .search-button {
    background: none;
    border: none;
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    }

    .filter-container {
    position: relative;
    }

    .filter-container select {
    padding: 8px 30px 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    appearance: none;
    background-color: white;
    }

    .filter-container i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    }

    .breadcrumbs {
    margin-bottom: 20px;
    }

    /* Pastikan tombol Add New Product tetap di kanan */
    .header-right {
    display: flex;
    align-items: center;
    }

    </style>

<script src="../Admin-HTML/js/scriptadmin.js"></script>
<script src="../Admin-HTML/js/script1.js"></script>


<?php
include '../views/footeradmin.php';
?>