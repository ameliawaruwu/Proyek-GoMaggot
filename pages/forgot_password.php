<?php include '../partials/headers.php'; ?>
<link rel="stylesheet" href="../Admin-HTML/css/login.css">
<div class="wrapper">
<div class="image-container">
            <img src="../Admin-HTML/images/foto login.jpg" alt="Login Illustration"> 
        </div>
    <div class="form-box login">
        <h2>Lupa Password</h2>
        <form action="../logic/update/send_reset_link.php" method="POST">
            <div class="input-box">
                <span class="icon"><ion-icon name="mail"></ion-icon></span>
                <input type="email" name="email" required>
                <label>Email terdaftar</label>
            </div>
            <button type="submit" class="btn">Kirim Link Reset</button>
        </form>
        <div class="login-register">
            <p><a href="login.php">Kembali ke Login</a></p>
        </div>
    </div>
</div>
