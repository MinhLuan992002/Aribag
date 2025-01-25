<?php
require_once './lib/Database.php';
include 'notifications/notifications.php';
session_start(); // Bắt đầu phiên


// Thông tin LDAP server
$ldap_host = "ldap://ctmatsuyard.com";
$ldap_port = 389;
$ldap_dn = "DC=ctmatsuyard,DC=com"; // Thư mục gốc của LDAP

// Lấy dữ liệu từ form đăng nhập
$username = $_POST['username'];
$password = $_POST['password'];
$isDeleted = 0;
$isActive = 1;
$manv = '';
$code = 'Employee';
$userType = 'Employee';

// Kết nối đến LDAP server
$ldap_conn = ldap_connect($ldap_host, $ldap_port);
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

if ($ldap_conn) {
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);  // Tắt auto referral
    // Chuẩn bị thông tin đăng nhập với tên domain
    $ldap_user = "$username@ctmatsuyard.com";

    // Xác thực với LDAP
    $ldap_bind = @ldap_bind($ldap_conn, $ldap_user, $password);

    if ($ldap_bind) {
        echo "LDAP bind successful!";
    } else {
        // Lưu thông báo vào session
        $_SESSION['error_message'] = 'Xác thực thất bại. Vui lòng kiểm tra tên đăng nhập và mật khẩu.';
        
        // Chuyển hướng về trang login
        header("Location: login.php");
        exit(); // Dừng thực thi mã sau khi chuyển hướng
    }
    

    if ($ldap_bind) {
        // Tìm kiếm `sAMAccountName` và `displayName` từ LDAP
        $filter = "(|(sAMAccountName=$username)(displayName=$username))";
        $attributes = ["sAMAccountName", "displayName","mail","description","employeeid"];

        
        $result = ldap_search($ldap_conn, $ldap_dn, $filter, $attributes);

        if ($result) {
            $entries = ldap_get_entries($ldap_conn, $result);

            if ($entries["count"] > 0) {
                $manv = isset($entries[0]["employeeid"][0]) ? $entries[0]["employeeid"][0] : 'Không có mã nhân viên';
                $displayName = isset($entries[0]["displayname"][0]) ? $entries[0]["displayname"][0] : 'Không có tên hiển thị';

                // Hiển thị thông báo chào mừng
                echo "Xác thực thành công. Chào mừng, " . htmlspecialchars($displayName) . "!";

                // Khởi tạo phiên
                $_SESSION['username'] = $username; // Lưu tên người dùng vào phiên
                $_SESSION['displayName'] = $displayName; // Lưu tên hiển thị vào phiên
                $_SESSION['manv'] = $manv; // Lưu mã nhân viên vào phiên

                // Kết nối và lưu thông tin người dùng vào cơ sở dữ liệu
                try {
                    $db = new Database('localhost:3309', 'airbag', 'root', '');
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $procedureName = 'sp_insert_update_users';
                    $params = [$manv, $displayName, $username, $hashed_password, $isDeleted, $isActive, $code, $userType];
                    $result = $db->call($procedureName, $params);

                    if ($result>0) {
                        // Chuyển hướng đến trang index
                        header("Location: index.php");
                        exit;
                    } else {
                        echo "<script>
                        showErrorNotification('Không thể lưu thông tin người dùng!');
                
                    </script>";
                    }

                } catch (PDOException $e) {
                    echo "Lỗi kết nối đến cơ sở dữ liệu: " . $e->getMessage();
                }
            } else {
                echo "Không tìm thấy thông tin người dùng.";
            }
        } else {
            echo "Lỗi khi tìm kiếm trong LDAP.";
        }
    } else {
        echo "<script>
        showErrorNotification('Xác thực thất bại. Vui lòng kiểm tra tên đăng nhập và mật khẩu.');

    </script>";
    }

    ldap_close($ldap_conn);
} else {
    echo "Không thể kết nối đến LDAP server.";
}

?>
