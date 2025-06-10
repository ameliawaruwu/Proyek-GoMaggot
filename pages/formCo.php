<?php include '../views/headerFormCo.php'; ?>
  <link rel="stylesheet" href="../Admin-HTML/css/formCo.css">

<div class="container">
<form action="terimakasih.php" method="POST">

            <div class="row">
                <div class="column">
                    <h3 class="title">Form Pembayaran</h3>
                    <div class="input-box">
                        <span>Nama Lengkap :</span>
                        <input type="text" placeholder="Nama Lengkap">
                    </div>
                    <div class="input-box">
                        <span>Email :</span>
                        <input type="email" placeholder="contoh@gmail.com">
                    </div>
                    <div class="input-box">
                        <span>Alamat :</span>
                        <input type="text" placeholder="Kopo Permai">
                    </div>
                    <div class="input-box">
                        <span>No Telepon :</span>
                        <input type="text" placeholder="0129382461">
                    </div>

                    <div class="flex">
                        <div class="input-box">
                            <span>Kota :</span>
                            <input type="text" placeholder="Bandung">
                        </div>
                        <div class="input-box">
                            <span>Kode Pos :</span>
                            <input type="number" placeholder="0329">
                        </div>
                    </div>
                </div>

                <div class="column">
                    <h3 class="title">Payment</h3>
                    <div class="input-box">
                        <span>Kartu Kredit :</span>
                        <img src="../Admin-HTML-HTML/images/imgcards.png" alt="">
                    </div>
                    <div class="input-box">
                        <span>Nama Pada Kartu :</span>
                        <input type="text" placeholder="taylorswift">
                    </div>
                    <div class="input-box">
                        <span>Nomor Kartu Kredit :</span>
                        <input type="number" placeholder="111 222 333">
                    </div>
                    <div class="input-box">
                        <span>Bulan Exp :</span>
                        <input type="text" placeholder="Agustus">
                    </div>

                    <div class="flex">
                        <div class="input-box">
                            <span>Tahun Exp :</span>
                            <input type="number" placeholder="2026">
                        </div>
                        <div class="input-box">
                            <span>CVV</span>
                            <input type="number" placeholder="123">
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn">Konfirmasi Pembayaran</button>

        </form>
    </div>

    <?php include '../views/footer.php'; ?>
    <script src="../Admin-HTML-HTML/js/formCo.js"></script>
