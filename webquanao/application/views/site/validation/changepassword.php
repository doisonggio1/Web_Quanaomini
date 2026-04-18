<!DOCTYPE html>
<html lang="en">

<head>
	<link rel="stylesheet" type="text/css" href="<?php echo public_url('site/'); ?>css/validation.css">
</head>

<body>
	<div class="container">
		<?php $this->load->view('site/header', $this->data); ?>
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
				<?php $this->load->view('admin/message.php'); ?>
				<?php if ($this->data['status']): ?>
					<div class="ngoclan-verification-container">
						<div class="ngoclan-verification-card">
							<img src="<?php echo base_url(); ?>public/upload/validation/checked.png" alt="Icon thành công" class="img-responsive ngoclan-verification-icon">
							<h1 class="ngoclan-verification-title">Đổi mật khẩu thành công!</h1>
							<p class="ngoclan-verification-message">Vui lòng đăng nhập lại để có thể bắt đầu mua sắm tại Shop quần áo mini.</p>
							<a href="<?php echo base_url('dang-nhap'); ?>" class="ngoclan-verification-button">Đăng nhập ngay</a>
						</div>
					</div>
				<?php else: ?>
					<div class="ngoclan-verification-container">
						<div class="ngoclan-verification-card">
							<img src="<?php echo base_url(); ?>public/upload/validation/cancel.png" alt="Icon thất bại" class="img-responsive ngoclan-verification-icon">
							<h1 class="ngoclan-verification-title">Đổi mật khẩu thất bại!</h1>
							<?php if (isset($this->data['message'])): ?>
								<?php if ($this->data['message'] === 'invalid_token'): ?>
									<p class="ngoclan-verification-message">Token không hợp lệ. Vui lòng kiểm tra lại liên kết của bạn.</p>

								<?php elseif ($this->data['message'] === 'expired_token'): ?>
									<p class="ngoclan-verification-message">Token đã hết hạn. Vui lòng yêu cầu liên kết mới để đặt lại mật khẩu.</p>

								<?php elseif ($this->data['message'] === 'already_use'): ?>
									<p class="ngoclan-verification-message">Yêu cầu đã được thực hiện trước đó rồi.</p>

								<?php elseif ($this->data['message'] === 'update_fail'): ?>
									<p class="ngoclan-verification-message">Có lỗi trong quá trình đổi mật khẩu, vui lòng thử lại sau.</p>
								<?php endif; ?>
							<?php endif; ?>
							<a href="<?php echo base_url(); ?>" class="ngoclan-verification-button">Về trang chủ</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php $this->load->view('site/footer', $this->data); ?>
	</div>
	<script src="<?php echo public_url('site/'); ?>bootstrap/js/bootstrap.min.js"></script>
</body>

</html>