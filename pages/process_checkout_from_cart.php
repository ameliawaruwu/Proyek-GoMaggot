<?php
include '../Logic/update/auth.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); // Respon dalam bentuk JSON

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartItems = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON data received.';
        echo json_encode($response);
        exit;
    }

    $calculatedTotalHarga = 0;
    $calculatedTotalProduk = 0;
    $checkoutItems = [];

    if (!empty($cartItems)) {
        foreach ($cartItems as $item) {
            // Pastikan item memiliki properti yang diperlukan dan numerik
            if (isset($item['idproduk'], $item['namaproduk'], $item['harga'], $item['jumlah']) &&
                is_numeric($item['harga']) && is_numeric($item['jumlah']) && $item['jumlah'] > 0) {

                $subtotal = (float)$item['harga'] * (int)$item['jumlah'];
                $calculatedTotalHarga += $subtotal;
                $calculatedTotalProduk += (int)$item['jumlah'];

                // Siapkan item untuk session checkout
                $checkoutItems[] = [
                    'idproduk' => htmlspecialchars($item['idproduk']),
                    'namaproduk' => htmlspecialchars($item['namaproduk']),
                    'harga' => (float)$item['harga'],
                    'jumlah' => (int)$item['jumlah'],
                    'gambar' => htmlspecialchars($item['gambar'] ?? '') // Pastikan ada gambar, default ke kosong
                ];
            } else {
                 // Log error jika ada item yang tidak valid, tapi tetap lanjutkan proses
                error_log("Item keranjang tidak valid: " . json_encode($item));
            }
        }
    }

    // Simpan data ke session
    $_SESSION['checkout_items'] = $checkoutItems;
    $_SESSION['checkout_total_harga'] = $calculatedTotalHarga;
    $_SESSION['checkout_total_produk'] = $calculatedTotalProduk;

    $response['success'] = true;
    $response['message'] = 'Data checkout berhasil disimpan ke sesi.';
    $response['redirect'] = 'checkOut.php'; // Arahkan ke halaman checkout
    echo json_encode($response);

} else {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
}
?>