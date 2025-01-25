<?php
require __DIR__ . '/../vendor/autoload.php'; // điều chỉnh đường dẫn tương ứng với cấu trúc file của bạn
$filepath = realpath(dirname(__FILE__));
include_once realpath(dirname(__FILE__) . '/../classes/Main.php');
$main = new Main();

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import-file'])) {
    $file = $_FILES['import-file'];

    if ($file['error'] === 0) {
        $filePath = $file['tmp_name'];

        try {
            // Đọc file Excel
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            $data = array_slice($data, 2); // Bỏ qua hàng đầu tiên (tiêu đề)

            // Duyệt qua từng dòng dữ liệu
            foreach ($data as $rowIndex => $row) {
                $testName = isset($row[0]) ? $row[0] : '';
                $departmentName = isset($row[1]) ? $row[1] : '';
                $questionText = isset($row[2]) ? $row[2] : '';
                $correctAnswer = isset($row[3]) ? $row[3] : null;
                $correctAnswerFlag = isset($row[4]) ? (int)$row[4] : 0; // Kiểm tra cột đáp án đúng (1 = đúng, 0 = sai)

                // Kiểm tra dữ liệu bắt buộc
                if (empty($testName) || empty($departmentName) || empty($questionText)) {
                    $errorMessage .= "Hàng " . ($rowIndex + 1) . " có dữ liệu thiếu. ";
                    continue;
                }

                // Kiểm tra và thêm bộ phận nếu chưa tồn tại
                $departmentId = $main->getDepartmentImp($departmentName);
                if (!$departmentId) {
                    $departmentId = $main->addDepartmentIm($departmentName);
                    if (!$departmentId) {
                        $errorMessage .= "Không thể thêm bộ phận: $departmentName tại hàng " . ($rowIndex + 1) . ". ";
                        continue;
                    }
                }

                // Kiểm tra và thêm bài test nếu chưa tồn tại
                $manageTestId = $main->getManageImp($testName);
                if (!$manageTestId) {
                    $manageTestId = $main->addTestIm($testName, $departmentId);
                    if (!$manageTestId) {
                        $errorMessage .= "Không thể thêm bài test: $testName tại hàng " . ($rowIndex + 1) . ". ";
                        continue;
                    }
                }

                // Xử lý câu hỏi và đáp án
                $questionId = $main->checkExistingQuestion($manageTestId, $departmentId, $questionText);
                if (!$questionId) {
                    $questionId = $main->impQuestion($manageTestId, $departmentId, $questionText, null);
                }

                if ($questionId) {
                    // Gán đúng/sai cho đáp án dựa trên giá trị cột correctAnswerFlag
                    $result = $main->addAll($questionId, [
                        'text' => $correctAnswer,
                        'image' => null,
                        'correct' => $correctAnswerFlag === 1, // Nếu correctAnswerFlag = 1 thì đúng, còn lại là sai
                    ]);

                    if ($result) {
                        $successMessage .= "Thêm đáp án thành công tại hàng: " . ($rowIndex + 1) . ". ";
                    } else {
                        $errorMessage .= "Không thể thêm đáp án tại hàng: " . ($rowIndex + 1) . ". ";
                    }
                } else {
                    $errorMessage .= "Không thể thêm câu hỏi tại hàng " . ($rowIndex + 1) . ". ";
                }
            }

            // Kiểm tra kết quả
            if (empty($errorMessage)) {
                $successMessage = "Import dữ liệu thành công!";
            }
        } catch (Exception $e) {
            $errorMessage = "Lỗi khi xử lý file: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Có lỗi khi tải lên tệp.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Import Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1>Kết quả Import Excel</h1>
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?php echo $errorMessage; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Không có thông báo cụ thể.
            </div>
        <?php endif; ?>
        <a href="index.php" class="btn btn-primary mt-3">Quay lại</a>
    </div>
</body>
</html>
