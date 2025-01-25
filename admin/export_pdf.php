<?php
require_once '../vendor/autoload.php'; // Đường dẫn đến Dompdf
include_once('../config/config.php');
include_once('../classes/Exam.php');

use Dompdf\Dompdf;
use Dompdf\Options;

// Bước 1: Cấu hình Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans'); // Sử dụng font hỗ trợ tiếng Việt

$dompdf = new Dompdf($options);

// Lấy giá trị của code từ GET
$code = $_GET['code'];

// Tạo đối tượng Exam
$exam = new Exam();

// Lấy thông tin bài kiểm tra và người dùng
$userAnswers = $exam->getUserAnswersByCode($code);
$userInfo = $exam->getUserInfoByCode($code);
$test_id = $userInfo['test_id'];
$testName = $exam->getTestName($test_id);
$testDetails = $exam->getQuestionsAndAnswers($test_id);

// Bước 2: Tạo HTML cho nội dung PDF
$html = '
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header { 
            text-align: center;
            background-color: #28a745;
            color: #fff;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            font-size: 20px;
            font-weight: bold;
        }
        .title {
            text-align: center;
            color: #28a745;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .info-table th {
            background-color: #f2f2f2;
        }
        .question {
            margin-top: 20px;
            font-weight: bold;
        }
        .options {
            margin-left: 20px;
        }
        .option {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .option input[type="radio"] {
            margin-right: 10px;
        }
        .correct {
            color: green;
            font-weight: bold;
        }
        .incorrect {
            color: red;
            font-weight: bold;
        }
        .image {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            BÀI TEST CỦA MÃ NHÂN VIÊN: ' . htmlspecialchars($userInfo['manv']) . '
        </div>
        <div class="title">
            BÀI KIỂM TRA: ' . htmlspecialchars($testName) . '
        </div>
        <table class="info-table">
            <tr>
                <th>Họ tên</th>
                <td>' . htmlspecialchars($userInfo['fullname']) . '</td>
            </tr>
            <tr>
                <th>Mã nhân viên</th>
                <td>' . htmlspecialchars($userInfo['manv']) . '</td>
            </tr>
            <tr>
                <th>Ngày làm bài</th>
                <td>' . htmlspecialchars($userInfo['test_date']) . '</td>
            </tr>
            <tr>
                <th>Số câu làm đúng</th>
                <td>' . $exam->getCorrectAnswersCount($userInfo['manv'], $test_id) . '</td>
            </tr>
        </table>
';

// Hiển thị câu hỏi và câu trả lời
$questionNumber = 1;
foreach ($testDetails as $detail) {
    $questions[$detail['question_id']]['question_text'] = $detail['question_text'];
    $questions[$detail['question_id']]['question_image'] = $detail['question_image'];
    $questions[$detail['question_id']]['answers'][] = [
        'id' => $detail['answer_id'],
        'text' => $detail['answer_text'],
        'is_correct' => $detail['is_correct'],
        'answer_image' => $detail['answer_image']
    ];
}

foreach ($questions as $question_id => $question_info) {
    $html .= '<div class="form-group">
    <label class="question">' . $question_info['question_text'] . '</label>
</div>';


    if (isset($userAnswers[$question_id])) {
        $userAnswer = $userAnswers[$question_id];
        $correctAnswer = false;

        foreach ($question_info['answers'] as $answer) {
            if ($answer['is_correct'] == $userAnswer) {
                $correctAnswer = $answer['is_correct'];
                break;
            }
        }

        $iconClass = $correctAnswer ? 'check-icon correct' : 'check-icon incorrect';
        $icon = $correctAnswer ? '✔' : '✘';
        $html .= '<span class="' . $iconClass . '">' . $icon . '</span>';
    }

    $html .= '</label>';

    // Hiển thị hình ảnh câu hỏi nếu có
    if (!empty($question_info['question_image'])) {
        $html .= '<img src="http://192.168.100.9:81/airbag/admin/' . htmlspecialchars($question_info['question_image']) . '" class="image" alt="Hình ảnh câu hỏi">';
    }

    $html .= '<div class="options">';
    // Biến lưu trữ câu sai được chọn ngẫu nhiên
    $randomWrongAnswer = false;

    foreach ($question_info['answers'] as $answer) {
        $isUserAnswer = ''; // Biến xác định câu trả lời có được chọn hay không

        // Kiểm tra nếu người dùng đã chọn câu trả lời
        if (isset($userAnswers[$question_id])) {
            // Nếu câu trả lời là đúng
            if ($userAnswers[$question_id] == $answer['is_correct']) {
                // $isUserAnswer =  ($userAnswers[$question_id] && $userAnswers[$question_id] == $answer['is_correct']) ? 'checked' : '';
                if ($userAnswers[$question_id] && $userAnswers[$question_id] == $answer['is_correct']) {
                    $isUserAnswer = 'checked';
                } else {
                    if (!$randomWrongAnswer) {
                        $isUserAnswer = 'checked';
                        $randomWrongAnswer = true;  // Đánh dấu là đã chọn câu sai ngẫu nhiên
                    }
                }
            }
        }

        // Tạo HTML cho câu trả lời
        $html .= '<div class="option">
                <input type="radio" ' . $isUserAnswer . ' disabled />
                ' . htmlspecialchars($answer['text']);

        // Hiển thị hình ảnh câu trả lời nếu có
        if (!empty($answer['answer_image'])) {
            $html .= '<br>  <img src="http://192.168.100.9:81/airbag/admin/' . htmlspecialchars($answer['answer_image']) . '" class="image" alt="Hình ảnh câu trả lời">';
        }

        $html .= '</div>';
    }



    $html .= '</div>';
    $questionNumber++;
}

$html .= '
    </div>
</body>
</html>
';

// Bước 3: Tạo PDF từ HTML
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Bước 4: Xuất file PDF
$dompdf->stream("ket_qua_bai_kiem_tra.pdf", ["Attachment" => false]);
