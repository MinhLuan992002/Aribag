<?php
$filepath = realpath(dirname(__FILE__));
include_once('../config/config.php');
// Kết nối đến cơ sở dữ liệu
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// Kiểm tra kết nối
if ($conn->connect_error) {
  die("Kết nối không thành công: " . $conn->connect_error);
}
// Tháng hiện tại
$currentMonth = date('m');
$currentYear = date('Y');
// Tháng trước
$previousMonth = $currentMonth - 1;
$previousYear = $currentYear;
if ($previousMonth == 0) {
  $previousMonth = 12;
  $previousYear -= 1;
}
// Khởi tạo các biến để lưu trữ dữ liệu
$currentData = ['totalTests' => 0, 'totalUsers' => 0, 'passedCount' => 0, 'failedCount' => 0, 'totalScore' => 0];
$previousData = ['totalTests' => 0, 'totalUsers' => 0, 'passedCount' => 0, 'failedCount' => 0, 'totalScore' => 0];
// Hàm để lấy dữ liệu cho tháng nhất định
function getDataByMonth($conn, $month, $year)
{
  $data = ['totalTests' => 0, 'totalUsers' => 0, 'passedCount' => 0, 'failedCount' => 0, 'totalScore' => 0];
  // Thực hiện thủ tục `getManagerTest` cho tháng được chỉ định
  $sql = "CALL getManagerTest(NULL, ?, ?, NULL, NULL)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $month, $year);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $data['totalTests']++;
    $data['totalUsers']++;
    $data['totalScore'] += $row['score'];

    if ($row['result_status'] == 'Đạt') {
      $data['passedCount']++;
    } else {
      $data['failedCount']++;
    }
  }

  // Đóng statement sau khi dùng
  $stmt->close();

  return $data;
}

// Lấy dữ liệu cho tháng hiện tại và tháng trước
$currentData = getDataByMonth($conn, $currentMonth, $currentYear);
$previousData = getDataByMonth($conn, $previousMonth, $previousYear);

// Tính tỷ lệ phần trăm
function calculatePercentageChange($current, $previous)
{
  if ($previous == 0) {
    return 0; // Tránh chia cho 0
  }
  return round((($current - $previous) / $previous) * 100, 2);
}
// Tính phần trăm cho các thông số
$totalTestsPercentage = calculatePercentageChange($currentData['totalTests'], $previousData['totalTests']);
$totalUsersPercentage = calculatePercentageChange($currentData['totalUsers'], $previousData['totalUsers']);
$passFailPercentage = calculatePercentageChange($currentData['passedCount'], $previousData['passedCount']);
$averageScorePercentage = calculatePercentageChange(
  $currentData['totalScore'] / max($currentData['totalTests'], 1),
  $previousData['totalScore'] / max($previousData['totalTests'], 1)
);
function getYearlyData($conn, $year)
{
  $monthlyData = [];

  for ($month = 1; $month <= 12; $month++) {
    $data = ['totalTests' => 0, 'totalUsers' => 0, 'passedCount' => 0, 'failedCount' => 0, 'totalScore' => 0];

    // Thực hiện thủ tục `getManagerTest` cho tháng được chỉ định
    $sql = "CALL getManagerTest(NULL, ?, ?, NULL, NULL)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
      die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }
    $stmt->bind_param("ii", $month, $year);
    if (!$stmt->execute()) {
      die("Lỗi thực thi câu lệnh: " . $stmt->error);
    }

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
      $data['totalTests']++;
      if ($row['manv']) {
        $data['totalUsers']++;
      }
      $data['totalScore'] += $row['score'];

      if ($row['result_status'] == 'Đạt') {
        $data['passedCount']++;
      } else {
        $data['failedCount']++;
      }
    }

    // Đóng statement sau khi dùng
    $stmt->close();

    $monthlyData[$month] = $data;
  }

  return $monthlyData;
}
// Năm hiện tại
$currentYear = date('Y');
// Lấy dữ liệu cho từng tháng trong năm hiện tại
$yearlyData = getYearlyData($conn, $currentYear);
// Chuẩn bị dữ liệu cho biểu đồ
$months = [];
$totalTests = [];
$totalUsers = [];
$passedCounts = [];
$averageScores = [];

foreach ($yearlyData as $month => $data) {
  $months[] = date('F', mktime(0, 0, 0, $month, 1)); // Tên tháng
  $totalTests[] = $data['totalTests'];
  $totalUsers[] = $data['totalUsers'];
  $passedCounts[] = $data['passedCount'];
  $averageScores[] = $data['totalTests'] > 0 ? round($data['totalScore'] / $data['totalTests'], 2) : 0;
}
// Trả về dữ liệu dưới dạng JSON
$sql = "SELECT manage_test.name, department.name AS department_name, manage_test.UpdateTime
        FROM manage_test
        JOIN department ON manage_test.department_id = department.id
        WHERE manage_test.IsDeleted = 0 AND manage_test.IsActive = 1
        ORDER BY department.name, manage_test.UpdateTime";
$result = $conn->query($sql);


$department = $conn->query('SELECT name, UpdateTime FROM department WHERE IsDeleted = 0 AND IsActive = 1');
// Đóng kết nối
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php include './share/share_head.php'; ?>

<body class="g-sidenav-show   bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <script src="./assets/js/plugins/chart.js"></script>
  <?php
  include './assets/common/sidebar.php';
  // Kết nối cơ sở dữ liệu
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  $conn->set_charset("utf8");
  // Kiểm tra kết nối
  if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
  }
  $conn->close();
  ?>
  <main class="main-content position-relative border-radius-lg ">
    <?php
    $pageTitle = "Dashboard";
    include('./assets/common/nav_main.php') ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Bài test tháng này</p>
                    <h5 class="font-weight-bolder"><?php echo $currentData['totalTests']; ?></h5>
                    <p class="mb-0">
                      <span class="<?php echo ($totalTestsPercentage >= 0) ? 'text-success' : 'text-danger'; ?> text-sm font-weight-bolder">
                        <?php echo ($totalTestsPercentage >= 0 ? '+' : '') . $totalTestsPercentage; ?>%
                      </span> so với tháng trước
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                    <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Người làm trong tháng</p>
                    <h5 class="font-weight-bolder"><?php echo $currentData['totalUsers']; ?></h5>
                    <p class="mb-0">
                      <span class="<?php echo ($totalUsersPercentage >= 0) ? 'text-success' : 'text-danger'; ?> text-sm font-weight-bolder">
                        <?php echo ($totalUsersPercentage >= 0 ? '+' : '') . $totalUsersPercentage; ?>%
                      </span> so với tuần trước
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                    <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Passed and Failed</p>
                    <h5 class="font-weight-bolder">Pass: <?php echo $currentData['passedCount']; ?>, Fail: <?php echo $currentData['failedCount']; ?></h5>
                    <p class="mb-0">
                      <span class="<?php echo ($passFailPercentage >= 0) ? 'text-success' : 'text-danger'; ?> text-sm font-weight-bolder">
                        <?php echo ($passFailPercentage >= 0 ? '+' : '') . $passFailPercentage; ?>%
                      </span> so với quý trước
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                    <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Kết quả bài kiểm tra</p>
                    <h5 class="font-weight-bolder"><?php echo round($currentData['totalScore'] / max($currentData['totalTests'], 1), 2); ?>%</h5>
                    <p class="mb-0">
                      <span class="<?php echo ($averageScorePercentage >= 0) ? 'text-success' : 'text-danger'; ?> text-sm font-weight-bolder">
                        <?php echo ($averageScorePercentage >= 0 ? '+' : '') . $averageScorePercentage; ?>%
                      </span> so với tháng trước
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                    <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Tests overview</h6>
              <p class="text-sm mb-0">
                <i class="fa fa-arrow-up text-success"></i>
                <span id="dynamic-percentage" class="font-weight-bold"></span> in <span id="dynamic-year"></span>
              </p>
            </div>
            <div class="card-body p-3">
              <div class="chart">
                <canvas id="trainingChart" class="chart-canvas"></canvas>
              </div>
            </div>
          </div>
          <script>
            const ctx = document.getElementById('trainingChart').getContext('2d');
            const trainingChart = new Chart(ctx, {
              type: 'bar',
              data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Tổng số bài kiểm tra',
                    data: <?= json_encode($totalTests) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                  },
                  {
                    label: 'Tổng số người dùng',
                    data: <?= json_encode($totalUsers) ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                  },
                  {
                    label: 'Số bài kiểm tra đạt',
                    data: <?= json_encode($passedCounts) ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                  },
                  {
                    label: 'Điểm trung bình',
                    data: <?= json_encode($averageScores) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                  }
                ]
              },
              options: {
                responsive: true,
                scales: {
                  y: {
                    beginAtZero: true
                  }
                }
              }
            });
          </script>
        </div>
        <div class="col-lg-5">
          <div class="card card-carousel overflow-hidden h-100 p-0">
            <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
              <div class="carousel-inner border-radius-lg h-100">
                <div class="carousel-item h-100 active" style="background-image: url('./assets/img/company.jpg');
      background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-camera-compact text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Get started with Airbag Forms</h5>
                    <p>You can easily manage all the tests of your department.</p>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('./assets/img/login-cover.png');
                      background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-bulb-61 text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Faster way to create tests</h5>
                    <p>This can improve the training process for the members of your department.</p>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('./assets/img/caseat.jpg');
                      background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-trophy text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Share your advice and opinions with us!</h5>
                    <p>To improve and enhance the quality of the website.</p>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('./assets/img/AIRBAG2.jpg');
      background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-trophy text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Welcome to Airbag Forms!</h5>
                    <p>Have a nice day!</p>
                  </div>
                </div>
              </div>
              <button class="carousel-control-prev w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">

        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-2">Bài Test Theo bộ phận</h6>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
              <table class="table align-items-center">
                <thead>
                  <tr>
                    <th>Tên Bài Test</th>
                    <th>Bộ Phận</th>
                    <th>Ngày Tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Kiểm tra nếu có kết quả từ truy vấn
                  if ($result->num_rows > 0) {
                    // Lặp qua từng dòng dữ liệu
                    while ($row = $result->fetch_assoc()) {
                  ?>
                      <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                        <td><?= htmlspecialchars($row['UpdateTime']) ?></td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo "<tr><td colspan='3' class='text-center'>Không có bài test nào</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-0">Danh Sách Bộ Phận</h6>
            </div>
            <div class="card-body p-3" class="table-responsive" style="max-height: 500px; overflow-y: auto;">
              <ul class="list-group">
                <?php


                // Kiểm tra nếu có kết quả từ truy vấn
                if ($department->num_rows > 0) {
                  // Lặp qua từng dòng dữ liệu và hiển thị
                  while ($row = $department->fetch_assoc()) {
                ?>
                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                      <div class="d-flex align-items-center">
                        <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                          <i class="ni ni-building text-white opacity-10"></i>
                        </div>
                        <div class="d-flex flex-column">
                          <h6 class="mb-1 text-dark text-sm"><?= htmlspecialchars($row['name']) ?></h6>
                          <span class="text-xs">Ngày tạo: <span class="font-weight-bold"><?= htmlspecialchars($row['UpdateTime']) ?></span></span>
                        </div>
                      </div>
                      <div class="d-flex">
                        <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                      </div>
                    </li>
                <?php
                  }
                } else {
                  echo "<li class='list-group-item border-0 text-center'>Không có bộ phận nào</li>";
                }
                ?>
              </ul>
            </div>
          </div>

        </div>
      </div>
      <footer class="footer pt-3  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                Matsuya R&D Việt Nam
              </div>
            </div>
            <div class="col-lg-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="#" class="nav-link text-muted" target="_blank"> Matsuya </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link text-muted" target="_blank">About Us</a>
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
            <span class="badge filter bg-gradient-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
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
          <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
          <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default" onclick="sidebarType(this)">Dark</button>
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
  <!--   Core JS Files   -->
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/chartjs.min.js"></script>
  <script>
    var ctx1 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
    new Chart(ctx1, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Mobile apps",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#5e72e4",
          backgroundColor: gradientStroke1,
          borderWidth: 3,
          fill: true,
          data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#fbfbfb',
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#ccc',
              padding: 20,
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
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
</body>

</html>