<?php
session_start();
include_once "class/brand.php";
$brand = new brand();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <style>
        :root {
            --primary: #3a0ca3;
            --secondary: #f72585;
            --accent: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            color: var(--dark);
            overflow-x: hidden;
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(58,12,163,0.9) 0%, rgba(247,37,133,0.85) 100%), 
                        url('https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center/cover;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        
        .hero-content {
            z-index: 2;
            text-align: center;
            color: white;
            animation: fadeInUp 1s ease-out;
        }
        
        .policy-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            margin-top: -80px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .policy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.15);
        }
        
        .section-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 10px 20px -5px rgba(58,12,163,0.3);
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 5px;
            top: 0;
            height: 100%;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-dot {
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--secondary);
            border: 2px solid white;
            box-shadow: 0 0 0 3px var(--secondary);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(58,12,163,0.3);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -5px rgba(58,12,163,0.4);
        }
        
        .btn-primary:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        
        .btn-primary:hover:after {
            opacity: 1;
        }
        
        .highlight {
            background: linear-gradient(120deg, rgba(76,201,240,0.2) 0%, rgba(76,201,240,0) 100%);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-delay-1 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.4s;
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .contact-card {
            background: linear-gradient(135deg, rgba(58,12,163,0.05) 0%, rgba(247,37,133,0.05) 100%);
            border: 1px solid rgba(58,12,163,0.1);
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(58,12,163,0.1);
        }
    </style>
</head>
<body>
    <?php include "layout/header.php"; ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate__animated animate__fadeInUp">Chính Sách Đổi Trả</h1>
            <p class="text-xl md:text-2xl font-light max-w-2xl mx-auto">Cam kết đổi trả linh hoạt - Bảo vệ quyền lợi khách hàng</p>
        </div>
    </section>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 md:px-6 lg:px-8 max-w-5xl">
        <!-- Policy Card -->
        <div class="policy-card animate__animated animate__fadeInUp">
            <div class="text-center mb-10">
                <div class="feature-icon mx-auto">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Cam Kết Từ Chúng Tôi</h2>
                <p class="text-gray-600 mt-3 max-w-2xl mx-auto">Chúng tôi tự hào mang đến những chiếc đồng hồ chất lượng cùng chính sách đổi trả minh bạch, đảm bảo quyền lợi tối đa cho khách hàng.</p>
            </div>
            
            <!-- Điều Kiện Đổi Trả -->
            <div class="mb-12 animate__animated animate__fadeInUp animate-delay-1">
                <h3 class="section-title text-2xl font-bold text-gray-900">Điều Kiện Đổi Trả</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-check-circle text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-2">Sản phẩm phải còn nguyên vẹn</h4>
                                <p class="text-gray-600">Sản phẩm chưa sử dụng, còn đầy đủ hộp, phụ kiện và tem bảo hành.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-clock text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-2">Thời gian đổi trả</h4>
                                <p class="text-gray-600">Trong vòng <span class="font-bold text-blue-600">7 ngày</span> kể từ ngày nhận hàng.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-tools text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-2">Lỗi sản xuất</h4>
                                <p class="text-gray-600">Đổi trả miễn phí nếu phát hiện lỗi từ nhà sản xuất.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-file-invoice text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-lg mb-2">Hóa đơn mua hàng</h4>
                                <p class="text-gray-600">Cần cung cấp hóa đơn hoặc mã đơn hàng khi yêu cầu đổi trả.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quy Trình Đổi Trả -->
            <div class="mb-12 animate__animated animate__fadeInUp animate-delay-1">
                <h3 class="section-title text-2xl font-bold text-gray-900">Quy Trình Đổi Trả</h3>
                <div class="timeline mt-6">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                            <h4 class="font-semibold text-lg mb-2">Bước 1: Liên hệ hỗ trợ</h4>
                            <p class="text-gray-600">Gọi ngay đến hotline <span class="highlight font-bold">0794628540</span> hoặc email <span class="highlight font-bold">support@watchstore.vn</span> để thông báo yêu cầu đổi trả.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                            <h4 class="font-semibold text-lg mb-2">Bước 2: Gửi sản phẩm</h4>
                            <p class="text-gray-600">Đóng gói sản phẩm nguyên vẹn và gửi về địa chỉ: <span class="highlight font-bold">235b đường Nguyễn Văn Cừ, phường Nguyễn Cư Trinh, Quận 1, TP.HCM</span>.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                            <h4 class="font-semibold text-lg mb-2">Bước 3: Kiểm tra</h4>
                            <p class="text-gray-600">Chúng tôi sẽ kiểm tra và xử lý yêu cầu của bạn trong vòng <span class="highlight font-bold">3-5 ngày làm việc</span>.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                            <h4 class="font-semibold text-lg mb-2">Bước 4: Hoàn tất</h4>
                            <p class="text-gray-600">Nhận sản phẩm mới hoặc hoàn tiền theo phương thức thanh toán ban đầu.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chi Phí Đổi Trả -->
            <div class="mb-12 animate__animated animate__fadeInUp animate-delay-2">
                <h3 class="section-title text-2xl font-bold text-gray-900">Chi Phí Đổi Trả</h3>
                <div class="grid md:grid-cols-2 gap-8 mt-8">
                    <div class="contact-card p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <i class="fas fa-sync-alt text-blue-600"></i>
                            </div>
                            <h4 class="font-bold text-xl text-gray-900">Đổi trả do lỗi</h4>
                        </div>
                        <p class="text-gray-600">Miễn phí 100% chi phí vận chuyển và đổi trả nếu sản phẩm có lỗi từ nhà sản xuất.</p>
                    </div>
                    <div class="contact-card p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="bg-pink-100 p-3 rounded-full mr-4">
                                <i class="fas fa-exchange-alt text-pink-600"></i>
                            </div>
                            <h4 class="font-bold text-xl text-gray-900">Đổi trả cá nhân</h4>
                        </div>
                        <p class="text-gray-600">Khách hàng chịu chi phí vận chuyển khi đổi trả vì lý do không ưng ý.</p>
                    </div>
                </div>
            </div>
            
            <!-- Liên Hệ -->
            <div class="text-center mt-10 animate__animated animate__fadeInUp animate-delay-2 mb-20">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Cần hỗ trợ thêm?</h3>
                <p class="text-gray-600 mb-6 max-w-2xl mx-auto">Đội ngũ chăm sóc khách hàng của chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="tel:1900636999" class="btn-primary inline-flex items-center">
                        <i class="fas fa-phone-alt mr-2"></i> Gọi ngay 0794628540
                    </a>
                    <a href="contact.php" class="btn-primary inline-flex items-center">
                        <i class="fas fa-envelope mr-2"></i> Email hỗ trợ
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div style="margin-bottom: 20px; margin-top: 20px;" class="container mx-auto px-4 md:px-6 lg:px-8 max-w-5xl mt-16 mb-20">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/3 bg-gradient-to-b from-purple-900 to-pink-700 p-8 text-white flex flex-col justify-center">
                    <h2 class="text-2xl font-bold mb-4">Câu Hỏi Thường Gặp</h2>
                    <p class="opacity-90">Tìm câu trả lời cho những thắc mắc phổ biến về chính sách đổi trả của chúng tôi.</p>
                    <div class="mt-6 hidden md:block">
                        <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" alt="Đồng hồ FAQ" class="rounded-lg floating">
                    </div>
                </div>
                <div class="md:w-2/3 p-8">
                    <div class="space-y-6">
                        <div class="border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Tôi có thể đổi sang sản phẩm khác giá trị cao hơn không?</h3>
                            <p class="text-gray-600">Có, bạn hoàn toàn có thể đổi sang sản phẩm khác có giá trị cao hơn và thanh toán phần chênh lệch.</p>
                        </div>
                        <div class="border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Thời gian hoàn tiền là bao lâu?</h3>
                            <p class="text-gray-600">Thông thường từ 3-5 ngày làm việc sau khi chúng tôi nhận được sản phẩm đổi trả.</p>
                        </div>
                        <div class="border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Tôi có thể đổi trả tại cửa hàng không?</h3>
                            <p class="text-gray-600">Có, bạn có thể mang sản phẩm đến bất kỳ cửa hàng nào của chúng tôi để được hỗ trợ đổi trả trực tiếp.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Sản phẩm khuyến mãi có được đổi trả không?</h3>
                            <p class="text-gray-600">Sản phẩm khuyến mãi vẫn được áp dụng chính sách đổi trả nếu đáp ứng đủ điều kiện.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "layout/footer.php"; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script>
        // Simple animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__fadeInUp');
                    }
                });
            }, {threshold: 0.1});
            
            document.querySelectorAll('.animate__animated').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>