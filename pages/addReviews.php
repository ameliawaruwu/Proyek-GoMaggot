<?php
 include '../logic/update/auth.php'; 
include '../views/headeradmin.php'; 
?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminreviews.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main>
    <div class="head-title">
        <div class="left">
            <h1>Add New Review</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="reviews.php">Reviews</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a href="#" class="active">Add Review</a></li>
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
            <form id="addReviewForm" action="api/add_review.php" method="POST">
                <div class="form-group">
                    <label for="productId">Product:</label>
                    <select id="productId" name="product_id" class="analytics-dropdown" required>
                        <option value="">-- Select a Product --</option>
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
                
                <button type="submit" class="btn-primary">Add Review</button>
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
    console.log("Add Review page DOMContentLoaded.");

    const addReviewForm = document.getElementById('addReviewForm');
    const productIdSelect = document.getElementById('productId');
    const ratingInputDiv = document.getElementById('ratingInput');
    const selectedRatingInput = document.getElementById('selectedRating');
    let currentRating = 0; // Untuk menyimpan rating yang dipilih

    // Fungsi untuk mengambil daftar produk
    function fetchProducts() {
        fetch('api/get_products.php') // Anda perlu membuat API ini untuk mengambil daftar produk
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    data.products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
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
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "Failed to load products.";
                productIdSelect.appendChild(option);
                productIdSelect.disabled = true;
            });
    }

    // Inisialisasi daftar produk saat halaman dimuat
    fetchProducts();

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

        function updateStarDisplay(ratingToDisplay, isHover = false) {
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
    }

    // Handler untuk submit form
    if (addReviewForm) {
        addReviewForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah submit form default

            const formData = new FormData(addReviewForm);
            const reviewData = Object.fromEntries(formData.entries());

            // Pastikan rating tidak 0
            if (parseInt(reviewData.rating) === 0) {
                alertSystem.show({ type: 'warning', title: 'Input Needed', message: 'Please select a rating for the review.' });
                return;
            }

            fetch(addReviewForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reviewData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertSystem.show({ type: 'success', title: 'Success', message: 'Review added successfully!' });
                    addReviewForm.reset(); // Reset form setelah berhasil
                    currentRating = 0; // Reset rating tampilan
                    updateStarDisplay(currentRating);
                    // Redirect atau lakukan sesuatu setelah berhasil menambahkan
                    // Misalnya, kembali ke halaman daftar ulasan
                    setTimeout(() => {
                        window.location.href = 'reviews.php'; 
                    }, 1500); // Redirect setelah 1.5 detik
                } else {
                    alertSystem.show({ type: 'error', title: 'Error', message: data.message || 'Failed to add review.' });
                }
            })
            .catch(error => {
                console.error('Error adding review:', error);
                alertSystem.show({ type: 'error', title: 'Error', message: 'An error occurred while adding the review.' });
            });
        });
    }
});
</script>