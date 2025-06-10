<?php
include '../Logic/update/auth.php';
session_start();
include '../views/header.php'; // Sertakan header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil</title>
    <link rel="stylesheet" href="../Admin-HTML/css/style.css"> <style>
        .success-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-container h2 {
            color: #28a745;
            margin-bottom: 20px;
            font-size: 2em;
        }
        .success-container p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 15px;
        }
        .success-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: rgb(121, 185, 0);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .success-container a:hover {
            background-color: rgb(0, 128, 0);
        }
        .error-message {
            color: #dc3545;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <h2>Terima Kasih!</h2>
            <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php elseif (isset($_SESSION['error_message'])): ?>
            <h2>Terjadi Kesalahan</h2>
            <p class="error-message"><?= htmlspecialchars($_SESSION['error_message']) ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php else: ?>
            <h2>Status Pesanan</h2>
            <p>Tidak ada informasi pesanan yang tersedia.</p>
        <?php endif; ?>
        <a href="home.php">Kembali ke Beranda</a>
    </div>
    <?php include '../views/footer.php'; // Sertakan footer ?>
</body>
</html>