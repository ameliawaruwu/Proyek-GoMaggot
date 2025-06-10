<?php
require_once __DIR__ . '/../update/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Normalisasi email
    $email = strtolower(trim($_POST['email']));
    $token = bin2hex(random_bytes(50)); // Panjang token = 100 karakter
    $otp = rand(100000, 999999); // OTP 6 digit

    // Cek apakah email ada
    $cek = mysqli_prepare($koneksi, "SELECT * FROM pengguna WHERE email = ?");
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    $hasil = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil) === 0) {
        echo "❌ Email tidak ditemukan di database.";
        exit;
    }

    // 🔄 Hapus token & OTP sebelumnya (hindari bentrok UNIQUE)
    $hapus = mysqli_prepare($koneksi, "UPDATE pengguna SET reset_token = NULL, reset_otp = NULL WHERE email = ?");
    mysqli_stmt_bind_param($hapus, "s", $email);
    if (!mysqli_stmt_execute($hapus)) {
        echo "❌ Gagal menghapus token dan OTP lama: " . mysqli_stmt_error($hapus);
        exit;
    }

    // Simpan token dan OTP baru
    $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET reset_token = ?, reset_otp = ? WHERE email = ?");
    if (!$stmt) {
        echo "❌ Prepare gagal: " . mysqli_error($koneksi);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sss", $token, $otp, $email);
    if (!mysqli_stmt_execute($stmt)) {
        echo "❌ Gagal menyimpan token dan OTP baru: " . mysqli_stmt_error($stmt);
        exit;
    }

    // Debugging: cek apakah update berhasil
    $query = "SELECT reset_token, reset_otp FROM pengguna WHERE email = ?";
    $stmt_check = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        echo "Token: " . $row['reset_token'] . "<br>";
        echo "OTP: " . $row['reset_otp'] . "<br>";
    } else {
        echo "❌ Gagal mengambil data token dan OTP.";
    }

    // ✅ Tampilkan hasil
    $reset_link = "http://localhost/Admingoma/pages/verify_otp.php?token=$token";
    echo "✅ Permintaan reset berhasil.<br>";
    echo "📨 Link reset password: <a href='$reset_link'>$reset_link</a><br>";
    echo "🔑 Kode OTP: <strong>$otp</strong><br>";
}
?>
