<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .countdown {
            font-size: 20px;
            font-weight: bold;
        }
        .countdown.green { color: green; }
        .countdown.orange { color: orange; }
        .countdown.red { color: red; }
    </style>
    <script>
        let countdown = 5; // Số giây đếm ngược
        function updateCountdown() {
            let countdownElement = document.getElementById("countdown");
            countdownElement.innerText = countdown;

            // Thay đổi màu sắc dựa trên số giây còn lại
            if (countdown > 3) {
                countdownElement.className = "countdown green";
            } else if (countdown > 1) {
                countdownElement.className = "countdown orange";
            } else {
                countdownElement.className = "countdown red";
            }

            if (countdown === 0) {
                window.location.href = "/";
            } else {
                countdown--;
                setTimeout(updateCountdown, 1000);
            }
        }
        setTimeout(updateCountdown, 1000);
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Kết quả thanh toán</h2>
        <table class="table table-bordered">
            <tr>
                <th>Mã đơn hàng:</th>
                <td><?= $txn_ref ?></td>
            </tr>
            <tr>
                <th>Số tiền:</th>
                <td><?= $amount ?></td>
            </tr>
            <tr>
                <th>Nội dung thanh toán:</th>
                <td><?= $order_info ?></td>
            </tr>
            <tr>
                <th>Mã giao dịch tại VNPAY:</th>
                <td><?= $transaction_no ?></td>
            </tr>
            <tr>
                <th>Mã ngân hàng:</th>
                <td><?= $bank_code ?></td>
            </tr>
            <tr>
                <th>Thời gian thanh toán:</th>
                <td><?= $pay_date ?></td>
            </tr>
            <tr>
                <th>Trạng thái:</th>
                <td class="<?= $response_code == '00' ? 'text-success' : 'text-danger' ?>">
                    <?= $status ?>
                </td>
            </tr>
            <?php if (!empty($error_message)) : ?>
            <tr>
                <th>Lý do lỗi:</th>
                <td class="text-danger"><?= $error_message ?></td>
            </tr>
            <?php endif; ?>
        </table>
        <p class="text-center text-muted">
            Bạn sẽ được chuyển hướng sau <span id="countdown" class="countdown green">5</span> giây...
        </p>
        <a href="/" class="btn btn-primary">Tiếp tục mua sắm</a>
    </div>
</body>
</html>
