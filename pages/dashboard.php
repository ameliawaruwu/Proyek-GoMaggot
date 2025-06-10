<?php
include '../logic/update/auth.php';
include '../views/headeradmin.php';
include '../configdb.php'; 
date_default_timezone_set('Asia/Jakarta');


if ($conn) {
    mysqli_query($conn, "SET time_zone = '+07:00'"); // +07:00 untuk WIB
}

// Inisialisasi variabel untuk menghindari error jika query gagal
$newOrdersCount = 0; // Ini akan diubah untuk daily visitors
$visitorsCount = 0; // Ini akan tetap menjadi total registered users untuk tampilan card atas
$totalSalesAmount = 0;

// --- 1. Ambil Data Daily Visitors (Today) ---
// Mengganti New Orders dengan Daily Visitors berdasarkan last_login
$today = date('Y-m-d'); // Mengambil tanggal hari ini dalam format YYYY-MM-DD
$sqlDailyVisitorsCard = "SELECT COUNT(DISTINCT id_pelanggan) AS daily_visitors FROM pengguna WHERE DATE(last_login) = '$today'";
$resultDailyVisitorsCard = mysqli_query($conn, $sqlDailyVisitorsCard);

if ($resultDailyVisitorsCard) {
    $rowDailyVisitorsCard = mysqli_fetch_assoc($resultDailyVisitorsCard);
    $newOrdersCount = $rowDailyVisitorsCard['daily_visitors']; // Menggunakan variabel yang sama untuk ditampilkan di card pertama
} else {
    // Handle error jika query gagal (komentar ini agar tidak muncul di halaman)
    // error_log("Error mengambil data Daily Visitors untuk card: " . mysqli_error($conn));
}

// --- 2. Ambil Data Total Registered Users (Untuk card atas) ---
// Ini tetap menghitung total pengguna terdaftar, bukan harian
$sqlTotalRegisteredUsers = "SELECT COUNT(*) AS total_visitors FROM pengguna";
$resultTotalRegisteredUsers = mysqli_query($conn, $sqlTotalRegisteredUsers);

if ($resultTotalRegisteredUsers) {
    $rowTotalRegisteredUsers = mysqli_fetch_assoc($resultTotalRegisteredUsers);
    $visitorsCount = $rowTotalRegisteredUsers['total_visitors'];
} else {
    // error_log("Error mengambil data Total Registered Users: " . mysqli_error($conn));
}

// --- 3. Ambil Data Total Sales (This Month) ---
$thisMonth = date('Y-m'); // Format YYYY-MM
$sqlTotalSales = "SELECT SUM(total_harga) AS total_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$thisMonth'";
$resultTotalSales = mysqli_query($conn, $sqlTotalSales);

if ($resultTotalSales) {
    $rowTotalSales = mysqli_fetch_assoc($resultTotalSales);
    $totalSalesAmount = $rowTotalSales['total_sales'] ? $rowTotalSales['total_sales'] : 0;
} else {
    // error_log("Error mengambil data Total Sales: " . mysqli_error($conn));
}
$formattedTotalSales = "Rp " . number_format($totalSalesAmount, 0, ',', '.');


// --- DATA UNTUK CHART ANALITIK (UNTUK SEMUA CHART) ---

// Sales Analytics: Daily (Last 7 Days)
$salesDailyData = [];
$salesDailyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $salesDailyLabels[] = date('D, d M', strtotime($date));
    $sqlDailySales = "SELECT SUM(total_harga) AS daily_sales FROM pesanan WHERE DATE(tanggal_pesanan) = '$date' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $resultDailySales = mysqli_query($conn, $sqlDailySales);
    $rowDailySales = mysqli_fetch_assoc($resultDailySales);
    $salesDailyData[] = $rowDailySales['daily_sales'] ? (float)$rowDailySales['daily_sales'] : 0;
}

// Sales Analytics: Weekly (Last 7 Weeks)
$salesWeeklyData = [];
$salesWeeklyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $startOfWeek = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
    $endOfWeek = date('Y-m-d', strtotime("sunday this week -{$i} weeks"));
    $salesWeeklyLabels[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek));
    $sqlWeeklySales = "SELECT SUM(total_harga) AS weekly_sales FROM pesanan WHERE tanggal_pesanan BETWEEN '$startOfWeek' AND '$endOfWeek' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $resultWeeklySales = mysqli_query($conn, $sqlWeeklySales);
    $rowWeeklySales = mysqli_fetch_assoc($resultWeeklySales);
    $salesWeeklyData[] = $rowWeeklySales['weekly_sales'] ? (float)$rowWeeklySales['weekly_sales'] : 0;
}

// Sales Analytics: Monthly (Last 12 Months)
$salesMonthlyData = [];
$salesMonthlyLabels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $salesMonthlyLabels[] = date('M Y', strtotime($month . '-01'));
    $sqlMonthlySales = "SELECT SUM(total_harga) AS monthly_sales FROM pesanan WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = '$month' AND status = (SELECT id_status FROM status_pesanan WHERE nama_status = 'Sudah Sampai')";
    $resultMonthlySales = mysqli_query($conn, $sqlMonthlySales);
    $rowMonthlySales = mysqli_fetch_assoc($resultMonthlySales);
    $salesMonthlyData[] = $rowMonthlySales['monthly_sales'] ? (float)$rowMonthlySales['monthly_sales'] : 0;
}

// Sales Analytics: Quarterly (Last 3 Months)
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

// Sales Analytics: Last 6 Months
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

// Sales Analytics: Last 9 Months
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

// Sales Analytics: Yearly (Last 12 Months)
$salesYearlyData = $salesMonthlyData; // Menggunakan data monthly karena sudah 12 bulan
$salesYearlyLabels = $salesMonthlyLabels; // Menggunakan data monthly karena sudah 12 bulan


// --- Visitor Statistics: Daily (Last 7 Days based on last_login) ---
$visitorDailyData = [];
$visitorDailyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $visitorDailyLabels[] = date('D, d M', strtotime($date));
    // Query yang sudah benar untuk daily unique logins
    $sqlDailyVisitors = "SELECT COUNT(DISTINCT id_pelanggan) AS daily_visitors
                          FROM pengguna
                          WHERE DATE(last_login) = '$date'";
    $resultDailyVisitors = mysqli_query($conn, $sqlDailyVisitors);
    $rowDailyVisitors = mysqli_fetch_assoc($resultDailyVisitors);
    $visitorDailyData[] = $rowDailyVisitors['daily_visitors'] ? (int)$rowDailyVisitors['daily_visitors'] : 0;
}

// --- Visitor Statistics: Weekly (Last 7 Weeks based on last_login) ---
$visitorWeeklyData = [];
$visitorWeeklyLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $startOfWeek = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
    $endOfWeek = date('Y-m-d', strtotime("sunday this week -{$i} weeks"));
    $visitorWeeklyLabels[] = date('M d', strtotime($startOfWeek)) . ' - ' . date('M d', strtotime($endOfWeek));
    // Query untuk weekly unique logins
    $sqlWeeklyVisitors = "SELECT COUNT(DISTINCT id_pelanggan) AS weekly_visitors
                          FROM pengguna
                          WHERE DATE(last_login) BETWEEN '$startOfWeek' AND '$endOfWeek'";
    $resultWeeklyVisitors = mysqli_query($conn, $sqlWeeklyVisitors);
    $rowWeeklyVisitors = mysqli_fetch_assoc($resultWeeklyVisitors);
    $visitorWeeklyData[] = $rowWeeklyVisitors['weekly_visitors'] ? (int)$rowWeeklyVisitors['weekly_visitors'] : 0;
}

// --- Visitor Statistics: Monthly (Last 12 Months based on last_login) ---
$visitorMonthlyData = [];
$visitorMonthlyLabels = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $visitorMonthlyLabels[] = date('M Y', strtotime($month . '-01'));
    // Query untuk monthly unique logins
    $sqlMonthlyVisitors = "SELECT COUNT(DISTINCT id_pelanggan) AS monthly_visitors
                           FROM pengguna
                           WHERE DATE_FORMAT(last_login, '%Y-%m') = '$month'";
    $resultMonthlyVisitors = mysqli_query($conn, $sqlMonthlyVisitors);
    $rowMonthlyVisitors = mysqli_fetch_assoc($resultMonthlyVisitors);
    $visitorMonthlyData[] = $rowMonthlyVisitors['monthly_visitors'] ? (int)$rowMonthlyVisitors['monthly_visitors'] : 0;
}


// Product Performance (Top 5 Products by Quantity Sold) - tidak ada perubahan
$productPerformanceData = [];
$productPerformanceLabels = [];
$sqlProductPerformance = "SELECT p.namaproduk, SUM(dp.jumlah) AS total_kuantitas_terjual FROM detail_pesanan dp JOIN produk p ON dp.idproduk = p.idproduk GROUP BY p.idproduk, p.namaproduk ORDER BY total_kuantitas_terjual DESC LIMIT 5";
$resultProductPerformance = mysqli_query($conn, $sqlProductPerformance);

if ($resultProductPerformance) {
    while ($row = mysqli_fetch_assoc($resultProductPerformance)) {
        $productPerformanceLabels[] = $row['namaproduk'];
        $productPerformanceData[] = (int)$row['total_kuantitas_terjual'];
    }
} else {
    $productPerformanceLabels = ['No Data'];
    $productPerformanceData = [0];
    // error_log("Error mengambil data Product Performance: " . mysqli_error($conn));
}

// Order Status - tidak ada perubahan
$orderStatusData = [];
$orderStatusLabels = [];
$orderStatusColors = [
    'menunggupembayaran' => 'rgba(255, 159, 64, 0.8)',
    'pembayarandikonfirmasi' => 'rgba(54, 162, 235, 0.8)',
    'sudahsampai' => 'rgba(75, 192, 192, 0.8)',
    'dibatalkan' => 'rgba(255, 99, 132, 0.8)',
    'diproses' => 'rgba(153, 102, 255, 0.8)',
    'dikirim' => 'rgba(201, 203, 207, 0.8)',
];
$statusBackgroundColors = [];

$sqlOrderStatus = "SELECT sp.nama_status, COUNT(p.id_pesanan) AS count_status
                   FROM pesanan p
                   JOIN status_pesanan sp ON p.status = sp.id_status
                   GROUP BY sp.nama_status
                   ORDER BY sp.nama_status ASC";
$resultOrderStatus = mysqli_query($conn, $sqlOrderStatus);

if ($resultOrderStatus) {
    while ($row = mysqli_fetch_assoc($resultOrderStatus)) {
        $statusKey = strtolower(str_replace(' ', '', $row['nama_status']));
        $orderStatusLabels[] = ucfirst($row['nama_status']);
        $orderStatusData[] = (int)$row['count_status'];
        $statusBackgroundColors[] = $orderStatusColors[$statusKey] ?? 'rgba(201, 203, 207, 0.8)';
    }
} else {
    $orderStatusLabels = ['No Data'];
    $orderStatusData = [0];
    $statusBackgroundColors = ['rgba(201, 203, 207, 0.8)'];
}

// Menggabungkan semua data chart ke dalam satu array untuk di-encode ke JSON
$chartData = [
    'salesDaily' => [
        'labels' => $salesDailyLabels,
        'data' => $salesDailyData,
    ],
    'salesWeekly' => [
        'labels' => $salesWeeklyLabels,
        'data' => $salesWeeklyData,
    ],
    'salesMonthly' => [
        'labels' => $salesMonthlyLabels,
        'data' => $salesMonthlyData,
    ],
    'sales3Months' => [
        'labels' => $sales3MonthsLabels,
        'data' => $sales3MonthsData,
    ],
    'sales6Months' => [
        'labels' => $sales6MonthsLabels,
        'data' => $sales6MonthsData,
    ],
    'sales9Months' => [
        'labels' => $sales9MonthsLabels,
        'data' => $sales9MonthsData,
    ],
    'salesYearly' => [
        'labels' => $salesYearlyLabels,
        'data' => $salesYearlyData,
    ],
    'visitorsDaily' => [
        'labels' => $visitorDailyLabels,
        'data' => $visitorDailyData,
    ],
    'visitorsWeekly' => [
        'labels' => $visitorWeeklyLabels,
        'data' => $visitorWeeklyData,
    ],
    'visitorsMonthly' => [
        'labels' => $visitorMonthlyLabels,
        'data' => $visitorMonthlyData,
    ],
    'productPerformance' => [
        'labels' => $productPerformanceLabels,
        'data' => $productPerformanceData,
    ],
    'orderStatus' => [
        'labels' => $orderStatusLabels,
        'data' => $orderStatusData,
        'colors' => $statusBackgroundColors,
    ]
];

// --- Tambahkan notifikasi di sini ---
if (isset($_GET['status_updated']) && $_GET['status_updated'] === 'true') {
    // Menampilkan notifikasi JavaScript (alert)
    echo "<script>alert('Status pesanan berhasil diperbarui!');</script>";
    // Opsional: Hapus parameter dari URL agar tidak muncul lagi jika user refresh manual
    echo "<script>history.replaceState({}, document.title, window.location.pathname);</script>";
}
?>
<link rel="stylesheet" href="../Admin-HTML/css/admin.css">
<link rel="stylesheet" href="../Admin-HTML/css/adminanalitik.css">
<main>
    <div class="head-title">
        <div class="left">
            <h1>Dashboard</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">Home</a></li>
            </ul>
        </div>
        <a href="#" class="btn-download">
            <i class='bx bxs-cloud-download'></i>
            <span class="text">Download Report</span>
        </a>
    </div>

    <ul class="box-info">
        <li>
            <i class='bx bxs-calendar-check'></i>
            <span class="text">
                <h3 id="newOrdersCount"><?php echo $newOrdersCount; ?></h3>
                <p>Daily Visitors (Today)</p> </span>
        </li>
        <li>
            <i class='bx bxs-group'></i>
            <span class="text">
                <h3 id="visitorsCount"><?php echo $visitorsCount; ?></h3>
                <p>Total Registered Users</p>
            </span>
        </li>
        <li>
            <i class='bx bxs-dollar-circle'></i>
            <span class="text">
                <h3 id="totalSalesAmount"><?php echo $formattedTotalSales; ?></h3>
                <p>Total Sales (This Month)</p>
            </span>
        </li>
    </ul>

    <div class="analytics-grid">
        <div class="chart-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3>Sales Analytics</h3>
                <select id="salesPeriodSelect" class="analytics-dropdown">
                    <option value="daily">Daily (Last 7 Days)</option>
                    <option value="weekly">Weekly (Last 7 Weeks)</option>
                    <option value="monthly" selected>Monthly (Last 12 Months)</option>
                    <option value="3_months">Quarterly (Last 3 Months)</option>
                    <option value="6_months">Last 6 Months</option>
                    <option value="9_months">Last 9 Months</option>
                    <option value="yearly">Yearly (Last 12 Months)</option>
                </select>
            </div>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3>Visitor Statistics</h3>
                <select id="visitorPeriodSelect" class="analytics-dropdown">
                    <option value="daily" selected>Daily (Last 7 Days)</option>
                    <option value="weekly">Weekly (Last 7 Weeks)</option>
                    <option value="monthly">Monthly (Last 12 Months)</option>
                </select>
            </div>
            <canvas id="visitorChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Produk</h3>
            <canvas id="productChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Order Status</h3>
            <canvas id="orderChart"></canvas>
        </div>
    </div>

    <div class="table-data">
        <div class="order">
            <div class="head">
                <h3>Recent Orders</h3>
                <i class='bx bx-search'></i>
                <i class='bx bx-filter'></i>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Bukti Bayar</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersTableBody">
                    <?php
                    // Modifikasi query untuk mengambil bukti_bayar dari tabel pembayaran
                    $sqlRecentOrders = "SELECT p.*, u.username, u.email, b.bukti_bayar, sp.nama_status
                                         FROM pesanan p
                                         JOIN pengguna u ON p.id_pelanggan = u.id_pelanggan
                                         LEFT JOIN pembayaran b ON p.id_pesanan = b.id_pesanan
                                         JOIN status_pesanan sp ON p.status = sp.id_status -- Join untuk nama status
                                         ORDER BY tanggal_pesanan DESC LIMIT 5";
                    $resultRecentOrders = mysqli_query($conn, $sqlRecentOrders);

                    if ($resultRecentOrders && mysqli_num_rows($resultRecentOrders) > 0) {
                        while ($row = mysqli_fetch_assoc($resultRecentOrders)) {
                            $orderDate = date('d M Y', strtotime($row['tanggal_pesanan']));
                            $orderTotal = "Rp " . number_format($row['total_harga'], 0, ',', '.');
                            $statusClass = '';
                            // Gunakan nama_status untuk menentukan kelas CSS
                            $status_name_for_class = strtolower(str_replace(' ', '', $row['nama_status'])); // Menghilangkan spasi juga

                            // Sesuaikan kelas CSS berdasarkan nama status yang sudah di-format
                            switch ($status_name_for_class) {
                                case 'menunggupembayaran':
                                    $statusClass = 'status pending'; // Contoh: 'status pending'
                                    break;
                                case 'pembayarandikonfirmasi':
                                    $statusClass = 'status confirmed'; // Contoh: 'status confirmed'
                                    break;
                                case 'sudahsampai':
                                    $statusClass = 'status completed'; // Contoh: 'status completed'
                                    break;
                                case 'dibatalkan':
                                    $statusClass = 'status cancelled'; // Contoh: 'status cancelled'
                                    break;
                                case 'diproses':
                                    $statusClass = 'status process'; // Contoh: 'status process'
                                    break;
                                case 'dikirim':
                                    $statusClass = 'status shipped'; // Contoh: 'status shipped'
                                    break;
                                default:
                                    $statusClass = 'status default'; // Default jika tidak ada yang cocok
                                    break;
                            }
                            ?>
                            <tr>
                                <td>
                                    <p><?php echo htmlspecialchars($row['username']); ?></p>
                                </td>
                                <td><?php echo $orderDate; ?></td>
                                <td><span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['nama_status']); ?></span></td>
                                <td><?php echo $orderTotal; ?></td>
                                <td>
                                    <?php
                                    // Tampilkan bukti pembayaran jika ada
                                    if (!empty($row['bukti_bayar'])): ?>
                                        <a href="../photos/<?php echo htmlspecialchars($row['bukti_bayar']); ?>" target="_blank">Lihat</a>
                                    <?php else: ?>
                                        Tidak ada
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="detailPesanan.php?id=<?php echo $row['id_pesanan']; ?>" class="action-btn">Detail</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6'>Tidak ada pesanan terbaru.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script src="../Admin-HTML/js/scriptadmin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const CHART_DATA = <?php echo json_encode($chartData); ?>;

    // --- SALES ANALYTICS CHART ---
    const salesCtx = document.getElementById('salesChart');
    let salesChart;
    if (salesCtx) {
        salesChart = new Chart(salesCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: CHART_DATA.salesMonthly.labels, // Default ke monthly
                datasets: [{
                    label: 'Total Sales',
                    data: CHART_DATA.salesMonthly.data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        document.getElementById('salesPeriodSelect').addEventListener('change', function() {
            const selectedPeriod = this.value;
            let newLabels = [];
            let newData = [];
            let newLabelText = '';

            switch (selectedPeriod) {
                case 'daily':
                    newLabels = CHART_DATA.salesDaily.labels;
                    newData = CHART_DATA.salesDaily.data;
                    newLabelText = 'Daily Sales';
                    break;
                case 'weekly':
                    newLabels = CHART_DATA.salesWeekly.labels;
                    newData = CHART_DATA.salesWeekly.data;
                    newLabelText = 'Weekly Sales';
                    break;
                case 'monthly':
                    newLabels = CHART_DATA.salesMonthly.labels;
                    newData = CHART_DATA.salesMonthly.data;
                    newLabelText = 'Monthly Sales';
                    break;
                case '3_months':
                    newLabels = CHART_DATA.sales3Months.labels;
                    newData = CHART_DATA.sales3Months.data;
                    newLabelText = 'Quarterly Sales (Last 3 Months)';
                    break;
                case '6_months':
                    newLabels = CHART_DATA.sales6Months.labels;
                    newData = CHART_DATA.sales6Months.data;
                    newLabelText = 'Sales (Last 6 Months)';
                    break;
                case '9_months':
                    newLabels = CHART_DATA.sales9Months.labels;
                    newData = CHART_DATA.sales9Months.data;
                    newLabelText = 'Sales (Last 9 Months)';
                    break;
                case 'yearly': // Ini akan sama dengan monthly karena data yearly kita ambil dari monthly (12 bulan)
                    newLabels = CHART_DATA.salesYearly.labels; // Menggunakan alias
                    newData = CHART_DATA.salesYearly.data;      // Menggunakan alias
                    newLabelText = 'Yearly Sales (Last 12 Months)';
                    break;
                default:
                    // Fallback jika ada nilai yang tidak terduga
                    newLabels = CHART_DATA.salesMonthly.labels;
                    newData = CHART_DATA.salesMonthly.data;
                    newLabelText = 'Monthly Sales';
            }

            salesChart.data.labels = newLabels;
            salesChart.data.datasets[0].data = newData;
            salesChart.data.datasets[0].label = newLabelText;
            salesChart.update();
        });
    }


    // --- VISITOR STATISTICS CHART ---
    const visitorCtx = document.getElementById('visitorChart');
    let visitorChart;
    if (visitorCtx) {
        visitorChart = new Chart(visitorCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: CHART_DATA.visitorsDaily.labels, // Default ke daily saat pertama kali dimuat
                datasets: [{
                    label: 'Daily Unique Logins',
                    data: CHART_DATA.visitorsDaily.data,
                    backgroundColor: 'rgba(255, 205, 86, 0.8)',
                    borderColor: 'rgba(255, 205, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Langkah 1 untuk jumlah visitor
                        }
                    },
                    x: {
                        // Opsi untuk sumbu x (label tanggal)
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('id-ID') + ' users'; // Format untuk jumlah user
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Event listener untuk dropdown visitor period
        document.getElementById('visitorPeriodSelect').addEventListener('change', function() {
            const selectedPeriod = this.value;
            let newLabels = [];
            let newData = [];
            let newLabelText = '';

            switch (selectedPeriod) {
                case 'daily':
                    newLabels = CHART_DATA.visitorsDaily.labels;
                    newData = CHART_DATA.visitorsDaily.data;
                    newLabelText = 'Daily Unique Logins';
                    break;
                case 'weekly':
                    newLabels = CHART_DATA.visitorsWeekly.labels;
                    newData = CHART_DATA.visitorsWeekly.data;
                    newLabelText = 'Weekly Unique Logins';
                    break;
                case 'monthly':
                    newLabels = CHART_DATA.visitorsMonthly.labels;
                    newData = CHART_DATA.visitorsMonthly.data;
                    newLabelText = 'Monthly Unique Logins';
                    break;
                default:
                    newLabels = CHART_DATA.visitorsDaily.labels; // Fallback
                    newData = CHART_DATA.visitorsDaily.data;
                    newLabelText = 'Daily Unique Logins';
            }
            visitorChart.data.labels = newLabels;
            visitorChart.data.datasets[0].data = newData;
            visitorChart.data.datasets[0].label = newLabelText;
            visitorChart.update(); // Perbarui grafik
        });
    }
    // --- AKHIR MODIFIKASI: VISITOR STATISTICS CHART ---

    // --- PRODUCT PERFORMANCE CHART ---
    const productCtx = document.getElementById('productChart');
    let productChart;
    if (productCtx) {
        productChart = new Chart(productCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: CHART_DATA.productPerformance.labels,
                datasets: [{
                    label: 'Quantity Sold',
                    data: CHART_DATA.productPerformance.data,
                    backgroundColor: 'rgba(153, 102, 255, 0.8)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // --- ORDER STATUS CHART ---
    const orderCtx = document.getElementById('orderChart');
    let orderChart;
    if (orderCtx) {
        orderChart = new Chart(orderCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: CHART_DATA.orderStatus.labels,
                datasets: [{
                    label: 'Order Status Distribution',
                    data: CHART_DATA.orderStatus.data,
                    backgroundColor: CHART_DATA.orderStatus.colors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) + '%' : '0%';
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Panggil resize untuk semua chart yang terinisialisasi
    window.addEventListener('resize', () => {
        if (salesChart) salesChart.resize();
        if (visitorChart) visitorChart.resize();
        if (productChart) productChart.resize();
        if (orderChart) orderChart.resize();
    });
</script>

<?php
include '../views/footeradmin.php';
mysqli_close($conn);
?>