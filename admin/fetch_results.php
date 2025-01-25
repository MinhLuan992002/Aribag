<?php
include_once realpath(dirname(__FILE__) . '/../classes/Main.php');
session_start();
$main = new Main();

// Lấy dữ liệu từ GET

$test_name = htmlspecialchars($_GET['test_name'] ?? '');
$manv = htmlspecialchars($_GET['manv'] ?? '');
$date = htmlspecialchars($_GET['date'] ?? '');
$start_date = $_GET['start_date'] ?? '';  // Ngày bắt đầu
$end_date = $_GET['end_date'] ?? '';      // Ngày kết thúc
// echo( $_GET['start_date'] );
// Debug để kiểm tra dữ liệu nhận từ form


// Lấy tên bộ phận từ session
$department_name = $_SESSION['department'];

// Khởi tạo các biến cho ngày, tháng, năm
$day = '';
$month = '';
$year = '';

// Nếu có giá trị ngày tháng năm, chia nhỏ thành các phần
if (!empty($date)) {
    $date_parts = explode('-', $date);
    if (!empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date_parts = explode('-', $date);
        $year = intval($date_parts[0]);
        $month = intval($date_parts[1]);
        $day = intval($date_parts[2]);
        echo "Ngày không hợp lệ!";
    }
}
// Gọi phương thức getResults với các tham số cần thiết
$results = $main->getResults(
    $manv,
    $day,
    $month,
    $year,
    $test_name,
    $department_name,
    $code = '',
    $start_date,
    $end_date
);


?>



<tr>
    <th><input type="checkbox" id="checkAll"></th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">STT</th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mã nv</th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tên bài test</th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Số câu đúng</th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kết quả</th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">Hoàn thành</th>
    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Thời gian</th>
    <th></th>
</tr>

<?php if (empty($results)): ?>
    <tr>
        <td colspan="9" class="text-center">Không có kết quả nào.</td>
    </tr>
<?php else: ?>
    <?php foreach ($results as $row): ?>
        <tr>
            <td><input type='checkbox' class='row-checkbox' value='<?= htmlspecialchars($row['code']) ?>'></td>
            <td class='text-sm font-weight-bold mb-0'><?= htmlspecialchars($row['STT']) ?></td>
            <td>
                <div class='d-flex flex-column justify-content-center'>
                    <h6 class='mb-0 text-sm'><?= htmlspecialchars($row['fullname']) ?></h6>
                    <p class='text-xs text-secondary mb-0'><?= htmlspecialchars($row['manv']) ?></p>
                </div>
            </td>
            <td>
                <div class='d-flex flex-column justify-content-center'>
                    <p class='text-xs font-weight-bold mb-0'><?= htmlspecialchars($row['test_name']) ?></p>
                </div>
            </td>
            <td>
                <div class='d-flex flex-column justify-content-center'>
                    <p class='mb-0 text-sm'><?= htmlspecialchars($row['correct_answers']) ?></p>
                </div>
            </td>
            <td>
                <span class='text-xs font-weight-bold <?= ($row['result_status'] == 'Đạt' ? 'text-success' : 'text-danger') ?>'><?= htmlspecialchars($row['result_status']) ?></span>
            </td>
            <td class='align-middle text-center'>
                <div class='d-flex align-items-center justify-content-center'>
                    <span class='me-2 text-xs font-weight-bold'><?= htmlspecialchars($row['score']) ?>%</span>
                    <div>
                        <div class='progress'>
                            <div class='progress-bar bg-gradient-<?= ($row['score'] >= 100 ? 'success' : 'danger') ?>' role='progressbar' aria-valuenow='<?= htmlspecialchars($row['score']) ?>' aria-valuemin='0' aria-valuemax='100' style='width: <?= htmlspecialchars($row['score']) ?>%;'></div>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div class='d-flex flex-column justify-content-center'>
                    <p class='mb-0 text-sm'><?= date('d/m/Y', strtotime($row['test_date'])) ?></p>
                </div>
            </td>
            <td class='align-middle'>
                <button class='btn btn-link text-secondary mb-0' type='button' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                    <i class='fa fa-ellipsis-v text-xs'></i>
                </button>
                <ul class='dropdown-menu dropdown-menu-end' aria-labelledby='dropdownMenuButton'>
                    <li><a class='dropdown-item' onclick='editResult("<?= htmlspecialchars($row["code"]) ?>")' data-bs-toggle='modal' data-bs-target='#resultModal' href='#'>Edit</a></li>
                    <li><a class='dropdown-item' onclick='loadResult("<?= htmlspecialchars($row["code"]) ?>")' data-bs-toggle='modal' data-bs-target='#resultModal' href='#'>View</a></li>
                    <!-- <li>
                        <a class='dropdown-item' onclick='deleteResult("<?= htmlspecialchars($row["code"]) ?>")' href='#'>Delete</a>
                    </li> -->
                    <?php
                    if ($_SESSION['department_id'] = 'null') {
                        echo '<li>';
                        echo '<a class="dropdown-item" href="#" onclick="deleteResult(\'' . htmlspecialchars($row['code']) . '\')">Delete</a>';
                        echo '</li>';
                    }
                    ?>

                </ul>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    $(document).ready(function() {
        // Check All
        $('#checkAll').on('click', function() {
            $('.row-checkbox').prop('checked', this.checked);
        });

        // Nếu tất cả các dòng đều được check, thì "Check All" sẽ được check, ngược lại thì bỏ check
        $('.row-checkbox').on('click', function() {
            if ($('.row-checkbox:checked').length == $('.row-checkbox').length) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        });
    });

    function deleteResult(code) {
        console.log("Code nhận được:", code); // Kiểm tra giá trị code
        if (confirm("Bạn có chắc chắn muốn xóa bản ghi này không?")) {
            fetch('delete_record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        code: code
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Phản hồi từ server:", data); // Kiểm tra phản hồi
                    if (data.success) {
                        alert("Đã xóa thành công!");
                        location.reload();
                    } else {
                        alert("Xóa không thành công!");
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        }
    }
</script>