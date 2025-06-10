document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle
    const sidebar = document.getElementById('sidebar'); // Pastikan ID ini ada di HTML sidebar Anda
    const menuBtn = document.getElementById('menu-btn'); // Pastikan ID ini ada di tombol menu Anda

    if (sidebar && menuBtn) {
        menuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('hide'); // Toggle class 'hide' untuk menyembunyikan/menampilkan
        });
    } else {
        console.error("Elemen sidebar atau menu-btn tidak ditemukan. Pastikan ID HTML sudah benar.");
    }

    // 2. Active Link di Sidebar
    const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

    allSideMenu.forEach(item => {
        item.addEventListener('click', function() {
            // Hapus 'active' dari semua item lain
            allSideMenu.forEach(i => {
                i.parentElement.classList.remove('active');
            });
            // Tambahkan 'active' ke parent (li) dari item yang diklik
            this.parentElement.classList.add('active');
        });
    });

    // 3. Adaptasi Responsif Otomatis untuk Sidebar
    // Menyembunyikan sidebar di layar kecil secara default dan menampilkannya kembali di layar besar
    function handleScreenSize() {
        if (window.innerWidth < 768) { // Contoh breakpoint untuk mobile
            if (sidebar && !sidebar.classList.contains('hide')) {
                sidebar.classList.add('hide'); // Sembunyikan sidebar di mobile jika belum
            }
        } else {
            if (sidebar && sidebar.classList.contains('hide')) {
                sidebar.classList.remove('hide'); // Tampilkan sidebar di desktop jika tersembunyi
            }
        }
    }

    // Panggil saat halaman dimuat
    handleScreenSize();
    // Panggil saat ukuran jendela berubah
    window.addEventListener('resize', handleScreenSize);

    // DEBUGGING: Untuk membantu melacak apakah script berjalan
    console.log("JavaScript untuk dashboard admin telah dimuat.");
});