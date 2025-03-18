<?php
    include '../class/contact.php';
    include '../lib/session.php';
    Session::checkSession();
    $idAcount = Session::get('idAcount');
    $contact = new contact();

    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $detailId = isset($_GET['detail']) ? intval($_GET['detail']) : null;
    $showModal = false;
    $contactDetail = null;

    if ($filter == 'supported') {
        $contactlist = $contact->getContactsByStatus(1);
    } elseif ($filter == 'pending') {
        $contactlist = $contact->getContactsByStatus(0);
    } else {
        $contactlist = $contact->show();
    }

    // Lấy chi tiết nếu có detail id
    if ($detailId) {
        $contactDetail = $contact->getContactById($detailId);
        $contactDetail = $contactDetail ? $contactDetail->fetch_assoc() : null;
        $showModal = $contactDetail !== null;
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change-status'])){
        $id = $_POST['id'];
        $idAcount = $_POST['idAcount'];
        $date = $ngayDuyet = date("Y-m-d H:i:s");
        $result = $contact->updateStatus($id, $idAcount, $date);
    }

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/manage.css">
    <link rel="stylesheet" href="css/model.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="js/customer.js"></script>
</head>
<body>
    <div class="container">
        <h2>Chăm sóc khách hàng</h2>
        <div class="search-container" style="margin-top: 30px; margin-bottom: 25px;">
            <form method="get">
                <select name="filter">
                    <option value="all" <?= ($filter == 'all') ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="supported" <?= ($filter == 'supported') ? 'selected' : ''; ?>>Đã liên hệ</option>
                    <option value="pending" <?= ($filter == 'pending') ? 'selected' : ''; ?>>Chưa liên hệ</option>
                </select>
                <button type="submit" name="Search"><i class="fa fa-filter"></i> Lọc</button>
            </form>
        </div>

        <!-- Bảng danh sách khách hàng -->
        <div class="table-container">
            <table class="table_slider">
                <thead>
                    <tr>
                        <th style="width: 5%;">STT</th>
                        <th style="width: 15%;">Tên khách hàng</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 15%;">Số điện thoại</th>
                        <th style="width: 15%;">Trạng thái</th>
                        <th style="width: 15%;">Người duyệt</th>
                        <th style="width: 15%;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $i = 0;
                        if ($contactlist && $contactlist->num_rows > 0) {
                            while ($result = $contactlist->fetch_assoc()) {
                                $i++;
                    ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($result['hoTen']); ?></td>
                        <td><?php echo htmlspecialchars($result['email']); ?></td>
                        <td><?php echo htmlspecialchars($result['soDT']); ?></td>
                        <td><?php echo ($result['trangThai'] == 1) ? "Đã liên hệ" : "Chưa liên hệ"; ?></td>
                        <?php $tenNhanVien = $result['tenNhanVien']?>
                        <td><?php echo htmlspecialchars("$tenNhanVien");?></td>
                        <td class="btn-container">
                        <form action="" method="POST" style="display: inline;">
                            <?php
                                $id = $result['id'];
                                $trangThai = $result['trangThai'];
                                $isDisabled = ($trangThai == 1) ? 'disabled' : ''; // Vô hiệu hóa nút khi trạng thái là 1
                            ?>
                            <input type="hidden" name="idAcount" value="<?php echo htmlspecialchars($idAcount); ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <input type="hidden" name="trangThai" value="<?php echo htmlspecialchars($trangThai); ?>">
                            
                            <button type="submit" name="change-status" class="btn-action btn-edit" title="Đổi trạng thái" <?php echo $isDisabled; ?>>
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </form>
                            <form method="get" style="display: inline;">
                                <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                                <input type="hidden" name="detail" value="<?php echo $result['id']; ?>">
                                <button type="submit" class="btn-action btn-detail" style="background-color: red;" title="Chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">Không có khách hàng nào.</td></tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Modal hiển thị chi tiết -->
        <div id="detailModal" class="support-modal<?php echo $showModal ? ' show' : ''; ?>">
            <div class="support-modal-content">
                <a href="?filter=<?php echo $filter; ?>" class="support-close">×</a>
                <h2>Chi tiết hỗ trợ</h2>
                <div id="contactDetail">
                    <?php if ($contactDetail): ?>
                        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($contactDetail['hoTen']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($contactDetail['email'] ?: 'Không có'); ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($contactDetail['soDT']); ?></p>
                        <p><strong>Nội dung:</strong> <?php echo htmlspecialchars($contactDetail['noiDung']); ?></p>
                        <p><strong>Ngày gửi:</strong> <?php echo htmlspecialchars($contactDetail['ngayGui']); ?></p>
                        <p><strong>Ngày duyệt:</strong> <?php echo htmlspecialchars($contactDetail['ngayDuyet']); ?></p>
                        <p><strong>Người duyệt:</strong> <?php echo htmlspecialchars($contactDetail['tenNhanVien']); ?></p>
                        <p><strong>Trạng thái:</strong> <?php echo $contactDetail['trangThai'] == 1 ? 'Đã liên hệ' : 'Chưa liên hệ'; ?></p>
                    <?php else: ?>
                        <p style="text-align: center; color: red;">Không tìm thấy thông tin chi tiết!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>