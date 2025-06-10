<?php
include '../Logic/update/auth.php';
include '../configdb.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    
    // Anonimisasi + Soft delete user
    $anon_username = 'Anonim_User_' . $userId;
    $anon_email = 'anonim.user.' . $userId . '@example.com';
    
    $sql = "UPDATE pengguna SET 
            username = ?, 
            email = ?, 
            password = NULL, 
            alamat = NULL, 
            nomor_telepon = NULL, 
            foto_profil = NULL,
            is_deleted = 1, 
            delete_at = NOW() 
            WHERE id_pelanggan = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $anon_username, $anon_email, $userId);
    
    if ($stmt->execute()) {
        echo "<script>alert('User berhasil dianonimkan dan dihapus!'); window.location.href = 'user.php';</script>";
    } else {
        echo "<script>alert('Gagal menganonimkan user!'); window.location.href = 'user.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('ID tidak ditemukan!'); window.location.href = 'user.php';</script>";
}
?>