<?php
$filepath = realpath(dirname(__FILE__));
include_once realpath(dirname(__FILE__) . '/../classes/Main.php');
session_start();
$main = new Main();
$users = $main->getAllUsers();
$department_name = $_SESSION['department'];
$results = $main->getResults($manv = '', $day = '', $month = '', $year = '', $test_name = '', $department_name, $code = '');
?>
<style>
    form {
        max-width: 100%;
        padding: 10px;
    }

    .form-control-sm {
        min-width: 120px;
        /* Điều chỉnh kích thước trường nhập liệu */
    }

    button {
        padding: 6px 12px;
        /* Điều chỉnh padding nếu cần */
    }

    /* Phong cách cho modal */
    .modal {
        display: none;
        /* Ẩn modal mặc định */
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }

    /* Hình ảnh trong modal */
    .modal-content {
        display: block;
        margin-left: 25%;
        margin-top: 10%;
        max-width: 50%;
        max-height: 50%;
    }

    /* Đóng modal */
    .modal-close {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #fff;
        font-size: 30px;
        font-weight: bold;
        width: 30px;
        cursor: pointer;
    }

    .question-image {
        position: relative;
    }

    .image-exchange-icon {
        position: absolute;
        padding-top: 3%;
        font-size: 27px;
        padding-left: 3%;
        color: orange;
        width: 100px;

        cursor: pointer;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
    }

    .image-exchange-icon:hover {
        color: #0056b3;
    }
</style>
<!DOCTYPE html>
<html lang="en">

<?php include './share/share_head.php'; ?>

<body class="g-sidenav-show   bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    <?php include './assets/common/sidebar.php'; ?>
    <main class="main-content position-relative border-radius-lg ">
        <!-- Navbar -->
        <?php
        $pageTitle = "User Answer";
        include('./assets/common/nav_main.php') ?>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Edit Content</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">


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




                            <style>
                                .test-list {
                                    display: none;
                                    margin-top: 10px;
                                }

                                .test-list.show {
                                    display: block;
                                }
                            </style>
                            </head>

                            <body>
                                <div class="container mt-4">
                                    <h2 style="text-align: center;">QUẢN LÍ BÀI KIỂM TRA</h2>
                                    <div class="container department-container">

                                        <?php
                                        $currentDepartment = null;
                                        foreach ($manageTests as $test) {
                                            if ($currentDepartment !== $test['department_name']) {
                                                if ($currentDepartment !== null) echo '</div></div>';
                                                $currentDepartment = $test['department_name'];
                                                echo '<div class="department-group mb-3">';
                                                echo '<div class="department-btn btn btn-primary d-flex justify-content-between align-items-center" onclick="toggleTests(\'' . htmlspecialchars($currentDepartment) . '\')">';
                                                echo '<span>' . htmlspecialchars($currentDepartment) . '</span>';
                                                echo '<span class="arrow">&#8593;</span>';
                                                echo '</div>';
                                                echo '<div class="test-list collapse" id="' . htmlspecialchars($currentDepartment) . '">';
                                            }
                                            echo '<div class="test-item card mt-2">';
                                            echo '<div class="card-body d-flex justify-content-between align-items-center">';
                                            echo '<p class="card-title mb-0">' . htmlspecialchars($test['name']) . '</p>';
                                            echo '<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editQuestionsModal" onclick="loadQuestions(' . $test['id'] . ')">Chỉnh sửa</button>';
                                            echo '</div></div>';
                                        }
                                        echo '</div></div>';
                                        ?>
                                    </div>
                                    <div class="modal fade" id="editQuestionsModal" tabindex="-1" aria-labelledby="editQuestionsLabel" aria-hidden="true" backdr>
                                        <div class="modal-dialog modal-dialog-scrollable" style="max-width: 70%;  ">
                                            <div class="modal-content" style="height: 100%; border-radius: 10px;">
                                                <div class="modal-header" style="background-color: #007bff; color: white;">
                                                    <h5 class="modal-title" id="editQuestionsLabel">CHỈNH SỬA CÂU HỎI</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body" style="max-height: calc(100vh - 130px); overflow-y: auto;">
                                                    <form id="questions-form">
                                                        <div id="questions-list" class="mb-3">
                                                            <!-- Nội dung câu hỏi sẽ được hiển thị ở đây -->
                                                        </div>
                                                        <button type="button" class="btn btn-success w-100 mt-3 py-2" onclick="confirmSaveChanges()">Lưu Thay Đổi</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div id="imageModal" class="modal">
                                    <span class="modal-close">&times;</span>
                                    <img class="modal-content" id="modalImage">
                                </div>

                                <script src="./assets/js/core/jquery-3.6.0.min.js"></script>
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
                                                                    <div class="form-check form-check-info text-start">
                                                                    <input type="checkbox"  class="form-check-input" name="correct_answer_${answer.id}" ${answer.correct ? 'checked' : ''}>
                                                                    <label>Đáp án đúng</label>
                                                </div>
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
                                                answers.push({
                                                    id: answerId,
                                                    text: answerText,
                                                    correct
                                                });
                                            });

                                            questionsData.push({
                                                id: questionId,
                                                text: questionText,
                                                answers
                                            });
                                        });

                                        $.post('update_questions.php', {
                                            questions: questionsData
                                        }, function(response) {
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
                                                // Xử lý câu hỏi
                                                const questionHtml = `
                    <div class="question-item mb-4" data-question-id="${question.id}">
                        <label>Câu hỏi ${index + 1}</label>
                        <textarea class="form-control mb-2" name="question_text_${question.id}">${question.text}</textarea>
                    <div class="question-container mb-4">
                    <div id="question-container-${question.id}" class="question-image mb-2 text-center">
                        ${question.question_image ? 
                            `
                            <img id="question-image-${question.id}" src="${question.question_image}" alt="Question Image" class="img-thumbnail" style="max-height: 200px; width: auto; cursor: pointer;" onclick="openModal('${question.question_image}')"/>
                            <div class="image-actions mt-2">
                                <button type="button" class="btn btn-sm btn-primary" onclick="changeQuestionImage('${question.id}')">Đổi hình</button>
                                <input type="file" id="question-image-upload-${question.id}" class="d-none" onchange="updateQuestionImage(event, '${question.id}')"/>
                            </div>
                            `
                            : `
<img src="img/noimage.png" alt="No Image" id="question-image-${question.id}" class="img-thumbnail" style="max-height: 200px; width: auto; cursor: pointer;" />
                            <div class="image-actions mt-2">
                                <button type="button" class="btn btn-sm btn-success" onclick="changeQuestionImage('${question.id}')">Thêm hình</button>
                                <input type="file" id="question-image-upload-${question.id}" class="d-none" onchange="updateQuestionImage(event, '${question.id}')"/>
                            </div>
                            `
                        }
                    </div>




    <div class="answers-container mt-3">
        ${question.answers.map(answer => `
            <div class="answer-item mb-3 p-2 border rounded" data-answer-id="${answer.id}">
                <div class="answer-image text-center mb-2">
${answer.image ? `
    <img src="${answer.image}" alt="Answer Image" id="answer-image-${answer.id}" class="img-thumbnail" style="max-height: 200px; width: auto; cursor: pointer;")"/>
    <div class="image-actions mt-2">
        <button type="button" class="btn btn-sm btn-primary" onclick="changeAnswerImage('${answer.id}')">Đổi hình</button>
        <input type="file" id="answer-image-upload-${answer.id}" class="d-none" onchange="updateAnswerImage(event, '${answer.id}')"/>
    </div>
` : `
<img src="./img/noimage.png" alt="No Image" id="answer-image-${answer.id}" class="img-thumbnail" style="max-height: 200px; width: auto; cursor: pointer;" />
    <div class="image-actions mt-2">
        <button type="button" class="btn btn-sm btn-success" onclick="changeAnswerImage('${answer.id}')">Thêm hình</button>
        <input type="file" id="answer-image-upload-${answer.id}" class="d-none" onchange="updateAnswerImage(event, '${answer.id}')"/>
    </div>
`}


                </div>
                <input type="text" class="form-control mb-2" name="answer_text_${answer.id}" value="${answer.text}" placeholder="Nhập đáp án">
                <div class="form-check form-check-info text-start">
                    <input type="checkbox" class="form-check-input" name="correct_answer_${answer.id}" ${answer.correct ? 'checked' : ''}>
                    <label class="form-check-label">Đáp án đúng</label>
                </div>
            </div>
        `).join('')}
    </div>
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

                                    // Mở modal
                                    function openModal(imageSrc) {
                                        const modal = document.getElementById("imageModal");
                                        const modalImage = document.getElementById("modalImage");

                                        modal.style.display = "block"; // Hiển thị modal
                                        modalImage.src = imageSrc; // Đặt hình ảnh vào trong modal
                                    }

                                    // Đóng modal
                                    document.querySelector(".modal-close").addEventListener("click", function() {
                                        const modal = document.getElementById("imageModal");
                                        modal.style.display = "none"; // Ẩn modal khi đóng
                                    });

                                    // Đóng modal khi nhấp vào vùng ngoài ảnh
                                    window.addEventListener("click", function(event) {
                                        const modal = document.getElementById("imageModal");
                                        if (event.target === modal) {
                                            modal.style.display = "none";
                                        }
                                    });
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
                                                answers.push({
                                                    id: answerId,
                                                    text: answerText,
                                                    correct
                                                });
                                            });
                                            questionsData.push({
                                                id: questionId,
                                                text: questionText,
                                                answers
                                            });
                                        });

                                        $.ajax({
                                            url: 'update_questions.php',
                                            type: 'POST',
                                            contentType: 'application/json',
                                            data: JSON.stringify({
                                                questions: questionsData
                                            }),
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
</div>
</div>
</div>
</div>
<footer class="footer pt-3  ">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
                <div class="copyright text-center text-sm text-muted text-lg-start">
                    ©
                    <script>
                        document.write(new Date().getFullYear())
                    </script>,
                    Matsuya R&D Việt Nam
                </div>
            </div>
            <div class="col-lg-6">
                <ul class="nav nav-footer justify-content-center justify-content-lg-end">

                    <li class="nav-item">
                        <a href="#" class="nav-link text-muted" target="_blank">About
                            Us</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link text-muted" target="_blank">Blog</a>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</footer>
</div>
</main>
<div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
        <i class="fa fa-cog py-2"> </i>
    </a>
    <div class="card shadow-lg">
        <div class="card-header pb-0 pt-3 ">
            <div class="float-start">
                <h5 class="mt-3 mb-0">Airbag Forms</h5>
                <p>See our dashboard options.</p>
            </div>
            <div class="float-end mt-4">
                <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
            <!-- End Toggle Button -->
        </div>
        <hr class="horizontal dark my-1">
        <div class="card-body pt-sm-3 pt-0 overflow-auto">
            <!-- Sidebar Backgrounds -->
            <div>
                <h6 class="mb-0">Sidebar Colors</h6>
            </div>
            <a href="javascript:void(0)" class="switch-trigger background-color">
                <div class="badge-colors my-2 text-start">
                    <span class="badge filter bg-gradient-primary active" data-color="primary"
                        onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
                    <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
                </div>
            </a>
            <!-- Sidenav Type -->
            <div class="mt-3">
                <h6 class="mb-0">Sidenav Type</h6>
                <p class="text-sm">Choose between 2 different sidenav types.</p>
            </div>
            <div class="d-flex">
                <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white"
                    onclick="sidebarType(this)">White</button>
                <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default"
                    onclick="sidebarType(this)">Dark</button>
            </div>
            <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
            <!-- Navbar Fixed -->
            <div class="d-flex my-3">
                <h6 class="mb-0">Navbar Fixed</h6>
                <div class="form-check form-switch ps-0 ms-auto my-auto">
                    <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
                </div>
            </div>
            <hr class="horizontal dark my-sm-4">
            <div class="mt-2 mb-5 d-flex">
                <h6 class="mb-0">Light / Dark</h6>
                <div class="form-check form-switch ps-0 ms-auto my-auto">
                    <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!--   Core JS Files   -->
<script src="./assets/js/core/popper.min.js"></script>
<script src="./assets/js/core/bootstrap.min.js"></script>
<script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="./assets/js/core/jquery-3.6.0.min.js"></script>


<script>
    function changeQuestionImage(questionId) {
        document.getElementById(`question-image-upload-${questionId}`).click();
    }

    function updateQuestionImage(event, questionId) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Here you can update the question image with the uploaded image
                const image = e.target.result;
                const imgElement = document.querySelector(`.question-image img`);
                imgElement.src = image;
                // You may want to send this to the server for saving if needed
            };
            reader.readAsDataURL(file);
        }
    }

    function changeAnswerImage(answerId) {
        document.getElementById(`answer-image-upload-${answerId}`).click();
    }

    function updateAnswerImage(event, answerId) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Here you can update the answer image with the uploaded image
                const image = e.target.result;
                const imgElement = document.querySelector(`[data-answer-id="${answerId}"] .answer-image img`);
                imgElement.src = image;
                // You may want to send this to the server for saving if needed
            };
            reader.readAsDataURL(file);
        }
    }
</script>

<script>
    function loadResult(code) {
        // Gửi yêu cầu AJAX tới server để lấy kết quả
        fetch(`get_test_result.php?code=${code}`)
            .then(response => response.text())
            .then(data => {
                // Cập nhật nội dung của modal với dữ liệu trả về
                document.getElementById('modal-body-content').innerHTML = data;
                // Hiển thị modal
                $('#resultModal').modal('show');
            })
            .catch(error => console.error('Có lỗi xảy ra:', error));
    }

    function editResult(code) {
        // Gửi yêu cầu AJAX tới server để lấy kết quả
        fetch(`update_answers.php?code=${code}`)
            .then(response => response.text())
            .then(data => {
                // Cập nhật nội dung của modal với dữ liệu trả về
                document.getElementById('modal-body-content').innerHTML = data;
                // Hiển thị modal
                $('#resultModal').modal('show');
            })
            .catch(error => console.error('Có lỗi xảy ra:', error));
    }

    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }

    function updateQuestionImage(event, questionId) {
        const file = event.target.files[0];

        // Kiểm tra nếu không chọn file
        if (!file) {
            alert("Vui lòng chọn hình ảnh!");
            return;
        }

        // Kiểm tra định dạng file
        const allowedExtensions = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedExtensions.includes(file.type)) {
            alert("Vui lòng chọn file ảnh hợp lệ (JPEG, PNG, GIF)!");
            return;
        }

        console.log("Selected file:", file); // Kiểm tra file

        const formData = new FormData();
        formData.append('question_image', file);
        formData.append('question_id', questionId);

        // Kiểm tra dữ liệu FormData
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        // Hiển thị trạng thái tải
        const uploadButton = document.querySelector(`#upload-button-${questionId}`);
        if (uploadButton) {
            uploadButton.disabled = true;
            uploadButton.textContent = "Đang tải...";
        }

        fetch('update_question_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log("Server response:", data); // Kiểm tra phản hồi từ server
                if (data.success) {
                    // Cập nhật URL ảnh với timestamp để tránh cache
                    const imgElement = document.querySelector(`#question-image-${questionId}`);
                    if (imgElement) {
                        imgElement.src = `${data.new_image_url}?t=${new Date().getTime()}`;
                    }
                    alert("Cập nhật hình ảnh thành công!");
                } else {
                    alert(data.error || "Cập nhật hình ảnh thất bại");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Có lỗi xảy ra khi tải hình ảnh. Vui lòng thử lại!");
            })
            .finally(() => {
                // Phục hồi trạng thái nút sau khi hoàn tất
                if (uploadButton) {
                    uploadButton.disabled = false;
                    uploadButton.textContent = "Tải lên";
                }
            });
    }


    function updateAnswerImage(event, answerId) {
    const file = event.target.files[0];

    // Kiểm tra nếu không chọn file
    if (!file) {
        alert("Vui lòng chọn hình ảnh!");
        return;
    }

    // Kiểm tra định dạng file
    const allowedExtensions = ['image/jpeg', 'image/png', 'image/gif'];
    if (!allowedExtensions.includes(file.type)) {
        alert("Vui lòng chọn file ảnh hợp lệ (JPEG, PNG, GIF)!");
        return;
    }

    const formData = new FormData();
    formData.append('answer_image', file);
    formData.append('answer_id', answerId);

    // Hiển thị trạng thái tải
    const uploadButton = document.querySelector(`#answer-image-upload-${answerId}`);
    if (uploadButton) {
        uploadButton.disabled = true;
    }

    fetch('update_answer_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật hình ảnh mới trên giao diện
            const imgElement = document.querySelector(`#answer-image-${answerId}`);
            const placeholder = document.querySelector(`#no-image-placeholder-${answerId}`);
            if (imgElement) {
                imgElement.src = `${data.new_image_url}?t=${new Date().getTime()}`;
                imgElement.classList.remove('d-none'); // Hiển thị hình ảnh nếu đang bị ẩn
            }

            // Ẩn placeholder nếu có
            if (placeholder) {
                placeholder.classList.add('d-none');
            }

            alert("Thêm hình ảnh thành công!");
        } else {
            alert(data.error || "Thêm hình ảnh thất bại!");
        }
    })
    .catch(error => {
        console.error("Lỗi:", error);
        alert("Có lỗi xảy ra khi tải hình ảnh. Vui lòng thử lại!");
    })
    .finally(() => {
        if (uploadButton) {
            uploadButton.disabled = false;
        }
    });
}

</script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script src="./assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>