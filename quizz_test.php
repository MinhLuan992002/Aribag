<?php include 'config/config.php'; ?>
<?php
// Kết nối cơ sở dữ liệu
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Khởi tạo các biến
    $employeeId = '';
    $employeeName = '';
    $manageTestId = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $employeeId = $_POST['manv'];
        $employeeName = $_POST['fullname'];
        $manageTestId = $_POST['manage_test'];

        // Kiểm tra và thêm người dùng nếu không tồn tại
        // $exam->checkAndAddUser($employeeId, $employeeName);

        // Truy vấn để lấy câu hỏi và đáp án theo bài kiểm tra
        $sql = "SELECT
                    q.id AS questions_id,
                    q.name AS question_name,
                    a.id AS answer_id,
                    a.answer AS answer_text
                FROM
                    questions q
                LEFT JOIN
                    answers a ON q.id = a.questions_id
                WHERE
                    q.manage_test_id = :manage_test_id
                ORDER BY
                    q.id, a.id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':manage_test_id', $manageTestId, PDO::PARAM_INT);
        $stmt->execute();

        // Lấy dữ liệu từ câu truy vấn
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tổ chức dữ liệu
        $questions = [];
        foreach ($results as $row) {
            $questions[$row["questions_id"]]['question_name'] = $row["question_name"];
            $questions[$row["questions_id"]]['answers'][] = [
                'answer_id' => $row["answer_id"],
                'answer_text' => $row["answer_text"]
            ];
        }
    }
 
    // Lấy danh sách bài kiểm tra từ cơ sở dữ liệu
    $sqlManageTests = "SELECT * FROM manage_test";
    $manageTests = $pdo->query($sqlManageTests)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
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
    <style>
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .fieldset {
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
            padding: 1rem;
        }

        .fieldset legend {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1>Airbag Forms</h1>
            <img src="img/bgtest.png" alt="Logo" class="img-fluid mb-4" style="max-width: 150px;">
        </div>

        <?php if (empty($manageTests)): ?>
            <div class="text-center">
                <p>Không có bài kiểm tra nào.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($manageTests as $test): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h4 style="font-weight: bold;" class="card-title"><?php echo htmlspecialchars($test['name']); ?></h4>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startTestModal" data-test-id="<?php echo htmlspecialchars($test['id']); ?>" data-test-name="<?php echo htmlspecialchars($test['name']); ?>">
                                    Bắt đầu
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Hiển thị form bài kiểm tra nếu có ID bài kiểm tra -->
        <?php if (!empty($manageTestId) && !empty($questions)): ?>
            <form id="testForm" action="submit_test.php" method="post" class="p-4 border rounded shadow-sm bg-white">
                <?php foreach ($questions as $question_id => $question_data): ?>
                    <fieldset class="fieldset">
                        <legend><?php echo htmlspecialchars($question_data['question_name']); ?></legend>
                        <?php foreach ($question_data['answers'] as $answer): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="question_<?php echo htmlspecialchars($question_id); ?>" value="<?php echo htmlspecialchars($answer['answer_id']); ?>" id="answer_<?php echo htmlspecialchars($answer['answer_id']); ?>">
                                <label class="form-check-label" for="answer_<?php echo htmlspecialchars($answer['answer_id']); ?>">
                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                <?php endforeach; ?>
                <input type="hidden" name="manv" value="<?php echo htmlspecialchars($employeeId); ?>">
                <input type="hidden" name="test_id" value="<?php echo htmlspecialchars($manageTestId); ?>">
                <input type="hidden" name="fullname" value="<?php echo htmlspecialchars($employeeName); ?>">
                <button type="submit" class="btn btn-success">Nộp bài</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Modal HTML -->
    <!-- <div class="modal fade" id="startTestModal" tabindex="-1" aria-labelledby="startTestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="startTestModalLabel">Bắt Đầu Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="testStartForm" action="test.php" method="post">
                        <input type="hidden" name="manage_test" id="modalTestId">
                        <div class="mb-3">
                            <label for="modalManv" class="form-label">Mã NV</label>
                            <input type="text" class="form-control" id="modalManv" name="manv" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalFullname" class="form-label">Tên</label>
                            <input type="text" class="form-control" id="modalFullname" name="fullname" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Làm bài</button>
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var testButtons = document.querySelectorAll('button[data-bs-toggle="modal"]');
            testButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var testId = this.getAttribute('data-test-id');
                    document.getElementById('modalTestId').value = testId;
                });
            });
        });
    </script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
</body>

</html>