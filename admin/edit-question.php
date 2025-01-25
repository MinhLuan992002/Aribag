<?php include '../config/config.php'; ?>

<?php
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlManageTests = "
    SELECT mt.id, mt.name, d.name as department_name
    FROM manage_test mt
    JOIN department d ON mt.department_id = d.id
    ORDER BY mt.department_id ASC, mt.id ASC;
    ";
    $manageTests = $pdo->query($sqlManageTests)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Bài Kiểm Tra</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-list { display: none; margin-top: 10px; }
        .test-list.show { display: block; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2>Quản Lý Bài Kiểm Tra</h2>
    <div class="department-container">
        <?php
        $currentDepartment = null;
        foreach ($manageTests as $test) {
            if ($currentDepartment !== $test['department_name']) {
                if ($currentDepartment !== null) echo '</div></div>';
                $currentDepartment = $test['department_name'];
                echo '<div class="department-group">';
                echo '<div class="department-btn" onclick="toggleTests(\'' . htmlspecialchars($currentDepartment) . '\')">';
                echo '<span>' . htmlspecialchars($currentDepartment) . '</span>';
                echo '<div class="arrow">&#8593;</div>';
                echo '</div>';
                echo '<div class="test-list" id="' . htmlspecialchars($currentDepartment) . '">';
            }
            echo '<div class="test-item">';
            echo '<p class="card-title">' . htmlspecialchars($test['name']) . '</p>';
            echo '<button class="btn btn-primary" onclick="loadQuestions(' . $test['id'] . ')">Chỉnh sửa</button>';
            echo '</div>';
        }
        echo '</div></div>';
        ?>
    </div>
    <div id="questions-container" class="mt-4" style="display: none;">
        <h3>Chỉnh sửa câu hỏi</h3>
        <form id="questions-form">
            <div id="questions-list"></div>
            <button type="button" class="btn btn-success mt-3" onclick="confirmSaveChanges()">Lưu Thay Đổi</button>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Hàm hiển thị danh sách câu hỏi của một bài kiểm tra
function loadQuestions(testId) {
    $.get(`get_questions.php?test_id=${testId}`, function(response) {
        if (response.questions.length === 0) {
            alert('Không có câu hỏi nào trong bài kiểm tra này.');
            return;
        }

        $('#questions-container').show();
        $('#questions-list').empty();

        response.questions.forEach((question, index) => {
            const questionHtml = `
                <div class="question-item mb-4" data-question-id="${question.id}">
                    <label>Câu hỏi ${index + 1}</label>
                    <textarea class="form-control mb-2" name="question_text_${question.id}">${question.text}</textarea>
                    <div class="answers-container">
                        ${question.answers.map(answer => `
                            <div class="answer-item mb-2" data-answer-id="${answer.id}">
                                <input type="text" class="form-control mb-1" name="answer_text_${answer.id}" value="${answer.text}">
                                <input type="checkbox" class="form-check-input" name="correct_answer_${answer.id}" ${answer.correct ? 'checked' : ''}>
                                <label>Đáp án đúng</label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            $('#questions-list').append(questionHtml);
        });
    }, 'json');
}

// Xác nhận lưu thay đổi
function confirmSaveChanges() {
    if (confirm("Bạn có chắc muốn lưu các thay đổi?")) {
        saveAllQuestions();
    }
}

// Hàm lưu lại các thay đổi
function saveAllQuestions() {
    const questionsData = [];
    $('#questions-list .question-item').each(function() {
        const questionId = $(this).data('question-id');
        const questionText = $(this).find(`textarea[name="question_text_${questionId}"]`).val();
        const answers = [];

        $(this).find('.answer-item').each(function() {
            const answerId = $(this).data('answer-id');
            const answerText = $(this).find(`input[name="answer_text_${answerId}"]`).val();
            const correct = $(this).find(`input[name="correct_answer_${answerId}"]`).is(':checked');
            answers.push({ id: answerId, text: answerText, correct });
        });

        questionsData.push({ id: questionId, text: questionText, answers });
    });

    $.post('update_questions.php', { questions: questionsData }, function(response) {
        if (response.status === 'success') {
            alert('Đã lưu thành công!');
        } else {
            alert('Có lỗi xảy ra: ' + response.message);
        }
    }, 'json');
}
// Mở và đóng danh sách câu hỏi theo từng bộ phận
function toggleTests(departmentName) {
    var testList = document.getElementById(departmentName);
    document.querySelectorAll('.test-list').forEach(list => {
        if (list !== testList) list.classList.remove('show');
    });
    testList.classList.toggle('show');
}
</script>
<script>
function loadQuestions(testId) {
    console.log("Loading questions for test ID:", testId); // Debugging

    $.get(`get_questions.php?test_id=${testId}`, function(response) {
        console.log("Response received:", response); // Debugging: xem phản hồi từ server

        if (!response || !response.questions || response.questions.length === 0) {
            alert('Không có câu hỏi nào trong bài kiểm tra này hoặc dữ liệu không hợp lệ.');
            return;
        }

        $('#questions-container').show();
        $('#questions-list').empty();

        response.questions.forEach((question, index) => {
            const questionHtml = `
                <div class="question-item mb-4" data-question-id="${question.id}">
                    <label>Câu hỏi ${index + 1}</label>
                    <textarea class="form-control mb-2" name="question_text_${question.id}">${question.text}</textarea>
                    <div class="answers-container">
                        ${question.answers.map(answer => `
                            <div class="answer-item mb-2" data-answer-id="${answer.id}">
                                <input type="text" class="form-control mb-1" name="answer_text_${answer.id}" value="${answer.text}">
                                <input type="checkbox" class="form-check-input" name="correct_answer_${answer.id}" ${answer.correct ? 'checked' : ''}>
                                <label>Đáp án đúng</label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            $('#questions-list').append(questionHtml);
        });
    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Request failed: ", textStatus, errorThrown); // Debugging lỗi yêu cầu
        alert("Lỗi khi tải câu hỏi. Vui lòng kiểm tra lại.");
    });
}

</script>
<script>
    function confirmSaveChanges() {
    if (confirm("Bạn có chắc muốn lưu các thay đổi?")) {
        saveAllQuestions();
    }
}
function saveAllQuestions() {
    const questionsData = [];
    $('#questions-list .question-item').each(function() {
        const questionId = $(this).data('question-id');
        const questionText = $(this).find(`textarea[name="question_text_${questionId}"]`).val();
        const answers = [];

        $(this).find('.answer-item').each(function() {
            const answerId = $(this).data('answer-id');
            const answerText = $(this).find(`input[name="answer_text_${answerId}"]`).val();
            const correct = $(this).find(`input[name="correct_answer_${answerId}"]`).is(':checked');
            answers.push({ id: answerId, text: answerText, correct });
        });

        questionsData.push({ id: questionId, text: questionText, answers });
    });

    $.ajax({
        url: 'update_questions.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ questions: questionsData }),
        success: function(response) {
            if (response && response.status === 'success') {
                alert(response.message || 'Đã lưu thành công!');
            } else {
                alert('Có lỗi xảy ra: ' + (response.message || 'Không xác định'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('Lỗi khi gửi yêu cầu: ' + textStatus);
        }
    });
}
</script>
</body>
</html>
