<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/buy_now.css">
    <title>Hóa đơn mua hàng</title>
</head>
<body>
    <div class="cart-fragment">
        <h2 class="title-name">Hóa đơn mua hàng</h2>

        <!-- Địa chỉ giao hàng -->
        <div class="info__cart location">
            <div class="title-content">
                <div class="note">Vui lòng cung cấp địa chỉ giao hàng</div>
                <div class="address">
                    <i class="fa-solid fa-location-dot"></i><span class="address-content">Đồng Nai</span>
                </div>
            </div>
            <div class="delivery_arrow">
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="product-list">
            <div class="product-item">
                <div class="product-item-info">
                    <div class="product-item__left">
                        <img class="proImg" src="images/cart.png" alt="Sản phẩm">
                    </div>
                    <div class="product-item__right">
                        <div class="product-item__details">
                            <div class="product-item__name">Tên sản phẩm</div>
                            <div class="product-item__limit">Còn lại:</div>
                            <div class="product-item__quanty">SL:</div>
                        </div>
                        <div class="product-item__price">100.000đ</div>
                    </div>
                </div>
                <div class="product-item-info-quanty">
                    <div class="info-quanty">
                        <span>Số lượng:</span>
                        <button class="btn-minus">-</button>
                        <input type="number" class="product-quantity" value="1" min="1">
                        <button class="btn-plus">+</button>
                    </div>
                </div>
                <hr class="boderHr">
                <div class="total-provisional">
                    <span class="total-product-quantity">
                        <span class="total-label">Tạm tính </span>(1 sản phẩm):
                    </span>
                    <span class="temp-total-money">
                        <span class="temp-total-money-data">495.000đ</span>
                    </span>
                </div>

                <div class="discount">
                    <span class="total-product-quantity">
                        <span class="total-label">Miễn giảm:</span>
                    </span>
                    <span class="temp-total-money">
                        <span class="temp-total-money-data">0</span>
                    </span>
                </div>

                <div class="totalmoney">
                    <span class="total-product-quantity">
                        <span class="total-label">Cần thanh toán:</span>
                    </span>
                    <span class="temp-total-money">
                        <span class="temp-total-money-data">495.000đ</span>
                        
                    </span>
                    
                </div>
            </div>
        </div> 

        <!-- Hình thức thanh toán -->
        <div class="payment-method">
            <h3>Hình thức thanh toán</h3>
            <label>
                <input type="radio" name="payment" checked> Thanh toán khi nhận hàng
            </label><br>
            <label>
                <input type="radio" name="payment"> Chuyển khoản ngân hàng
            </label><br>
            <label>
                <input type="radio" name="payment"> Ví điện tử (Momo, ZaloPay)
            </label>
        </div>

        <!-- Nút Thanh toán / Hủy -->
        <div class="payment-actions">
            <button class="btn-cancel">Hủy đơn</button>
            <button class="btn-pay">Thanh toán</button>  
        </div>
    </div>
</body>
</html>