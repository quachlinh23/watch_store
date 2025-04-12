<?php
include '../class/dashboard.php';

$dashboard = new dashboard();
$total_customers = $dashboard->getTotalCustomers();
$total_employees = $dashboard->getTotalEmployees();
$total_invoice = $dashboard->getDailyOrders();
$total_export_bills = $dashboard->getTotalExportBills();
$revenue_month = $dashboard->getRevenueStats('month');
$revenue_year = $dashboard->getRevenueStats('year');
$profit_month = $dashboard->getProfitStats('month');
$profit_year = $dashboard->getProfitStats('year');

$total_brands = 7;
$ordersByYear = [];
$currentYear = 2025; // Current year as of your context
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
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        
    </style>
</head>
<body>
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
                <h3>Thương hiệu đồng hồ</h3>
                <p><?php echo $total_brands; ?></p>
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
                <button class="toggle-btn" onclick="toggleData('month')">Doanh thu theo tháng</button>
                <button class="toggle-btn" onclick="toggleData('year')">Sản phẩm bán chạy theo năm</button>
                <canvas id="mainChart"></canvas>
            </div>

            <!-- Small Widgets (Pie Chart and Bar Chart) -->
            <div class="small-widget">
                <!-- Pie Chart -->
                <div class="chart-widget">
                    <h3>Đồng Hồ Bán Chạy Trong Tháng</h3>
                    <canvas id="pieChart"></canvas>
                </div>

                <!-- Bar Chart with Year Selector -->
                <div class="chart-widget">
                    <h3>Đơn hàng theo tháng</h3>
                    <select class="year-selector" id="yearSelector" onchange="updateBarChart()">
                        <?php
                        $currentYear = 2025; // Current year as of your context
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

        // Real data for bar chart (orders by month for multiple years, fetched from getMonthlyOrders)
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
                            bodyColor: '#d4af37'
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
                        legend: { position: 'bottom', labels: { color: '#1e3a8a' } }
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