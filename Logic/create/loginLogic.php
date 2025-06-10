<?php
session_start(); //untuk memulai atau melanjutkan sesi pengguna (user session)
include __DIR__ . '/../update/koneksi.php'; // Menyertakan koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Menyiapkan query untuk mencari pengguna berdasarkan email
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pengguna WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Modifikasi bagian verifikasi password
    if ($row = mysqli_fetch_assoc($result)) {
        // Untuk admin, terima password plain text atau hash
        if ($row['role'] === 'admin' && ($password === $row['password'] || password_verify($password, $row['password']))) {
            // Login berhasil untuk admin (mendukung password plain dan hash)
            $_SESSION['id_pelanggan'] = $row['id_pelanggan'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            header("Location: ../../pages/dashboard.php"); 
            exit;
        } 
        // Untuk konsumen, tetap pakai verifikasi normal
        elseif ($row['role'] === 'konsumen' && password_verify($password, $row['password'])) {
            $_SESSION['id_pelanggan'] = $row['id_pelanggan'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            header("Location: ../../pages/home.php"); 
            exit;
        } 
        else {
            echo "❌ PASSWORD SALAH!";
            exit;
        }
    } else {
        echo "❌ EMAIL TIDAK DITEMUKAN!";
        exit;
    }
}
?>