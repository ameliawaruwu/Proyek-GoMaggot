<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sertakan header Anda
include '../views/headerFormCo.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Online</title>
    <link rel="stylesheet" href="../Admin-HTML/css/CO.css">
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Sour+Gummy:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>

<div class="checkout-page-container">
    <div class="left-section">
        <a href="keranjang.php" class="back-to-cart">← Kembali ke keranjang</a>
        <h2>Keranjang Saya</h2>

        <div id="checkoutMessageArea" style="margin-bottom: 15px; text-align: center;"></div>

        <div class="list-checkout-items" id="listCheckoutItems">
            <p id="emptyCheckoutMessage" style="text-align: center; display: none;">Keranjang kosong. <a href="keranjang.php">Belanja sekarang</a></p>
        </div>

        <h1 class="total-summary-checkout">Total Harga: <span id="overallTotalPriceDisplayCheckout">Rp.0</span></h1>
    </div>

    <div class="right-section">
        <h3>Detail Pengiriman & Pembayaran</h3>
        <form id="checkoutForm"> 
            <div class="form-group">
                <label for="nama_penerima">Nama Penerima</label>
                <input type="text" id="nama_penerima" name="nama_penerima" required>
            </div>
            <div class="form-group">
                <label for="nomor_telepon">Nomor Telepon</label>
                <input type="text" id="nomor_telepon" name="nomor_telepon" required>
            </div>
            <div class="form-group">
                <label for="alamat_lengkap">Alamat Lengkap</label>
                <textarea id="alamat_lengkap" name="alamat_lengkap" rows="3" placeholder="Contoh: Jl. Merdeka No. 12, Kelurahan X, Kecamatan Y" required></textarea>
            </div>
            <div class="form-group">
                <label for="kota">Kota</label>
                <select id="kota" name="kota" required>
                    <option value="">Pilih Kota</option>
                    <option value="Bandung">Bandung</option>
                    <option value="Jakarta">Jakarta</option>
                    </select>
            </div>
            <div class="form-group">
                <label for="metode_pembayaran">Metode Pembayaran</label>
                <select id="metode_pembayaran" name="metode_pembayaran" required>
                    <option value="">-- Pilih Metode Pembayaran --</option>
                    <option value="Qris">Qris</option>
                    <option value="Tunai">Tunai (COD)</option>
                    </select>
            </div>

            <div class="summary-details">
                <div class="summary-row">
                    <span>Total Produk:</span>
                    <span id="totalProdukDisplay">0</span>
                </div>
                <div class="summary-row">
                    <span>Total Harga:</span>
                    <span id="totalHargaFormDisplay">Rp.0</span>
                    </div>
            </div>

            <button type="submit" class="checkout-submit-button" disabled>
                CHECKOUT
            </button>
        </form>
    </div>
</div>

<?php include '../views/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const listCheckoutItems = document.getElementById('listCheckoutItems');
    const overallTotalPriceDisplayCheckout = document.getElementById('overallTotalPriceDisplayCheckout');
    const totalProdukDisplay = document.getElementById('totalProdukDisplay');
    const totalHargaFormDisplay = document.getElementById('totalHargaFormDisplay');
    const emptyCheckoutMessage = document.getElementById('emptyCheckoutMessage');
    const checkoutSubmitButton = document.querySelector('.checkout-submit-button');
    const checkoutForm = document.getElementById('checkoutForm');
    const checkoutMessageArea = document.getElementById('checkoutMessageArea');

    let carts = []; // Akan diisi dari localStorage

    // Fungsi untuk menampilkan pesan (error/sukses)
    function displayMessage(message, type = 'error') {
        const messageDiv = document.createElement('div');
        messageDiv.textContent = message;
        messageDiv.style.padding = '10px';
        messageDiv.style.marginBottom = '15px';
        messageDiv.style.textAlign = 'center';
        messageDiv.style.borderRadius = '5px';

        if (type === 'error') {
            messageDiv.style.color = 'red';
            messageDiv.style.backgroundColor = '#ffe0e0';
            messageDiv.style.border = '1px solid red';
        } else if (type === 'success') {
            messageDiv.style.color = 'green';
            messageDiv.style.backgroundColor = '#e0ffe0';
            messageDiv.style.border = '1px solid green';
        }
        checkoutMessageArea.innerHTML = ''; // Hapus pesan sebelumnya
        checkoutMessageArea.appendChild(messageDiv);
    }

    // Fungsi untuk memuat dan merender item keranjang dari Local Storage
    function loadAndRenderCartItems() {
        const storedCart = localStorage.getItem('shoppingCart');
        if (storedCart) {
            carts = JSON.parse(storedCart);
        } else {
            carts = [];
        }

        listCheckoutItems.innerHTML = ''; // Bersihkan tampilan sebelumnya
        let totalQuantity = 0;
        let totalPrice = 0;

        if (carts.length > 0) {
            emptyCheckoutMessage.style.display = 'none';
            carts.forEach(item => {
                const quantity = parseInt(item.jumlah || 0);
                const price = parseFloat(item.harga || 0);
                const subtotal = quantity * price;

                totalQuantity += quantity;
                totalPrice += subtotal;

                let newDiv = document.createElement('div');
                newDiv.classList.add('checkout-item');
                newDiv.innerHTML = `
                    <img src="${item.gambar || ''}" alt="${item.namaproduk || 'Gambar Produk'}">
                    <div class="item-details">
                        <div class="item-name">${item.namaproduk || 'Nama Produk Tidak Diketahui'}</div>
                        <div class="item-quantity">× ${quantity}</div>
                    </div>
                    <div class="item-price">Rp.${(subtotal).toLocaleString('id-ID')}</div>
                `;
                listCheckoutItems.appendChild(newDiv);
            });
        } else {
            emptyCheckoutMessage.style.display = 'block';
        }

        overallTotalPriceDisplayCheckout.innerText = `Rp.${totalPrice.toLocaleString('id-ID')}`;
        totalProdukDisplay.innerText = totalQuantity;
        totalHargaFormDisplay.innerText = `Rp.${totalPrice.toLocaleString('id-ID')}`;
        
        // Aktifkan/nonaktifkan tombol checkout
        if (carts.length > 0 && totalQuantity > 0 && totalPrice > 0) {
            checkoutSubmitButton.removeAttribute('disabled');
        } else {
            checkoutSubmitButton.setAttribute('disabled', 'true');
        }
    }

    // Event listener untuk form submission (menggunakan AJAX)
    checkoutForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Mencegah submit form HTML biasa

        // Ambil data dari form
        const namaPenerima = document.getElementById('nama_penerima').value.trim();
        const nomorTelepon = document.getElementById('nomor_telepon').value.trim();
        const alamatLengkap = document.getElementById('alamat_lengkap').value.trim();
        const kota = document.getElementById('kota').value;
        const metodePembayaran = document.getElementById('metode_pembayaran').value;

        // Validasi JavaScript tambahan
        if (!namaPenerima || !nomorTelepon || !alamatLengkap || !kota || !metodePembayaran) {
            displayMessage('Mohon lengkapi semua data pengiriman dan pembayaran.', 'error');
            return; // Hentikan proses jika validasi gagal
        }

        if (carts.length === 0) {
            displayMessage('Keranjang Anda kosong. Tidak dapat melanjutkan checkout.', 'error');
            return;
        }

        // Siapkan data untuk dikirim ke server
        const checkoutData = {
            nama_penerima: namaPenerima,
            nomor_telepon: nomorTelepon,
            alamat_lengkap: alamatLengkap,
            kota: kota,
            metode_pembayaran: metodePembayaran,
            cartItems: carts // Kirim seluruh array keranjang dari localStorage
        };

        // Kirim permintaan AJAX ke process_checkout.php
        fetch('process_checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(checkoutData)
        })
        .then(response => {
            if (!response.ok) {
                // Tangani respons non-OK (misal, 500 Internal Server Error)
                return response.text().then(text => { throw new Error(text) });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayMessage(data.message, 'success');
                localStorage.removeItem('shoppingCart'); // Kosongkan keranjang di localStorage
                carts = []; // Kosongkan array di JS
                loadAndRenderCartItems(); // Perbarui tampilan keranjang di halaman checkout
                
                // Beri sedikit waktu untuk pesan terlihat, lalu redirect
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1500); // Redirect setelah 1.5 detik
            } else {
                displayMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error during checkout:', error);
            displayMessage('Terjadi kesalahan jaringan atau server saat memproses checkout. Silakan coba lagi.', 'error');
        });
    });

    // Panggil fungsi untuk memuat dan merender item keranjang saat halaman dimuat
    loadAndRenderCartItems();
});
</script>

</body>
</html>