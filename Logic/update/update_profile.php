<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan'])) {
    die("Akses ditolak!");
}

$id = $_SESSION['user_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$repeat_password = $_POST['repeat_password'];
$about = $_POST['about'];

// Validasi password cocok
if ($password !== $repeat_password) {
    echo "<script>alert('Password tidak cocok!'); window.history.back();</script>";
    exit;
}

// Jika password tidak diubah, jangan update
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET username=?, email=?, password=?, about=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $about, $id);
} else {
    $sql = "UPDATE users SET username=?, email=?, about=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $about, $id);
}

if ($stmt->execute()) {
    // Perbarui session juga
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['about'] = $about;

    echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='../../pages/profile.php';</script>";
} else {
    echo "Gagal update data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
