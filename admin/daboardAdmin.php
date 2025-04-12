<?php
include '../class/dashboard.php';

$dashboard = new dashboard();
$total_customers = $dashboard->getTotalCustomers();
$total_employees = $dashboard->getTotalEmployees();
$total_invoice = $dashboard->getDailyOrders();
$total_export_bills = $dashboard->getTotalExportBills();
$total_order_not_approve = $dashboard->getOrdersnotapproved();
$revenue_month = $dashboard->getRevenueStats('month');
$revenue_year = $dashboard->getRevenueStats('year');
$profit_month = $dashboard->getProfitStats('month');
$profit_year = $dashboard->getProfitStats('year');
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            transition: all 0.3s ease;
            color: #1e3a8a;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #1e3a8a 0%, #4b5e8a 100%);
            color: #fff;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1e3a8a;
            color: #fff;
            border: none;
            padding: 10px;
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

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-box {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dark-mode .stat-box {
            background: #2d4b8a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .stat-box i {
            font-size: 2rem;
            color: #d4af37;
            margin-bottom: 10px;
            transition: transform 0.3s;
        }

        .stat-box:hover i {
            transform: scale(1.2);
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

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .chart-widget {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
        }

        .dark-mode .chart-widget h3 {
            color: #fff;
        }

        .toggle-btn {
            background: #e5e7eb;
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

        .small-widget {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Responsive Design */
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

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-box, .chart-widget {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="light-mode">
    <!-- Theme Toggle -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Stats Container -->
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
                <h3>Đơn cần được duyệt</h3>
                <p><?php echo $total_order_not_approve; ?></p>
            </div>
            <div class="stat-box">
                <i class="fas fa-clock"></i>
                <h3>Đơn hàng trong ngày</h3>
                <p><?php echo $total_invoice; ?></p>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <!-- Line Chart (Revenue & Profit Combined) -->
            <div class="chart-widget">
                <h3>Thống kê doanh thu & lợi nhuận</h3>
                <div class="filter-buttons">
                    <button class="toggle-btn active" onclick="toggleData('month')">Doanh thu theo tháng</button>
                    <button class="toggle-btn" onclick="toggleData('year')">Doanh thu theo năm</button>
                </div>
                <canvas id="mainChart"></canvas>
            </div>

            <!-- Small Widgets (Pie Chart and Bar Chart) -->
            <div class="small-widget">
                <!-- Pie Chart -->
                <div class="chart-widget">
                    <h3>Đồng hồ bán chạy trong tháng</h3>
                    <canvas id="pieChart"></canvas>
                </div>

                <!-- Bar Chart with Year Selector -->
                <div class="chart-widget">
                    <h3>Đơn hàng theo tháng</h3>
                    <select class="year-selector" id="yearSelector" onchange="updateBarChart()">
                        <?php
                        $currentYear = 2025;
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Dữ liệu từ PHP
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

        // Dummy data for pie chart (adjusted for watch categories)
        const pieData = {
            labels: ['Đồng hồ cơ', 'Đồng hồ điện tử', 'Đồng hồ thông minh'],
            values: [45, 30, 25]
        };

        // Real data for bar chart (orders by month for multiple years)
        const barData = <?php echo json_encode($ordersByYear); ?>;

        let mainChart, pieChart, barChart;

        function initCharts() {
            // Main Chart (Line - Combined Revenue & Profit)
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
                        legend: { position: 'top', labels: { color: '#1e3a8a' } },
                        tooltip: {
                            backgroundColor: '#1e3a8a',
                            titleColor: '#ffffff',
                            bodyColor: '#d4af37',
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toLocaleString('vi-VN')} VNĐ`;
                                }
                            }
                        }
                    }
                }
            });

            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: pieData.labels,
                    datasets: [{
                        data: pieData.values,
                        backgroundColor: ['#1e3a8a', '#d4af37', '#4b5e8a'],
                        hoverOffset: 20,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#1e3a8a' } },
                        tooltip: {
                            backgroundColor: '#1e3a8a',
                            titleColor: '#ffffff',
                            bodyColor: '#d4af37'
                        }
                    }
                }
            });

            // Bar Chart (Orders by Month)
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
                            bodyColor: '#d4af37'
                        }
                    }
                }
            });
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

        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            document.body.classList.toggle('light-mode');
            const icon = document.querySelector('.theme-toggle i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');
        }

        window.onload = function() {
            initCharts();
        };
    </script>
</body>
</html>