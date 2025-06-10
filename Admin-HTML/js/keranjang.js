// Dijalankan setelah DOM selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    const listProduct = document.querySelector('.listProduct');
    const listCart = document.querySelector('.ListCart');
    const cartTab = document.querySelector('.cartTab');
    const body = document.querySelector('body');
    const closeCartBtn = document.querySelector('.close');
    const checkOutBtn = document.querySelector('.checkOut');
    const cartIcon = document.querySelector('.icon-cart');
    const cartQuantitySpan = document.querySelector('.icon-cart span');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const loadingCartMessage = document.getElementById('loadingCartMessage');

    // Menggunakan let karena carts akan diubah
    let carts = []; // Array untuk menyimpan item keranjang di sisi klien (dari localStorage)
    let products = []; // Untuk menyimpan data produk yang tersedia jika diperlukan di masa depan

    // --- Event Listeners ---

    // Buka/tutup keranjang saat ikon keranjang diklik
    cartIcon.addEventListener('click', () => {
        body.classList.toggle('showCart');
        loadCartItemsFromLocalStorage(); // Muat item keranjang dari local storage setiap kali keranjang dibuka
    });

    // Tutup keranjang saat tombol 'Tutup' diklik
    closeCartBtn.addEventListener('click', () => {
        body.classList.remove('showCart');
    });

    // Event listener untuk tombol "Masukan Keranjang"
    // Menggunakan event delegation karena tombol ditambahkan secara dinamis
    listProduct.addEventListener('click', (event) => {
        if (event.target.classList.contains('add-to-cart-btn')) {
            try {
                const productData = JSON.parse(event.target.dataset.productData);
                addToCartLocal(productData); // Panggil fungsi lokal
            } catch (e) {
                console.error("Error parsing product data:", e);
                alert("Terjadi kesalahan saat menambahkan produk: Data produk tidak valid.");
            }
        }
    });

    // Event listener untuk tombol kuantitas (+/-) dan hapus di dalam keranjang
    listCart.addEventListener('click', (event) => {
        let positionClick = event.target;
        if (positionClick.classList.contains('minus')) {
            let idProduk = positionClick.closest('.item').dataset.id;
            changeQuantityLocal(idProduk, 'minus');
        } else if (positionClick.classList.contains('plus')) {
            let idProduk = positionClick.closest('.item').dataset.id;
            changeQuantityLocal(idProduk, 'plus');
        } else if (positionClick.classList.contains('remove-item-btn')) {
            let idProdukToRemove = positionClick.dataset.idProduk; // Menggunakan idProduk untuk penghapusan
            removeFromCartLocal(idProdukToRemove);
        }
    });

    // Event listener untuk tombol Checkout
    checkOutBtn.addEventListener('click', () => {
        if (carts.length > 0) {
            // Untuk checkout di halaman keranjang.php, kita tidak langsung proses di sini.
            // Kita akan redirect ke checkOut.php, di mana form detail pengiriman akan diisi.
            // Pastikan data keranjang sudah di localStorage saat redirect.
            window.location.href = 'checkOut.php'; 
        } else {
            alert('Keranjang Anda kosong. Tidak dapat melanjutkan checkout.');
        }
    });

    // --- Functions ---

    // Fungsi untuk menambahkan produk ke keranjang di Local Storage
    function addToCartLocal(product) {
        let positionInCart = carts.findIndex((value) => value.idproduk == product.idproduk);
        if (positionInCart < 0) {
            // Jika produk belum ada di keranjang, tambahkan
            carts.push({
                idproduk: product.idproduk,
                namaproduk: product.namaproduk,
                harga: product.harga,
                gambar: product.gambar,
                jumlah: 1,
                // Stok produk tidak perlu disimpan di keranjang, hanya harga saat pembelian
                // Stok akan dicek di backend saat checkout
            });
        } else {
            // Jika produk sudah ada, tambahkan jumlahnya
            // Opsional: Anda bisa menambahkan cek stok di sini juga jika diperlukan,
            // tapi validasi utama harus tetap di backend saat checkout.
            carts[positionInCart].jumlah++;
        }
        saveCartToLocalStorage();
        renderCart();
        updateCartQuantityIcon();
        alert('Produk berhasil ditambahkan ke keranjang!'); // Notifikasi
    }

    // Fungsi untuk mengubah jumlah item di keranjang di Local Storage
    function changeQuantityLocal(idProduk, type) {
        let positionInCart = carts.findIndex((value) => value.idproduk == idProduk);
        if (positionInCart >= 0) {
            if (type === 'plus') {
                // Opsional: Cek stok maks di sini jika Anda ingin membatasi penambahan kuantitas di frontend
                // Namun, validasi utama stok tetap di backend.
                carts[positionInCart].jumlah++;
            } else if (type === 'minus') {
                carts[positionInCart].jumlah--;
                if (carts[positionInCart].jumlah <= 0) {
                    // Jika jumlah menjadi 0 atau kurang, hapus item dari keranjang
                    carts.splice(positionInCart, 1);
                }
            }
        }
        saveCartToLocalStorage();
        renderCart();
        updateCartQuantityIcon();
    }

    // Fungsi untuk menghapus item dari keranjang di Local Storage
    function removeFromCartLocal(idProduk) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
            return;
        }
        carts = carts.filter(item => item.idproduk != idProduk);
        saveCartToLocalStorage();
        renderCart();
        updateCartQuantityIcon();
        alert('Produk berhasil dihapus dari keranjang.');
    }

    // Fungsi untuk menyimpan array keranjang ke Local Storage
    function saveCartToLocalStorage() {
        localStorage.setItem('shoppingCart', JSON.stringify(carts));
    }

    // Fungsi untuk memuat array keranjang dari Local Storage
    function loadCartItemsFromLocalStorage() {
        const storedCart = localStorage.getItem('shoppingCart');
        if (storedCart) {
            carts = JSON.parse(storedCart);
        } else {
            carts = [];
        }
        renderCart(); // Render ulang keranjang setelah dimuat
        updateCartQuantityIcon();
        updateCheckoutButtonState();
        loadingCartMessage.style.display = 'none'; // Sembunyikan pesan loading setelah dimuat
    }

    // Fungsi untuk merender item keranjang ke DOM
    function renderCart() {
        listCart.innerHTML = ''; // Bersihkan tampilan sebelumnya
        let totalQuantity = 0;
        let totalPrice = 0;

        if (carts.length > 0) {
            emptyCartMessage.style.display = 'none';
            carts.forEach(item => {
                const quantity = parseInt(item.jumlah || 0);
                const price = parseFloat(item.harga || 0);
                const subtotal = quantity * price;

                totalQuantity += quantity;
                totalPrice += subtotal;

                let newDiv = document.createElement('div');
                newDiv.classList.add('item');
                newDiv.dataset.id = item.idproduk; // Data ID produk untuk tombol kuantitas

                newDiv.innerHTML = `
                    <div class="image">
                        <img src="${item.gambar}" alt="${item.namaproduk}">
                    </div>
                    <div class="name">${item.namaproduk}</div>
                    <div class="totalPrice">Rp.${(subtotal).toLocaleString('id-ID')}</div>
                    <div class="quantity">
                        <span class="minus">-</span>
                        <span>${quantity}</span>
                        <span class="plus">+</span>
                    </div>
                    <button class="remove-item-btn" data-id-produk="${item.idproduk}">Hapus</button>
                `;
                listCart.appendChild(newDiv);
            });
        } else {
            emptyCartMessage.style.display = 'block';
        }
        totalPriceDisplay.innerText = `Rp.${totalPrice.toLocaleString('id-ID')}`;
        cartQuantitySpan.innerText = totalQuantity;
        updateCheckoutButtonState();
    }

    // Fungsi untuk memperbarui angka di ikon keranjang
    function updateCartQuantityIcon() {
        let total = 0;
        carts.forEach(item => {
            total += parseInt(item.jumlah || 0);
        });
        cartQuantitySpan.innerText = total;
    }

    // Fungsi untuk mengaktifkan/menonaktifkan tombol checkout
    function updateCheckoutButtonState() {
        if (carts.length > 0) {
            checkOutBtn.removeAttribute('disabled');
            checkOutBtn.style.opacity = '1';
            checkOutBtn.style.cursor = 'pointer';
        } else {
            checkOutBtn.setAttribute('disabled', 'true');
            checkOutBtn.style.opacity = '0.5';
            checkOutBtn.style.cursor = 'not-allowed';
        }
    }

    // Fungsi yang menangani proses checkout (mengirim data keranjang ke server)
    // Fungsi ini tidak akan dipanggil di keranjang.js lagi untuk langsung memproses checkout,
    // melainkan akan mengarahkan ke checkOut.php.
    // Jika Anda ingin proses checkout langsung dari keranjang.js, maka implementasi ini akan dipakai
    // dengan tambahan detail pengiriman di sini.
    // Saat ini, fungsi ini TIDAK AKAN DIPANGGIL LANGSUNG DARI TOMBOL CHECKOUT DI KERANJANG.JS
    // Tombol checkout sekarang akan melakukan window.location.href = 'checkOut.php';
    function processCheckout() {
        // Implementasi ini hanya relevan jika checkout dilakukan langsung dari keranjang.js
        // Tanpa mengisi detail pengiriman di halaman yang sama.
        // Karena kita sudah punya checkOut.php untuk mengisi detail, fungsi ini bisa diabaikan
        // atau dihapus jika tidak ada kebutuhan untuk AJAX checkout langsung dari keranjang.
        // Jika tetap ingin ada fungsi ini, Anda perlu menambahkan input detail pengiriman
        // di halaman keranjang ini atau mengambilnya dari tempat lain.
    }

    // Inisialisasi: Muat item keranjang dari Local Storage saat halaman dimuat
    loadCartItemsFromLocalStorage();
});