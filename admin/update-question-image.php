<?php
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        // Kiểm tra kết nối PDO
        if (!isset($pdo)) {
            throw new Exception('Database connection error.');
        }

        $questionId = $_POST['id'];
        $type = $_POST['type']; // 'question' hoặc 'answer'
        $file = $_FILES['image'];

        // Kiểm tra lỗi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $file['error']);
        }

        // Kiểm tra loại file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type.');
        }

        // Kiểm tra kích thước file
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxFileSize) {
            throw new Exception('File size exceeds the limit of 5MB.');
        }

        // Xác định thư mục lưu tệp
        $uploadDir = $type === 'question' ? 'uploads/questions/' : 'uploads/answers/';
        $allowedDirs = ['uploads/questions/', 'uploads/answers/'];
        if (!in_array($uploadDir, $allowedDirs)) {
            throw new Exception('Invalid upload directory.');
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tạo tên file duy nhất
        $fileName = uniqid('img_', true) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;

        // Lưu file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to move uploaded file.');
        }

        // Lấy ảnh cũ từ DB
        $stmt = $pdo->prepare("SELECT question_image FROM questions WHERE id = ?");
        $stmt->execute([$questionId]);
        $oldImage = $stmt->fetchColumn();
        if (!$oldImage) {
            $oldImage = null; // Không có ảnh cũ
        }

        // Cập nhật DB với ảnh mới
        $stmt = $pdo->prepare("UPDATE questions SET question_image = ?, UpdateTime = NOW() WHERE id = ?");
        if ($stmt->execute([$filePath, $questionId])) {
            // Xóa ảnh cũ nếu có
            if ($oldImage && file_exists($oldImage)) {
                unlink($oldImage);
            }

            echo json_encode([
                'success' => true,
                'newImage' => $filePath,
                'oldImage' => $oldImage
            ]);
        } else {
            throw new Exception('Failed to update database.');
        }
    } catch (Exception $e) {
        error_log($e->getMessage(), 3, "error.log");
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
