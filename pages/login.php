<?php include '../partials/headers.php';?>
<link rel="stylesheet" href="../Admin-HTML/css/login.css">

<!-- LOGIN SECTION -->
<form action="../logic/create/loginLogic.php" method="POST">
    <div class="wrapper">
        <div class="image-container">
            <img src="../Admin-HTML/images/foto login.jpg" alt="Login Illustration"> 
        </div>
        <div class="form-box login">
            <h2>Login</h2>
            
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
                <label><input type="checkbox">Remember me</label>
                <a href="forgot_password.php">Forgot Password</a>
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="login-register">
                <p>Don't have an account? <a href="register.php" class="register-link">Register</a></p>
            </div>
        </div>
    </div>
</form>

<script src="../Admin-HTML/js/script.css"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
