<?php
// Ini buat ngecek, cuma admin yang boleh akses halaman ini ya!
include '../logic/update/auth.php';
// Panggil bagian kepala admin (header, navigasi samping, dll.)
include '../views/headeradmin.php';
// Sambungin ke database kita dong
include '../configdb.php';
// Atur zona waktu ke Jakarta biar jamnya pas (WIB)
date_default_timezone_set('Asia/Jakarta');


// Kalau koneksi database-nya aman, kita samain juga zona waktu di database-nya
if ($conn) {
    mysqli_query($conn, "SET time_zone = '+07:00'"); // +07:00 itu buat WIB
}

// Siapin dulu nih variabel-variabel buat nampung data statistik, biar gak error kalau kosong
$newOrdersCount = 0; // Nanti ini bakal jadi jumlah pengunjung hari ini
$visitorsCount = 0; // Ini buat total semua user yang daftar
$totalSalesAmount = 0; // Ini buat total omset penjualan kita

// --- Oke, kita mulai dari jumlah pengunjung hari ini! ---
// Kita cek nih, berapa orang yang login hari ini (berdasarkan 'last_login' mereka)
$today = date('Y-m-d'); // Ambil tanggal hari ini (misal: 2025-06-10)
$sqlDailyVisitorsCard = "SELECT COUNT(DISTINCT id_pelanggan) AS daily_visitors FROM pengguna WHERE DATE(last_login) = '$today'";
$resultDailyVisitorsCard = mysqli_query($conn, $sqlDailyVisitorsCard);

if ($resultDailyVisitorsCard) {
    $rowDailyVisitorsCard = mysqli_fetch_assoc($resultDailyVisitorsCard);
    $newOrdersCount = $rowDailyVisitorsCard['daily_visitors']; // Isi deh kartu "Daily Visitors" di dashboard
} else {
    // Kalau gagal ambil data, diem-diem aja catat di log, jangan bikin halaman error
    // error_log("Duh, gagal ngambil data pengunjung harian buat kartu nih: " . mysqli_error($conn));
}

// --- Lanjut ke total semua pengguna yang sudah daftar di website kita ---
$sqlTotalRegisteredUsers = "SELECT COUNT(*) AS total_visitors FROM pengguna";
$resultTotalRegisteredUsers = mysqli_query($conn, $sqlTotalRegisteredUsers);

if ($resultTotalRegisteredUsers) {
    $rowTotalRegisteredUsers = mysqli_fetch_assoc($resultTotalRegisteredUsers);
    $visitorsCount = $rowTotalRegisteredUsers['total_visitors']; // Isi kartu "Total Registered Users"
} else {
    // error_log("Waduh, gagal ngambil data total user terdaftar: " . mysqli_error($conn));
}

// --- Nah, yang ini buat ngitung total penjualan kita bulan ini! ---
$thisMonth = date('Y-m'); // Contoh: '2025-06'
$sqlTotalSales = "SELECT SUM(total_harga) AS total_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$thisMonth' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')"; // Penting: cuma yang statusnya 'Sudah Sampai' yang dihitung sebagai penjualan
$resultTotalSales = mysqli_query($conn, $sqlTotalSales);

if ($resultTotalSales) {
    $rowTotalSales = mysqli_fetch_assoc($resultTotalSales);
    $totalSalesAmount = $rowTotalSales['total_sales'] ? $rowTotalSales['total_sales'] : 0; // Kalo belum ada penjualan, set ke 0 aja
} else {
    // error_log("Hadeh, gagal ngambil data total penjualan bulan ini: " . mysqli_error($conn));
}
// Format angka penjualan jadi duit Rupiah biar gampang dibaca orang awam
$formattedTotalSales = "Rp " . number_format($totalSalesAmount, 0, ',', '.');


// --- Siapin data buat semua grafik-grafik keren di dashboard kita! ---

// Data penjualan harian (7 hari terakhir)
$salesDailyData = [];
$salesDailyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $salesDailyLabels[] = date('D, d M', strtotime($date)); // Labelnya: "Senin, 10 Jun"
    $sqlDailySales = "SELECT SUM(total_harga) AS daily_sales FROM pesanan WHERE DATE(tanggal_pesanan) = '$date' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $resultDailySales = mysqli_query($conn, $sqlDailySales);
    $rowDailySales = mysqli_fetch_assoc($resultDailySales);
    $salesDailyData[] = $rowDailySales['daily_sales'] ? (float)$rowDailySales['daily_sales'] : 0; // Pastikan angkanya desimal
}

// Data penjualan mingguan (7 minggu terakhir)
$salesWeeklyData = [];
$salesWeeklyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $startOfWeek = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
    $endOfWeek = date('Y-m-d', strtotime("sunday this week -{$i} weeks"));
    $salesWeeklyLabels[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek)); // Labelnya: "Jun 03 - Jun 09"
    $sqlWeeklySales = "SELECT SUM(total_harga) AS weekly_sales FROM pesanan WHERE tanggal_pesanan BETWEEN '$startOfWeek' AND '$endOfWeek' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $resultWeeklySales = mysqli_query($conn, $sqlWeeklySales);
    $rowWeeklySales = mysqli_fetch_assoc($resultWeeklySales);
    $salesWeeklyData[] = $rowWeeklySales['weekly_sales'] ? (float)$rowWeeklySales['weekly_sales'] : 0;
}

// Data penjualan bulanan (12 bulan terakhir)
$salesMonthlyData = [];
$salesMonthlyLabels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $salesMonthlyLabels[] = date('M Y', strtotime($month . '-01')); // Labelnya: "Jun 2024"
    $sqlMonthlySales = "SELECT SUM(total_harga) AS monthly_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$month' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $resultMonthlySales = mysqli_query($conn, $sqlMonthlySales);
    $rowMonthlySales = mysqli_fetch_assoc($resultMonthlySales);
    $salesMonthlyData[] = $rowMonthlySales['monthly_sales'] ? (float)$rowMonthlySales['monthly_sales'] : 0;
}

// Data penjualan 3 bulan terakhir (ini mirip bulanan, tapi cuma 3 aja)
$sales3MonthsData = [];
$sales3MonthsLabels = [];
for ($i = 2; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $sales3MonthsLabels[] = date('M Y', strtotime($month . '-01'));
    $sql3MonthsSales = "SELECT SUM(total_harga) AS monthly_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$month' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $result3MonthsSales = mysqli_query($conn, $sql3MonthsSales);
    $row3MonthsSales = mysqli_fetch_assoc($result3MonthsSales);
    $sales3MonthsData[] = $row3MonthsSales['monthly_sales'] ? (float)$row3MonthsSales['monthly_sales'] : 0;
}

// Data penjualan 6 bulan terakhir
$sales6MonthsData = [];
$sales6MonthsLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $sales6MonthsLabels[] = date('M Y', strtotime($month . '-01'));
    $sql6MonthsSales = "SELECT SUM(total_harga) AS monthly_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$month' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $result6MonthsSales = mysqli_query($conn, $sql6MonthsSales);
    $row6MonthsSales = mysqli_fetch_assoc($result6MonthsSales);
    $sales6MonthsData[] = $row6MonthsSales['monthly_sales'] ? (float)$row6MonthsSales['monthly_sales'] : 0;
}

// Data penjualan 9 bulan terakhir
$sales9MonthsData = [];
$sales9MonthsLabels = [];
for ($i = 8; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $sales9MonthsLabels[] = date('M Y', strtotime($month . '-01'));
    $sql9MonthsSales = "SELECT SUM(total_harga) AS monthly_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$month' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $result9MonthsSales = mysqli_query($conn, $sql9MonthsSales);
    $row9MonthsSales = mysqli_fetch_assoc($result9MonthsSales);
    $sales9MonthsData[] = $row9MonthsSales['monthly_sales'] ? (float)$row9MonthsSales['monthly_sales'] : 0;
}

// Data penjualan tahunan (kita pakai data bulanan 12 bulan yang sudah di atas aja, kan sama tuh setahun)
$salesYearlyData = $salesMonthlyData;
$salesYearlyLabels = $salesMonthlyLabels;


// --- Statistik pengunjung: Harian (7 hari terakhir, berdasarkan kapan mereka terakhir buka aplikasi/web) ---
$visitorDailyData = [];
$visitorDailyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $visitorDailyLabels[] = date('D, d M', strtotime($date));
    // Query buat ngitung berapa user unik yang login di hari itu
    $sqlDailyVisitors = "SELECT COUNT(DISTINCT id_pelanggan) AS daily_visitors
                         FROM pengguna
                         WHERE DATE(last_login) = '$date'";
    $resultDailyVisitors = mysqli_query($conn, $sqlDailyVisitors);
    $rowDailyVisitors = mysqli_fetch_assoc($resultDailyVisitors);
    $visitorDailyData[] = $rowDailyVisitors['daily_visitors'] ? (int)$rowDailyVisitors['daily_visitors'] : 0;
}

// --- Statistik pengunjung: Mingguan (7 minggu terakhir, berdasarkan last_login juga) ---
$visitorWeeklyData = [];
$visitorWeeklyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $startOfWeek = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
    $endOfWeek = date('Y-m-d', strtotime("sunday this week -{$i} weeks"));
    $visitorWeeklyLabels[] = date('M d