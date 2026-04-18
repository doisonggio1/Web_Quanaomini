<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->library('form_validation');
		$this->load->library('verify_library');
		$this->load->helper('form');
		$this->load->helper('email');
	}

	public function index()
	{
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url());
		}
		$where = array('id' => $user->id);
		$user = $this->user_model->get_info_rule($where);
		$this->session->set_userdata('user', $user);

		$this->data['user_info'] = $user;
		$this->data['temp'] = 'site/user/index.php';
		$this->load->view('site/layoutsub', $this->data);
	}
	public function validate_email($to_mail, $to_name, $subject, $body, $altBody)
	{
		if (send_email($to_mail, $to_name, $subject, $body, $altBody)) {
			return true;
		} else {
			return false;
		}
	}
	public function register()
	{
		// nếu user tồn tại thì chuyển về home
		$user = $this->session->userdata('user');
		if (isset($user)) {
			redirect(base_url());
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');
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
			if (!$this->check_email($data['email'])) {
				$errors['email'] = "Email đã tồn tại";
			}
			if ($data['password'] < 8) {
				echo "Mật khẩu phải từ 8 kí tự trở lên";
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

			// Kiểm tra recaptcha
			$captcha = $data['recaptcha'];
			if (!$this->verify_library->verify_recaptcha($captcha)) {
				$errors['recaptcha'] = "xác thực recaptcha thất bại";
			}

			// Nếu có lỗi, trả về danh sách lỗi
			if (!empty($errors)) {
				echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ", "errors" => $errors],  JSON_UNESCAPED_UNICODE);
				return;
			}

			$data_saved = array();
			$time = date('Y-m-d H:i:s');
			$name = $data['name'];
			$email = $data['email'];
			$data_saved = array(
				'name' => $name,
				'email' => $email,
				'password' => md5($data['password']),
				'address' => $data['address'],
				'city' => $data['city'],
				'district' => $data['district'],
				'ward' => $data['ward'],
				'phone' => $data['ward'],
				'created' => $time
			);

			// Gửi Email xác thực 
			$token = $this->verify_library->generate_verification_token($data_saved);
			if (!$token) {
				echo json_encode(["status" => "error", "message" => "Không thể tạo token xác thực", "errors" => $errors],  JSON_UNESCAPED_UNICODE);
				return;
			}
			$verification_link = base_url("xac-thuc-mail/$token");
			$validation_email = $this->validate_email(
				$email,
				$name,
				'Xác thực Email - Shop quần áo mini',
				"
				<!DOCTYPE html>
				<html>
				<head>
					<meta charset='UTF-8'>
					<title>Xác thực Email - Shop quần áo mini</title>
					<style>
						p {
							font-size: 20px;
						}
					</style>
				</head>
				<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; text-align: center;'>
					<h2>Chào bạn, $name </h2>
					<p>Bạn đã đăng ký tài khoản trên <strong>Shop quần áo mini</strong>. Vui lòng xác thực email của bạn bằng cách nhấn vào nút bên dưới:</p>
					<p>
						<a href='" . $verification_link . "' 
						style='display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; text-decoration: none; border-radius: 5px;'>
							Xác Thực Email
						</a>
					</p>
					<p>Nếu bạn không thực hiện yêu cầu trên, vui lòng bỏ qua email này.</p>
					<p>Trân trọng,<br>Đội ngũ Shop quần áo mini</p>
				</body>
				</html>
				",
				"
				Chào bạn,

				Bạn đã đăng ký tài khoản trên Shop quần áo mini. Vui lòng xác thực email của bạn bằng cách nhấp vào liên kết bên dưới:

				👉 " . $verification_link . "

				Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.

				Trân trọng,
				Đội ngũ Shop quần áo mini
				"
			);
			if (!$validation_email) {
				echo json_encode(["status" => "error", "message" => "Gửi email thất bại", "errors" => $errors],  JSON_UNESCAPED_UNICODE);
				return;
			}
			echo json_encode(["status" => "success", "message" => "Vui lòng xác nhận email tại $email"],  JSON_UNESCAPED_UNICODE);
			return;
		}
		$this->load->view('site/user/register');
	}
	function check_validation_mail()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);
			$where = array('email' => $data['email']);
			if ($this->user_model->check_exists($where)) {
				echo json_encode(["status" => "success", "message" => "Xác nhận thành công"],  JSON_UNESCAPED_UNICODE);
				return;
			}
			echo json_encode(["status" => "error", "message" => "Xác nhận thất bại"],  JSON_UNESCAPED_UNICODE);
			return;
		}
	}
	function check_forgot_password_mail()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);

			$user = $this->session->userdata('user');
			if (isset($user)) {
				$id = $user->id;
			} else {
				$id = $this->user_model->get_info_rule(['email' => $data['email']], 'id');
				$id = $id->id;
			}

			if (!$id) {
				json_encode(["status" => "error", "message" => "Id không tồn tại"],  JSON_UNESCAPED_UNICODE);
				return;
			}

			//Kiểm tra xem email đã được kích hoạt chưa
			$date_send_mail = $this->session->userdata('time');
			$is_verified = $this->user_model->get_info($id, 'is_verified');
			$date_modified = $this->user_model->get_info($id, 'date_modified');

			// Chuyển về timestamp nếu là chuỗi 'Y-m-d H:i:s'
			$date_modified = new DateTime($date_modified->date_modified);
			$date_send_mail = new DateTime($date_send_mail);


			if ($is_verified->is_verified != 1 || $date_send_mail >= $date_modified) {
				echo json_encode(["status" => "error", "message" => "Email chưa được xác thực"],  JSON_UNESCAPED_UNICODE);
				return;
			}

			// Lấy password ứng với user hiện tại
			$user_info = $this->user_model->get_info_rule(['id' => $id], 'password');
			$password = $user_info->password;

			// Kiểm tra xem mật khẩu đã được cập nhật chưa 
			if (md5($data['password']) === $password) {
				echo json_encode(["status" => "success", "message" => "Cập nhật mật khẩu thành công"],  JSON_UNESCAPED_UNICODE);
				return;
			}
			echo json_encode(["status" => "error", "message" => "Mật khẩu chưa được cập nhật"],  JSON_UNESCAPED_UNICODE);
			return;
		}
	}
	function check_email($email)
	{
		$where = array('email' => $email);
		if ($this->user_model->check_exists($where)) {
			$this->form_validation->set_message(__FUNCTION__, 'Tên đăng nhập đã tồn tại');
			return FALSE;
		}
		return TRUE;
	}

	private function build_google_client()
	{
		require_once FCPATH . 'vendor/autoload.php';

		$this->load->config('google_oauth');
		$oauthConfig = $this->config->item('google_oauth');

		if (empty($oauthConfig['client_id']) || empty($oauthConfig['client_secret']) || empty($oauthConfig['redirect_uri'])) {
			return NULL;
		}

		$client = new \Google\Client();
		$client->setClientId($oauthConfig['client_id']);
		$client->setClientSecret($oauthConfig['client_secret']);
		$client->setRedirectUri($oauthConfig['redirect_uri']);
		$client->addScope('email');
		$client->addScope('profile');
		$client->setPrompt('select_account consent');

		return $client;
	}

	public function google_login()
	{
		if ($this->session->userdata('user')) {
			redirect(base_url());
		}

		$client = $this->build_google_client();
		if (!$client) {
			$this->session->set_flashdata('google_login_error', 'Google Login chưa được cấu hình. Vui lòng thêm GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET và GOOGLE_REDIRECT_URI.');
			redirect(base_url('dang-nhap'));
		}

		redirect($client->createAuthUrl());
	}

	public function google_callback()
	{
		if ($this->session->userdata('user')) {
			redirect(base_url());
		}

		$client = $this->build_google_client();
		if (!$client) {
			$this->session->set_flashdata('google_login_error', 'Google Login chưa được cấu hình đúng trên server.');
			redirect(base_url('dang-nhap'));
		}

		$code = $this->input->get('code', TRUE);
		if (!$code) {
			$this->session->set_flashdata('google_login_error', 'Không nhận được mã xác thực từ Google.');
			redirect(base_url('dang-nhap'));
		}

		$token = $client->fetchAccessTokenWithAuthCode($code);
		if (isset($token['error'])) {
			log_message('error', 'Google OAuth token error: ' . json_encode($token));
			$this->session->set_flashdata('google_login_error', 'Đăng nhập Google thất bại. Vui lòng thử lại.');
			redirect(base_url('dang-nhap'));
		}

		$accessToken = isset($token['access_token']) ? $token['access_token'] : '';
		if ($accessToken === '') {
			$this->session->set_flashdata('google_login_error', 'Không lấy được access token từ Google.');
			redirect(base_url('dang-nhap'));
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode !== 200 || !$response) {
			log_message('error', 'Google userinfo error: HTTP ' . $httpCode . ' - ' . $response);
			$this->session->set_flashdata('google_login_error', 'Không thể lấy thông tin người dùng từ Google.');
			redirect(base_url('dang-nhap'));
		}

		$googleUser = json_decode($response, true);
		$email = isset($googleUser['email']) ? trim($googleUser['email']) : '';
		$name = isset($googleUser['name']) ? trim($googleUser['name']) : '';

		if ($email === '') {
			$this->session->set_flashdata('google_login_error', 'Tài khoản Google không trả về email.');
			redirect(base_url('dang-nhap'));
		}

		if ($name === '') {
			$emailParts = explode('@', $email);
			$name = $emailParts[0];
		}

		$where = array('email' => $email);
		$user = $this->user_model->get_info_rule($where);

		if (!$user) {
			$data = array(
				'name' => $name,
				'email' => $email,
				'password' => md5(uniqid('google_', TRUE)),
				'phone' => '',
				'address' => '',
				'created' => date('Y-m-d H:i:s')
			);

			if ($this->db->field_exists('is_verified', 'user')) {
				$data['is_verified'] = 1;
			}

			if ($this->db->field_exists('date_modified', 'user')) {
				$data['date_modified'] = date('Y-m-d H:i:s');
			}

			if (!$this->user_model->create($data)) {
				$this->session->set_flashdata('google_login_error', 'Không thể tạo tài khoản từ Google.');
				redirect(base_url('dang-nhap'));
			}

			$user = $this->user_model->get_info_rule($where);
		}

		if (!$user) {
			$this->session->set_flashdata('google_login_error', 'Không thể lấy thông tin tài khoản sau khi đăng nhập Google.');
			redirect(base_url('dang-nhap'));
		}

		if (isset($user->is_verified) && (int) $user->is_verified !== 1) {
			$this->user_model->update($user->id, array(
				'is_verified' => 1,
				'date_modified' => date('Y-m-d H:i:s')
			));
			$user = $this->user_model->get_info($user->id);
		}

		$this->session->set_userdata('user', $user);
		redirect(base_url());
	}

	public function login()
	{
		// nếu user tồn tại, redirect về home
		if ($this->session->userdata('user')) {
			redirect(base_url());
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);

			// Kiểm tra nếu không có dữ liệu
			if (!$data) {
				echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
				return;
			}

			//Kiểm tra tài khoản và mật khẩu
			$email = $data['email'];
			$password = $data['password'];

			$user = array();
			$where = array('email' => $email, 'password' => md5($password));
			$user = $this->user_model->get_info_rule($where);

			if (!$user) {
				echo json_encode(["status" => "error", "message" => "Tài khoản hoặc mật khẩu không đúng"], JSON_UNESCAPED_UNICODE);
				return;
			}

			$this->session->set_userdata('user', $user);

			echo json_encode(["status" => "success", "message" => "Đăng nhập thành công"], JSON_UNESCAPED_UNICODE);
			return;
		}

		$this->data['google_login_url'] = base_url('dang-nhap/google');
		$this->data['google_login_error'] = $this->session->flashdata('google_login_error');

		$this->load->view('site/user/login');
	}
	public function logout()
	{
		if ($this->session->userdata('user')) {
			$this->session->unset_userdata('user');
		}
		redirect(base_url());
	}
	public function get_info_forgot_user($email)
	{
		$where = array('email' => $email);
		$id = $this->user_model->get_info_rule($where, 'id');

		if (isset($id) || $id) {
			return $id->id;
		} else {
			return false;
		}
	}
	public function forgotpassword()
	{
		// nếu user tồn tại thì tự động điền email
		$user = $this->session->userdata('user');
		$session_pass = null;
		if (isset($user)) {
			$this->data['auto_fill'] = $user->email;
			$id = $user->id;
			$session_pass = $user->password;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);

			// Kiểm tra nếu không có dữ liệu
			if (!$data) {
				echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
				return;
			}

			// Kiểm tra recaptcha
			$captcha = $data['recaptcha'];
			if (!$this->verify_library->verify_recaptcha($captcha)) {
				$errors['recaptcha'] = "xác thực recaptcha thất bại";
			}

			$email = $data['email'];

			// nếu chưa đăng nhập thì kiểm tra mail
			if (!isset($user)) {
				//Kiểm tra mail có tồn tại không
				if ($this->check_email($email)) {
					$errors['email'] = "Email không tồn tại";
				} else {
					$id = $this->user_model->get_info_rule(['email' => $email], 'id');
					$id = $id->id;
				}
			}

			// Kiểm tra mật khẩu cũ có trùng mật khẩu mới không
			$password = $this->user_model->get_info_rule(['email' => $email], 'password');
			$password = $password->password;

			// Cập nhật mật khẩu nếu nhận thấy khác với trong DB
			if ($session_pass !== $password && $session_pass != null) {
				$where = array('email' => $email);
				$user = $this->user_model->get_info_rule($where);
				if (!$user) {
					echo json_encode(["status" => "error", "message" => "Thông tin user không hợp lệ"], JSON_UNESCAPED_UNICODE);
					return;
				}
				$this->session->set_userdata('user', $user); //Cập nhật password -> cập nhật lại toàn bộ $user
			}

			if ($password === md5($data['password'])) {
				$errors['password'] = "mật khẩu mới trùng với mật khẩu cũ trong 3 tháng gần đây";
			}

			// Nếu có lỗi, trả về danh sách lỗi
			if (!empty($errors)) {
				echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ", "errors" => $errors],  JSON_UNESCAPED_UNICODE);
				return;
			}

			$temp = array(
				'time' => date('Y-m-d H:i:s') // hoặc time() nếu muốn lưu timestamp
			);
			$this->session->set_userdata($temp);

			if ($id !== null) {
				$temp2 = ['is_verified' => 0];
				$this->user_model->update($id, $temp2);
			}

			$data = array(
				'password' => $data['password'],
				'id' => $this->get_info_forgot_user($email)
			);
			// Gửi Email xác thực 
			$token = $this->verify_library->generate_verification_token($data);
			if (!$token) {
				echo json_encode(["status" => "error", "message" => "Không thể tạo token xác thực"], JSON_UNESCAPED_UNICODE);
				return;
			}
			$reset_link = base_url("doi-mat-khau/$token");
			$expire_time = getenv('JWT_EXPIRE') / 60;

			$validation_email = $this->validate_email(
				$email,
				$email,
				'Xác thực Email - Shop quần áo mini',
				'
					<!DOCTYPE html>
					<html>
					<head>
						<meta charset="UTF-8">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<title>Xác nhận đổi mật khẩu</title>
						<style>
							body {
								font-family: Arial, sans-serif;
								background-color: #f4f4f4;
								margin: 0;
								padding: 20px;
							}
							.container {
								max-width: 600px;
								background: #ffffff;
								padding: 20px;
								border-radius: 8px;
								box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
								text-align: center;
								margin: auto;
							}
							.button {
								display: inline-block;
								background: #007bff;
								color: white !important;
								padding: 12px 20px;
								text-decoration: none;
								border-radius: 5px;
								font-size: 16px;
							}
							.footer {
								margin-top: 20px;
								font-size: 12px;
								color: #666;
							}
						</style>
					</head>
					<body>

						<div class="container">
							<h2>Xác nhận đổi mật khẩu</h2>
							<p>Chúng tôi nhận được yêu cầu thay đổi mật khẩu cho tài khoản của bạn.</p>
							<p>Nếu bạn không yêu cầu thay đổi mật khẩu, hãy bỏ qua email này.</p>
							<p>Để đặt lại mật khẩu, vui lòng nhấn vào nút bên dưới:</p>
							<a href="' . $reset_link . '" class="button">Đổi mật khẩu</a>
							<p>Liên kết này sẽ hết hạn sau ' . $expire_time . ' phút.</p>
						</div>

					</body>
					</html>
					',
				"
					Tiêu đề: Xác nhận thay đổi mật khẩu

					Nội dung:

					Xin chào " . $email . ",

					Chúng tôi nhận được yêu cầu thay đổi mật khẩu cho tài khoản của bạn. Nếu bạn đã yêu cầu điều này, vui lòng nhấn vào liên kết dưới đây để đặt lại mật khẩu:

					👉 " . $reset_link . "

					Lưu ý: Liên kết này sẽ hết hạn sau " . $expire_time . " phút. Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.

					Nếu bạn gặp bất kỳ vấn đề nào, hãy liên hệ với chúng tôi qua email: support@ngoclan.com.

					Trân trọng,
					Đội ngũ hỗ trợ
					Ngoc Lan team
					"
			);

			if (!$validation_email) {
				echo json_encode(["status" => "error", "message" => "Không thể gửi email"], JSON_UNESCAPED_UNICODE);
				return;
			}
			echo json_encode(["status" => "success", "message" => "Vui lòng xác thực mail tại $email"], JSON_UNESCAPED_UNICODE);
			return;
		}
		$this->load->view('site/user/forgot', $this->data);
	}
	public function update_info()
	{
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url('/dang-nhap'));
		}
		if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
			$data = json_decode(file_get_contents("php://input"), true);
			$id = $user->id;
			if ($data['id'] == 'address-number') {
				if (
					!isset($data['value']) || trim($data['value']) === '' ||
					!isset($data['province']) || trim($data['province']) === '' ||
					!isset($data['district']) || trim($data['district']) === '' ||
					!isset($data['ward']) || trim($data['ward']) === ''
				) {
					echo json_encode(['status' => 'error', 'message' => 'Thông tin địa chỉ không được để trống']);
					return;
				}
				$temp = array(
					'address' => $data['value'],
					'city' => $data['province'],
					'district' => $data['district'],
					'ward' => $data['ward'],
				);
				$this->user_model->update($id, $temp);

				// Bước 1: Xóa dữ liệu cũ
				$this->session->unset_userdata('user');
				$where = array('email' => $user->email);
				$user = $this->user_model->get_info_rule($where);
				if (!$user) {
					echo json_encode(["status" => "error", "message" => "Thông tin user không hợp lệ"], JSON_UNESCAPED_UNICODE);
					return;
				}
				$this->session->set_userdata('user', $user); //-> cập nhật lại toàn bộ $user

			} else {
				if (!isset($data['value']) || !isset($data['id']) || trim($data['value']) === '' || trim($data['id']) === '') {
					echo json_encode(['status' => 'error', 'message' => 'Thông tin trường điền không được để trống']);
					return;
				}
				$row = $data['id'];
				$temp = array(
					$row => $data['value']
				);
				$this->user_model->update($id, $temp);

				// Bước 1: Xóa dữ liệu cũ
				$this->session->unset_userdata('user');
				$where = array('email' => $user->email);
				$user = $this->user_model->get_info_rule($where);
				if (!$user) {
					echo json_encode(["status" => "error", "message" => "Thông tin user không hợp lệ"], JSON_UNESCAPED_UNICODE);
					return;
				}
				$this->session->set_userdata('user', $user); //-> cập nhật lại toàn bộ $user
			}
			echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Phương thức không hợp lệ']);
		}
	}
	public function delete_info()
	{
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url('/dang-nhap'));
		}
		if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
			$data = json_decode(file_get_contents("php://input"), true);

			if (!isset($data['id'])) {
				echo json_encode(['status' => 'error', 'message' => 'Thiếu dữ liệu']);
				return;
			}

			$where = array(
				'email' => $user->email
			);

			$data = array(
				$data['id'] => ''
			);

			if ($this->user_model->update_rule($where, $data)) {
				echo json_encode(['status' => 'success', 'message' => 'xoá thông tin thành công']);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'xoá thông tin thất bại']);
			}
		} else {
			echo json_encode(['status' => 'error', 'message' => 'phương thức không hợp lệ']);
		}
	}
	public function checkpassword()
	{
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url('/dang-nhap'));
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');
			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);
			// Kiểm tra nếu không có dữ liệu
			if (!$data) {
				echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
				return;
			}

			$id = $user->id;
			$where = array('id' => $id);
			$user = $this->user_model->get_info_rule($where, 'password');
			if ($user->password !== md5($data['password'])) {
				echo json_encode(["status" => "error", "message" => "Mật khẩu sai"], JSON_UNESCAPED_UNICODE);
				return;
			}

			echo json_encode(["status" => "success", "message" => "Mật khẩu đúng"], JSON_UNESCAPED_UNICODE);
			return;
		}
	}
	public function changepassword()
	{
		// Kiểm tra nếu user không tồn tại thì redirect về home 
		$user = $this->session->userdata('user');
		if (!isset($user)) {
			redirect(base_url('/dang-nhap'));
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			header('Content-Type: application/json;');

			// Nhận dữ liệu JSON từ request
			$data = json_decode(file_get_contents("php://input"), true);

			// Kiểm tra nếu không có dữ liệu
			if (!$data) {
				echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
				return;
			}

			// Kiểm tra recaptcha
			$captcha = $data['recaptcha'];
			if (!$this->verify_library->verify_recaptcha($captcha)) {
				$errors['recaptcha'] = "xác thực recaptcha thất bại";
			}

			// Kiểm tra mật khẩu cũ có đúng không
			$email = $user->email;
			$where = array('email' => $email);
			$user = $this->user_model->get_info_rule($where);
			if (!$user) {
				echo json_encode(["status" => "error", "message" => "Thông tin user không hợp lệ"], JSON_UNESCAPED_UNICODE);
				return;
			}
			$this->session->set_userdata('user', $user); //Cập nhật password -> cập nhật lại toàn bộ $user

			if ($user->password !== md5($data['old_password'])) {
				$errors['password'] = "mật khẩu cũ sai";
			}

			// Nếu có lỗi, trả về danh sách lỗi
			if (!empty($errors)) {
				echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ", "errors" => $errors],  JSON_UNESCAPED_UNICODE);
				return;
			}
			$id = $user->id;

			$email = $user->email;
			$new_password = $data['new_password'];

			//Cập nhật mật khẩu
			$temp = array(
				'password' => md5($new_password),
				'date_modified' => date('Y-m-d H:i:s') // Lấy thời gian hiện tại
			);
			$this->user_model->update($id, $temp);

			echo json_encode(["status" => "success", "message" => "Đổi mật khẩu thành công"], JSON_UNESCAPED_UNICODE);
			return;
		}

		$this->load->view('site/user/alter', $this->data);
	}
}
