<?php include '../views/header.php'; ?>
<?php
include '../configdb.php';
?>

<link rel="stylesheet" href="../Admin-HTML/css/halajakan.css">
<link rel="stylesheet" href="../Admin-HTML/css/footer.css">

<div id="image">
        <img src="../Admin-HTML/images/Logo Artikel Fix.png" alt="" width="500" height="400">
    </div>

    <div class="Sub-topik">
        <h2>Ayo Belajar!</h2>
        <ul>
            <li>
                <summary>Mengenal Lebih Jauh Apa itu Maggot BSF</summary>
                <details>
                    <p>Maggot merupakan larva dari jenis lalat Black Soldier Fly (BSF) sehingga sering disebut maggot BSF.
                    Lalat BSF sendiri memiliki nama latin Heremetia illucens. Bentuknya mirip ulat, dengan ukuran 
                    larva dewasa 15-22 mm dan berwarna coklat. Siklus hidup lalat BSF kurang lebih selama 40-43 hari. 
                    Larva/maggot BSF bertahan selama 14-18 hari sebelum bermetamorfosis menjadi pupa dan lalat dewasa.</p>
                </details>
            </li>
            <br>
            <li>
                <summary>Mengetahui Manfaat Budidaya Maggot BSF</summary>
                <details>
                    <p>Pengelola Sampah Organik, Pakan Ternak, Pupuk Organik</p>
                </details>
            </li>
            <br>
            <li>
                <summary>Melakukan Pelestarian Maggot BSF dengan Pembudidayaan</summary>
                <details>
                    <p>Proses budidaya maggot dimulai dengan pemilihan telur yang berkualitas. 
                    Telur-telur tersebut kemudian ditempatkan di dalam kandang yang telah disiapkan. 
                    Setelah menetas, larva maggot diberi pakan berupa limbah organik seperti sisa sayuran dan buah-buahan.</p>
                </details>
            </li>
            <br>
            <br>
            <a href="halgaleri.php" class="button">
                <span>Kunjungi Gallery kami</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1); transform: ; msFilter:;">
                    <path d="M20 2H6c-1.206 0-3 .799-3 3v14c0 2.201 1.794 3 3 3h15v-2H6.012C5.55 19.988 5 19.806 5 19s.55-.988 1.012-1H21V3a1 1 0 0 0-1-1zM9.503 5a1.503 1.503 0 1 1 0 3.006 1.503 1.503 0 0 1 0-3.006zM12 13H7l3-3 1.5 1.399L14.5 8l3.5 5h-6z"></path>
                </svg>
            </a>
            
        </ul>
    </div>

    <h3 style="text-align: center;">Artikel Kami</h3>

    <div class="gallery-container">
        <div class="gallery">
            <img src="../Admin-HTML/images/maggot.jpg" alt="Maggot BSF">
            <div class="desc">Mengenal Lebih Dalam Maggot BSF</div><br>
            <a href="artikelsatu.php" class="button">Pelajari Lebih Lanjut</a>
        </div>
    
    
        <div class="gallery">
            <img src="../Admin-HTML/images/maggot kompos.jpg" alt="Manfaat Maggot">
            <div class="desc">Manfaat Maggot Dalam Segi Kehidupan</div><br>
            <a href="artikeltiga.php" class="button">Pelajari Lebih Lanjut</a>
        </div>
    
        <div class="gallery">
            <img src="../Admin-HTML/images/ternak maggot.jpeg" alt="Budidaya Maggot">
            <div class="desc">Melakukan Budidaya Maggot Dengan Ternak Sederhana</div>
            <a href="artikelsatu.php" class="button">Pelajari Lebih Lanjut</a>
        </div>
        <div class="gallery">
        <img src="../Admin-HTML/images/maggot.jpg" alt="Maggot BSF">
        <div class="desc">Mengenal Lebih Dalam Maggot BSF</div><br>
        <a href="artikeldua.php" class="button">Pelajari Lebih Lanjut</a>
    </div>

    
<!--
    <div class="gallery">
        <img src="../Admin-HTML/images/maggot kompos.jpg" alt="Manfaat Maggot">
        <div class="desc">Manfaat Maggot Untuk Segi Kehidupan</div><br>
        <a href="artikeltiga.php" class="button">Pelajari Lebih Lanjut</a>
    </div>
    <div class="gallery">
        <img src="../Admin-HTML/images/ternak maggot.jpeg" alt="Budidaya Maggot">
        <div class="desc">Melakukan Budidaya Maggot Dengan Ternak Sederhana</div>
        <a href="artikelsatu.php" class="button">Pelajari Lebih Lanjut</a>
    </div> -->
    </div>
    <script src="..\Admin-HTML\js\script.js"></script>

    <?php include '../partials/footer.php'; ?>