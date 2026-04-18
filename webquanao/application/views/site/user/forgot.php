<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view('site/head', $this->data); ?>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
	<!-- tích hợp reCAPTCHA -->
	<script src="https://www.google.com/recaptcha/api.js" async defer>
	</script>

	<style>
		.g-recaptcha>div:first-child {
			margin: 10px auto 20px auto;
		}

		.g-recaptcha {
			transform: scale(0.75);
			/* thu nhỏ 85% */
		}

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

		/* custom cho đăng ký | đổi mật khẩu */
		a:hover,
		a:focus {
			color: #23527c !important;
			text-decoration: underline !important;
		}

		.text-blue-600 a {
			--tw-text-opacity: 1 !important;
			color: rgb(37 99 235 / var(--tw-text-opacity, 1)) !important;
		}


		/* Loại bỏ conflict tailwind */
	</style>
	<!-- tích hợp reCAPTCHA -->
</head>

<body>
	<div class="container">
		<?php $this->load->view('site/header', $this->data); ?>

		<div class="col-12 clearpaddingr">
			<ol class="breadcrumb">
				<li><a href="<?php echo base_url(); ?>#"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Trang chủ</a></li>
				<li class="active">Quên mật khẩu</li>
			</ol>
			<div class="flex flex-col items-center">
				<div class="mb-10 mt-[70px]">
					<label class="block text-gray-700 mb-4 text-2xl" for="email">Email</label>
					<input
						type="email"
						name="email"
						id="email"
						class="w-[400px] p-3 border border-gray-300 rounded text-lg"
						<?php if (isset($auto_fill) && $auto_fill !== ''): ?>
						value="<?= htmlspecialchars($auto_fill, ENT_QUOTES, 'UTF-8') ?>" readonly
						<?php endif; ?>>
				</div>
				<form>
					<div class="mb-10">
						<label class="block text-gray-700 mb-4 text-2xl" for="password">Mật khẩu mới</label>
						<input type="password" id="password" name="password"
							class="w-[400px] p-3 border border-gray-300 rounded text-lg">
					</div>
					<div class="mb-10">
						<label class="block text-gray-700 mb-4 text-2xl" for="password">Nhập lại mật khẩu mới</label>
						<input type="password" id="repassword" name="repassword"
							class="w-[400px] p-3 border border-gray-300 rounded text-lg">
					</div>
				</form>
				<div class="g-recaptcha form-group" style="margin: 0;" data-sitekey="6LcKdPUqAAAAAGv-BwfXyqkrqpTuVEUCQLGwbG6Z" data-callback="onCaptchaSuccess"></div>

				<button id="submitBtn"
					class="w-[400px] text-white text-2xl font-semibold py-3 rounded-lg transition mb-10 duration-300"
					style="background-color: rgb(61, 177, 212);"
					onmouseover="this.style.backgroundColor='rgb(39, 147, 180)'"
					onmouseout="this.style.backgroundColor='rgb(61, 177, 212)'">
					Đổi mật khẩu
				</button>

				<?php if (!isset($auto_fill)): ?>
					<div class="text-center text-2xl text-blue-600 space-x-4 mb-[100px]">
						<a href="/dang-ky" class="hover:underline">Đăng ký</a>
						<span>|</span>
						<a href="/dang-nhap" class="hover:underline">Đăng nhập</a>
					</div>
				<?php else: ?>
					<div class="text-center text-2xl text-green-600 space-x-4 mb-[75px]">
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div id="hiddenData" data-expire="<?php echo getenv('JWT_EXPIRE'); ?>" style="display: none"></div>
		<?php $this->load->view('site/footer', $this->data); ?>
	</div>
	<script src="<?php echo public_url('site/'); ?>js/forgot.js"></script>
	<script src="<?php echo public_url('site/'); ?>bootstrap/js/bootstrap.min.js"></script>
</body>

</html>