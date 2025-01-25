<?php
include '../config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_FILES['answer_image']) && isset($_POST['answer_id'])) {
            $answer_id = $_POST['answer_id'];
            $image = $_FILES['answer_image'];

            // Kiểm tra lỗi tệp
            if ($image['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi tải tệp lên: ' . $image['error']
                ]);
                exit;
            }

            // Kiểm tra định dạng tệp
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Định dạng file không được hỗ trợ. Chỉ chấp nhận JPG, PNG, GIF.'
                ]);
                exit;
            }

            // Xử lý upload
            $upload_dir = 'uploads/answers/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_filename = 'answer_' . $answer_id . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (file_exists($target_file)) {
                unlink($target_file); // Xóa file cũ
            }

            if (move_uploaded_file($image['tmp_name'], $target_file)) {
                $new_image_url = $upload_dir . $new_filename;

                // Cập nhật vào cơ sở dữ liệu
                $stmt = $pdo->prepare("UPDATE answers SET answer_image = :image_url WHERE id = :answer_id");
                $stmt->execute([
                    ':image_url' => $new_image_url,
                    ':answer_id' => $answer_id
                ]);

                echo json_encode([
                    'success' => true,
                    'new_image_url' => $new_image_url
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể di chuyển tệp vào thư mục tải lên.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Thiếu tệp tải lên hoặc ID câu trả lời.'
            ]);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
?>
