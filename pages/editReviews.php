<?php
include '../Logic/update/auth.php';
include '../views/headeradmin.php';

// Mendapatkan ID review dari parameter URL
$review_id = $_GET['id'] ?? null;

if (!is_numeric($review_id) || $review_id <= 0) {
    // Redirect atau tampilkan pesan error jika ID tidak valid
    echo "<main><div class='head-title'><div class='left'><h1>Error</h1></div></div><div class='table-data'><div class='order'><p style='color: red; text-align: center;'>Invalid review ID provided.</p><p style='text-align: center;'><a href='reviews.php'>Back to Reviews</a></p></div></div></main>";
    include '../views/footeradmin.php';
    exit();
}
?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminreviews.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

<main>
    <div class="head-title">
        <div class="left">
            <h1>Edit Review</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="reviews.php">Reviews</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#" class="active">Edit Review</a></li>
            </ul>
        </div>
        <a href="reviews.php" class="btn-download">
            <i class='bx bx-arrow-back'></i>
            <span class="text">Back to Reviews</span>
        </a>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Review Details</h3>
            </div>
            <form id="editReviewForm" action="editReviews.php" method="POST">
                <input type="hidden" name="review_id" id="reviewId" value="<?php echo htmlspecialchars($review_id); ?>">
                
                <div class="form-group">
                    <label for="productId">Product:</label>
                    <select id="productId" name="product_id" class="analytics-dropdown" required>
                        <option value="">Loading Products...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reviewerName">Reviewer Name:</label>
                    <input type="text" id="reviewerName" name="reviewer_name" class="form-input" placeholder="Enter reviewer name" required>
                </div>
                <div class="form-group">
                    <label for="reviewerEmail">Reviewer Email (Optional):</label>
                    <input type="email" id="reviewerEmail" name="reviewer_email" class="form-input" placeholder="Enter reviewer email">
                </div>
                <div class="form-group">
                    <label for="rating">Rating:</label>
                    <div class="star-rating-input" id="ratingInput">
                        <i class="bx bx-star" data-rating="1"></i>
                        <i class="bx bx-star" data-rating="2"></i>
                        <i class="bx bx-star" data-rating="3"></i>
                        <i class="bx bx-star" data-rating="4"></i>
                        <i class="bx bx-star" data-rating="5"></i>
                        <input type="hidden" name="rating" id="selectedRating" value="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="comment">Comment:</label>
                    <textarea id="comment" name="comment" rows="6" class="form-input" placeholder="Write the review comment here..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="analytics-dropdown">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="replyText">Admin Reply (Optional):</label>
                    <textarea id="replyText" name="reply_text" rows="3" class="form-input" placeholder="Enter admin reply here..."></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Save Changes</button>
                <button type="button" class="btn-secondary" onclick="window.location.href='reviews.php'">Cancel</button>
            </form>
        </div>
    </div>
</main>

<script src="../Admin-HTML/js/scriptadmin.js"></script> 
<?php
include '../views/footeradmin.php'; // Menggunakan footeradmin.php yang sama
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Edit Review page DOMContentLoaded.");

    const reviewId = document.getElementById('reviewId').value;
    const editReviewForm = document.getElementById('editReviewForm');
    const productIdSelect = document.getElementById('productId');
    const reviewerNameInput = document.getElementById('reviewerName');
    const reviewerEmailInput = document.getElementById('reviewerEmail');
    const ratingInputDiv = document.getElementById('ratingInput');
    const selectedRatingInput = document.getElementById('selectedRating');
    const commentInput = document.getElementById('comment');
    const statusSelect = document.getElementById('status');
    const replyTextInput = document.getElementById('replyText');

    let currentRating = 0; // Untuk menyimpan rating yang dipilih

    // Fungsi untuk menampilkan bintang berdasarkan rating
    function updateStarDisplay(ratingToDisplay) {
        ratingInputDiv.querySelectorAll('.bx').forEach((star, index) => {
            if (index < ratingToDisplay) {
                star.classList.remove('bx-star');
                star.classList.add('bxs-star'); // Bintang penuh
            } else {
                star.classList.remove('bxs-star');
                star.classList.add('bx-star'); // Bintang kosong
            }
        });
    }

    // Logika untuk sistem rating bintang
    if (ratingInputDiv) {
        ratingInputDiv.addEventListener('click', function(e) {
            const star = e.target.closest('.bx');
            if (star && star.dataset.rating) {
                currentRating = parseInt(star.dataset.rating);
                selectedRatingInput.value = currentRating;
                updateStarDisplay(currentRating);
            }
        });

        ratingInputDiv.addEventListener('mouseover', function(e) {
            const star = e.target.closest('.bx');
            if (star && star.dataset.rating) {
                const hoverRating = parseInt(star.dataset.rating);
                updateStarDisplay(hoverRating, true);
            }
        });

        ratingInputDiv.addEventListener('mouseout', function() {
            updateStarDisplay(currentRating); // Kembali ke rating yang dipilih saat mouse keluar
        });
    }

    // Fungsi untuk mengambil daftar produk
    function fetchProducts(selectedProductId = null) {
        productIdSelect.innerHTML = '<option value="">Loading Products...</option>';
        productIdSelect.disabled = true;

        fetch('api/get_products.php') // Anda perlu membuat API ini untuk mengambil daftar produk
            .then(response => response.json())
            .then(data => {
                productIdSelect.innerHTML = '<option value="">-- Select a Product --</option>'; // Reset
                productIdSelect.disabled = false;
                if (data.success && data.products.length > 0) {
                    data.products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        if (selectedProductId && parseInt(product.id) === parseInt(selectedProductId)) {
                            option.selected = true;
                        }
                        productIdSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "No products found.";
                    productIdSelect.appendChild(option);
                    productIdSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                productIdSelect.innerHTML = '<option value="">Failed to load products.</option>';
                productIdSelect.disabled = true;
            });
    }

    // Fungsi untuk mengambil detail review dan mengisi form
    function fetchReviewDetails(id) {
        fetch(`api/get_review_details.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.review) {
                    const review = data.review;
                    reviewerNameInput.value = review.reviewer_name;
                    reviewerEmailInput.value = review.reviewer_email || '';
                    commentInput.value = review.comment;
                    statusSelect.value = review.status;
                    replyTextInput.value = review.reply_text || '';

                    currentRating = parseInt(review.rating);
                    selectedRatingInput.value = currentRating;
                    updateStarDisplay(currentRating);

                    fetchProducts(review.product_id); // Panggil fungsi untuk mengisi produk dropdown
                } else {
                    alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to load review details.' });
                    // Opsional: Redirect kembali ke halaman reviews jika gagal load
                    setTimeout(() => {
                        window.location.href = 'reviews.php'; 
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error fetching review details:', error);
                alertSystem.show({ type: 'error', title: 'Error', message: 'An error occurred while fetching review details.' });
                // Opsional: Redirect kembali ke halaman reviews jika gagal load
                setTimeout(() => {
                    window.location.href = 'reviews.php'; 
                }, 2000);
            });
    }

    // Panggil fungsi untuk mengambil detail review saat halaman dimuat
    if (reviewId) {
        fetchReviewDetails(reviewId);
    } else {
        alertSystem.show({ type: 'error', title: 'Error', message: 'No review ID provided in URL.' });
        // Redirect jika tidak ada ID
        setTimeout(() => {
            window.location.href = 'reviews.php'; 
        }, 2000);
    }

    // Handler submit form edit
    if (editReviewForm) {
        editReviewForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(editReviewForm);
            const reviewData = Object.fromEntries(formData.entries());

            if (parseInt(reviewData.rating) === 0) {
                alertSystem.show({ type: 'warning', title: 'Input Needed', message: 'Please select a rating for the review.' });
                return;
            }

            fetch(editReviewForm.action, { // Mengirim data ke update_review.php
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reviewData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertSystem.show({ type: 'success', title: 'Success', message: 'Review updated successfully!' });
                    // Opsional: Redirect kembali ke halaman daftar review setelah update berhasil
                    setTimeout(() => {
                        window.location.href = 'reviews.php'; 
                    }, 1500);
                } else {
                    alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to update review.' });
                }
            })
            .catch(error => {
                console.error('Error updating review:', error);
                alertSystem.show({ type: 'error', title: 'Error', message: 'An error occurred while updating the review.' });
            });
        });
    }
});
</script>