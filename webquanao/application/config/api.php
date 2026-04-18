<?php
// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// 1. Đọc cấu hình từ database.php
require_once(__DIR__ . '/database.php');
$db_config = $db['default'];

$servername = $db_config['hostname'];
$username = $db_config['username'];
$password = $db_config['password'];
$database = $db_config['database'];

$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Kết nối thất bại: " . $conn->connect_error]));
}

// 2. Hàm lấy dữ liệu từ bảng bất kỳ
function fetchTableData($conn, $table) {
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// 3. Lấy dữ liệu từ nhiều bảng
$response = [
    "status" => "success",
    "product" => fetchTableData($conn, "product"),
    "user" => fetchTableData($conn, "user"),
    "order" => fetchTableData($conn, "order"),
    "catalog" => fetchTableData($conn, "catalog"),
    "transaction" => fetchTableData($conn, "transaction")
];

// 4. Trả về dữ liệu dưới dạng JSON
echo json_encode($response, JSON_PRETTY_PRINT);

$conn->close();
?>
