<?php 
include '../logic/update/koneksi.php';
include '../partials/headers.php'; 



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek apakah email sudah terdaftar
    $cekEmail = mysqli_prepare($koneksi, "SELECT id_pelanggan FROM pengguna WHERE email = ?"); //dieksekusi pada database dan bind
    mysqli_stmt_bind_param($cekEmail, "s", $email); //mengikat parameter
    mysqli_stmt_execute($cekEmail); //eksekusi
    mysqli_stmt_store_result($cekEmail); //hasil

    if (mysqli_stmt_num_rows($cekEmail) > 0) { //ditolak jika email sudah ada.
        echo "<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.location.href='register.php';</script>";
    } else { 

        $stmt = mysqli_prepare($koneksi, "
            INSERT INTO pengguna (username, email, password) VALUES (?, ?, ?)
        ");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPassword);
        mysqli_stmt_execute($stmt);

        header('Location: login.php');
        exit;
    }
}
?>




<link rel="stylesheet" href="../Admin-HTML/css/register.css">

<!--REGISTER SECTION -->
<div class="wrapper-register">
    <div class="image-container">
         <img src="../Admin-HTML/images/foto login.jpg" alt="Login Illustration"> 
    </div>
    <span class="icon-close">
        <ion-icon name="close"></ion-icon>
    </span> 
    <div class="form-box register">
        <h2>Registration</h2>
        <form action="" method="POST">
            <div class="input-box">
                <span class="icon">
                   <ion-icon name="person"></ion-icon></span>
                <input type="text" name="username" required>
                <label>Username</label>
           </div>
            <div class="input-box">
                 <span class="icon">
                    <ion-icon name="mail"></ion-icon></span>
                 <input type="email" name="email" required>
                 <label>Email</label>
            </div>
            <div class="input-box">
                 <span class="icon">
                    <ion-icon name="lock-closed"></ion-icon></span>
                 <input type="password" name="password" required>
                 <label>Password</label>
            </div>
            <div class="remember-forgot">
                 <label><input type="checkbox" required>I agree to the terms & conditions</label>
            </div>
                 <button type="submit" class="btn">Register</button>
            <div class="login-register">
                <p>Already have an account? <a href="login.php" class="register-link">Login</a></p>
            </div>
        </form>
    </div>
</div>

<script src="../js/script.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
