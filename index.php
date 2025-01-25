<?php include 'inc/header.php'; ?>
<?php include 'config/config.php'; ?>
<?php
// Kết nối cơ sở dữ liệu
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['manv']) || !isset($_SESSION['displayName'])) {
        header("Location: login.php");
        exit();
    }
    $employeeId = $_SESSION['manv'];
    $employeeName = $_SESSION['displayName'];

    // Lấy danh sách bài kiểm tra từ cơ sở dữ liệu
    $sqlManageTests = "
SELECT 
    mt.id, 
    mt.name, 
    d.name AS department_name, 
    sd.name AS sub_department_name, 
    pc.name AS product_code
FROM 
    manage_test mt
JOIN 
    department d ON mt.department_id = d.id
LEFT JOIN 
    sub_department sd ON mt.sub_department_id = sd.id  -- Nhánh bộ phận
LEFT JOIN 
    product_code pc ON mt.product_code_id = pc.id  -- Mã hàng
ORDER BY 
    mt.department_id ASC, 
    mt.sub_department_id ASC,  -- Sắp xếp theo nhánh bộ phận
    mt.product_code_id ASC,  -- Sắp xếp theo mã hàng
    mt.id ASC;

    ";
    $manageTests = $pdo->query($sqlManageTests)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // echo "Lỗi: " . $e->getMessage();
}

// Đóng kết nối PDO
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Trắc Nghiệm</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="76x76" href="./admin/assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="./admin/assets/img/favicon.png">
    <style>
        body {
            background-color: #f4f9fd;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        h1.display-4 {
            font-weight: 900;
            color: #3498db;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        p.lead {
            font-size: 1.1rem;
            color: #95a5a6;
        }

        .title-header {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2980b9;
            text-align: center;
            margin-bottom: 20px;
        }

        .department-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .department-group {
            margin-bottom: 10px;
        }

        .department-btn {
            width: 100%;
        }

        .test-list {
            display: none;
            margin-top: 5px;
            padding: 15px;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }


        .department-btn {
            flex: 1 1 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f4f4f4;
            color: #2980b9;
            padding: 10px;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .department-btn:hover {
            background-color: #2980b9;
            color: #ffffff;
        }

        .department-btn .arrow {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background-color: #ffffff;
            color: #2980b9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .department-btn.open .arrow {
            transform: rotate(180deg);
        }

        .test-list {
            display: none;
            margin-top: 10px;
            padding: 15px;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .test-list.show {
            display: block;
        }

        .test-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .test-item:last-child {
            border-bottom: none;
        }

        .test-item .card-title {
            font-size: 1rem;
            color: #2980b9;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0;
            padding-right: 10px;
        }

        .btn-start {
            background-color: #3498db;
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
            font-size: 0.9rem;
        }

        .btn-start:hover {
            background-color: #1abc9c;
        }

        /* Responsive design */
        @media (min-width: 768px) {
            .department-btn {
                flex: 1 1 calc(33.333% - 20px);
            }
        }

        @media (max-width: 480px) {
            .title-header {
                font-size: 1.5rem;
            }

            .btn-start {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .test-item .card-title {
                white-space: normal;
                /* Cho phép xuống dòng */
                overflow: visible;
                /* Bỏ giới hạn */
                text-overflow: clip;
                /* Bỏ dấu ba chấm */
            }
        }

        /* Phong cách chung cho nút */
        .product-code-btn {
            background-color: #007BFF;
            /* Nền xanh */
            color: white;
            /* Màu chữ trắng */
            padding: 10px 15px;
            /* Khoảng cách trong nút */
            border-radius: 5px;
            /* Bo góc */
            font-size: 16px;
            /* Kích thước chữ */
            font-weight: bold;
            /* Chữ đậm */
            cursor: pointer;
            /* Con trỏ thay đổi khi hover */
            display: flex;
            /* Dùng flexbox để căn chỉnh nội dung */
            align-items: center;
            /* Căn giữa theo chiều dọc */
            justify-content: space-between;
            /* Căn đều hai bên (span và arrow) */
            width: 100%;
            /* Đảm bảo nút chiếm toàn bộ chiều rộng */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Đổ bóng */
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-bottom: 10px;

        }

        /* Hiệu ứng hover cho nút */
        .product-code-btn:hover {
            background-color: #0056b3;
            /* Nền đậm hơn khi hover */
            transform: translateY(-2px);
            /* Hiệu ứng nổi lên */
        }

        /* Hiệu ứng khi nhấn */
        .product-code-btn:active {
            background-color: #003d80;
            /* Nền tối hơn khi nhấn */
            transform: translateY(0);
            /* Trở lại vị trí ban đầu */
        }

        /* Phong cách cho mũi tên */
        .product-code-btn .arrow {
            font-size: 18px;
            /* Kích thước mũi tên */
            font-weight: bold;
            /* Đậm nét hơn */
            margin-left: 10px;
            /* Khoảng cách giữa chữ và mũi tên */
            color: white;
            /* Màu trắng cho mũi tên */
            transition: transform 0.3s ease;
            /* Hiệu ứng xoay */
        }

        /* Khi hover, mũi tên xoay nhẹ */
        .product-code-btn:hover .arrow {
            transform: rotate(180deg);
            /* Xoay 180 độ */
        }
    </style>


    <style>
/* Container chính */
.manage-tests-container {
    font-family: Arial, sans-serif;
    margin: 20px;
}

.accordion {
    background-color: #007BFF;
    color: white;
    padding: 10px;
    width: 100%;
    text-align: left;
    border: none;
    outline: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 5px;
    margin-top: 5px;
}

.accordion:hover {
    background-color: #0056b3;
}

.panel {
    display: none;
    background-color: #f1f1f1;
    padding: 0 18px;
    margin: 0 0 10px 0;
    border-left: 3px solid #80bdff;
}

.test-item {
    margin-left: 20px;
    padding: 5px 0;
}

.test-item p {
    margin: 0;
    font-size: 14px;
}

.btn-start-test {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
}

.btn-start-test:hover {
    background-color: #0056b3;
}

    </style>
</head>

<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="display-4" style="font-family: 'Kaushan Script', cursive;">Airbag Forms</h1>
            <p class="lead">Welcome to the Training Management System</p>
        </div>

        <?php if (empty($manageTests)) : ?>
            <div class="text-center">
                <p>Không có bài kiểm tra nào.</p>
            </div>
        <?php else : ?>
            <div class="department-container">
    <?php 
    $currentDepartment = null;
    $currentSubDepartment = null;
    $currentProductCode = null;

    foreach ($manageTests as $test): 
        // Kiểm tra nếu bộ phận lớn thay đổi
        if ($currentDepartment !== $test['department_name']) {
            // Đóng nhóm bộ phận trước đó
            if ($currentDepartment !== null) {
                if ($currentProductCode !== null) {
                    echo '</div>'; // Đóng nhóm mã hàng
                    $currentProductCode = null;
                }
                if ($currentSubDepartment !== null) {
                    echo '</div>'; // Đóng nhóm nhánh bộ phận
                    $currentSubDepartment = null;
                }
                echo '</div>'; // Đóng nhóm bộ phận lớn
            }

            // Mở nhóm bộ phận lớn mới
            $currentDepartment = $test['department_name'];
            echo '<div class="department-group">';
            
            echo '<div class="department-btn-wrapper" onclick="togglePanel(\'' . htmlspecialchars($currentDepartment) . '\')">';
            echo '    <div class="department-btn">' . htmlspecialchars($currentDepartment) ;
            echo '    <div class="arrow">&#8595;</div>'; // Mũi tên khi mở
            echo '</div>';
            echo '</div>';
            echo '<div class="panel" id="' . htmlspecialchars($currentDepartment) . '" style="display: none;">';

            // $currentDepartment = $test['department_name'];
            // echo '<div class="department-group">';
            // echo '<div class="department-btn" onclick="toggleTests(\'' . htmlspecialchars($currentDepartment) . '\')">';
    
            // echo '<div class="arrow">&#8595;</div>'; // Mũi tên khi mở
            // echo '</div>';
            // echo '<div class="sub-department-list" id="' . htmlspecialchars($currentDepartment) . '" style="display:none;">';
            
        }

        // Kiểm tra nếu nhánh bộ phận thay đổi
        if ($currentSubDepartment !== $test['sub_department_name']) {
            // Đóng nhóm nhánh bộ phận trước đó
            if ($currentSubDepartment !== null) {
                if ($currentProductCode !== null) {
                    echo '</div>'; // Đóng nhóm mã hàng
                    $currentProductCode = null;
                }
                echo '</div>'; // Đóng nhóm nhánh bộ phận
            }

            // Mở nhóm nhánh bộ phận mới
            $currentSubDepartment = $test['sub_department_name'];
            echo '<div class="department-btn">' . htmlspecialchars($currentSubDepartment) ;
            echo '    <div class="arrow">&#8595;</div>'; // Mũi tên khi mở
            echo '</div>';
            echo '<div class="panel">';
        }

        // Kiểm tra nếu mã hàng thay đổi
        if ($currentProductCode !== $test['product_code']) {
            // Đóng nhóm mã hàng trước đó
            if ($currentProductCode !== null) {
                echo '</div>'; // Đóng nhóm mã hàng
            }

            // Mở nhóm mã hàng mới
            $currentProductCode = $test['product_code'];
            echo '<div class="department-btn">' . htmlspecialchars($currentProductCode) ;
            echo '    <div class="arrow">&#8595;</div>'; // Mũi tên khi mở
            echo '</div>';
            echo '<div class="panel">';
        }

        // Hiển thị bài test
        echo '<div class="test-item">';
        echo '<p class="card-title">' . htmlspecialchars($test['name']) . '</p>';
        echo '<form action="test.php" method="post" style="margin: 0;">';
        echo '<input type="hidden" name="manage_test" value="' . htmlspecialchars($test['id']) . '">';
        echo '<input type="hidden" name="manv" value="' . $employeeId . '">';
        echo '<input type="hidden" name="fullname" value="' . $employeeName . '">';
        echo '<button type="submit" class="btn btn-start">Bắt đầu</button>';
        echo '</form>';
        echo '</div>';
    endforeach;

    // Đóng nhóm cuối cùng
    if ($currentProductCode !== null) {
        echo '</div>'; // Đóng nhóm mã hàng
    }
    if ($currentSubDepartment !== null) {
        echo '</div>'; // Đóng nhóm nhánh bộ phận
    }
    if ($currentDepartment !== null) {
        echo '</div>'; // Đóng nhóm bộ phận lớn
    }
    ?>
</div>


        <?php endif; ?>
    </div>

    <script>



    </script>
    <script>
// Lấy tất cả các nút department-btn
var acc = document.getElementsByClassName("department-btn");

// Duyệt qua tất cả các nút accordion
for (var i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
        // Lấy panel liên kết với nút hiện tại
        var panel = this.nextElementSibling;

        // Tạo hiệu ứng xổ xuống
        if (panel.style.display === "block") {
            panel.style.display = "none";
        } else {
            panel.style.display = "block";
        }
    });
}




        function togglePanel(departmentId) {
    const panel = document.getElementById(departmentId);
    const btnWrapper = panel.previousElementSibling; // Lấy nút liên quan

    if (panel.style.display === "none") {
        panel.style.display = "block"; // Hiển thị panel
        btnWrapper.classList.add("active"); // Thêm class active để mũi tên xoay
    } else {
        panel.style.display = "none"; // Ẩn panel
        btnWrapper.classList.remove("active"); // Gỡ class active
    }
}

    </script>
</body>
</html>