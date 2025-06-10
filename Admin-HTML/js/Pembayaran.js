// script.js - simple client-side enhancements for form pembayaran

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('paymentForm');
    const qrInput = document.getElementById('qr_code');
    const phoneInput = document.getElementById('phone');
    const paymentProofInput = document.getElementById('payment_proof');

    form.addEventListener('submit', (e) => {
        // Basic validation example (HTML5 validation already covers required)
        if (!qrInput.value.trim()) {
            alert('Kolom Scan QR Code harus diisi.');
            qrInput.focus();
            e.preventDefault();
            return;
        }
        if (!phoneInput.checkValidity()) {
            alert('Nomor telepon tidak valid.');
            phoneInput.focus();
            e.preventDefault();
            return;
        }
        if (paymentProofInput.files.length === 0) {
            alert('Anda harus mengunggah bukti pembayaran.');
            paymentProofInput.focus();
            e.preventDefault();
            return;
        }
        // Optional: Check file extension on client side as extra protection
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        const file = paymentProofInput.files[0];
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExt)) {
            alert('Format file bukti pembayaran harus JPG, PNG, atau PDF.');
            paymentProofInput.focus();
            e.preventDefault();
            return;
        }
    });
});
