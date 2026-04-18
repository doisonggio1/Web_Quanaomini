<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view('site/head', $this->data); ?>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"> </script>
	<script src="https://www.google.com/recaptcha/api.js" async defer>
	</script>

	<style>
		.g-recaptcha>div:first-child {
			margin: 10px auto 20px auto;
		}
	</style>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
	<style>
		.my-custom-button {
			background-color: #31B0D5 !important;
			/* Màu cam đỏ */
			color: white !important;
			/* Chữ màu trắng */
			border-radius: 5px !important;
			/* Bo góc */
			padding: 10px 20px !important;
			font-size: 16px !important;
		}

		.swal2-timer-progress-bar {
			height: 6px;
			/* Chiều cao của thanh tiến trình */
			background-color: #007bff;
			/* Màu sắc của thanh tiến trình */
			border-radius: 3px;
			/* Bo tròn góc thanh tiến trình */
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
			/* Thêm bóng cho thanh tiến trình */
		}

		.custom-toast {
			width: 90% !important;
			max-width: none !important;
			font-size: 1.1rem !important;
			padding: 1rem 1.5rem !important;
			left: 50% !important;
			transform: translateX(-50%) !important;
		}

		.swal2-success-toast {
			background-color: #d4edda !important;
			color: #155724 !important;
			border: 1px solid #c3e6cb;
		}

		.swal2-error-toast {
			background-color: #f8d7da !important;
			color: #721c24 !important;
			border: 1px solid #f5c6cb;
		}

		/* Loại bỏ conflict tailwind */
		.collapse {
			visibility: unset !important;
		}

		a {
			color: #337ab7 !important;
			text-decoration: none !important;
		}

		a:hover {
			text-decoration: none !important;
		}

		.navbar-info .navbar-nav>.active>a,
		.navbar-info .navbar-nav>.active>a:hover,
		.navbar-info .navbar-nav>.active>a:focus {
			color: #fff !important;
			background-color: #4c66a4 !important;
		}

		.navbar-info .navbar-nav>li>a:hover,
		.navbar-info .navbar-nav>li>a:focus {
			color: #fff !important;
			background-color: #337ab7 !important;
			border-top-left-radius: 4px !important;
			border-top-right-radius: 4px !important;
		}

		a.product_title:hover {
			color: #337ab7 !important;
		}

		.dropdown-menu>li>a {
			color: #333 !important;
		}

		@media (min-width: 1200px) {
			.container {
				width: 1170px !important;
			}
		}

		/* Loại bỏ conflict tailwind */
	</style>
</head>

<body>
	<div class="container">
		<?php $this->load->view('site/header', $this->data); ?>

		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding" style="margin-top: 15px;">
				<ol class="breadcrumb">
					<li><a href="#"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Home</a></li>
					<li class="active">Đăng ký</li>
				</ol>
				<div class="panel panel-info flex justify-center"> <!-- Thêm flex và justify-center để căn giữa -->
					<div class="bg-white p-6 text-3xl w-4/5"> <!-- Thêm w-4/5 để chiếm 80% chiều rộng -->
						<h2 class="text-4xl font-semibold mb-10 mt-[55px]">Đăng ký tài khoản</h2>

						<div class="mb-10">
							<label class="block text-gray-700 mb-4" for="name">Họ và tên</label>
							<input type="text" id="name" autocomplete="new-name" name="name" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded" value="">
						</div>

						<div class="mb-10">
							<label class="block text-gray-700 mb-4" for="email">Email</label>
							<input type="email" id="email" autocomplete="new-email" name="email" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded" placeholder="example@gmail.com" value="">
						</div>
						<form>
							<div class="mb-10">
								<label class="block text-gray-700 mb-4" for="email">Mật khẩu</label>
								<input type="password" id="password" autocomplete="new-password" name="password" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded" value="">
							</div>
							<div class="mb-10">
								<label class="block text-gray-700 mb-4" for="email">Nhập lại mật khẩu</label>
								<input type="password" id="re-password" autocomplete="new-password" name="re-password" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded" value="">
							</div>
						</form>

						<div class="mb-10">
							<label class="block text-gray-700 mb-4" for="phone">Số điện thoại</label>
							<input type="text" id="phone" autocomplete="new-phone" name="phone" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded" value="">
						</div>

						<div class="mb-10">
							<label class="block text-gray-700 mb-4" for="address">Địa chỉ</label>
							<input type="text" id="address" autocomplete="new-address" name="address" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded" placeholder="VD: 208-E5" value="">
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
							<div>
								<label class="block text-gray-700 mb-4" for="city">Tỉnh/Thành phố</label>
								<select id="city" autocomplete="new-city" name="city" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded">
									<option value="" selected></option>
								</select>
							</div>

							<div>
								<label class="block text-gray-700 mb-4" for="district">Quận/Huyện</label>
								<select id="district" autocomplete="new-district" name="district" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded">
									<option value="" selected></option>
								</select>
							</div>
							<div>
								<label class="block text-gray-700 mb-4" for="district">Phường/Xã</label>
								<select id="ward" autocomplete="new-ward" name="ward" style="font-size:85% !important;" class="w-full p-2 border border-gray-300 rounded">
									<option value="" selected></option>
								</select>
							</div>
						</div>
						<div>
							<div class="g-recaptcha form-group" style="margin: 0;" data-sitekey="6LcKdPUqAAAAAGv-BwfXyqkrqpTuVEUCQLGwbG6Z" data-callback="onCaptchaSuccess"></div>
						</div>
						<button class="w-full text-white text-3xl font-medium py-3 rounded-lg transition duration-300 mb-[100px]"
							style="background-color: rgb(61, 177, 212);" id="submitBtn"
							onmouseover="this.style.backgroundColor='rgb(39, 147, 180)'"
							onmouseout="this.style.backgroundColor='rgb(61, 177, 212)'">
							Đăng ký
						</button>
					</div>
				</div>
			</div>
			<div id="hiddenData" data-expire="<?php echo getenv('JWT_EXPIRE'); ?>" style="display: none"></div>
			<?php $this->load->view('site/footer', $this->data); ?>
		</div>
		<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
			async defer>
		</script>
		<script src="<?php echo public_url('site/'); ?>bootstrap/js/bootstrap.min.js"></script>
		<script src="<?php echo public_url('site/'); ?>js/register.js"></script>
</body>

</html>