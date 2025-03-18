<?php
    include '../class/customer.php';
	$customer = new customer();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['trangThai'])) {
        $id = intval($_POST['id']);
        $status = intval($_POST['trangThai']);
        $update_status = $customer->updateStatus($id, $status);
    }
    $search = isset($_POST['searchdata']) ? trim($_POST['searchdata']) : "";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="js/customer.js"></script>
</head>
<body>
    <div class="container">
        <h2>Quản lý khách hàng</h2>
        <div class="search-container" style="margin-top: 40px;margin-bottom: 40px;">
            <form method="POST" class="search-input">
                <input type="text" name="searchdata" id="searchdata" placeholder="Tìm kiếm theo tên ..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" name="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
            </form>
        </div>

        <!-- Bảng danh sách khách hàng -->
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 10%;">STT</th>
                        <th style="width: 30%;">Tên tài khoản</th>
                        <th style="width: 40%;">Trạng thái</th>
                        <th style="width: 20%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!empty($search)) {
                            $customerlist = $customer->searchCustomers($search);
                        } else {
                            $customerlist = $customer->getAllCustomers();
                        }

                        $i = 0;
                        if ($customerlist !== false && $customerlist->num_rows > 0) {
                            while ($result = $customerlist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($result['username']); ?></td>
                        <td><?php echo ($result['trangThai'] == 1) ? "Hoạt động" : "Khóa"; ?></td>
                        <td class="btn-container">
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?php echo $result['id']; ?>">
                                <input type="hidden" name="trangThai" value="<?php echo ($result['trangThai'] == 1) ? 1 : 0; ?>">
                                <button style="margin-left: 30px;" type="submit" class="btn-action btn-edit" title="Đổi trạng thái">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" style="text-align: center; padding: 20px;">';
                            if ($customerlist === false) {
                                echo "Không tìm thấy khách hàng nào phù hợp với từ khóa '" . htmlspecialchars($search) . "'";
                            }
                            echo '</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>