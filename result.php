<?php
$manageTests = $exam->getManageTests();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả bài thi</title>
    <link rel="stylesheet" href="style.css"> <!-- Đường dẫn đến file CSS của bạn -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .result-container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .result-table {
            width: 100%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        .result-table th,
        .result-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .result-table th {
            background-color: #f4f4f4;
            text-align: left;
        }
        .result-table td {
            background-color: #ffffff;
        }
        .btn-home {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn-home:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "index.php";
        }, 4000); // 5000 milliseconds = 5 seconds
    </script>
</head>
<body>

<div class="result-container">
    <h2 style="color:green; font-weight: bold; text-transform: uppercase;">KẾT QUẢ BÀI LÀM: <?php echo htmlspecialchars($manageTestName); ?></h2>
    <table class="result-table">
        <?php if ($test_id != 1): ?>
        <tr>
            <th>Mã nhân viên:</th>
            <td><?php echo htmlspecialchars($manv); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th>Họ và tên:</th>
            <td><?php echo htmlspecialchars($fullname); ?></td>
        </tr>
        <tr>
            <th>Số câu trả lời đúng:</th>
            <td style="color:green">
            <?php echo htmlspecialchars($correct_answers) . " / " . htmlspecialchars($total_questions); ?>
        </td>
        </tr>
    </table>
    <a class="btn-home" href="index.php">Trở về trang chính</a>
</div>

</body>
</html>
