<?php
include '../class/dashboard.php';

$dashboard = new dashboard();
$total_customers = $dashboard->getTotalCustomers();
$total_employees = $dashboard->getTotalEmployees();
$total_invoice = $dashboard->getDailyOrders();
$total_order_not_approve = $dashboard->getOrdersnotapproved();
$revenue_month = $dashboard->getRevenueStats('month');
$revenue_year = $dashboard->getRevenueStats('year');
$profit_month = $dashboard->getProfitStats('month');
$profit_year = $dashboard->getProfitStats('year');
$top_products = $dashboard->getTopSellingProduct();
$ordersByYear = [];
$currentYear = 2025;
$labels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
for ($year = 2020; $year <= $currentYear; $year++) {
    $monthlyOrders = $dashboard->getMonthlyOrders($year);
    $ordersByYear[$year] = [
        'labels' => $labels,
        'values' => $monthlyOrders
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Bán Đồng Hồ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
            padding: 20px;
            transition: all 0.3s ease;
            color: #1e3a8a;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #1e3a8a 0%, #2d4b8a 100%);
            color: #fff;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1e3a8a;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
        }

        .dark-mode .theme-toggle {
            background: #d4af37;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-box {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dark-mode .stat-box {
            background: #2d4b8a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-box i {
            font-size: 2.2rem;
            color: #d4af37;
            margin-bottom: 12px;
            transition: transform 0.3s;
        }

        .stat-box:hover i {
            transform: scale(1.15);
        }

        .stat-box h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .dark-mode .stat-box h3 {
            color: #fff;
        }

        .stat-box p {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3a8a;
        }

        .dark-mode .stat-box p {
            color: #d4af37;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .chart-widget {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .dark-mode .chart-widget {
            background: #2d4b8a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .chart-widget:hover {
            transform: translateY(-5px);
        }

        .chart-widget h3 {
            font-size: 1.3rem;
            font-weight: 500;
            margin-bottom: 15px;
            color: #1e3a8a;
        }

        .dark-mode .chart-widget h3 {
            color: #fff;
        }

        .toggle-btn {
            background: #f0f4ff;
            border: none;
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s, color 0.3s;
        }

        .toggle-btn.active, .toggle-btn:hover {
            background: #1e3a8a;
            color: #fff;
        }

        .dark-mode .toggle-btn {
            background: #4b5e8a;
        }

        .dark-mode .toggle-btn.active, .dark-mode .toggle-btn:hover {
            background: #d4af37;
            color: #1e3a8a;
        }

        .year-selector {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #1e3a8a;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .dark-mode .year-selector {
            background: #4b5e8a;
            color: #fff;
            border-color: #d4af37;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease-out;
        }

        .dark-mode .modal-content {
            background: #2d4b8a;
            color: #fff;
        }

        .modal-content h2 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .modal-content p {
            margin-bottom: 10px;
        }

        .modal-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }

        .modal-content .return-btn {
            background: #d4af37;
            color: #1e3a8a;
        }

        .modal-content .return-btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #ccc;
            color: #666;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .stat-box {
                padding: 15px;
            }

            .stat-box p {
                font-size: 1.5rem;
            }

            .toggle-btn {
                padding: 8px 15px;
                font-size: 0.8rem;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-box, .chart-widget {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .invoices-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .invoice-item {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dark-mode .invoice-item {
            background: #4b5e8a;
        }

        .invoice-item:hover {
            background: #e0e7ff;
        }

        .dark-mode .invoice-item:hover {
            background: #5b6e9a;
        }

        .top-product-widget {
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .dark-mode .top-product-widget {
            background: #2d4b8a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .top-product-widget:hover {
            transform: translateY(-5px);
        }

        .top-product-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 250px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .top-product-list::-webkit-scrollbar {
            width: 6px;
        }

        .top-product-list::-webkit-scrollbar-track {
            background: #f0f4ff;
            border-radius: 3px;
        }

        .top-product-list::-webkit-scrollbar-thumb {
            background: #1e3a8a;
            border-radius: 3px;
        }

        .dark-mode .top-product-list::-webkit-scrollbar-track {
            background: #4b5e8a;
        }

        .dark-mode .top-product-list::-webkit-scrollbar-thumb {
            background: #d4af37;
        }

        .top-product-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .top-product-item img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #d4af37;
        }

        .top-product-item .product-info {
            flex: 1;
        }

        .top-product-widget h3 {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 15px;
            color: #1e3a8a;
        }

        .dark-mode .top-product-widget h3 {
            color: #fff;
        }

        .top-product-item p {
            font-size: 0.9rem;
            color: #4b5e8a;
            margin-bottom: 4px;
        }

        .dark-mode .top-product-item p {
            color: #d4af37;
        }
    </style>
</head>
<body class="light-mode">
    <div class="main-content">
        <div class="stats-container">
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <h3>Khách hàng</h3>
                <p><?php echo $total_customers; ?></p>
            </div>
            <div class="stat-box">
                <i class="fas fa-user-tie"></i>
                <h3>Nhân viên</h3>
                <p><?php echo $total_employees; ?></p>
            </div>
            <div class="stat-box">
                <i class="fas fa-tags"></i>
                <h3>Đơn cần duyệt</h3>
                <p><?php echo $total_order_not_approve; ?></p>
            </div>
            <div class="stat-box">
                <i class="fas fa-clock"></i>
                <h3>Đơn hàng trong ngày</h3>
                <p><?php echo $total_invoice; ?></p>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-widget">
                <h3>Thống kê doanh thu & lợi nhuận</h3>
                <div class="filter-buttons">
                    <button class="toggle-btn active" onclick="toggleData('month')">Theo tháng</button>
                    <button class="toggle-btn" onclick="toggleData('year')">Theo năm</button>
                </div>
                <canvas id="mainChart"></canvas>
            </div>

            <div class="small-widget">
                <div class="top-product-widget">
                    <h3>Sản phẩm bán chạy nhất</h3>
                    <div class="top-product-list">
                        <?php foreach ($top_products['products'] as $product): ?>
                            <div class="top-product-item">
                                <img src="<?php echo htmlspecialchars($product['hinhAnh']); ?>" alt="Sản phẩm bán chạy">
                                <div class="product-info">
                                    <p><strong>Tên:</strong> <?php echo htmlspecialchars($product['tenSanPham']); ?></p>
                                    <p><strong>Số lượng bán:</strong> <?php echo $product['totalSold']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="chart-widget">
                    <h3>Đơn hàng theo tháng</h3>
                    <select class="year-selector" id="yearSelector" onchange="updateBarChart()">
                        <?php
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    <script>
        const revenueData = {
            month: {
                labels: <?php echo json_encode($revenue_month['labels']); ?>,
                values: <?php echo json_encode($revenue_month['values']); ?>
            },
            year: {
                labels: <?php echo json_encode($revenue_year['labels']); ?>,
                values: <?php echo json_encode($revenue_year['values']); ?>
            }
        };
        const profitData = {
            month: {
                labels: <?php echo json_encode($profit_month['labels']); ?>,
                values: <?php echo json_encode($profit_month['values']); ?>
            },
            year: {
                labels: <?php echo json_encode($profit_year['labels']); ?>,
                values: <?php echo json_encode($profit_year['values']); ?>
            }
        };

        const barData = <?php echo json_encode($ordersByYear); ?>;

        let mainChart, barChart;

        function initCharts() {
            const mainCtx = document.getElementById('mainChart').getContext('2d');
            mainChart = new Chart(mainCtx, {
                type: 'line',
                data: {
                    labels: revenueData.month.labels,
                    datasets: [
                        {
                            label: 'Doanh thu (VNĐ)',
                            data: revenueData.month.values,
                            borderColor: '#1e3a8a',
                            backgroundColor: 'rgba(30, 58, 138, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#d4af37',
                            pointBorderColor: '#1e3a8a'
                        },
                        {
                            label: 'Lợi nhuận (VNĐ)',
                            data: profitData.month.values,
                            borderColor: '#d4af37',
                            backgroundColor: 'rgba(212, 175, 55, 0.2)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#1e3a8a',
                            pointBorderColor: '#d4af37'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e5e7eb' },
                            ticks: { color: '#4b5e8a' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#4b5e8a' }
                        }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { color: '#1e3a8a', font: { size: 14 } } },
                        tooltip: {
                            backgroundColor: '#1e3a8a',
                            titleColor: '#ffffff',
                            bodyColor: '#d4af37',
                            titleFont: { size: 14 },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toLocaleString('vi-VN')} VNĐ`;
                                }
                            }
                        }
                    }
                }
            });

            const barCtx = document.getElementById('barChart').getContext('2d');
            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: barData[2025].labels,
                    datasets: [{
                        label: 'Đơn hàng',
                        data: barData[2025].values,
                        backgroundColor: '#1e3a8a',
                        borderRadius: 4,
                        hoverBackgroundColor: '#d4af37'
                    }]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e5e7eb' },
                            ticks: { color: '#4b5e8a' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#4b5e8a' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e3a8a',
                            titleColor: '#ffffff',
                            bodyColor: '#d4af37',
                            titleFont: { size: 14 },
                            bodyFont: { size: 12 }
                        }
                    }
                }
            });

            if (!profitData.month.values.length || profitData.month.values.every(v => v === 0)) {
                console.warn('Không có dữ liệu lợi nhuận để hiển thị.');
            }
        }

        function toggleData(type) {
            mainChart.data.labels = revenueData[type].labels;
            mainChart.data.datasets[0].data = revenueData[type].values;
            mainChart.data.datasets[1].data = profitData[type].values;
            mainChart.update();
            document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        function updateBarChart() {
            const selectedYear = document.getElementById('yearSelector').value;
            barChart.data.labels = barData[selectedYear].labels;
            barChart.data.datasets[0].data = barData[selectedYear].values;
            barChart.update();
        }

        window.onload = function() {
            initCharts();
        };
    </script>
</body>
</html>