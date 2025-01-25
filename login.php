<!DOCTYPE html>
<html lang="en">

<!-- share.php -->

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="./admin/assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="./admin/assets/img/favicon.png">
  <title>
    Sign In Airbag Forms
  </title>
  <!-- Fonts and icons -->

  <!-- Nucleo Icons -->
  <link href="./admin/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./admin/assets/css/nucleo-svg.css" rel="stylesheet" />

  <link rel="stylesheet" href="./fontawesome/css/all.min.css">

  <link href="./admin/assets/css/nucleo-svg.css" rel="stylesheet" />
  <link id="pagestyle" href="./admin/assets/css/argon-dashboard.css?v=2.0.4" rel="stylesheet" />
</head>


<body class="">

  <?php
  // include_once ("../lib/Session.php");
  // Session::checkAdminLogin();

  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: pre-check=0, post-check=0, max-age=0");
  header("Pragma: no-cache");
  header("Expires: Mon, 6 Dec 1977 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

  ?>
  <?php

  session_start(); // Khởi động phiên
  include './notifications/notifications.php';
  if (isset($_SESSION['error_message'])) {
    // Hiển thị thông báo lỗi bằng alert
    echo "<script>
    showErrorNotification('" . $_SESSION['error_message'] . "');
</script>";


    unset($_SESSION['error_message']);
  }

  ?>
  <nav class="navbar navbar-expand-lg blur border-radius-lg top-0 z-index-3 shadow position-absolute mt-4 py-2 start-0 end-0 mx-4">
    <div class="container-fluid">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 " href="#">
        Airbag Forms
      </a>
      <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon mt-2">
          <span class="navbar-toggler-bar bar1"></span>
          <span class="navbar-toggler-bar bar2"></span>
          <span class="navbar-toggler-bar bar3"></span>
        </span>
      </button>
      <div class="collapse navbar-collapse" id="navigation">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center me-2 active" aria-current="page" href="/airbag/admin">
              <i class="fa fa-chart-pie opacity-6 text-dark me-1"></i>
              Admin
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link me-2" href="#">
              <i class="fa fa-user opacity-6 text-dark me-1"></i>
              Profile
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link me-2" href="#">
              <i class="fas fa-user-circle opacity-6 text-dark me-1"></i>
              Sign Up
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link me-2" href="#">
              <i class="fas fa-key opacity-6 text-dark me-1"></i>
              Sign In
            </a>
          </li>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg" style="background-image: url('./img/signup-cover.jpg'); background-position: top;">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-5 text-center mx-auto">
            <h1 class="text-white mb-2 mt-5">Welcome!</h1>
            <p class="text-lead text-white">Welcome to the exam! Good luck with your test.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card z-index-0">
            <div class="card-header text-center pt-4">
              <h5>Register with</h5>
            </div>
            <div class="row px-xl-5 px-sm-4 px-3">
              <div class="col-3 mx-auto text-center px-1">
                <img style=" height: 69px;" src="./img/logo_company.png" alt="">
                </a>
              </div>

              <div class="mt-2 position-relative text-center w-100">
                <p class="text-sm font-weight-bold mb-2 text-secondary text-border d-inline z-index-2 bg-white px-3">

                </p>
              </div>
            </div>

            <div class="card-body">
              <form role="form" action="getlogin.php" method="post">
                <div class="mb-3">
                  <input type="text" id="username" name="username" class="form-control" placeholder="Mã nhân viên" aria-label="Name" required>
                </div>
                <!-- <div class="mb-3">
                  <input type="email" class="form-control" placeholder="Email" aria-label="Email">
                </div> -->
                <div class="mb-3">
                  <input type="password" id="password" name="password" class="form-control" placeholder="Password" aria-label="Password" required>
                </div>
                <div class="form-check form-check-info text-start">
                  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
                  <label class="form-check-label" for="flexCheckDefault">
                    I agree the <a href="javascript:;" class="text-dark font-weight-bolder">Terms and Conditions</a>
                  </label>
                </div>
                <div class="text-center">
                  <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Sign in</button>
                </div>
                <p class="text-sm mt-3 mb-0">Already have an account? <a href="javascript:;" class="text-dark font-weight-bolder">Sign in</a></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <footer class="footer py-5">
    <div class="container">
      <div class="row">

      </div>
      <div class="row">
        <div class="col-8 mx-auto text-center mt-1">
          <p class="mb-0 text-secondary">
            Copyright © <script>
              document.write(new Date().getFullYear())
            </script> Matsuya R&D Việt Nam
          </p>
        </div>
      </div>
    </div>
  </footer>
  <script src=".admin/assets/js/core/popper.min.js"></script>
  <script src=".admin/assets/js/core/bootstrap.min.js"></script>
  <script src=".admin/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src=".admin/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script async defer src="./js/buttons.js"></script>
  <script src="./assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>