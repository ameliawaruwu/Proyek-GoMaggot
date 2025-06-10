<?php

<<<<<<< HEAD
include '../Logic/update/auth.php'; 
include '../views/headeradmin.php'; 
include '../configdb.php';
?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminriviews.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
=======
include '../Logic/update/auth.php';
include '../views/headeradmin.php';
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
// Menggunakan 'review' dan join yang benar
$countSql = "SELECT COUNT(r.id_review) AS total
             FROM review r
             LEFT JOIN produk p ON r.idproduk = p.idproduk
             LEFT JOIN pengguna u ON r.id_pelanggan = u.id_pelanggan "
             . $whereSql;

$stmtCount = $conn->prepare($countSql);
if ($stmtCount) {
    if (!empty($types)) {
        // Buat salinan params dan types untuk stmtCount
        $temp_params = $params;
        $temp_types = $types;
        // Hapus 'ii' terakhir dari $temp_types karena itu untuk LIMIT dan OFFSET
        // yang tidak diperlukan untuk query COUNT
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
// Menggunakan 'review' dan join yang benar, serta nama kolom yang benar
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
// Menggunakan alias 'id' agar sesuai dengan JS
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
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986

<main>
    <div class="head-title">
        <div class="left">
<<<<<<< HEAD
            <h1>Product Reviews & Comments</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#" class="active">Reviews</a></li>
            </ul>
        </div>
        <a href="addreviews.php" class="btn-download"> <i class='bx bx-plus-circle'></i>
            <span class="text">Add New Review</span>
        </a>
    </div>

    <div class="filter-section">
        <div class="filter-group">
            <label for="reviewStatusFilter">Status:</label>
            <select id="reviewStatusFilter" class="analytics-dropdown">
                <option value="all">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="reviewRatingFilter">Rating:</label>
            <select id="reviewRatingFilter" class="analytics-dropdown">
                <option value="all">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
            </select>
        </div>
        <div class="filter-group search-box">
            <input type="text" id="reviewSearchInput" placeholder="Search by product or comment..." class="form-input search-input">
            <button id="reviewSearchButton" class="btn-primary"><i class='bx bx-search'></i></button>
        </div>
        <button id="clearFiltersBtn" class="btn-secondary">Clear Filters</button>
=======
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
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
    </div>
    <div class="table-data">
        <div class="order">
            <div class="head">
<<<<<<< HEAD
                <h3>Customer Reviews</h3>
                </div>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Status</th>
                        <th>Review Date</th>
                        <th>Reply</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reviewsTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px;">Loading reviews...</td>
                    </tr>
                </tbody>
            </table>
            <div class="pagination-controls" style="text-align: center; margin-top: 20px;">
                <button id="prevPageBtn" class="btn-secondary">Previous</button>
                <span id="currentPage">Page 1</span> / <span id="totalPages">1</span>
                <button id="nextPageBtn" class="btn-secondary">Next</button>
=======
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
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
            </div>
        </div>
    </div>
</main>

<<<<<<< HEAD
<div id="replyModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Reply to Review</h2>
        <form id="replyForm">
            <input type="hidden" id="replyReviewId">
            <div class="form-group">
                <label for="reviewTextDisplay">Original Comment:</label>
                <p id="reviewTextDisplay" style="background-color: #f0f0f0; padding: 10px; border-radius: 5px; margin-bottom: 15px;"></p>
            </div>
            <div class="form-group">
                <label for="replyTextInput">Your Reply:</label>
                <textarea id="replyTextInput" rows="5" placeholder="Type your reply here..." required></textarea>
            </div>
            <button type="submit" class="btn-primary">Send Reply</button>
            <button type="button" class="btn-secondary" id="cancelReply">Cancel</button>
        </form>
    </div>
</div>

<div id="editReviewModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeEditModalBtn">&times;</span>
        <h2>Edit Review</h2>
        <form id="editReviewForm">
            <input type="hidden" id="editReviewId">
            <div class="form-group">
                <label for="editProductId">Product:</label>
                <select id="editProductId" name="product_id" class="analytics-dropdown" required>
                    </select>
            </div>
            <div class="form-group">
                <label for="editReviewerName">Reviewer Name:</label>
                <input type="text" id="editReviewerName" name="reviewer_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="editReviewerEmail">Reviewer Email (Optional):</label>
                <input type="email" id="editReviewerEmail" name="reviewer_email" class="form-input">
            </div>
            <div class="form-group">
                <label for="editRating">Rating:</label>
=======
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
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
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
<<<<<<< HEAD
                <label for="editComment">Comment:</label>
=======
                <label for="editComment">Komentar:</label>
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
                <textarea id="editComment" name="comment" rows="6" class="form-input" required></textarea>
            </div>
            <div class="form-group">
                <label for="editStatus">Status:</label>
                <select id="editStatus" name="status" class="analytics-dropdown">
<<<<<<< HEAD
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editReplyText">Admin Reply (Optional):</label>
                <textarea id="editReplyText" name="reply_text" rows="3" class="form-input"></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Save Changes</button>
            <button type="button" class="btn-secondary" id="cancelEdit">Cancel</button>
=======
                    <option value="pending">Tertunda</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <button type="button" class="btn-secondary" id="cancelEdit">Batal</button>
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
        </form>
    </div>
</div>

<<<<<<< HEAD
<script src="../Admin-HTML/js/scriptadmin.js"></script> 
<?php
include '../views/footeradmin.php'; // Menggunakan footeradmin.php yang sama
=======
<script src="../Admin-HTML/js/scriptadmin.js"></script>
<?php
include '../views/footeradmin.php';
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Reviews page DOMContentLoaded.");

<<<<<<< HEAD
    // Memanggil fungsi inisialisasi manajemen review
    initReviewsPage(); 
});

// Fungsi untuk menampilkan bintang berdasarkan rating
function getStarRatingHtml(rating) {
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += '<i class="bx bxs-star star-filled"></i>'; // Bintang penuh
        } else {
            starsHtml += '<i class="bx bx-star star-empty"></i>'; // Bintang kosong
        }
    }
    return `<div class="star-rating">${starsHtml}</div>`;
}

// Fungsi utama untuk manajemen halaman reviews
function initReviewsPage() {
    console.log("initReviewsPage() called.");

    const reviewsTableBody = document.getElementById('reviewsTableBody');
    const reviewStatusFilter = document.getElementById('reviewStatusFilter');
    const reviewRatingFilter = document.getElementById('reviewRatingFilter');
    const reviewSearchInput = document.getElementById('reviewSearchInput');
    const reviewSearchButton = document.getElementById('reviewSearchButton');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    // Reply Modal elements
    const replyModal = document.getElementById('replyModal');
    const closeReplyModalBtn = replyModal ? replyModal.querySelector('.close-button') : null;
    const cancelReplyBtn = document.getElementById('cancelReply');
    const replyForm = document.getElementById('replyForm');
    const replyReviewIdInput = document.getElementById('replyReviewId');
    const reviewTextDisplay = document.getElementById('reviewTextDisplay');
    const replyTextInput = document.getElementById('replyTextInput');

    // Edit Review Modal elements (NEW)
=======
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
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
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
<<<<<<< HEAD
    const editReplyTextInput = document.getElementById('editReplyText');

    let currentEditRating = 0; // Untuk menyimpan rating yang dipilih di modal edit

    // Pagination elements
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const currentPageSpan = document.getElementById('currentPage');
    const totalPagesSpan = document.getElementById('totalPages');

    let currentPage = 1;
    const itemsPerPage = 10; // Jumlah ulasan per halaman

    // --- Fungsionalitas Umum ---

    // Fungsi untuk mengambil dan merender ulasan
    function fetchAndRenderReviews() {
        reviewsTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">Loading reviews...</td></tr>';

        const status = reviewStatusFilter ? reviewStatusFilter.value : 'all';
        const rating = reviewRatingFilter ? reviewRatingFilter.value : 'all';
        const search = reviewSearchInput ? reviewSearchInput.value : '';

        // Membangun URL API dengan parameter filter dan pagination
        const apiUrl = `api/get_product_reviews.php?status=${status}&rating=${rating}&search=${search}&page=${currentPage}&limit=${itemsPerPage}`;
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                reviewsTableBody.innerHTML = ''; // Kosongkan tabel
                if (data.reviews && data.reviews.length > 0) {
                    data.reviews.forEach(review => {
                        const row = document.createElement('tr');
                        const statusClass = review.status ? review.status.toLowerCase() : '';

                        row.innerHTML = `
                            <td>
                                <img src="${review.product_image || '../Admin-HTML/images/default-product.webp'}" alt="Product" style="width: 40px; height: 40px; border-radius: 5px; margin-right: 10px;">
                                <p>${review.product_name || 'N/A'}</p>
                            </td>
                            <td>${review.reviewer_name || 'Anonymous'}</td>
                            <td>${getStarRatingHtml(review.rating)}</td>
                            <td>${review.comment ? (review.comment.length > 100 ? review.comment.substring(0, 100) + '...' : review.comment) : 'No comment'}</td>
                            <td><span class="status ${statusClass}">${review.status || 'N/A'}</span></td>
                            <td>${new Date(review.review_date).toLocaleDateString('id-ID')}</td>
                            <td>${review.reply_text ? `Replied: ${review.reply_text.substring(0, 50)}...` : '<span style="color: #888;">No Reply</span>'}</td>
                            <td>
                                ${review.status !== 'approved' ? `<button class="btn-action approve-review" data-id="${review.id}"><i class='bx bx-check-circle'></i></button>` : ''}
                                ${review.status !== 'rejected' ? `<button class="btn-action reject-review" data-id="${review.id}"><i class='bx bx-x-circle'></i></button>` : ''}
                                <button class="btn-action reply-review" data-id="${review.id}" data-comment="${review.comment}"><i class='bx bx-reply'></i></button>
                                <button class="btn-action edit-review" data-id="${review.id}"><i class='bx bx-edit-alt'></i></button> <button class="btn-action delete-review" data-id="${review.id}"><i class='bx bx-trash'></i></button>
                            </td>
                        `;
                        reviewsTableBody.appendChild(row);
                    });

                    // Update pagination info
                    totalPagesSpan.textContent = data.total_pages;
                    currentPageSpan.textContent = currentPage;
                    prevPageBtn.disabled = currentPage === 1;
                    nextPageBtn.disabled = currentPage === data.total_pages;

                    // Add event listeners for new buttons
                    addReviewButtonListeners();

                } else {
                    reviewsTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No reviews found based on current filters.</td></tr>';
                    totalPagesSpan.textContent = '1';
                    currentPageSpan.textContent = '1';
                    prevPageBtn.disabled = true;
                    nextPageBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching reviews:', error);
                reviewsTableBody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: red;">Failed to load reviews. Please try again.</td></tr>';
            });
    }

    // Fungsi untuk menambahkan event listener ke tombol aksi (Approve, Reject, Reply, Edit, Delete)
    function addReviewButtonListeners() {
        document.querySelectorAll('.approve-review').forEach(button => {
            button.onclick = (e) => {
                const reviewId = e.currentTarget.dataset.id;
                updateReviewStatus(reviewId, 'approved');
            };
        });
        document.querySelectorAll('.reject-review').forEach(button => {
            button.onclick = (e) => {
                const reviewId = e.currentTarget.dataset.id;
                updateReviewStatus(reviewId, 'rejected');
            };
        });
        document.querySelectorAll('.reply-review').forEach(button => {
            button.onclick = (e) => {
                const reviewId = e.currentTarget.dataset.id;
                const commentText = e.currentTarget.dataset.comment;
                openReplyModal(reviewId, commentText);
            };
        });
        document.querySelectorAll('.edit-review').forEach(button => { // NEW EDIT LISTENER
            button.onclick = (e) => {
                const reviewId = e.currentTarget.dataset.id;
                fetchReviewForEdit(reviewId);
            };
        });
        document.querySelectorAll('.delete-review').forEach(button => {
            button.onclick = (e) => {
                const reviewId = e.currentTarget.dataset.id;
                deleteReview(reviewId);
            };
        });
    }

    // Fungsi untuk memperbarui status ulasan
    function updateReviewStatus(reviewId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus} this review?`)) {
            return;
        }
        fetch('api/update_review_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ review_id: reviewId, new_status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alertSystem.show({ type: 'success', title: 'Success', message: `Review ${newStatus} successfully!` });
                fetchAndRenderReviews(); // Refresh tabel setelah update
            } else {
                alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to update review status.' });
            }
        })
        .catch(error => console.error('Error updating review status:', error));
    }

    // --- Fungsionalitas Reply Modal (Existing) ---

    // Fungsi untuk membuka modal balasan
    function openReplyModal(reviewId, commentText) {
        replyReviewIdInput.value = reviewId;
        reviewTextDisplay.textContent = commentText;
        replyTextInput.value = ''; // Kosongkan input balasan
        replyModal.style.display = 'block';
    }

    // Fungsi untuk menutup modal balasan
    function closeReplyModal() {
        replyModal.style.display = 'none';
        replyForm.reset();
    }

    // Event listeners untuk modal balasan
    if (closeReplyModalBtn) {
        closeReplyModalBtn.onclick = closeReplyModal;
    }
    if (cancelReplyBtn) {
        cancelReplyBtn.onclick = closeReplyModal;
    }
    window.onclick = (event) => {
        if (event.target === replyModal) {
            closeReplyModal();
        }
    };

    // Handler submit form balasan
    if (replyForm) {
        replyForm.onsubmit = (e) => {
            e.preventDefault();
            const reviewId = replyReviewIdInput.value;
            const replyText = replyTextInput.value.trim();
            if (!replyText) {
                alertSystem.show({ type: 'warning', title: 'Input Needed', message: 'Reply cannot be empty!' });
                return;
            }
            sendReply(reviewId, replyText);
        };
    }

    // Fungsi untuk mengirim balasan admin
    function sendReply(reviewId, replyText) {
        const adminId = 1; // Ganti dengan ID admin yang sebenarnya (misalnya dari PHP session)
        fetch('api/add_review_reply.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ review_id: reviewId, admin_id: adminId, reply_text: replyText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alertSystem.show({ type: 'success', title: 'Success', message: 'Reply sent successfully!' });
                closeReplyModal();
                fetchAndRenderReviews(); // Refresh tabel setelah balasan
            } else {
                alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to send reply.' });
            }
        })
        .catch(error => console.error('Error sending reply:', error));
    }

    // --- Fungsionalitas Edit Review Modal (NEW) ---

    // Fungsi untuk mengambil detail review dan mengisi form edit
    function fetchReviewForEdit(reviewId) {
        fetch(`api/get_review_details.php?id=${reviewId}`) // Anda perlu membuat API ini
            .then(response => response.json())
            .then(data => {
                if (data.success && data.review) {
                    const review = data.review;
                    editReviewIdInput.value = review.id;
                    editReviewerNameInput.value = review.reviewer_name;
                    editReviewerEmailInput.value = review.reviewer_email || '';
                    editCommentInput.value = review.comment;
                    editStatusSelect.value = review.status;
                    editReplyTextInput.value = review.reply_text || '';

                    // Setel rating bintang di modal edit
                    currentEditRating = parseInt(review.rating);
                    editSelectedRatingInput.value = currentEditRating;
                    updateEditStarDisplay(currentEditRating);

                    // Ambil daftar produk dan pilih produk yang relevan
                    fetchProductsForEditModal(review.product_id);

                    editReviewModal.style.display = 'block';
                } else {
                    alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to load review details.' });
                }
            })
            .catch(error => {
                console.error('Error fetching review for edit:', error);
                alertSystem.show({ type: 'error', title: 'Error', message: 'An error occurred while fetching review details.' });
            });
    }

    // Fungsi untuk mengisi dropdown produk di modal edit
    function fetchProductsForEditModal(selectedProductId = null) {
        // Kosongkan dan reset pilihan
        editProductIdSelect.innerHTML = '<option value="">-- Select a Product --</option>';
        editProductIdSelect.disabled = false;

        fetch('api/get_products.php') // Menggunakan API yang sama untuk daftar produk
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    data.products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        if (selectedProductId && parseInt(product.id) === parseInt(selectedProductId)) {
                            option.selected = true;
                        }
                        editProductIdSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "No products found.";
                    editProductIdSelect.appendChild(option);
                    editProductIdSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching products for edit modal:', error);
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "Failed to load products.";
                editProductIdSelect.appendChild(option);
                editProductIdSelect.disabled = true;
            });
    }

    // Logika untuk sistem rating bintang di modal edit
=======

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
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
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
<<<<<<< HEAD
                updateEditStarDisplay(hoverRating, true);
=======
                updateEditStarDisplay(hoverRating);
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
            }
        });

        editRatingInputDiv.addEventListener('mouseout', function() {
<<<<<<< HEAD
            updateEditStarDisplay(currentEditRating); // Kembali ke rating yang dipilih saat mouse keluar
=======
            updateEditStarDisplay(currentEditRating);
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
        });

        function updateEditStarDisplay(ratingToDisplay) {
            editRatingInputDiv.querySelectorAll('.bx').forEach((star, index) => {
                if (index < ratingToDisplay) {
                    star.classList.remove('bx-star');
<<<<<<< HEAD
                    star.classList.add('bxs-star'); // Bintang penuh
=======
                    star.classList.add('bxs-star'); // Bintang terisi
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
                } else {
                    star.classList.remove('bxs-star');
                    star.classList.add('bx-star'); // Bintang kosong
                }
            });
        }
    }
<<<<<<< HEAD

    // Fungsi untuk menutup modal edit
    function closeEditModal() {
        editReviewModal.style.display = 'none';
        editReviewForm.reset();
        currentEditRating = 0; // Reset rating tampilan
        updateEditStarDisplay(currentEditRating);
    }

    // Event listeners untuk modal edit
    if (closeEditModalBtn) {
        closeEditModalBtn.onclick = closeEditModal;
    }
    if (cancelEditBtn) {
        cancelEditBtn.onclick = closeEditModal;
    }
    window.onclick = (event) => { // Pastikan ini juga menangani editReviewModal
        if (event.target === replyModal) {
            closeReplyModal();
        } else if (event.target === editReviewModal) {
            closeEditModal();
        }
    };

    // Handler submit form edit
    if (editReviewForm) {
        editReviewForm.onsubmit = (e) => {
            e.preventDefault();
            const reviewId = editReviewIdInput.value;
            const formData = new FormData(editReviewForm);
            const reviewData = Object.fromEntries(formData.entries());
            reviewData.review_id = reviewId; // Tambahkan ID review ke data

            if (parseInt(reviewData.rating) === 0) {
                alertSystem.show({ type: 'warning', title: 'Input Needed', message: 'Please select a rating for the review.' });
                return;
            }

            fetch('api/update_review.php', { // Anda perlu membuat API ini
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(reviewData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertSystem.show({ type: 'success', title: 'Success', message: 'Review updated successfully!' });
                    closeEditModal();
                    fetchAndRenderReviews(); // Refresh tabel setelah update
                } else {
                    alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to update review.' });
                }
            })
            .catch(error => {
                console.error('Error updating review:', error);
                alertSystem.show({ type: 'error', title: 'Error', message: 'An error occurred while updating the review.' });
            });
        };
    }

    // --- Fungsionalitas Delete Review (Existing, but re-checked) ---

    // Fungsi untuk menghapus ulasan
    function deleteReview(reviewId) {
        if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
            return;
        }
        fetch('api/delete_review.php', { // Ini bisa berupa soft delete (is_deleted = 1)
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ review_id: reviewId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alertSystem.show({ type: 'warning', title: 'Deleted', message: 'Review deleted successfully!' });
                fetchAndRenderReviews(); // Refresh tabel setelah hapus
            } else {
                alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to delete review.' });
            }
        })
        .catch(error => console.error('Error deleting review:', error));
    }

    // --- Event listeners untuk filter dan pagination (Existing) ---
    if (reviewStatusFilter) {
        reviewStatusFilter.addEventListener('change', () => { currentPage = 1; fetchAndRenderReviews(); });
    }
    if (reviewRatingFilter) {
        reviewRatingFilter.addEventListener('change', () => { currentPage = 1; fetchAndRenderReviews(); });
    }
    if (reviewSearchButton) {
        reviewSearchButton.addEventListener('click', () => { currentPage = 1; fetchAndRenderReviews(); });
    }
    if (reviewSearchInput) {
        reviewSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') { currentPage = 1; fetchAndRenderReviews(); }
        });
    }
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            if (reviewStatusFilter) reviewStatusFilter.value = 'all';
            if (reviewRatingFilter) reviewRatingFilter.value = 'all';
            if (reviewSearchInput) reviewSearchInput.value = '';
            currentPage = 1;
            fetchAndRenderReviews();
        });
    }

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchAndRenderReviews();
            }
        });
    }
    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', () => {
            const totalPages = parseInt(totalPagesSpan.textContent);
            if (currentPage < totalPages) {
                currentPage++;
                fetchAndRenderReviews();
            }
        });
    }

    // Panggil pertama kali saat halaman dimuat
    fetchAndRenderReviews();
}
=======
});
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
</script>