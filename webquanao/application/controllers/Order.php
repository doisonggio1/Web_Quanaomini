<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->config->load('vnpay'); // Load cấu hình VNPAY
		$this->load->helper('email');
		$this->load->model('cart_model');
		$this->load->model('shipping_rule_model');
		$this->load->model('coupon_model');
		$this->load->model('user_coupon_model');
	}

	public function validate_email($to_mail, $to_name, $subject, $body, $altBody)
	{
		if (send_email($to_mail, $to_name, $subject, $body, $altBody)) {
			return true;
		} else {
			return false;
		}
	}

	public function index()
	{
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url('/dang-nhap'));
		}
		$carts = $this->cart_model->get_cart_with_catalog_id(['cart.user_id' => $user->id]);
		if (empty($carts)) {
			redirect(base_url('/'));
			return;
		}

		$this->data['user'] = $user;
		$total_amount = 0;
		foreach ($carts as $value) {
			$total_amount = $total_amount + ($value->price * $value->qty);
		}

		$this->data['carts_info'] = $carts;
		$this->data['total_amount'] = $total_amount;

		$this->data['temp'] = 'site/order/index.php';
		$this->load->view('site/layoutsub', $this->data);
	}

	public function sending_mail($email, $total_amount, $payment, $formatted_time)
	{
		$validation_email = $this->validate_email(
			$email,
			$email,
			'Xác thực Email - Shop quần áo mini',
			"
				<!DOCTYPE html>
				<html lang='vi'>
				<head>
				<meta charset='UTF-8'>
				<meta name='viewport' content='width=device-width, initial-scale=1.0'>
				<title>Thông báo thanh toán</title>
				<style>
					body {
					font-family: Arial, sans-serif;
					background-color: #f4f4f4;
					margin: 0;
					padding: 0;
					}
					.email-container {
					width: 100%;
					max-width: 600px;
					margin: 0 auto;
					background-color: #ffffff;
					padding: 20px;
					border-radius: 8px;
					box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
					}
					.header {
					text-align: center;
					margin-bottom: 20px;
					}
					.header h1 {
					color: #007bff;
					font-size: 24px;
					}
					.content {
					font-size: 16px;
					color: #333;
					line-height: 1.6;
					}
					.content p {
					margin: 10px 0;
					}
					.footer {
					text-align: center;
					font-size: 14px;
					color: #777;
					margin-top: 20px;
					}
					.footer a {
					color: #007bff;
					text-decoration: none;
					}
				</style>
				</head>
				<body>

				<div class='email-container'>
					<div class='header'>
					<h1>Thông báo thanh toán thành công</h1>
					</div>
					<div class='content'>
					<p>Chào bạn,</p>
					<p>Chúng tôi xin thông báo rằng bạn đã thanh toán thành công đơn hàng có giá trị <strong>$total_amount VNĐ</strong> với phương thức thanh toán: <strong>$payment</strong> vào lúc: <strong>$formatted_time</strong>.</p>
					<p>Shop quần áo mini xin chân thành cảm ơn bạn đã lựa chọn và tin tưởng sản phẩm của chúng tôi. Chúng tôi hy vọng bạn sẽ hài lòng với đơn hàng của mình!</p>
					</div>
					<div class='footer'>
					<p>Để biết thêm thông tin, vui lòng truy cập website của chúng tôi: <a href='http://localhost:8080/'>www.quanaongoclan.com</a></p>
					</div>
				</div>

				</body>
				</html>
				",
			"
				Tiêu đề: Xác nhận thay đổi mật khẩu

				Nội dung:

				<p>Xin chào,</p>
				<p>Chúng tôi rất vui khi thông báo rằng bạn đã hoàn tất thanh toán cho đơn hàng có giá trị <strong>$total_amount</strong>. Đơn hàng của bạn đã được thanh toán thông qua phương thức <strong>$payment</strong> vào lúc <strong>$formatted_time</strong>.</p>
				<p>Chúng tôi chân thành cảm ơn sự tin tưởng và lựa chọn của bạn. Đội ngũ Shop quần áo mini rất mong bạn sẽ hài lòng với sản phẩm của mình và sẽ tiếp tục đồng hành cùng chúng tôi trong những lần mua sắm tiếp theo.</p>
				<p>Nếu bạn có bất kỳ thắc mắc nào về đơn hàng, vui lòng liên hệ với chúng tôi qua email hoặc điện thoại hỗ trợ.</p>
				"
		);
		if (!$validation_email) {
			return false;
		}
		return true;
	}

	// Khởi tạo thanh toán
	public function payment()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');

			$user = $this->session->userdata('user');
			if (!isset($user)) {
				redirect(base_url('/dang-nhap'));
			}

			if (!$user) {
				echo json_encode(["status" => "error", "message" => "Người dùng không tồn tại"], JSON_UNESCAPED_UNICODE);
				return;
			}

			$carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);

			if (empty($carts)) {
				echo json_encode(["status" => "error", "message" => "Giỏ hàng trống"], JSON_UNESCAPED_UNICODE);
				return;
			}
			$total_amount = 0;
			foreach ($carts as $value) {
				$total_amount = $total_amount + ($value->qty * $value->price);
			}

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);

			// Kiểm tra nếu không có dữ liệu
			if (!$data) {
				echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
				return;
			}
			$this->session->set_userdata('formData', $data);

			$vnp_TmnCode = $this->config->item('vnp_TmnCode');
			$vnp_HashSecret = $this->config->item('vnp_HashSecret');
			$vnp_Url = $this->config->item('vnp_Url');
			$vnp_Locale = $this->config->item('vnp_Locale');
			$vnp_ReturnUrl = $this->config->item('vnp_Returnurl');
			$vnp_StartTime = date("YmdHis");
			$vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($vnp_StartTime))); // Thời gian hết hạn
			$vnp_CurrCode = $this->config->item('vnp_CurrCode');
			$vnp_Command = $this->config->item('vnp_Command');
			$vnp_BankCode = $this->config->item('vnp_BankCode');

			$invalidKeys = array('YOUR_KEY', 'YOUR_TMNCODE', 'YOUR_HASHSECRET');
			if (
				empty($vnp_TmnCode)
				|| empty($vnp_HashSecret)
				|| in_array(strtoupper(trim($vnp_TmnCode)), $invalidKeys)
				|| in_array(strtoupper(trim($vnp_HashSecret)), $invalidKeys)
			) {
				echo json_encode([
					"status" => "error",
					"message" => "VNPAY chưa cấu hình TMNCODE/HASHSECRET hợp lệ trong file .env"
				], JSON_UNESCAPED_UNICODE);
				return;
			}

			if (strpos($vnp_ReturnUrl, 'localhost') !== false || strpos($vnp_ReturnUrl, '127.0.0.1') !== false) {
				echo json_encode([
					"status" => "error",
					"message" => "VNPAY_RETURN_URL đang là localhost. Hãy dùng domain public (ví dụ ngrok) và khai báo đúng trên VNPAY Sandbox"
				], JSON_UNESCAPED_UNICODE);
				return;
			}

			// Lấy IP của user 
			$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
			$vnp_Amount = $total_amount;

			// Kiểm tra dữ liệu nhập vào
			if (!$vnp_Amount || $vnp_Amount <= 0) {
				echo "Số tiền không hợp lệ!";
				return;
			}

			// Chuyển số tiền sang đơn vị VNĐ (VNPAY yêu cầu nhân 100)
			$vnp_Amount = $vnp_Amount * 100;

			// Mã đơn hàng duy nhất
			$vnp_TxnRef = time();
			$vnp_OrderInfo = "Thanh toán đơn hàng #" . $vnp_TxnRef;

			// Dữ liệu gửi lên VNPAY
			$inputData = array(
				"vnp_Version" => "2.1.0",
				"vnp_TmnCode" => $vnp_TmnCode,
				"vnp_Amount" => $vnp_Amount,
				"vnp_Command" => $vnp_Command,
				"vnp_CreateDate" => $vnp_StartTime,
				"vnp_CurrCode" => $vnp_CurrCode,
				"vnp_IpAddr" => $vnp_IpAddr,
				"vnp_Locale" => $vnp_Locale,
				"vnp_OrderInfo" => $vnp_OrderInfo,
				"vnp_OrderType" => "billpayment",
				"vnp_ReturnUrl" => $vnp_ReturnUrl,
				"vnp_TxnRef" => $vnp_TxnRef,
				"vnp_ExpireDate" => $vnp_ExpireDate // Thêm thời gian hết hạn vào request
			);

			// Nếu người dùng chọn ngân hàng, thêm vào request
			if (!empty($vnp_BankCode)) {
				$inputData["vnp_BankCode"] = $vnp_BankCode;
			}

			ksort($inputData);
			$query = "";
			$hashdata = "";
			$i = 0;

			foreach ($inputData as $key => $value) {
				if ($key != "vnp_SecureHash") { // Loại bỏ chữ ký cũ trước khi ký lại
					if ($i == 1) {
						$hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
					} else {
						$hashdata .= urlencode($key) . "=" . urlencode($value);
						$i = 1;
					}
					$query .= urlencode($key) . "=" . urlencode($value) . '&';
				}
			}

			// Loại bỏ ký tự '&' cuối cùng khỏi $query
			$query = rtrim($query, '&');

			$vnp_Url = $vnp_Url . "?" . $query;

			if (isset($vnp_HashSecret)) {
				$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
				$vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
			}
			echo json_encode(["payment_url" => $vnp_Url]);
			die();
		}
	}

	public function saveOrder()
	{
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url('/dang-nhap'));
		}

		$carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);

		if (empty($carts)) {
			return false;
		}

		if ($this->session->userdata('user')) {
			$user = $this->session->userdata('user');
			$user_id = $user->id;
		}

		$total_amount = 0;
		foreach ($carts as $value) {
			$total_amount = $total_amount + ($value->qty * $value->price);
		}

		$formData = $this->session->userdata('formData');
		// Kiểm tra tính hợp lệ của dữ liệu
		$errors = [];

		if (empty($formData['name'])) {
			$errors['name'] = "Họ và tên không được để trống";
		}

		if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = "Email không hợp lệ";
		}

		if (!preg_match('/^[0-9]{8,11}$/', $formData['phone'])) {
			$errors['phone'] = "Số điện thoại không hợp lệ (8-11 chữ số)";
		}

		if (empty($formData['address'])) {
			$errors['address'] = "Địa chỉ không được để trống";
		}

		if (empty($formData['city'])) {
			$errors['city'] = "Vui lòng chọn Tỉnh/Thành";
		}

		if (empty($formData['district'])) {
			$errors['district'] = "Vui lòng chọn Quận/Huyện";
		}

		if (empty($formData['ward'])) {
			$errors['ward'] = "Vui lòng chọn Phường/Xã";
		}

		// Nếu có lỗi, trả về danh sách lỗi
		if (!empty($errors)) {
			return false;
		}
		$data_saved = array();
		$time = date('Y-m-d H:i:s');
		$data_saved = array(
			'user_id' => $user_id, // Nếu chưa đăng nhập, user_id = 0
			'status' => 1,
			'delivery_name' => $formData['name'],
			'delivery_email' => $formData['email'],
			'delivery_address' => $formData['address'],
			'delivery_city' => $formData['city'],
			'delivery_district' => $formData['district'],
			'delivery_ward' => $formData['ward'],
			'delivery_phone' => $formData['phone'],
			'message' => $formData['message'] ?? '',
			'shipping_fee' => $formData['shipping_fee'],
			'discount_amount' => $formData['discount_amount'] ?? 0,
			'amount' => ($total_amount + $formData['shipping_fee'] - $formData['discount_amount']) < 0 ? 0
				: ($total_amount + $formData['shipping_fee'] - $formData['discount_amount']),
			'payment' => $formData['payment'] ?? '',
			'created' => $time
		);

		$this->db->trans_start(); // Bắt đầu Transaction
		$this->load->model('transaction_model');
		$this->transaction_model->create($data_saved);
		$transaction_id = $this->db->insert_id();

		if ($formData['coupon_id'] != null) {
			if (!$this->user_coupon_model->mark_coupon_used($user->id, $formData['coupon_id'])) {
				$this->db->trans_rollback();
				echo json_encode(["status" => "error", "message" => "Lưu voucher thất bại", "errors" => "Rollback transaction"],  JSON_UNESCAPED_UNICODE);
				return;
			}
		}

		// Kiểm tra nếu không lấy được ID thì rollback
		if (!$transaction_id) {
			$this->db->trans_rollback();
			return false;
		}

		$this->load->model('order_model');
		foreach ($carts as $items) {
			$data_saved = array();
			$data_saved = array(
				'transaction_id' => $transaction_id,
				'product_id' => $items->product_id,
				'qty' => $items->qty,
				'amount' => $items->qty * $items->price
			);
			$order_info = $this->order_model->create($data_saved);

			if (!$order_info) {
				$this->db->trans_rollback();
				return false;
			}
		}

		$email = $user->email;
		$payment = $formData['payment'];
		$formatted_time = date('d/m/Y H:i:s', strtotime($time));

		if (!$this->sending_mail($email, $total_amount, $payment, $formatted_time)) {
			$this->db->trans_rollback();
			echo json_encode(["status" => "error", "message" => "Gửi mail thất bại", "errors" => "Rollback transaction"], JSON_UNESCAPED_UNICODE);
			return;
		}

		$this->cart_model->del_rule(['user_id' => $user->id]);

		// Hoàn tất transaction (tự động commit nếu không có lỗi, rollback nếu có lỗi)
		$this->db->trans_complete();

		return true;
	}

	public function callback()
	{
		$vnp_HashSecret = $this->config->item('vnp_HashSecret');
		$inputData = array();

		foreach ($_GET as $key => $value) {
			if (substr($key, 0, 4) == "vnp_") {
				$inputData[$key] = $value;
			}
		}

		$secureHash = $inputData['vnp_SecureHash'];
		unset($inputData['vnp_SecureHash']);

		ksort($inputData);

		$i = 0;
		$hashdata = "";
		foreach ($inputData as $key => $value) {
			if ($i == 1) {
				$hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
			} else {
				$hashdata .= urlencode($key) . "=" . urlencode($value);
				$i = 1;
			}
		}

		$checkSum = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

		// Chuẩn bị dữ liệu cho view
		$this->data['txn_ref'] = $inputData['vnp_TxnRef']; // Mã đơn hàng
		$this->data['amount'] = number_format($inputData['vnp_Amount'] / 100, 2) . " VND"; // Số tiền (chia 100)
		$this->data['order_info'] = $inputData['vnp_OrderInfo']; // Nội dung thanh toán
		$this->data['response_code'] = $inputData['vnp_ResponseCode']; // Mã phản hồi
		$this->data['transaction_no'] = $inputData['vnp_TransactionNo']; // Mã GD tại VNPAY
		$this->data['bank_code'] = $inputData['vnp_BankCode']; // Mã ngân hàng
		$this->data['pay_date'] = date("d-m-Y H:i:s", strtotime($inputData['vnp_PayDate'])); // Thời gian thanh toán

		// Kiểm tra chữ ký bảo mật
		if ($checkSum === $secureHash) {
			if ($inputData['vnp_ResponseCode'] == '00') {
				if (!$this->saveOrder()) {
					$this->data['error_message'] = "Lưu vào DB thất bại.";
				}
				$this->data['status'] = "Giao dịch thành công!";
			} else {
				$this->data['status'] = "Giao dịch thất bại!";
				$this->data['error_message'] = "Lỗi: " . $inputData['vnp_ResponseCode']; // Thêm thông báo lỗi
			}
		} else {
			$this->data['status'] = "Chữ ký không hợp lệ!";
			$this->data['error_message'] = "Dữ liệu phản hồi không hợp lệ!";
		}

		// Load view với dữ liệu
		$this->load->view('site/order/vnpay_return', $this->data);
	}

	public function complete()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');
			$user = $this->session->userdata('user');
			if (!$user) {
				echo json_encode(["status" => "null_user", "message" => "Người dùng không tồn tại!"], JSON_UNESCAPED_UNICODE);
				return;
			}
			$carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);

			if (empty($carts)) {
				echo json_encode(["status" => "error", "message" => "Giỏ hàng trống"], JSON_UNESCAPED_UNICODE);
				return;
			}

			$user = $this->session->userdata('user');
			$user_id = $user->id;

			$total_amount = 0;
			foreach ($carts as $value) {
				$total_amount = $total_amount + ($value->qty * $value->price);
			}

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);
			// Kiểm tra nếu không có dữ liệu
			if (!$data) {
				echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
				return;
			}

			// Kiểm tra tính hợp lệ của dữ liệu
			$errors = [];

			if (empty($data['name'])) {
				$errors['name'] = "Họ và tên không được để trống";
			}

			if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
				$errors['email'] = "Email không hợp lệ";
			}

			if (!preg_match('/^[0-9]{8,11}$/', $data['phone'])) {
				$errors['phone'] = "Số điện thoại không hợp lệ (8-11 chữ số)";
			}

			if (empty($data['address'])) {
				$errors['address'] = "Địa chỉ không được để trống";
			}

			if (empty($data['city'])) {
				$errors['city'] = "Vui lòng chọn Tỉnh/Thành";
			}

			if (empty($data['district'])) {
				$errors['district'] = "Vui lòng chọn Quận/Huyện";
			}

			if (empty($data['ward'])) {
				$errors['ward'] = "Vui lòng chọn Phường/Xã";
			}

			// Nếu có lỗi, trả về danh sách lỗi
			if (!empty($errors)) {
				echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ", "errors" => $errors],  JSON_UNESCAPED_UNICODE);
				return;
			}

			$data_saved = array();
			$time = date('Y-m-d H:i:s');
			$data_saved = array(
				'user_id' => $user_id, // Nếu chưa đăng nhập, user_id = 0
				'delivery_name' => $data['name'],
				'delivery_email' => $data['email'],
				'delivery_address' => $data['address'],
				'delivery_city' => $data['city'],
				'delivery_district' => $data['district'],
				'delivery_ward' => $data['ward'],
				'delivery_phone' => $data['phone'],
				'message' => $data['message'] ?? '',
				'shipping_fee' => $data['shipping_fee'],
				'discount_amount' => $data['discount_amount'] ?? 0,
				'amount' => ($total_amount + $data['shipping_fee'] - $data['discount_amount']) < 0 ? 0
					: ($total_amount + $data['shipping_fee'] - $data['discount_amount']),
				'payment' => $data['payment'] ?? '',
				'created' => $time
			);

			$this->db->trans_start(); // Bắt đầu Transaction
			$this->load->model('transaction_model');
			$this->transaction_model->create($data_saved);
			$transaction_id = $this->db->insert_id();

			if ($data['coupon_id'] != null) {
				if (!$this->user_coupon_model->mark_coupon_used($user->id, $data['coupon_id'])) {
					$this->db->trans_rollback();
					echo json_encode(["status" => "error", "message" => "Lưu voucher thất bại", "errors" => "Rollback transaction"],  JSON_UNESCAPED_UNICODE);
					return;
				}
			}

			if ($data['giftcode_id'] != null) {
				if (!$this->user_coupon_model->mark_gift_code_used($user->id, $data['giftcode_id'])) {
					$this->db->trans_rollback();
					echo json_encode(["status" => "error", "message" => "Lưu gift code thất bại", "errors" => "Rollback transaction"],  JSON_UNESCAPED_UNICODE);
					return;
				}
			}

			// Kiểm tra nếu không lấy được ID thì rollback
			if (!$transaction_id) {
				$this->db->trans_rollback();
				echo json_encode(["status" => "error", "message" => "Đặt hàng thất bại", "errors" => "Rollback transaction"],  JSON_UNESCAPED_UNICODE);
				return;
			}

			$this->load->model('order_model');

			$total_amount = 0; // Biến để lưu tổng giá trị đơn hàng
			foreach ($carts as $items) {
				$data_saved = array();
				$data_saved = array(
					'transaction_id' => $transaction_id,
					'product_id' => $items->product_id,
					'qty' => $items->qty,
					'amount' => $items->qty * $items->price
				);
				$order_info = $this->order_model->create($data_saved);

				// Cộng dồn giá trị sản phẩm vào tổng giá trị đơn hàng
				$total_amount += ($items->qty * $items->price);

				if (!$order_info) {
					$this->db->trans_rollback();
					echo json_encode(["status" => "error", "message" => "Đặt hàng thất bại", "errors" => "Rollback transaction"], JSON_UNESCAPED_UNICODE);
					return;
				}
			}

			// Gửi mail xác nhận đơn hàng
			$user = $this->session->userdata('user');
			if (!isset($user)) {
				$this->db->trans_rollback();
				echo json_encode(["status" => "error", "message" => "User không tồn tại", "errors" => "Rollback transaction"], JSON_UNESCAPED_UNICODE);
				return;
			}
			$email = $user->email;
			$payment = $data['payment'];
			$formatted_time = date('d/m/Y H:i:s', strtotime($time));

			if (!$this->sending_mail($email, $total_amount, $payment, $formatted_time)) {
				$this->db->trans_rollback();
				echo json_encode(["status" => "error", "message" => "Gửi mail thất bại", "errors" => "Rollback transaction"], JSON_UNESCAPED_UNICODE);
				return;
			}

			$this->cart_model->del_rule(['user_id' => $user->id]);

			// Hoàn tất transaction (tự động commit nếu không có lỗi, rollback nếu có lỗi)
			$this->db->trans_complete();

			// Trả về JSON phản hồi
			if ($data['payment'] == 'cash') {
				echo json_encode([
					"status" => "success",
					"message" =>  "Đơn vị giao hàng sẽ giao tới nhà bạn trong thời gian tới",
				], JSON_UNESCAPED_UNICODE);
			} elseif ($data['payment'] == 'vietqr') {
				echo json_encode([
					"status" => "success",
					"message" =>  "Chúng tôi sẽ kiểm tra thông tin chuyển khoản và chuyển tới nhà bạn trong thời gian tới ",
				], JSON_UNESCAPED_UNICODE);
			} elseif ($data['payment'] == 'pos') {
				echo json_encode([
					"status" => "success",
					"message" =>  "Chúng tôi sẽ mang máy pos và hàng tới nhà bạn trong thời gian tới",
				], JSON_UNESCAPED_UNICODE);
			} else {
				echo json_encode([
					"status" => "success",
					"message" =>  "Cảm ơn quý khách đã mua hàng tại NgocLanShop",
				], JSON_UNESCAPED_UNICODE);
			}
		}
	}

	public function shipping_fee_rule()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			header('Content-Type: application/json; charset=utf-8');

			$user = $this->session->userdata('user');
			if (!$user) {
				echo json_encode([
					"status" => "null_user",
					"message" => "Người dùng không tồn tại!"
				], JSON_UNESCAPED_UNICODE);
				return;
			}

			$list = $this->shipping_rule_model->get_list();

			echo json_encode([
				"status" => "success",
				"message" => "lấy quy tắc ship thành công",
				"data" => $list
			], JSON_UNESCAPED_UNICODE);
		}
	}

	public function get_voucher()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			header('Content-Type: application/json; charset=utf-8');

			$user = $this->session->userdata('user');
			if (!$user) {
				echo json_encode([
					"status" => "null_user",
					"message" => "Người dùng không tồn tại!"
				], JSON_UNESCAPED_UNICODE);
				return;
			}

			$coupon_info = $this->coupon_model->get_coupons_with_user_and_used_total($user->id);

			echo json_encode([
				"status" => "success",
				"message" => "Lấy thông tin voucher thành công",
				"data" => [
					"coupon_info" => $coupon_info
				]
			], JSON_UNESCAPED_UNICODE);
		}
	}
	public function check_gift_code()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json; charset=utf-8');

			$user = $this->session->userdata('user');
			if (!$user) {
				echo json_encode([
					"status" => "null_user",
					"message" => "Người dùng không tồn tại!"
				], JSON_UNESCAPED_UNICODE);
				return;
			}

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);

			$info = $this->user_coupon_model->get_coupon_by_gift_code($data['giftCode']);
			if ($info == null) {
				echo json_encode([
					"status" => "error",
					"message" => "Không tìm thấy voucher"
				], JSON_UNESCAPED_UNICODE);
				return;
			}
			// if (!$this->user_coupon_model->mark_gift_code_used($user->id, $data['giftCode'])) {
			// 	echo json_encode([
			// 		"status" => "error",
			// 		"message" => "Cập nhật coupon thất bại",
			// 	], JSON_UNESCAPED_UNICODE);
			// 	return;
			// }

			echo json_encode([
				"status" => "success",
				"message" => "Lấy thông tin voucher thành công",
				"data" => $info
			], JSON_UNESCAPED_UNICODE);
		}
	}

	public function sepay()
	{
		redirect(base_url('/'));
	}
}