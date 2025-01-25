<?php
$filepath = realpath(dirname(__FILE__));
include_once realpath(dirname(__FILE__) . '/../classes/Main.php');
session_start();
$main = new Main();
$users = $main->getAllUsers();
$department_name = $_SESSION['department'];
$manv = $_GET['manv'] ?? '';              // Lấy mã nhân viên từ form hoặc URL
$day = '';                                // Ngày, nếu không dùng có thể để rỗng
$month = '';                              // Tháng
$year = '';                               // Năm
$test_name = $_GET['test_name'] ?? '';    // Tên bài test từ form hoặc URL
$department_name = $_GET['department_name'] ?? ''; // Bộ phận
$code = '';                               // Mã nhóm
$start_date = $_GET['start_date'] ?? '';  // Ngày bắt đầu
$end_date = $_GET['end_date'] ?? '';      // Ngày kết thúc

$results = $main->getResults(
    $manv,
    $day,
    $month,
    $year,
    $test_name,
    $department_name,
    $code,
    $start_date,
    $end_date
);

echo ($_SESSION['department']);

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
                            <h6>Projects table</h6>
                            <?php echo ($department_name);  ?>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <form method="GET" action="" id="searchForm" class="mb-4 px-4">
                                <div class="d-flex justify-content-end align-items-center flex-wrap">
                                    <div class="me-2">
                                        <label for="test_name" class="form-label" style="font-size: 0.875rem;">Tên bài</label>
                                        <input type="text" name="test_name" class="form-control form-control-sm" id="test_name" style="width: 100px;" value="<?= htmlspecialchars($_GET['test_name'] ?? '') ?>">
                                    </div>
                                    <div class="me-2">
                                        <label for="manv" class="form-label" style="font-size: 0.875rem;">Mã nv</label>
                                        <input type="text" name="manv" class="form-control form-control-sm" id="manv" style="width: 100px;" value="<?= htmlspecialchars($_GET['manv'] ?? '') ?>">
                                    </div>
                                    <div class="me-2">
                                        <label for="date" class="form-label" style="font-size: 0.875rem;">Ngày tháng</label>
                                        <input type="date" name="date" class="form-control form-control-sm" id="date" style="width: 100px;" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                                    </div>
                                    <!-- Thêm các trường tìm kiếm từ ngày đến ngày -->
                                    <div class="me-2">
                                        <label for="start_date" class="form-label" style="font-size: 0.875rem;">Từ ngày</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm" id="start_date" style="width: 100px;" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                                    </div>
                                    <div class="me-2">
                                        <label for="end_date" class="form-label" style="font-size: 0.875rem;">Đến ngày</label>
                                        <input type="date" name="end_date" class="form-control form-control-sm" id="end_date" style="width: 100px;" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                                    </div>

                                    <div class="me-2">
                                        <!-- Chuyển nút thành submit để gửi form -->
                                        <button type="button" style="margin-top: 45px;" class="btn btn-success btn-sm" onclick="exportToExcel()">
                                            Xuất Excel
                                        </button>
                                    </div>
                                </div>
                            </form>



                            <div class="table-responsive p-0">
                                <form action="export_excel.php" method="post">
                                    <table class="table align-items-center justify-content-center mb-0">
                                        <!-- <thead>
                                        <tr>
                                            <th><input  style="margin-left: 0px;" type="checkbox" id="checkAll"></th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">STT</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mã nv</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tên bài test</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Số câu đúng</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kết quả</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">Hoàn thành</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Thời gian</th>
                                            <th></th>
                                        </tr>
                                    </thead> -->
                                        <tbody id="tbody">

                                        </tbody>
                                    </table>
                                </form>
                                <!-- Modal -->
                                <div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            </div>
                                            <div class="modal-body" id="modal-body-content">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
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
    </script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>

    <!-- Github buttons -->
    <!-- <script async defer src="https://buttons.github.io/buttons.js"></script> -->
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="./assets/js/argon-dashboard.min.js?v=2.0.4"></script>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script>
        $(document).ready(function() {
            // Gọi AJAX khi tải trang lần đầu để hiển thị dữ liệu mặc định
            fetchResults();

            // Gọi AJAX khi nhập liệu vào form tìm kiếm
            $('#searchForm input').on('input', function() {
                fetchResults();
            });

            // Hàm AJAX để tải dữ liệu từ `fetch_results.php`
            function fetchResults() {
                const test_name = $('#test_name').val();
                const manv = $('#manv').val();
                const date = $('#date').val();
                const start_date = $('#start_date').val();
                const end_date = $('#end_date').val();
                $.ajax({
                    url: 'fetch_results.php',
                    method: 'GET',
                    data: {
                        test_name,
                        manv,
                        date,
                        start_date,
                        end_date
                    },
                    success: function(data) {
                        $('#tbody').html(data); // Cập nhật nội dung `tbody`
                    }
                });
            }
        });
    </script>

    <script>
        function exportToExcel() {
            // Lấy tất cả checkbox được chọn
            const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');

            // Tạo một mảng để lưu mã code
            const codes = [];

            // Duyệt qua các checkbox được chọn và lấy giá trị của chúng
            checkedCheckboxes.forEach(checkbox => {
                codes.push(checkbox.value); // Lưu mã code vào mảng
            });

            // Kiểm tra xem có mã code nào được chọn không
            if (codes.length === 0) {
                alert('Vui lòng chọn ít nhất một bản ghi trước khi xuất Excel.');
                return;
            }

            const url = `export_excel.php?codes=${encodeURIComponent(codes.join(','))}`;

            // Chuyển hướng tới URL để tải file Excel
            window.location.href = url;
        }
    </script>


</body>

</html>