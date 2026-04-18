<?php
// Proxy script để gọi đến container OpenAI

// Nhận dữ liệu từ client
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Thêm thông tin về loại phân tích dựa trên panel_title nếu có
if (isset($data['panel_title'])) {
    // Ghi log để debug
    file_put_contents('analyze_log.txt', date('Y-m-d H:i:s') . ' - Panel: ' . $data['panel_title'] . "\n", FILE_APPEND);
    
    // Nếu có dữ liệu bảng, thêm vào log
    if (isset($data['table_data'])) {
        file_put_contents('analyze_log.txt', date('Y-m-d H:i:s') . ' - Data: ' . json_encode($data['table_data']) . "\n", FILE_APPEND);
    }
}

// Gọi đến container OpenAI
$openai_url = 'http://openai:5002/api/market/analyze'; // Sử dụng tên service trong docker-compose

$ch = curl_init($openai_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Thực hiện request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Đặt header để luôn trả về JSON
header('Content-Type: application/json');

// Kiểm tra lỗi kết nối
if ($curl_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi kết nối đến service AI: ' . $curl_error
    ]);
    exit;
}

// Kiểm tra response có phải JSON hợp lệ không
if ($response) {
    $json_test = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Kiểm tra nếu có thông báo lỗi về API key
        if (isset($json_test['success']) && $json_test['success'] === false && 
            isset($json_test['message']) && strpos($json_test['message'], 'API key') !== false) {
            // Thay thế thông báo lỗi API key bằng thông báo thân thiện hơn
            echo json_encode([
                'status' => 'error',
                'message' => 'Xin lỗi, không thể phân tích dữ liệu lúc này. Dịch vụ AI không khả dụng hoặc API key không hợp lệ. Vui lòng liên hệ quản trị viên để kiểm tra cấu hình API.'
            ]);
        } else {
            // Nếu là JSON hợp lệ khác, trả về nguyên bản
            echo $response;
        }
    } else {
        // Nếu không phải JSON, bọc nó trong một JSON hợp lệ
        echo json_encode([
            'status' => 'error',
            'message' => 'Phản hồi không phải định dạng JSON',
            'raw_response' => $response
        ]);
    }
} else {
    // Không có response
    echo json_encode([
        'status' => 'error',
        'message' => 'Không nhận được phản hồi từ service AI',
        'http_code' => $http_code
    ]);
}