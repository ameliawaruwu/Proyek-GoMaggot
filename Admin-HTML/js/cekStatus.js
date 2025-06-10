// js/script_cekstatus.js

document.addEventListener('DOMContentLoaded', function() {
    // Anda bisa menambahkan JavaScript di sini untuk efek dinamis.
    // Misalnya, animasi saat halaman dimuat, atau tooltip.

    // Contoh: Log status pesanan ke konsol browser
    const statusElement = document.querySelector('.current-status .status-text');
    if (statusElement) {
        console.log('Status Pesanan:', statusElement.textContent);
    }

    // Tidak ada fungsionalitas kompleks yang dibutuhkan untuk saat ini,
    // karena semua status ditentukan oleh PHP.
});