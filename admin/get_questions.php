<?php
include '../config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $test_id = $_GET['test_id'] ?? null;

    if ($test_id) {
        $sql = "SELECT q.id, q.name, q.question_image, a.id AS answer_id, a.answer AS answer_text,a.answer_image, a.correct AS is_correct
                FROM questions q
                LEFT JOIN answers a ON q.id = a.questions_id
                WHERE q.manage_test_id = :test_id
                ORDER BY q.id, a.id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':test_id', $test_id, PDO::PARAM_INT);
        $stmt->execute();

        $questions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $questions[$row['id']]['id'] = $row['id'];
            $questions[$row['id']]['text'] = $row['name'];
            $questions[$row['id']]['question_image'] = $row['question_image'];
            $questions[$row['id']]['answers'][] = [
                'id' => $row['answer_id'],
                'text' => $row['answer_text'],
                'image' => $row['answer_image'], // Thêm hình ảnh của đáp án
                'correct' => (bool)$row['is_correct'],
            ];
        }

        if (!empty($questions)) {
            echo json_encode(['status' => 'success', 'questions' => array_values($questions)]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No questions found for this test ID.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid test ID.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
