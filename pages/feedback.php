<?php include '../partials/headers.php'; ?>
<?php include '../logic/update/auth.php'; ?>

<link rel="stylesheet" href="../Admin-HTML/css/feedback.css">
<link rel="stylesheet" href="../Admin-HTML/css/footer.css">

<header class="hero">
    <div class="header-wrapper">     
    <div class="container">
        <div class="header">PRODUCT REVIEWS</div>
        <h3>Product Quality</h3>

        <div class="stars" id="product-rating">
            <span class="star" data-value="1">★</span>
            <span class="star" data-value="2">★</span>
            <span class="star" data-value="3">★</span>
            <span class="star" data-value="4">★</span>
            <span class="star" data-value="5">★</span>
        </div>

        <form action="submit_feedback.php" method="POST" enctype="multipart/form-data">
            <!-- Input tersembunyi untuk rating produk dan rating seller -->
            <input type="hidden" name="rating_produk" id="rating_produk">
            <input type="hidden" name="rating_seller" id="rating_seller">

            <div class="input-group">
                <label for="review">Share your rating:</label>
                <textarea id="review" name="review" rows="4" placeholder="Share more thoughts on the product to help other buyers...."></textarea>
            </div>

            <div class="media-buttons">
                <div class="media-button">
                    <i class="ri-image-line"></i> Add Photo
                    <input type="file" name="photo" accept="image/*">
                </div>
                <div class="media-button">
                    <i class="ri-video-line"></i> Add Video
                    <input type="file" name="video" accept="video/*">
                </div>
            </div>

            <div class="additional-info">
                <div class="input-group">
                    <label>Condition:</label>
                    <input type="text" name="condition" placeholder="For example: according to the picture">
                </div>
                <div class="input-group">
                    <label>Quality:</label>
                    <input type="text" name="quality" placeholder="For example: very good and satisfying">
                </div>
            </div>

            <div class="toggle-switch">
                <label>Show Username On Your Review:</label>
                <input type="checkbox" name="username-toggle" id="username-toggle" checked>
            </div>

            <div class="stars-section">
                <label>Seller Service:</label>
                <div class="stars" id="seller-service-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
            </div>

            <button type="submit" class="submit-button">SUBMIT</button>
        </form>
    </div>
</header>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Function to handle star rating
        function handleStarRating(starContainerId, hiddenInputId) {
            const starsContainer = document.getElementById(starContainerId);
            const stars = starsContainer.querySelectorAll(".star");
            const hiddenInput = document.getElementById(hiddenInputId);

            stars.forEach((star) => {
                star.addEventListener("mouseover", () => {
                    const value = parseInt(star.getAttribute("data-value"));
                    highlightStars(stars, value);
                });

                starsContainer.addEventListener("mouseleave", () => {
                    const selectedValue = starsContainer.getAttribute("data-selected-value");
                    highlightStars(stars, parseInt(selectedValue));
                });

                star.addEventListener("click", () => {
                    const value = parseInt(star.getAttribute("data-value"));
                    starsContainer.setAttribute("data-selected-value", value);
                    highlightStars(stars, value);
                    hiddenInput.value = value; // Menyimpan rating ke input tersembunyi
                });
            });
        }

        // Function to highlight stars based on a given value
        function highlightStars(stars, value) {
            stars.forEach((star) => {
                const starValue = parseInt(star.getAttribute("data-value"));
                if (starValue <= value) {
                    star.classList.add("selected");
                } else {
                    star.classList.remove("selected");
                }
            });
        }

        // Initialize star rating for all rating sections
        handleStarRating("product-rating", "rating_produk");
        handleStarRating("seller-service-rating", "rating_seller");
    });
</script>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js"></script>
<script src="s../Admin-HTML/js/script2.js"></script>

<script src="script.js"></script>

<?php include '../partials/footer.php'; ?>
