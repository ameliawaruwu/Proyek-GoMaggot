<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $cartData = json_decode($input, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($cartData)) {
        // Logika opsional: Anda bisa memeriksa apakah setiap item memiliki kunci yang diharapkan
        // Ini adalah langkah validasi tambahan jika Anda ingin lebih ketat
        $isValidCart = true;
        foreach ($cartData as $index => $item) {
            // Memeriksa apakah kunci 'gambar', 'namaproduk', 'harga', dan 'jumlah' ada
            if (!isset($item['gambar']) || !isset($item['namaproduk']) || !isset($item['harga']) || !isset($item['jumlah'])) {
                error_log("Invalid item data in cartData at index " . $index . ": Missing 'gambar', 'namaproduk', 'harga', or 'jumlah'.");
                $isValidCart = false; // Set flag ke false jika ada item yang tidak lengkap
                // Opsional: Anda bisa menghapus item yang tidak valid atau menghentikan proses
            }
        }

        if ($isValidCart) {
            $_SESSION['checkout_items'] = $cartData; // Simpan seluruh array cart ke sesi
            echo json_encode(['success' => true, 'message' => 'Data keranjang berhasil disimpan ke sesi untuk checkout.']);
        } else {
            // Jika ada item yang tidak lengkap, berikan pesan error yang lebih spesifik
            echo json_encode(['success' => false, 'message' => 'Beberapa item keranjang tidak memiliki data yang lengkap (gambar/nama produk).']);
        }

    } else {
        error_log("Invalid cart data received or JSON decoding failed in save_checkout.php. Input: " . $input);
        echo json_encode(['success' => false, 'message' => 'Data keranjang tidak valid atau gagal didekode.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak valid. Hanya POST yang diizinkan.']);
}
?>