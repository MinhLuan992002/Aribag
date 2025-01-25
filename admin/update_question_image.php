<?php
include '../config/config.php';


    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['question_image']) && isset($_POST['question_id'])) {
        // Lấy ID câu hỏi và tệp hình ảnh
        $question_id = $_POST['question_id'];
        $image = $_FILES['question_image'];

        // Kiểm tra tệp hình ảnh (đảm bảo đây là hình ảnh hợp lệ)
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            // Tạo tên tệp hình ảnh mới và di chuyển tệp vào thư mục
            $new_filename = 'question_' . $question_id . '.' . $file_extension;
            $upload_dir = 'uploads/questions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file = $upload_dir . $new_filename;
            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                // Cập nhật đường dẫn hình ảnh trong cơ sở dữ liệu
                $new_image_url = $upload_dir . $new_filename;
                // Thực hiện truy vấn UPDATE để lưu đường dẫn hình ảnh vào cơ sở dữ liệu
                // Ví dụ sử dụng PDO:
                $pdo = new PDO('mysql:host=localhost:3309;dbname=airbag', 'root', '');
                $stmt = $pdo->prepare("UPDATE questions SET question_image = :image_url WHERE id = :question_id");
                $stmt->execute([
                    ':image_url' => $new_image_url,
                    ':question_id' => $question_id
                ]);

                // Trả về kết quả JSON
                echo json_encode([
                    'success' => true,
                    'new_image_url' => $new_image_url
                ]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
