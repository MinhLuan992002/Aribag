<?php
require '../vendor/autoload.php';
include_once('../config/config.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

// Đường dẫn đến file mẫu
$templatePath = 'read_files/Nhận định.xlsx';

// Mở file mẫu
$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getActiveSheet();

// Kết nối đến cơ sở dữ liệu
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra và lấy danh sách `codes` từ URL
$codes = isset($_GET['codes']) ? explode(',', $_GET['codes']) : [];
if (empty($codes)) {
    die("Không có mã code nào được chọn!");
}

// Truy vấn dữ liệu cho từng mã code và lưu tất cả vào mảng
$dataList = [];
foreach ($codes as $code) {
    $stmt = $conn->prepare("CALL getCheckAnswerUser(NULL, NULL, NULL, NULL, NULL, NULL, ?)");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($data = $result->fetch_assoc()) {
        $dataList[] = $data;
    }
    $stmt->close();
}

if (empty($dataList)) {
    die("Không tìm thấy dữ liệu cho các mã code đã chọn.");
}

// Tìm dòng đánh dấu <data-start> và xóa dấu, xác định dòng bắt đầu để chèn dữ liệu
$startRow = null;
foreach ($sheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        if ($cell->getValue() === '<data-start>') {
            $startRow = $cell->getRow();
            $cell->setValue(''); // Xóa đánh dấu <data-start>
            break 2; // Thoát vòng lặp sau khi tìm thấy <data-start>
        }
    }
}

if (!$startRow) {
    die("Không tìm thấy vị trí <data-start> trong file mẫu.");
}

// Lấy định dạng của dòng mẫu dưới dạng mảng
$rowStyleArray = [
    'font' => [
        'name' => $sheet->getStyle("A{$startRow}")->getFont()->getName(),
        'bold' => $sheet->getStyle("A{$startRow}")->getFont()->getBold(),
        'italic' => $sheet->getStyle("A{$startRow}")->getFont()->getItalic(),
        'underline' => $sheet->getStyle("A{$startRow}")->getFont()->getUnderline(),
        'size' => $sheet->getStyle("A{$startRow}")->getFont()->getSize(),
        'color' => ['rgb' => $sheet->getStyle("A{$startRow}")->getFont()->getColor()->getRGB()],
    ],
    'alignment' => [
        'horizontal' => $sheet->getStyle("A{$startRow}")->getAlignment()->getHorizontal(),
        'vertical' => $sheet->getStyle("A{$startRow}")->getAlignment()->getVertical(),
    ],
    'fill' => [
        'fillType' => $sheet->getStyle("A{$startRow}")->getFill()->getFillType(),
        'startColor' => ['rgb' => $sheet->getStyle("A{$startRow}")->getFill()->getStartColor()->getRGB()],
    ]
];
$stt = 1;
// Chèn dữ liệu vào từ dòng `<data-start>` và áp dụng định dạng từ dòng mẫu
$currentRow = $startRow;
foreach ($dataList as $data) {
    $sheet->insertNewRowBefore($currentRow, 1); // Chèn một dòng mới trước mỗi lần thêm dữ liệu
    $sheet->setCellValue("A{$currentRow}", $stt); // Số thứ tự tự động
    // $sheet->setCellValue("A{$currentRow}", $data['STT'] ?? '');
    // $sheet->setCellValue("B{$currentRow}", $data['department_name'] ?? '');
    $sheet->setCellValue("B{$currentRow}", $data['manv'] ?? '');
    // $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
    $sheet->setCellValue("C{$currentRow}", $data['fullname'] ?? '');
    $sheet->setCellValue("D{$currentRow}", $dt='Công Nhân' ?? '');
    $sheet->setCellValue("E{$currentRow}", $data['result_status'] ?? '');
    // $sheet->setCellValue("E{$currentRow}", $data['test_date'] ?? '');
    // $sheet->setCellValue("F{$currentRow}", $data['code'] ?? '');
    // $sheet->setCellValue("G{$currentRow}", $data['test_date'] ?? '');
    // $sheet->setCellValue("H{$currentRow}", $data['total_questions'] ?? '');

    // $sheet->mergeCells("H{$currentRow}:I{$currentRow}");

    $dateTime = !empty($data['test_date']) ? date("d/m/Y H:i:s", strtotime($data['test_date'])) : '';
    $sheet->setCellValue("D2", $dateTime);
    
    $sheet->setCellValue("A4", 'Danh sách những nhân viên có tên dưới đây đã được đào tạo về quy trình kiểm tra ngoại quan, đo khí, đóng gói các mã hàng: ' . ($data['test_name'] ?? ''));


    $sheet->setCellValue("F{$currentRow}", ($data['score'] ?? '') . '%');

    // $sheet->setCellValue("L{$currentRow}", $data['question_text'] ?? ''); 

    // Áp dụng định dạng từ dòng đánh dấu cho từng ô trong dòng
    $sheet->getStyle("A{$currentRow}:L{$currentRow}")->applyFromArray($rowStyleArray);
    $stt++; // Tăng số thứ tự
    $currentRow++; // Di chuyển xuống dòng tiếp theo
}
// Thiết lập tên file đầu ra
$outputFileName = 'bang_diem_danh_gia.xlsx';

// Thiết lập header để tải file về
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$outputFileName\"");
header('Cache-Control: max-age=0');

// Lưu file Excel và xuất
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
