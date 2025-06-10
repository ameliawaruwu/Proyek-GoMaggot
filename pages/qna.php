<?php include '../partials/headers.php'; ?>
<?php include '../logic/update/auth.php'; ?>

<link rel="stylesheet" href="/MAGGOT/css/qna.css">
<link rel="stylesheet" href="/MAGGOT/css/footer.css">


<!-- QNA SECTION-->
    <h1>Frequently Asked Question</h1>

    <div class="container">
        <div class="faq active">
            <h3 class="fas-tittle">Apakah maggot aman digunakan?</h3>
            <p class="faq-text">
                Maggot dapat digunakan sebagai alternatif pengurai sampah, dan aman untuk
                pakan ternak karena bukan termasuk lalat penyebar penyakit.
            </p>

            <button class="faq-toggle">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-times"></i>
            </button>
            </div>
            
            <div class="faq active">
            <h3 class="fas-tittle">Apakah pembayaran dapat COD?</h3>
            <p class="faq-text">
                Pembayaran tidak dapat dilakukan dengan COD.
            </p>

            <button class="faq-toggle">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-times"></i>
            </button>
        </div>
            
            <div class="faq active">
            <h3 class="fas-tittle">Di manakah masyarakat dapat memelihara maggot?</h3>
            <p class="faq-text">
                Untuk pemula bisa lakukan ternak di ruang lingkup kecil 
                terlebih dahulu,  salah satunya di belakang rumah.
            </p>
            
            <button class="faq-toggle">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="faq active">
            <h3 class="fas-tittle">Apakah maggot yang dijual disini terjamin kualitasnya?</h3>
            <p class="faq-text">
                Tentu, sudah banyak review positif dari beberapa pelanggan kami.
            </p>
            
            <button class="faq-toggle">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-times"></i>
            </button>
        </div>
       
        <div class="faq active">
            <h3 class="fas-tittle">Apakah ada fitur pick up untuk mengantarkan maggot ke alamat tujuan?</h3>
            <p class="faq-text">
                Ya, kami menyediakan fitur antar pesanan ke alamat pelanggan. Agar pelanggan tidak
                perlu datang jauh-jauh ke sini.
            </p>
            
            <button class="faq-toggle">
                <i class="fas fa-chevron-down"></i>
                <i class="fas fa-times"></i>
            </button>
        </div>
        <br>
        <br>
        <form>

        <div class="isibox">
        <details>
            <p>Jika ada pertanyaan lain, silahkan kirim pertanyaanya di bawah ini.</p>
            <summary>Ada Pertanyaan?</summary>
        </details>
        <div class="Questionbox">
            <table>
                <tr>
                    <td>Question Box</td>
                </tr>
                <tr>
                    <td><input type="text" name="box" id="box"></td>
                </tr>

                <tr>
                    <td><input type="button" value="Batal"></td> 
                    <td><input type="button" value="Kirim"></td>
                </tr>
            </table>
        </form>
    </div>
</div>

    <br>
    <br>

    <script src="QNA.js"></script>
    <?php include '../partials/footer.php'; ?>