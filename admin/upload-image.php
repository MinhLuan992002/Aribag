<?php
include '../config/config.php';


    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = ['success' => false];
    
        // Kiểm tra dữ liệu
        if (!isset($_POST['type'], $_POST['id']) || !isset($_FILES['image'])) {
            $response['message'] = 'Thiếu thông tin cần thiết!';
            echo json_encode($response);
            exit;
        }
    
        $type = $_POST['type'];
        $id = intval($_POST['id']);
        $file = $_FILES['image'];
    
        // Kiểm tra lỗi file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Lỗi khi tải lên hình ảnh!';
            echo json_encode($response);
            exit;
        }
    
        // Đường dẫn lưu trữ
        $uploadDir = __DIR__ . '/uploads/' . ($type === 'question' ? 'questions/' : 'answers/');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        // Tên file
        $fileName = uniqid() . '-' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
    
        // Di chuyển file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Cập nhật database (giả sử dùng PDO)
            $db = new PDO('mysql:host=localhost;dbname=test', 'root', '');
            $table = $type === 'question' ? 'questions' : 'answers';
            $column = $type === 'question' ? 'question_image' : 'answer_image';
    
            $stmt = $db->prepare("UPDATE $table SET $column = ? WHERE id = ?");
            if ($stmt->execute(['/uploads/' . ($type === 'question' ? 'questions/' : 'answers/') . $fileName, $id])) {
                $response['success'] = true;
            } else {
                $response['message'] = 'Cập nhật database thất bại!';
            }
        } else {
            $response['message'] = 'Không thể lưu file vào thư mục!';
        }
    
        echo json_encode($response);
    }
    