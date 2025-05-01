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
    <title>Hướng Dẫn Mua Hàng - Thời Gian Sang Trọng</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <style>
        :root {
            --tgst-primary: #3a0ca3;
            --tgst-secondary: #f72585;
            --tgst-accent: #4cc9f0;
            --tgst-light: #f8f9fa;
            --tgst-dark: #212529;
        }
        
        .tgst-body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: var(--tgst-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        .tgst-hero-section {
            background: linear-gradient(135deg, rgba(58,12,163,0.9) 0%, rgba(247,37,133,0.85) 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-bottom: 50px;
        }
        
        .tgst-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .tgst-guide-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin-bottom: 40px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0,0,0,0.03);
        }
        
        .tgst-guide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }
        
        .tgst-step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--tgst-primary), var(--tgst-secondary));
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .tgst-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .tgst-subtitle {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--tgst-primary);
            margin: 30px 0 20px;
            display: flex;
            align-items: center;
        }
        
        .tgst-section-title {
            font-size: 1.3rem;
            font-weight: 500;
            color: #333;
            margin: 25px 0 15px;
        }
        
        .tgst-text, 
        .tgst-list-item {
            color: #555;
            font-size: 1.05rem;
        }
        
        .tgst-list {
            padding-left: 20px;
        }
        
        .tgst-list-item {
            margin-bottom: 10px;
            position: relative;
            padding-left: 25px;
        }
        
        .tgst-list-item:before {
            content: "•";
            color: var(--tgst-secondary);
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .tgst-note-box {
            background: linear-gradient(to right, rgba(76,201,240,0.1), rgba(76,201,240,0.05));
            border-left: 4px solid var(--tgst-accent);
            padding: 20px;
            border-radius: 0 8px 8px 0;
            margin: 25px 0;
        }
        
        .tgst-note-box strong {
            color: var(--tgst-primary);
        }
        
        .tgst-cta-section {
            text-align: center;
            padding: 40px 0;
        }
        
        .tgst-cta-button {
            display: inline-block;
            background: linear-gradient(135deg, var(--tgst-primary), var(--tgst-secondary));
            color: white;
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(58,12,163,0.2);
        }
        
        .tgst-cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(58,12,163,0.3);
        }
        
        .tgst-step-icon {
            color: var(--tgst-secondary);
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .tgst-step-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .tgst-step-desc {
            margin: 0;
        }
        
        .tgst-primary-color {
            color: var(--tgst-primary);
        }
        
        .tgst-secondary-color {
            color: var(--tgst-secondary);
        }
        
        .tgst-lead-text {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .tgst-title {
                font-size: 2.2rem;
            }
            
            .tgst-subtitle {
                font-size: 1.5rem;
            }
            
            .tgst-guide-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body class="tgst-body">
    <!-- Header Section -->
    <?php include "layout/header.php"; ?>
    
    <!-- Hero Section -->
    <section class="tgst-hero-section">
        <div class="tgst-container">
            <h1 class="tgst-title">Hướng Dẫn Mua Hàng</h1>
            <p class="tgst-lead-text">Khám phá quy trình mua sắm dễ dàng và nhanh chóng tại Thời Gian Sang Trọng</p>
        </div>
    </section>
    
    <!-- Main Content -->
    <div class="tgst-container">
        <!-- Step 1 -->
        <div class="tgst-guide-card">
            <h2 class="tgst-subtitle"><span class="tgst-step-number">1</span> Xem và chọn sản phẩm</h2>
            <div class="tgst-step-header">
                <i class="fas fa-search tgst-step-icon"></i>
                <p class="tgst-step-desc">Tìm kiếm sản phẩm đồng hồ phù hợp với phong cách của bạn</p>
            </div>
            
            <ol class="tgst-list">
                <li class="tgst-list-item">Truy cập trang web và duyệt danh mục sản phẩm hoặc sử dụng thanh tìm kiếm để tìm đồng hồ bạn muốn.</li>
                <li class="tgst-list-item">Nhấn vào hình ảnh hoặc tên sản phẩm để xem chi tiết, bao gồm giá, chất liệu, kích thước và hình ảnh minh họa.</li>
                <li class="tgst-list-item">Sử dụng bộ lọc để thu hẹp kết quả theo thương hiệu, giá cả, kiểu dáng hoặc tính năng.</li>
            </ol>
            
            <div class="tgst-note-box">
                <p><strong>Mẹo:</strong> Bạn có thể lưu sản phẩm yêu thích vào danh sách mong muốn để xem lại sau.</p>
            </div>
        </div>
        
        <!-- Step 2 -->
        <div class="tgst-guide-card">
            <h2 class="tgst-subtitle"><span class="tgst-step-number">2</span> Đăng ký tài khoản hoặc đăng nhập</h2>
            <div class="tgst-step-header">
                <i class="fas fa-user-circle tgst-step-icon"></i>
                <p class="tgst-step-desc">Tạo tài khoản để trải nghiệm mua sắm tốt nhất</p>
            </div>
            
            <h3 class="tgst-section-title">Nếu chưa có tài khoản:</h3>
            <ol class="tgst-list">
                <li class="tgst-list-item">Nhấn nút "Thêm vào giỏ hàng" hoặc "Mua ngay" trên trang sản phẩm.</li>
                <li class="tgst-list-item">Hệ thống sẽ chuyển bạn đến trang đăng ký.</li>
                <li class="tgst-list-item">Điền thông tin (họ tên, email, mật khẩu, số điện thoại, v.v.).</li>
                <li class="tgst-list-item">Xác nhận email (nếu được yêu cầu) và đăng nhập vào tài khoản mới.</li>
                <li class="tgst-list-item">Sau khi đăng nhập, bạn sẽ được chuyển lại trang sản phẩm để tiếp tục.</li>
            </ol>
            
            <h3 class="tgst-section-title">Nếu đã có tài khoản:</h3>
            <p class="tgst-text">Đăng nhập bằng email và mật khẩu để tiếp tục quá trình mua hàng.</p>
            
            <div class="tgst-note-box">
                <p><strong>Lợi ích khi có tài khoản:</strong> Quản lý đơn hàng dễ dàng, lưu giỏ hàng cho lần sau, nhận ưu đãi đặc biệt và tích lũy điểm thưởng.</p>
            </div>
        </div>
        
        <!-- Step 3 -->
        <div class="tgst-guide-card">
            <h2 class="tgst-subtitle"><span class="tgst-step-number">3</span> Quản lý giỏ hàng</h2>
            <div class="tgst-step-header">
                <i class="fas fa-shopping-cart tgst-step-icon"></i>
                <p class="tgst-step-desc">Kiểm tra và điều chỉnh sản phẩm trước khi thanh toán</p>
            </div>
            
            <p class="tgst-text">Sau khi thêm sản phẩm vào giỏ hàng, bạn có thể:</p>
            <ul class="tgst-list">
                <li class="tgst-list-item">Nhấn vào biểu tượng giỏ hàng ở góc trên cùng bên phải để xem giỏ hàng.</li>
                <li class="tgst-list-item">Điều chỉnh số lượng sản phẩm hoặc xóa sản phẩm không mong muốn.</li>
                <li class="tgst-list-item">Áp dụng mã giảm giá (nếu có) tại bước này.</li>
                <li class="tgst-list-item">Nhấn "Tiến hành thanh toán" để chuyển sang bước thanh toán.</li>
            </ul>
            
            <div class="tgst-note-box">
                <p><strong>Lưu ý:</strong> Giá sản phẩm có thể thay đổi tùy theo chương trình khuyến mãi. Giá cuối cùng sẽ được hiển thị rõ trước khi thanh toán.</p>
            </div>
        </div>
        
        <!-- Step 4 -->
        <div class="tgst-guide-card">
            <h2 class="tgst-subtitle"><span class="tgst-step-number">4</span> Thanh toán</h2>
            <div class="tgst-step-header">
                <i class="fas fa-credit-card tgst-step-icon"></i>
                <p class="tgst-step-desc">Hoàn tất đơn hàng với nhiều phương thức thanh toán</p>
            </div>
            
            <p class="tgst-text">Ở bước thanh toán, bạn cần:</p>
            <ol class="tgst-list">
                <li class="tgst-list-item">Nhập thông tin giao hàng (địa chỉ, số điện thoại liên hệ).</li>
                <li class="tgst-list-item">Chọn phương thức thanh toán:
                    <ul class="tgst-list">
                        <li class="tgst-list-item">Thẻ tín dụng/ghi nợ</li>
                        <li class="tgst-list-item">Ví điện tử (Momo, ZaloPay, VNPay)</li>
                        <li class="tgst-list-item">Chuyển khoản ngân hàng</li>
                        <li class="tgst-list-item">Thanh toán khi nhận hàng (COD)</li>
                    </ul>
                </li>
                <li class="tgst-list-item">Kiểm tra lại đơn hàng và nhấn "Xác nhận đơn hàng" để hoàn tất.</li>
            </ol>
            
            <div class="tgst-note-box">
                <p><strong>Bảo mật:</strong> Thông tin thanh toán của bạn được mã hóa và bảo vệ an toàn. Chúng tôi không lưu trữ thông tin thẻ tín dụng của bạn.</p>
            </div>
        </div>
        
        <!-- Step 5 -->
        <div class="tgst-guide-card">
            <h2 class="tgst-subtitle"><span class="tgst-step-number">5</span> Theo dõi đơn hàng</h2>
            <div class="tgst-step-header">
                <i class="fas fa-truck tgst-step-icon"></i>
                <p class="tgst-step-desc">Cập nhật trạng thái đơn hàng từ lúc đặt đến khi nhận</p>
            </div>
            
            <p class="tgst-text">Sau khi đặt hàng thành công:</p>
            <ul class="tgst-list">
                <li class="tgst-list-item">Bạn sẽ nhận được email xác nhận đơn hàng với mã đơn hàng.</li>
                <li class="tgst-list-item">Đăng nhập vào tài khoản và vào mục "Đơn hàng của tôi" để kiểm tra trạng thái đơn hàng.</li>
                <li class="tgst-list-item">Nhận thông báo qua email/SMS khi đơn hàng được giao cho đơn vị vận chuyển.</li>
                <li class="tgst-list-item">Theo dõi lộ trình giao hàng trực tiếp trên website.</li>
            </ul>
            
            <div class="tgst-note-box">
                <p><strong>Hỗ trợ:</strong> Nếu có bất kỳ thắc mắc nào về đơn hàng, vui lòng liên hệ với chúng tôi qua hotline hoặc email hỗ trợ.</p>
            </div>
        </div>
        
        <!-- CTA Section -->
        <div class="tgst-cta-section">
            <h3 class="tgst-section-title tgst-primary-color" style="margin-bottom: 20px;">Sẵn sàng trải nghiệm mua sắm?</h3>
            <a href="#" class="tgst-cta-button">Bắt đầu mua sắm ngay <i class="fas fa-arrow-right"></i></a>
            <p class="tgst-text" style="margin-top: 30px;">Cần hỗ trợ thêm? Liên hệ với chúng tôi qua <a href="mailto:support@thoigiansangtrong.vn" class="tgst-primary-color" style="font-weight: 500;">support@thoigiansangtrong.vn</a> hoặc hotline <strong class="tgst-secondary-color">1900 636 999</strong></p>
        </div>
    </div>
    
    <!-- Footer Section -->
    <?php include "layout/footer.php"; ?>
</body>
</html>