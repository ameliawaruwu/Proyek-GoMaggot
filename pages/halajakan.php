<?php include '../views/header.php'; ?>
<?php
include '../Logic/update/auth.php'; 
include '../configdb.php';
?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminriviews.css">
<<<<<<< HEAD
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

<main>
    <div class="head-title">
        <div class="left">
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
    </div>
    <div class="table-data">
        <div class="order">
            <div class="head">
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
            </div>
        </div>
    </div>
</main>

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
                <label for="editComment">Comment:</label>
                <textarea id="editComment" name="comment" rows="6" class="form-input" required></textarea>
            </div>
            <div class="form-group">
                <label for="editStatus">Status:</label>
                <select id="editStatus" name="status" class="analytics-dropdown">
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
        </form>
    </div>
</div>

<script src="../Admin-HTML/js/scriptadmin.js"></script> 
<?php
include '../views/footeradmin.php'; // Menggunakan footeradmin.php yang sama
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Reviews page DOMContentLoaded.");

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
                updateEditStarDisplay(hoverRating, true);
            }
        });

        editRatingInputDiv.addEventListener('mouseout', function() {
            updateEditStarDisplay(currentEditRating); // Kembali ke rating yang dipilih saat mouse keluar
        });

        function updateEditStarDisplay(ratingToDisplay) {
            editRatingInputDiv.querySelectorAll('.bx').forEach((star, index) => {
                if (index < ratingToDisplay) {
                    star.classList.remove('bx-star');
                    star.classList.add('bxs-star'); // Bintang penuh
                } else {
                    star.classList.remove('bxs-star');
                    star.classList.add('bx-star'); // Bintang kosong
                }
            });
        }
    }

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
</script>

=======
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
>>>>>>> ce5ec8126cfcf8cc0467651fa803dc3855ca4986
<link rel="stylesheet" href="../Admin-HTML/css/halajakan.css">
<link rel="stylesheet" href="../Admin-HTML/css/footer.css">

<div id="image">
        <img src="../Admin-HTML/images/Logo Artikel Fix.png" alt="" width="500" height="400">
    </div>

    <div class="Sub-topik">
        <h2>Ayo Belajar!</h2>
        <ul>
            <li>
                <summary>Mengenal Lebih Jauh Apa itu Maggot BSF</summary>
                <details>
                    <p>Maggot merupakan larva dari jenis lalat Black Soldier Fly (BSF) sehingga sering disebut maggot BSF.
                    Lalat BSF sendiri memiliki nama latin Heremetia illucens. Bentuknya mirip ulat, dengan ukuran 
                    larva dewasa 15-22 mm dan berwarna coklat. Siklus hidup lalat BSF kurang lebih selama 40-43 hari. 
                    Larva/maggot BSF bertahan selama 14-18 hari sebelum bermetamorfosis menjadi pupa dan lalat dewasa.</p>
                </details>
            </li>
            <br>
            <li>
                <summary>Mengetahui Manfaat Budidaya Maggot BSF</summary>
                <details>
                    <p>Pengelola Sampah Organik, Pakan Ternak, Pupuk Organik</p>
                </details>
            </li>
            <br>
            <li>
                <summary>Melakukan Pelestarian Maggot BSF dengan Pembudidayaan</summary>
                <details>
                    <p>Proses budidaya maggot dimulai dengan pemilihan telur yang berkualitas. 
                    Telur-telur tersebut kemudian ditempatkan di dalam kandang yang telah disiapkan. 
                    Setelah menetas, larva maggot diberi pakan berupa limbah organik seperti sisa sayuran dan buah-buahan.</p>
                </details>
            </li>
            <br>
            <br>
            <a href="halgaleri.php" class="button">
                <span>Kunjungi Gallery kami</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1); transform: ; msFilter:;">
                    <path d="M20 2H6c-1.206 0-3 .799-3 3v14c0 2.201 1.794 3 3 3h15v-2H6.012C5.55 19.988 5 19.806 5 19s.55-.988 1.012-1H21V3a1 1 0 0 0-1-1zM9.503 5a1.503 1.503 0 1 1 0 3.006 1.503 1.503 0 0 1 0-3.006zM12 13H7l3-3 1.5 1.399L14.5 8l3.5 5h-6z"></path>
                </svg>
            </a>
            
        </ul>
    </div>

    <h3 style="text-align: center;">Artikel Kami</h3>

    <div class="gallery-container">
        <div class="gallery">
            <img src="../Admin-HTML/images/maggot.jpg" alt="Maggot BSF">
            <div class="desc">Mengenal Lebih Dalam Maggot BSF</div><br>
            <a href="artikelsatu.php" class="button">Pelajari Lebih Lanjut</a>
        </div>
    
    
        <div class="gallery">
            <img src="../Admin-HTML/images/maggot kompos.jpg" alt="Manfaat Maggot">
            <div class="desc">Manfaat Maggot Dalam Segi Kehidupan</div><br>
            <a href="artikeltiga.php" class="button">Pelajari Lebih Lanjut</a>
        </div>
    
        <div class="gallery">
            <img src="../Admin-HTML/images/ternak maggot.jpeg" alt="Budidaya Maggot">
            <div class="desc">Melakukan Budidaya Maggot Dengan Ternak Sederhana</div>
            <a href="artikelsatu.php" class="button">Pelajari Lebih Lanjut</a>
        </div>
        <div class="gallery">
        <img src="../Admin-HTML/images/maggot.jpg" alt="Maggot BSF">
        <div class="desc">Mengenal Lebih Dalam Maggot BSF</div><br>
        <a href="artikeldua.php" class="button">Pelajari Lebih Lanjut</a>
    </div>

    
<!--
    <div class="gallery">
        <img src="../Admin-HTML/images/maggot kompos.jpg" alt="Manfaat Maggot">
        <div class="desc">Manfaat Maggot Untuk Segi Kehidupan</div><br>
        <a href="artikeltiga.php" class="button">Pelajari Lebih Lanjut</a>
    </div>
    <div class="gallery">
        <img src="../Admin-HTML/images/ternak maggot.jpeg" alt="Budidaya Maggot">
        <div class="desc">Melakukan Budidaya Maggot Dengan Ternak Sederhana</div>
        <a href="artikelsatu.php" class="button">Pelajari Lebih Lanjut</a>
    </div> -->
    </div>
    <script src="..\Admin-HTML\js\script.js"></script>

    <?php include '../partials/footer.php'; ?>