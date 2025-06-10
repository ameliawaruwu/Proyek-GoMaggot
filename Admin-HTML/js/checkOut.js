// Ambil data keranjang dari URL parameter
const urlParams = new URLSearchParams(window.location.search);
const cartDataString = urlParams.get('cartData');
const cart = JSON.parse(cartDataString);

// Fungsi untuk menampilkan produk yang ada di keranjang pada halaman checkout
function displayCart() {
    const cartList = document.querySelector('.list');
    cartList.innerHTML = ''; // Kosongkan daftar produk sebelum menampilkan produk baru

    let totalQuantity = 0;
    let totalPrice = 0;

    if (cart.length === 0) {
        cartList.innerHTML = '<p>Keranjang Anda kosong.</p>';
        return;
    }

    // Looping untuk menampilkan setiap produk di keranjang
    cart.forEach(product => {
        totalQuantity += product.quantity;
        totalPrice += product.price * product.quantity;

        cartList.innerHTML += `
            <div class="item">
                <img src="${product.image}" alt="${product.name}" width="100" height="70">
                <div class="info">
                    <div class="name">${product.name}</div>
                    <div class="price">Rp${product.price.toLocaleString()}/1 produk</div>
                </div>
                <div class="quantity">${product.quantity}</div>
                <div class="returnPrice">Rp${(product.price * product.quantity).toLocaleString()}</div>
            </div>
        `;
    });

    // Menampilkan total jumlah produk dan total harga
    document.querySelector('.totalQuantity').innerText = totalQuantity;
    document.querySelector('.totalPrice').innerText = `Rp${totalPrice.toLocaleString()}`;
}

// Panggil fungsi displayCart untuk menampilkan produk di keranjang
displayCart();