<?php
    include '../lib/session.php';
    Session::checkSession();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body 
    onload="showPage('test1.php', document.querySelector('.sidebar ul li:first-child'))">
    
    <?php
        include 'layout/header.php';
    ?>

    <div class="container">
        <?php
            include 'layout/sidebar.php';
        ?>
        <main class="content_right">
            <iframe id="pageFrame" src="test1.php" frameborder="0" width="100%" height="600px"></iframe>
        </main>
    </div>

    <?php
        include 'layout/footer.php';
    ?>
    <script>
        function showPage(page, element) {
            console.log("Đang tải trang: " + page);
            if (!page.includes('.php') && !page.includes('.html')) {
                page += ".php";
            }
            document.getElementById("pageFrame").src = page;
            let items = document.querySelectorAll(".sidebar ul li");
            items.forEach(item => item.classList.remove("active"));
            if (element) {
                element.classList.add("active");
            }
        }
    </script>
</body>
</html>