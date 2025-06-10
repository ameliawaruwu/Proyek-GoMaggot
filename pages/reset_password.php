<?php
require_once __DIR__ . '/../logic/update/koneksi.php';
include '../partials/headers.php'; 
?>

<link rel="stylesheet" href="../Admin-HTML/css/reset.css">

<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek apakah token valid dan ambil data reset_otp & reset_token
    $stmt = mysqli_prepare($koneksi, "SELECT reset_otp, reset_token FROM pengguna WHERE reset_token = ?");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Jika token valid dan form dikirim
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Simpan otp dan token sebelum dihapus
            $otp = $row['reset_otp'];
            $token_db = $row['reset_token'];

            // Update password dan hapus token + otp
            $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET password = ?, reset_token = NULL, reset_otp = NULL WHERE reset_token = ?");
            mysqli_stmt_bind_param($stmt, "ss", $new_password, $token_db);

            if (mysqli_stmt_execute($stmt)) {
                $affected_rows = mysqli_stmt_affected_rows($stmt);
                if ($affected_rows > 0) {
                    echo '
                    <div class="wrapper">
                      <div class="form-box">
                        <div class="alert-success">
                          ✅ Password berhasil direset. Silakan <a href="login.php">login</a>.<br>
                          🔑 OTP yang digunakan: <strong>' . htmlspecialchars($otp) . '</strong><br>
                        </div>
                      </div>
                    </div>';
                    exit;
                } else {
                    echo '
                    <div class="wrapper">
                      <div class="form-box">
                        <div class="alert-warning">
                          ⚠️ Token valid, tapi tidak ada perubahan data. Mungkin sudah dipakai sebelumnya.
                        </div>
                      </div>
                    </div>';
                }
            } else {
                echo '
                <div class="wrapper">
                  <div class="form-box">
                    <div class="alert-error">
                      ❌ Terjadi kesalahan saat menyimpan password. Silakan coba lagi.
                    </div>
                  </div>
                </div>';
            }
        }
?>

<!-- Form reset password -->
<form method="POST">
    <div class="wrapper">
        <div class="image-container">
            <img src="../Admin-HTML/images/foto login.jpg" alt="Login Illustration"> 
        </div>
        <div class="form-box login">
            <h2>Reset Password</h2>
            <div class="input-box">
                <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                <input type="password" name="password" required placeholder="Masukkan password baru">
            </div>
            <button type="submit" class="btn">Reset Password</button>
            <div class="login-register">
                <p><a href="login.php">Kembali ke Login</a></p>
            </div>
        </div>
    </div>
</form>

<?php
    } else {
        // Token tidak ditemukan di database
        echo '
        <div class="wrapper">
          <div class="form-box">
            <div class="alert-error">
              ❌ Link tidak valid atau sudah digunakan. Silakan minta reset ulang.
            </div>
          </div>
        </div>';
    }
} else {
    // Token tidak tersedia di URL
    echo '
    <div class="wrapper">
      <div class="form-box">
        <div class="alert-warning">
          ⚠️ Link tidak lengkap. Token tidak ditemukan di URL.
        </div>
      </div>
    </div>';
}
?>
