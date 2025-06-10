// SHOW MENU
let line = document.querySelector('.navbar .ri-menu-line');
let menu = document.querySelector('.navbar ul');

line.addEventListener('click', () => {
    menu.classList.toggle('showmenu');
});

window.addEventListener('scroll', () => {
    let nav = document.querySelector('.navbar');
    if (scrollY > 50) {
        nav.classList.add('navbarSticky')
    } else {
        nav.classList.remove('navbarSticky')
    };
});

// BUTTON
document.getElementById('myButton').addEventListener('click', function () {
    window.location.href = 'https://youtu.be/FPALstZU7fI?si=i_tNPEYZpE1yItQh';
});
document.getElementById('myButtonn').addEventListener('click', function () {
    window.location.href = '../pages/contact.php';
});
document.getElementById('myButtonnn').addEventListener('click', function () {
    window.location.href = '../pages/blog.php';
});
document.getElementById('myButonnnn').addEventListener('click', function () {
    window.location.href = 'portofolios.php';
});

// Memilih elemen bintang rating
const starsProduk = document.querySelectorAll('#product-rating .star');
const starsPenjual = document.querySelectorAll('#seller-service-rating .star');

// Fungsi untuk mengatur bintang yang dipilih
function handleStarRating(stars, hiddenInputId) {
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            // Reset semua bintang
            stars.forEach(s => s.classList.remove('selected'));
            // Tambah class "selected" pada bintang yang dipilih dan sebelumnya
            for (let i = 0; i <= index; i++) {
                stars[i].classList.add('selected');
            }
            // Menyimpan rating yang dipilih ke input tersembunyi
            document.getElementById(hiddenInputId).value = index + 1;
        });
    });
}

document.addEventListener("DOMContentLoaded", function () {
    handleStarRating(starsProduk, 'rating_produk');
    handleStarRating(starsPenjual, 'rating_seller');
});
