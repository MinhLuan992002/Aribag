<?php include 'config/config.php'; ?>

<?php

// Kết nối cơ sở dữ liệu

try {

    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Khởi tạo các biến

    $manageTestId = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $employeeId = $_POST['manv'];

        $employeeName = $_POST['fullname'];

        $manageTestId = $_POST['manage_test'];

        // Truy vấn để lấy câu hỏi và đáp án theo bài kiểm tra

        $sql = "SELECT

q.id AS questions_id,

q.name AS question_name,

q.question_image,

a.id AS answer_id,

a.answer AS answer_text,

a.answer_image

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

            $questions[$row["questions_id"]]['question_image'] = $row["question_image"];

            $questions[$row["questions_id"]]['answers'][] = [

                'answer_id' => $row["answer_id"],

                'answer_text' => $row["answer_text"],

                'answer_image' => $row["answer_image"]

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

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Form Trắc Nghiệm</title>

    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/lightbox.min.css">




    <style>
        body {

            background-color: #f4f8fb;
            font-family: 'Arial', 'Helvetica', 'Verdana', sans-serif;
            color: #253874;
            font-size: 20px;

        }


        .element {
            animation: continuousTransition 9.3s ease infinite;
        }

        .fieldset {

            background-color: #fff;

            border-color: #0d6efd;

        }

        .bg-light {
            --bs-bg-opacity: 1;
            background-color: rgba(var(--bs-light-rgb), var(--bs-bg-opacity)) !important;
        }

        legend {

            font-size: 1.25rem;

        }

        .form-check {

            transition: all 0.3s ease;

        }

        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .form-check:hover {

            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);

        }

        .hover-shadow:hover {

            background-color: #e9f0ff;

        }

        button {

            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);

        }

        img {

            max-height: 200px;

        }
    </style>

</head>

<body>

    <div class="container py-5">

        <?php if (!empty($manageTestId) && !empty($questions)): ?>

            <form id="testForm" action="submit_test.php" method="post">

                <?php
                $index = 1; // Biến đếm để theo dõi số thứ tự câu hỏi. 

                foreach ($questions as $question_id => $question_data): ?>

                    <fieldset class="mb-5 p-4 rounded shadow-sm border fieldset">

                        <legend class="fs-4 fw-bold text-dark mb-3">
                            <?php echo " " . $index . ": " . htmlspecialchars($question_data['question_name']); ?>
                        </legend>
                        <?php
                        $index++; // Tăng số thứ tự câu hỏi sau mỗi lần lặp.
                        ?>

                        <!-- Hiển thị hình ảnh câu hỏi, sử dụng Lightbox -->

                        <?php if (!empty($question_data['question_image'])): ?>

                            <div class="text-center mb-4">

                                <a href="./admin/<?php echo htmlspecialchars($question_data['question_image']); ?>" data-lightbox="question-<?php echo htmlspecialchars($question_id); ?>">

                                    <img src="./admin/<?php echo htmlspecialchars($question_data['question_image']); ?>" alt="Question Image" class="img-fluid rounded">

                                </a>

                            </div>

                        <?php endif; ?>

                        <div class="row g-3">

                            <?php foreach ($question_data['answers'] as $answer): ?>

                                <div class="col-12 col-md-6">

                                    <div class="form-check p-3 border rounded bg-light hover-shadow">

                                        <input class="form-check-input" type="radio" name="question_<?php echo htmlspecialchars($question_id); ?>" value="<?php echo htmlspecialchars($answer['answer_id']); ?>" id="answer_<?php echo htmlspecialchars($answer['answer_id']); ?>">

                                        <label class="form-check-label" for="answer_<?php echo htmlspecialchars($answer['answer_id']); ?>">

                                            <?php echo htmlspecialchars($answer['answer_text']); ?>

                                            <!-- Hiển thị hình ảnh đáp án, sử dụng Lightbox -->

                                            <?php if (!empty($answer['answer_image'])): ?>

                                                <div class="text-center mt-2">

                                                    <a href="./admin/<?php echo htmlspecialchars($answer['answer_image']); ?>" data-lightbox="answer-<?php echo htmlspecialchars($question_id); ?>">

                                                        <img src="./admin/<?php echo htmlspecialchars($answer['answer_image']); ?>" alt="Answer Image" class="img-fluid rounded">

                                                    </a>

                                                </div>

                                            <?php endif; ?>

                                        </label>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </fieldset>

                <?php endforeach; ?>
                <input type="hidden" name="manv" value="<?php echo htmlspecialchars($employeeId); ?>">
                <input type="hidden" name="test_id" value="<?php echo htmlspecialchars($manageTestId); ?>">
                <input type="hidden" name="fullname" value="<?php echo htmlspecialchars($employeeName); ?>">
                <button type="submit" class="btn btn-success btn-lg w-100">Nộp bài</button>

            </form>

        <?php endif; ?>

    </div>

    <script src="./js/bootstrap.bundle.min.js"></script>

    <script src="./js/lightbox.min.js"></script>


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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var testForm = document.getElementById('testForm');

            testForm.addEventListener('submit', function(event) {
                var isValid = true;
                var firstInvalidFieldset = null;
                var fieldsets = document.querySelectorAll('.fieldset');

                fieldsets.forEach(function(fieldset) {
                    var radioButtons = fieldset.querySelectorAll('input[type="radio"]');
                    var isAnswered = false;

                    radioButtons.forEach(function(radio) {
                        if (radio.checked) {
                            isAnswered = true;
                        }
                    });

                    // Nếu chưa chọn đáp án, thêm thông báo lỗi
                    if (!isAnswered) {
                        isValid = false;
                        if (!firstInvalidFieldset) {
                            firstInvalidFieldset = fieldset;
                        }

                        if (!fieldset.querySelector('.error-message')) {
                            var errorMessage = document.createElement('div');
                            errorMessage.classList.add('error-message');
                            errorMessage.textContent = 'Vui lòng chọn một đáp án.';
                            fieldset.appendChild(errorMessage);
                        }
                    } else {
                        var errorMessage = fieldset.querySelector('.error-message');
                        if (errorMessage) {
                            errorMessage.remove();
                        }
                    }
                });

                // Ngăn việc gửi form nếu có câu hỏi chưa được trả lời và cuộn đến câu hỏi đầu tiên chưa trả lời
                if (!isValid) {
                    event.preventDefault();
                    firstInvalidFieldset.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });

            // Lắng nghe sự kiện thay đổi để bỏ thông báo lỗi khi chọn câu trả lời
            var radioInputs = document.querySelectorAll('.form-check-input');
            radioInputs.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var fieldset = this.closest('.fieldset');
                    var errorMessage = fieldset.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            });
        });
    </script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>