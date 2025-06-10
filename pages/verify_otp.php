<?php
require_once __DIR__ . '/../logic/update/koneksi.php';
include '../partials/headers.php';

$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = $_POST['otp'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pengguna WHERE reset_token = ? AND reset_otp = ?");
    mysqli_stmt_bind_param($stmt, "ss", $token, $otp_input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_fetch_assoc($result)) {
        // OTP cocok → lanjut ke reset password
        header("Location: reset_password.php?token=$token");
        exit;
    } else {
        $error = "❌ Kode verifikasi salah.";
    }
}
?>

<link rel="stylesheet" href="../Admin-HTML/css/reset.css">

<div class="wrapper">
  <div class="form-box login">
    <h2>Verifikasi OTP</h2>
    <?php if (!empty($error)) echo "<div class='alert-error'>$error</div>"; ?>
    <form method="POST">
      <div class="input-box">
        <input type="text" name="otp" required placeholder="Masukkan kode OTP">
      </div>
      <button type="submit" class="btn">Verifikasi</button>
    </form>
    <div class="login-register">
      <p><a href="login.php">Kembali ke Login</a></p>
    </div>
  </div>
</div>



