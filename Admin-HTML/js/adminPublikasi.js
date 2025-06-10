// admin/artikel/js/script_artikel.js
document.addEventListener('DOMContentLoaded', function() {
    // Image preview
    const productImageInput = document.getElementById('gambarArtikel');
    const imagePreviewContainer = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');

    if (productImageInput && imagePreviewContainer && previewImage) {
        productImageInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    imagePreviewContainer.classList.add('active');
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreviewContainer.classList.remove('active');
                previewImage.src = ''; // Clear image if no file selected
            }
        });
    }

    // Form validation (contoh sederhana, bisa dikembangkan)
    const articleForm = document.getElementById('articleForm');
    if (articleForm) {
        articleForm.addEventListener('submit', function(e) {
            const judul = document.getElementById('judul').value.trim();
            const konten = document.getElementById('konten').value.trim();
            const penulis = document.getElementById('penulis').value.trim();
            let errorDiv = document.querySelector('.error-message-js'); // Use a specific class for JS errors

            // Remove existing JS error message
            if (errorDiv) {
                errorDiv.remove();
            }

            let errorMessageText = '';

            if (!judul) {
                errorMessageText = 'Judul artikel tidak boleh kosong.';
            } else if (!konten) {
                errorMessageText = 'Konten artikel tidak boleh kosong.';
            } else if (!penulis) {
                errorMessageText = 'Nama penulis tidak boleh kosong.';
            }

            if (errorMessageText) {
                e.preventDefault();
                errorDiv = document.createElement('div');
                errorDiv.className = 'error-message error-message-js'; // Add specific class
                errorDiv.innerHTML = `<i class='bx bx-error-circle'></i> ${errorMessageText}`;
                
                const formTitle = document.querySelector('.form-title');
                if (formTitle) {
                    formTitle.insertAdjacentElement('afterend', errorDiv);
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    articleForm.prepend(errorDiv); // Fallback if form-title is not found
                }
            }
        });
    }

    // Cancel button
    const btnCancel = document.getElementById('btnCancel');
    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            window.location.href = 'daftar_artikel.php';
        });
    }

    // Toast notification logic
    const toast = document.getElementById('toast-notification');
    if (toast) {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        let message = '';
        let type = '';

        if (status === 'added') {
            message = 'Artikel berhasil ditambahkan!';
            type = 'success';
        } else if (status === 'updated') {
            message = 'Artikel berhasil diperbarui!';
            type = 'success';
        } else if (status === 'deleted') {
            message = 'Artikel berhasil dihapus!';
            type = 'success';
        } else if (status === 'error') {
            message = 'Terjadi kesalahan!';
            type = 'error';
        } else if (status === 'fileerror') {
            message = 'Gagal mengupload gambar. Pastikan format dan ukuran sesuai.';
            type = 'error';
        } else if (status === 'adderror') {
            message = 'Gagal menambahkan artikel. Coba lagi.';
            type = 'error';
        } else if (status === 'updateerror') {
            message = 'Gagal memperbarui artikel. Coba lagi.';
            type = 'error';
        } else if (status === 'deleteerror') {
            message = 'Gagal menghapus artikel. Coba lagi.';
            type = 'error';
        }


        if (message) {
            showToast(message, type);
            // Clean URL (remove status parameter)
            if (history.replaceState) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                history.replaceState({ path: cleanUrl }, '', cleanUrl);
            }
        }
    }
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-notification');
    const toastMessage = document.getElementById('toast-message');
    const toastIcon = document.getElementById('toast-icon');
    
    if (!toast || !toastMessage || !toastIcon) return;

    toastMessage.textContent = message;
    toast.className = 'toast-notification'; // Reset classes

    if (type === 'success') {
        toast.classList.add('toast-success');
        toastIcon.className = 'bx bx-check-circle toast-icon';
    } else if (type === 'error') {
        toast.classList.add('toast-error');
        toastIcon.className = 'bx bx-error-circle toast-icon';
    }
    
    toast.classList.add('show');
    
    setTimeout(function() {
        closeToast();
    }, 5000);
}

function closeToast() {
    const toast = document.getElementById('toast-notification');
    if (toast) {
       toast.classList.remove('show');
    }
}

// Confirmation for delete
function confirmDelete(idArtikel) {
    if (confirm("Apakah Anda yakin ingin menghapus artikel ini?")) {
        window.location.href = 'hapus_artikel.php?id=' + idArtikel;
    }
    return false; // Prevent default link behavior if used in an <a> tag's onclick
}