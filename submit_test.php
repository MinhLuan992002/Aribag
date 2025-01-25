<?php include 'inc/header.php'; ?>
<?php include 'config/config.php'; ?>
<?php include 'notifications/notifications.php'; ?>
<?php
$manv = $_SESSION['manv'];  // Lấy thông tin từ phiên
$questions = [];
$employeeId = '';
$employeeName = '';
$manageTestId = '';
$manageTestName = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employeeId = $_POST['manv'] ?? '';
    $employeeName = $_POST['fullname'] ?? '';
    $manageTestId = $_POST['manage_test'] ?? '';
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Kiểm tra manv
    $manv = $_POST['manv'] ?? '';

    // Kiểm tra và lấy test_id
    if (isset($_POST['test_id']) && !empty($_POST['test_id'])) {
        $test_id = intval($_POST['test_id']);
    } else {
        echo "<script>showErrorNotification('Lỗi: Không tìm thấy test_id. Vui lòng thử lại.');</script>";
        exit(); // Dừng thực thi nếu không có test_id
    }

    // Lấy department_id từ bảng manage_test
    $sql = "SELECT department_id FROM manage_test WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$test_id]);
    $department_id = $stmt->fetchColumn();

    if (!$department_id) {
        echo "<script>showErrorNotification('Lỗi: Không tìm thấy bộ phận cho bài kiểm tra này.');</script>";
        exit(); // Dừng thực thi nếu không tìm thấy department_id
    }

    // Tạo mã code từ manv, test_id và ngày hiện tại
    $code = $manv . '-' . $test_id . '-' . date('Y-m');
    $attempts = 0; // Khởi tạo biến $attempts

    // Kiểm tra số lần làm bài nếu test_id không phải là 1
    if ($test_id != 1) {
        $sql = "SELECT COUNT(*) FROM user_answer WHERE code = ? AND isDeleted = 0 AND isActive = 1;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code]);
        $attempts = $stmt->fetchColumn(); // Lấy số lần làm bài kiểm tra

        // Kiểm tra số lần làm bài
        if ($attempts >= 1) {
            echo "<script>showErrorNotification('Bạn đã làm vượt quá số lần quy định.');</script>";
            exit(); // Dừng thực thi nếu vượt quá số lần làm bài
        }
    }

    // Nếu chưa làm quá số lần quy định, tiếp tục xử lý
    $user_answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') === 0) {
            $question_id = str_replace('question_', '', $key);
            $answer_id = intval($value);

            // Kiểm tra giá trị correct từ bảng answers
            $sql = "SELECT correct FROM answers WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$answer_id]);
            $correct = $stmt->fetchColumn();

            $user_answers[] = ['question_id' => $question_id, 'correct' => $correct];
        }
    }

    // Bắt đầu giao dịch
    $pdo->beginTransaction();

    // Gọi stored procedure để thêm câu trả lời
    $sql = "CALL AddUserAnswer(?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    foreach ($user_answers as $answer) {
        $result_test = $answer['correct'] ? 1 : 0; // Đánh dấu kết quả đúng hoặc sai

        // Thực hiện gọi stored procedure với department_id
        $stmt->execute([$manv, $answer['question_id'], $answer['correct'], $test_id, $code, 'Training', $result_test, $department_id]);
    }

    // Commit giao dịch sau khi hoàn thành
    $pdo->commit();

    // Tính số câu trả lời đúng
    $correct_answers = 0;
    foreach ($user_answers as $answer) {
        if ($answer['correct']) {
            $correct_answers++;
        }
    }

// Tính tổng số câu hỏi
$total_questions = count($user_answers);


    // Lấy tên bài kiểm tra từ bảng manage_test
    $sql = "SELECT name FROM manage_test WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$test_id]);
    $manageTestName = $stmt->fetchColumn();

    // Hiển thị kết quả
    if ($test_id == 1) {
        $fullname = $employeeName;
    } else {
        $sql = "SELECT fullname FROM users WHERE manv = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$manv]);
        $fullname = $stmt->fetchColumn();
    }

    include 'result.php';

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Rollback nếu có lỗi xảy ra
    }
    echo "Lỗi: " . $e->getMessage(); // Hiển thị thông báo lỗi
}

$pdo = null;
?>
