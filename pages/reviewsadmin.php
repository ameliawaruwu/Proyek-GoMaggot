<?php
// Pastikan hanya admin yang bisa mengakses halaman ini
include '../Logic/update/auth.php';
// Panggil bagian kepala admin (header, navigasi samping, dll.)
include '../views/headeradmin.php';
// Sambungkan ke database kita dong
include '../configdb.php'; // Pastikan ini menyediakan $conn yang aktif dan berfungsi

// Inisialisasi variabel untuk pesan feedback
$success_message = '';
$error_message = '';

// --- Logika PHP untuk Mengambil Data Ulasan ---
$reviews = [];
$totalReviews = 0;
$totalPages = 1;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

$itemsPerPage = 10; // Jumlah ulasan per halaman

// Filter dan Pencarian
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$ratingFilter = isset($_GET['rating']) ? $_GET['rating'] : 'all';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClauses = [];
$params = [];
$types = '';

// Tambahkan kondisi status
if ($statusFilter !== 'all') {
    $whereClauses[] = "r.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Tambahkan kondisi rating
if ($ratingFilter !== 'all' && is_numeric($ratingFilter)) {
    $whereClauses[] = "r.rating_produk = ?";
    $params[] = (int)$ratingFilter;
    $types .= 'i';
}

// Tambahkan kondisi pencarian
if (!empty($searchTerm)) {
    $searchTermParam = "%" . $searchTerm . "%";
    $whereClauses[] = "(r.komentar LIKE ? OR p.namaproduk LIKE ? OR u.username LIKE ?)";
    $params[] = $searchTermParam;
    $params[] = $searchTermParam;
    $params[] = $searchTermParam;
    $types .= 'sss';
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

// Query untuk menghitung total ulasan (untuk pagination)
$countSql = "SELECT COUNT(r.id_review) AS total
             FROM review r
             LEFT JOIN produk p ON r.idproduk = p.idproduk
             LEFT JOIN pengguna u ON r.id_pelanggan = u.id_pelanggan "
             . $whereSql;

$stmtCount = $conn->prepare($countSql);
if ($stmtCount) {
    if (!empty($types)) {
        $temp_params = $params;
        $temp_types = $types;

        // Hapus 'ii' terakhir dari $temp_types karena itu untuk LIMIT dan OFFSET
        // yang tidak diperlukan untuk query COUNT (jika sebelumnya sudah ada)
        if (strlen($temp_types) > 2 && substr($temp_types, -2) == 'ii') {
            $temp_types = substr($temp_types, 0, -2);
            array_pop($temp_params); // Hapus offset
            array_pop($temp_params); // Hapus itemsPerPage
        }
        if (!empty($temp_types)) {
            $stmtCount->bind_param($temp_types, ...$temp_params);
        }
    }
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalReviews = $resultCount->fetch_assoc()['total'];
    $stmtCount->close();
} else {
    $error_message = "Gagal menyiapkan pernyataan hitungan: " . $conn->error;
    error_log($error_message);
}

$totalPages = ceil($totalReviews / $itemsPerPage);
if ($totalPages == 0) $totalPages = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;

$offset = ($currentPage - 1) * $itemsPerPage;
if ($offset < 0) {
    $offset = 0;
}

// Query untuk mengambil ulasan yang sebenarnya
$sql = "SELECT
            r.id_review,
            r.id_pelanggan,
            r.idproduk,
            r.rating_produk,
            r.komentar,
            r.foto AS review_photo,
            r.video AS review_video,
            r.fitur,
            r.kegunaan,
            r.tampilkan_username,
            r.rating_seller,
            r.tanggal_review,
            r.status,
            p.namaproduk AS product_name,
            p.gambar AS product_image,
            u.username AS reviewer_username,
            u.email AS reviewer_email
        FROM review r
        LEFT JOIN produk p ON r.idproduk = p.idproduk
        LEFT JOIN pengguna u ON r.id_pelanggan = u.id_pelanggan
        " . $whereSql . "
        ORDER BY r.tanggal_review DESC
        LIMIT ? OFFSET ?";

// Salin parameter asli, lalu tambahkan parameter LIMIT dan OFFSET
$current_params_for_data_query = $params;
$current_types_for_data_query = $types;

// Hapus 'ii' terakhir yang mungkin sudah ditambahkan untuk query COUNT, lalu tambahkan lagi untuk query data
if (strlen($current_types_for_data_query) > 2 && substr($current_types_for_data_query, -2) == 'ii') {
    $current_types_for_data_query = substr($current_types_for_data_query, 0, -2);
    array_pop($current_params_for_data_query);
    array_pop($current_params_for_data_query);
}

$current_params_for_data_query[] = $itemsPerPage;
$current_params_for_data_query[] = $offset;
$current_types_for_data_query .= 'ii';

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($current_params_for_data_query) && !empty($current_types_for_data_query) && count($current_params_for_data_query) === strlen($current_types_for_data_query)) {
        $stmt->bind_param($current_types_for_data_query, ...$current_params_for_data_query);
    } else {
        $error_message = "Jumlah parameter atau tipe tidak cocok untuk pernyataan ulasan.";
        error_log($error_message);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if (isset($row['tampilkan_username']) && $row['tampilkan_username'] == 0) {
                $row['reviewer_name'] = 'Anonim'; // Ganti Anonymous jadi Anonim
            } else {
                $row['reviewer_name'] = $row['reviewer_username'] ?? 'N/A';
            }

            $row['product_image'] = $row['product_image'] ?? '../Admin-HTML/images/default-product.webp';

            $reviews[] = $row;
        }
    } else {
        $error_message = "Gagal mengeksekusi pernyataan ulasan: " . $stmt->error;
        error_log($error_message);
    }
    $stmt->close();
} else {
    $error_message = "Gagal menyiapkan pernyataan ulasan: " . $conn->error;
    error_log($error_message);
}

// --- Logika PHP untuk Aksi (Setujui, Tolak, Hapus, Edit) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $reviewId = (int)($_POST['review_id'] ?? 0);

        $current_status_filter = $_POST['status_filter'] ?? 'all';
        $current_rating_filter = $_POST['rating_filter'] ?? 'all';
        $current_search_term = $_POST['search_term'] ?? '';
        $current_page_num = (int)($_POST['page_num'] ?? 1);

        function redirectToReviewsPage($message_type, $message_content, $status, $rating, $search, $page) {
            $redirect_url = "reviews.php?status=" . urlencode($status) .
                                "&rating=" . urlencode($rating) .
                                "&search=" . urlencode($search) .
                                "&page=" . urlencode($page);
            if ($message_type === 'success') {
                $redirect_url .= "&success_message=" . urlencode($message_content);
            } else {
                $redirect_url .= "&error_message=" . urlencode($message_content);
            }
            header("Location: " . $redirect_url);
            exit();
        }

        if ($action === 'update_status' && isset($_POST['new_status'])) {
            $newStatus = $_POST['new_status'];
            $updateSql = "UPDATE review SET status = ? WHERE id_review = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            if ($stmtUpdate) {
                $stmtUpdate->bind_param("si", $newStatus, $reviewId);
                if ($stmtUpdate->execute()) {
                    redirectToReviewsPage('success', "Status ulasan berhasil diperbarui ke " . htmlspecialchars($newStatus) . "!", $current_status_filter, $current_rating_filter, $current_search_term, $current_page_num);
                } else {
                    $error_message = "Gagal memperbarui status: " . $stmtUpdate->error;
                }
                $stmtUpdate->close();
            } else {
                $error_message = "Gagal menyiapkan pembaruan status: " . $conn->error;
            }
        } elseif ($action === 'delete_review') {
            $deleteSql = "DELETE FROM review WHERE id_review = ?";
            $stmtDelete = $conn->prepare($deleteSql);
            if ($stmtDelete) {
                $stmtDelete->bind_param("i", $reviewId);
                if ($stmtDelete->execute()) {
                    redirectToReviewsPage('success', "Ulasan berhasil dihapus!", $current_status_filter, $current_rating_filter, $current_search_term, $current_page_num);
                } else {
                    $error_message = "Gagal menghapus ulasan: " . $stmtDelete->error;
                }
                $stmtDelete->close();
            } else {
                $error_message = "Gagal menyiapkan penghapusan: " . $conn->error;
            }
        } elseif ($action === 'edit_review' && isset($_POST['product_id'], $_POST['rating'], $_POST['comment'], $_POST['status'])) {
            $editProductId = (int)($_POST['product_id'] ?? 0);
            $editRating = (int)($_POST['rating'] ?? 0);
            $editComment = trim($_POST['comment']);
            $editStatus = $_POST['status'];

            if ($editRating === 0) {
                redirectToReviewsPage('error', "Peringkat (rating) tidak boleh kosong!", $current_status_filter, $current_rating_filter, $current_search_term, $current_page_num);
            }
            if (empty($editComment)) {
                redirectToReviewsPage('error', "Komentar tidak boleh kosong!", $current_status_filter, $current_rating_filter, $current_search_term, $current_page_num);
            }

            $updateSql = "UPDATE review SET
                                 idproduk = ?,
                                 rating_produk = ?,
                                 komentar = ?,
                                 status = ?
                               WHERE id_review = ?";
            $stmtEdit = $conn->prepare($updateSql);
            if ($stmtEdit) {
                $stmtEdit->bind_param("iissi",
                                         $editProductId,
                                         $editRating,
                                         $editComment,
                                         $editStatus,
                                         $reviewId);
                if ($stmtEdit->execute()) {
                    redirectToReviewsPage('success', "Ulasan berhasil diperbarui!", $current_status_filter, $current_rating_filter, $current_search_term, $current_page_num);
                } else {
                    $error_message = "Gagal memperbarui ulasan: " . $stmtEdit->error;
                }
                $stmtEdit->close();
            } else {
                $error_message = "Gagal menyiapkan pembaruan: " . $conn->error;
            }
        }
    }
}

// Dapatkan produk untuk dropdown modal edit (ini diambil sekali)
$productsForEdit = [];
$sqlProducts = "SELECT idproduk AS id, namaproduk AS name FROM produk ORDER BY namaproduk ASC";
$resultProducts = $conn->query($sqlProducts);
if ($resultProducts) {
    while($rowProduct = $resultProducts->fetch_assoc()) {
        $productsForEdit[] = $rowProduct;
    }
} else {
    $error_message = "Gagal mengambil produk untuk modal edit: " . $conn->error;
    error_log($error_message);
}

// Tutup koneksi setelah semua data diambil dan aksi diproses
$conn->close();

// Tampilkan pesan sukses/error jika ada dari redirect
$success_message = $_GET['success_message'] ?? $success_message;
$error_message = $_GET['error_message'] ?? $error_message;

?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminriviews.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Ulasan & Komentar Produk</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dasbor</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#" class="active">Ulasan</a></li>
            </ul>
        </div>
        <a href="addreviews.php" class="btn-download"> <i class='bx bx-plus-circle'></i>
            <span class="text">Tambah Ulasan Baru</span>
        </a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert success-alert" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="alert error-alert" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="filter-section">
        <form action="" method="GET" id="filterForm">
            <div class="filter-group">
                <label for="reviewStatusFilter">Status:</label>
                <select id="reviewStatusFilter" name="status" class="analytics-dropdown" onchange="document.getElementById('filterForm').submit();">
                    <option value="all" <?php echo ($statusFilter == 'all') ? 'selected' : ''; ?>>Semua</option>
                    <option value="pending" <?php echo ($statusFilter == 'pending') ? 'selected' : ''; ?>>Tertunda</option>
                    <option value="approved" <?php echo ($statusFilter == 'approved') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="rejected" <?php echo ($statusFilter == 'rejected') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="reviewRatingFilter">Peringkat:</label>
                <select id="reviewRatingFilter" name="rating" class="analytics-dropdown" onchange="document.getElementById('filterForm').submit();">
                    <option value="all" <?php echo ($ratingFilter == 'all') ? 'selected' : ''; ?>>Semua Peringkat</option>
                    <option value="5" <?php echo ($ratingFilter == '5') ? 'selected' : ''; ?>>5 Bintang</option>
                    <option value="4" <?php echo ($ratingFilter == '4') ? 'selected' : ''; ?>>4 Bintang</option>
                    <option value="3" <?php echo ($ratingFilter == '3') ? 'selected' : ''; ?>>3 Bintang</option>
                    <option value="2" <?php echo ($ratingFilter == '2') ? 'selected' : ''; ?>>2 Bintang</option>
                    <option value="1" <?php echo ($ratingFilter == '1') ? 'selected' : ''; ?>>1 Bintang</option>
                </select>
            </div>
            <div class="filter-group search-box">
                <input type="text" id="reviewSearchInput" name="search" placeholder="Cari berdasarkan produk, komentar, atau pengulas..." class="form-input search-input" value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" id="reviewSearchButton" class="btn-primary"><i class='bx bx-search'></i></button>
            </div>
            <button type="button" id="clearFiltersBtn" class="btn-secondary" onclick="window.location.href='reviews.php'">Bersihkan Filter</button>
        </form>
    </div>
    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Ulasan Pelanggan</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Pengulas</th>
                        <th>Peringkat</th>
                        <th>Komentar</th>
                        <th>Status</th>
                        <th>Tanggal Ulasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="reviewsTableBody">
                    <?php if (empty($reviews)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada ulasan ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($review['product_image']); ?>" alt="Product" style="width: 40px; height: 40px; border-radius: 5px; margin-right: 10px;">
                                    <p><?php echo htmlspecialchars($review['product_name'] ?? 'N/A'); ?></p>
                                </td>
                                <td><?php echo htmlspecialchars($review['reviewer_name']); ?></td>
                                <td>
                                    <div class="star-rating">
                                        <?php
                                        $rating = (int)$review['rating_produk'];
                                        for ($i = 1; $i <= 5; $i++):
                                            if ($i <= $rating): ?>
                                                <i class="bx bxs-star star-filled"></i>
                                            <?php else: ?>
                                                <i class="bx bx-star star-empty"></i>
                                            <?php endif;
                                        endfor; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars(strlen($review['komentar']) > 100 ? substr($review['komentar'], 0, 100) . '...' : $review['komentar']); ?></td>
                                <td><span class="status <?php echo strtolower(htmlspecialchars($review['status'])); ?>"><?php echo htmlspecialchars($review['status']); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($review['tanggal_review'])); ?></td>
                                <td>
                                    <?php if ($review['status'] !== 'approved'): ?>
                                        <form action="" method="POST" style="display: inline-block;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id_review']; ?>">
                                            <input type="hidden" name="new_status" value="approved">
                                            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                            <input type="hidden" name="rating_filter" value="<?php echo htmlspecialchars($ratingFilter); ?>">
                                            <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                            <input type="hidden" name="page_num" value="<?php echo htmlspecialchars($currentPage); ?>">
                                            <button type="submit" class="btn-action approve-review"><i class='bx bx-check-circle'></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($review['status'] !== 'rejected'): ?>
                                        <form action="" method="POST" style="display: inline-block;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="review_id" value="<?php echo $review['id_review']; ?>">
                                            <input type="hidden" name="new_status" value="rejected">
                                            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                            <input type="hidden" name="rating_filter" value="<?php echo htmlspecialchars($ratingFilter); ?>">
                                            <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                            <input type="hidden" name="page_num" value="<?php echo htmlspecialchars($currentPage); ?>">
                                            <button type="submit" class="btn-action reject-review"><i class='bx bx-x-circle'></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn-action edit-review"
                                            data-id="<?php echo htmlspecialchars($review['id_review']); ?>"
                                            data-productid="<?php echo htmlspecialchars($review['idproduk']); ?>"
                                            data-reviewername="<?php echo htmlspecialchars($review['reviewer_name']); ?>"
                                            data-revieweremail="<?php echo htmlspecialchars($review['reviewer_email']); ?>"
                                            data-rating="<?php echo htmlspecialchars($review['rating_produk']); ?>"
                                            data-comment="<?php echo htmlspecialchars($review['komentar']); ?>"
                                            data-status="<?php echo htmlspecialchars($review['status']); ?>"
                                            data-replytext=""><i class='bx bx-edit-alt'></i></button>
                                    <form action="" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini? Tindakan ini tidak dapat dibatalkan.');">
                                        <input type="hidden" name="action" value="delete_review">
                                        <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['id_review']); ?>">
                                        <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                        <input type="hidden" name="rating_filter" value="<?php echo htmlspecialchars($ratingFilter); ?>">
                                        <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                        <input type="hidden" name="page_num" value="<?php echo htmlspecialchars($currentPage); ?>">
                                        <button type="submit" class="btn-action delete-review"><i class='bx bx-trash'></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="pagination-controls" style="text-align: center; margin-top: 20px;">
                <a href="reviews.php?status=<?php echo htmlspecialchars($statusFilter); ?>&rating=<?php echo htmlspecialchars($ratingFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>&page=<?php echo max(1, $currentPage - 1); ?>" class="btn-secondary <?php echo ($currentPage == 1) ? 'disabled' : ''; ?>">Sebelumnya</a>
                <span id="currentPage">Halaman <?php echo htmlspecialchars($currentPage); ?></span> / <span id="totalPages"><?php echo htmlspecialchars($totalPages); ?></span>
                <a href="reviews.php?status=<?php echo htmlspecialchars($statusFilter); ?>&rating=<?php echo htmlspecialchars($ratingFilter); ?>&search=<?php echo htmlspecialchars($searchTerm); ?>&page=<?php echo min($totalPages, $currentPage + 1); ?>" class="btn-secondary <?php echo ($currentPage == $totalPages) ? 'disabled' : ''; ?>">Berikutnya</a>
            </div>
        </div>
    </div>
</main>

<div id="editReviewModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeEditModalBtn">&times;</span>
        <h2>Edit Ulasan</h2>
        <form id="editReviewForm" action="" method="POST">
            <input type="hidden" id="editReviewId" name="review_id">
            <input type="hidden" name="action" value="edit_review">
            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <input type="hidden" name="rating_filter" value="<?php echo htmlspecialchars($ratingFilter); ?>">
            <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <input type="hidden" name="page_num" value="<?php echo htmlspecialchars($currentPage); ?>">
            <div class="form-group">
                <label for="editProductId">Produk:</label>
                <select id="editProductId" name="product_id" class="analytics-dropdown" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($productsForEdit as $product): ?>
                        <option value="<?php echo htmlspecialchars($product['id']); ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="editReviewerName">Nama Pengulas:</label>
                <input type="text" id="editReviewerName" name="reviewer_name" class="form-input" required readonly> </div>
            <div class="form-group">
                <label for="editReviewerEmail">Email Pengulas (Opsional):</label>
                <input type="email" id="editReviewerEmail" name="reviewer_email" class="form-input" readonly> </div>
            <div class="form-group">
                <label for="editRating">Peringkat:</label>
                <div class="star-rating-input" id="editRatingInput">
                    <i class="bx bx-star" data-rating="1"></i>
                    <i class="bx bx-star" data-rating="2"></i>
                    <i class="bx bx-star" data-rating="3"></i>
                    <i class="bx bx-star" data-rating="4"></i>
                    <i class="bx bx-star" data-rating="5"></i>
                    <input type="hidden" name="rating" id="editSelectedRating" value="0" required>
                </div>
            </div>
            <div class="form-group">
                <label for="editComment">Komentar:</label>
                <textarea id="editComment" name="comment" rows="6" class="form-input" required></textarea>
            </div>
            <div class="form-group">
                <label for="editStatus">Status:</label>
                <select id="editStatus" name="status" class="analytics-dropdown">
                    <option value="pending">Tertunda</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <button type="button" class="btn-secondary" id="cancelEdit">Batal</button>
        </form>
    </div>
</div>

<script src="../Admin-HTML/js/scriptadmin.js"></script>
<?php
include '../views/footeradmin.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Reviews page DOMContentLoaded.");

    window.onclick = (event) => {
        const editReviewModal = document.getElementById('editReviewModal');
        if (event.target === editReviewModal) {
            editReviewModal.style.display = 'none';
            const editReviewForm = document.getElementById('editReviewForm');
            editReviewForm.reset();
            currentEditRating = 0;
            updateEditStarDisplay(currentEditRating);
        }
    };

    // --- Modal Edit Logic ---
    const editReviewModal = document.getElementById('editReviewModal');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEdit');
    const editReviewForm = document.getElementById('editReviewForm');
    const editReviewIdInput = document.getElementById('editReviewId');
    const editProductIdSelect = document.getElementById('editProductId');
    const editReviewerNameInput = document.getElementById('editReviewerName');
    const editReviewerEmailInput = document.getElementById('editReviewerEmail');
    const editRatingInputDiv = document.getElementById('editRatingInput');
    const editSelectedRatingInput = document.getElementById('editSelectedRating');
    const editCommentInput = document.getElementById('editComment');
    const editStatusSelect = document.getElementById('editStatus');

    let currentEditRating = 0;

    document.querySelectorAll('.edit-review').forEach(button => {
        button.onclick = (e) => {
            const reviewId = e.currentTarget.dataset.id;
            const productId = e.currentTarget.dataset.productid;
            const reviewerName = e.currentTarget.dataset.reviewername;
            const reviewerEmail = e.currentTarget.dataset.revieweremail;
            const rating = parseInt(e.currentTarget.dataset.rating);
            const comment = e.currentTarget.dataset.comment;
            const status = e.currentTarget.dataset.status;

            editReviewIdInput.value = reviewId;
            editProductIdSelect.value = productId;
            editReviewerNameInput.value = reviewerName;
            editReviewerEmailInput.value = reviewerEmail;
            editCommentInput.value = comment;
            editStatusSelect.value = status;

            currentEditRating = rating;
            editSelectedRatingInput.value = currentEditRating;
            updateEditStarDisplay(currentEditRating);

            editReviewModal.style.display = 'block';
        };
    });

    closeEditModalBtn.onclick = () => {
        editReviewModal.style.display = 'none';
        editReviewForm.reset();
        currentEditRating = 0;
        updateEditStarDisplay(currentEditRating);
    };
    cancelEditBtn.onclick = () => {
        editReviewModal.style.display = 'none';
        editReviewForm.reset();
        currentEditRating = 0;
        updateEditStarDisplay(currentEditRating);
    };

    // Star rating logic for edit modal
    if (editRatingInputDiv) {
        editRatingInputDiv.addEventListener('click', function(e) {
            const star = e.target.closest('.bx');
            if (star && star.dataset.rating) {
                currentEditRating = parseInt(star.dataset.rating);
                editSelectedRatingInput.value = currentEditRating;
                updateEditStarDisplay(currentEditRating);
            }
        });

        editRatingInputDiv.addEventListener('mouseover', function(e) {
            const star = e.target.closest('.bx');
            if (star && star.dataset.rating) {
                const hoverRating = parseInt(star.dataset.rating);
                updateEditStarDisplay(hoverRating);
            }
        });

        editRatingInputDiv.addEventListener('mouseout', function() {
            updateEditStarDisplay(currentEditRating);
        });

        function updateEditStarDisplay(ratingToDisplay) {
            editRatingInputDiv.querySelectorAll('.bx').forEach((star, index) => {
                if (index < ratingToDisplay) {
                    star.classList.remove('bx-star');
                    star.classList.add('bxs-star');
                } else {
                    star.classList.remove('bxs-star');
                    star.classList.add('bx-star');
                }
            });
        }
    }
});
</script>