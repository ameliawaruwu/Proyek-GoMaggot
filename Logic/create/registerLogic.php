<?php
include __DIR__.'/../update/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { //kirim lewat post 
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = 'konsumen';

    // Enkripsi password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); //algoritma terbaik

    // query INSERT dengan role
    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO pengguna (username, email, password, role) 
        VALUES (?, ?, ?, ?)  
    ");     // sebagai parameter bind buat ngehindari sql injection

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);
        mysqli_stmt_execute($stmt);

        
        header('Location: ../pages/login.php'); 
    exit;
    }
}
?>