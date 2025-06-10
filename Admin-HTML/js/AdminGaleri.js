// js/editgaleri.js

document.addEventListener('DOMContentLoaded', function() {
    const messageElement = document.querySelector('.form-message');
    if (messageElement) {
        setTimeout(() => {
            messageElement.style.opacity = '0';
            messageElement.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => messageElement.remove(), 500);
        }, 3000);
    }

    // Optional: Preview gambar baru saat diupload
    const inputGambar = document.getElementById('gambar');
    const currentImagePreview = document.querySelector('.current-image-preview img');
    if (inputGambar && currentImagePreview) {
        inputGambar.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    currentImagePreview.src = e.target.result; // Ganti gambar yang sedang ditampilkan
                };
                reader.readAsDataURL(file);
            }
        });
    }
});



// ADD GALERI 
// js/addgaleri.js

document.addEventListener('DOMContentLoaded', function() {
    const messageElement = document.querySelector('.form-message');
    if (messageElement) {
        setTimeout(() => {
            messageElement.style.opacity = '0';
            messageElement.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => messageElement.remove(), 500);
        }, 3000);
    }

    // Optional: Preview gambar sebelum diupload
    const inputGambar = document.getElementById('gambar');
    if (inputGambar) {
        inputGambar.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Anda bisa menambahkan elemen img di suatu tempat di form untuk preview
                    // Misalnya: <div id="image-preview-container"></div>
                    // const previewContainer = document.getElementById('image-preview-container');
                    // if (!previewContainer.querySelector('img')) {
                    //     const img = document.createElement('img');
                    //     img.style.maxWidth = '100px';
                    //     img.style.maxHeight = '100px';
                    //     img.style.marginTop = '10px';
                    //     previewContainer.appendChild(img);
                    // }
                    // previewContainer.querySelector('img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});