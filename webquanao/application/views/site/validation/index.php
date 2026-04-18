<!DOCTYPE html>
<html lang="en">

<head>
	<?php $this->load->view('site/head', $this->data); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo public_url('site/'); ?>css/validation.css">
</head>

<body>
	<div class="container">
		<?php $this->load->view('site/header', $this->data); ?>
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
				<?php $this->load->view('admin/message.php'); ?>
				<?php if ($this->data['token_info']['status']): ?>
					<div class="ngoclan-verification-container">
						<div class="ngoclan-verification-card">
							<img src="<?php echo base_url(); ?>public/upload/validation/checked.png" alt="Icon thành công" class="img-responsive ngoclan-verification-icon">
							<h1 class="ngoclan-verification-title">Xác thực thành công!</h1>
							<p class="ngoclan-verification-message">Tài khoản <?php $this->data['token_info']['data']['email']?> đã được xác thực. Cảm ơn 
							<?php $this->data['token_info']['data']['name']?> đã lựa chọn chúng tôi</p>
							<p class="ngoclan-verification-message">Hãy đăng nhập để có thể bắt đầu mua sắm tại Shop quần áo mini.</p>
							<a href="<?php echo base_url('dang-nhap'); ?>" class="ngoclan-verification-button">Đăng nhập ngay</a>
						</div>
					</div>
				<?php else: ?>
					<div class="ngoclan-verification-container">
						<div class="ngoclan-verification-card">
							<img src="<?php echo base_url(); ?>public/upload/validation/cancel.png" alt="Icon thất bại" class="img-responsive ngoclan-verification-icon">
							<h1 class="ngoclan-verification-title">Xác thực thất bại!</h1>
							<p class="ngoclan-verification-message">Link xác thực không hợp lệ hoặc đã hết hạn.</p>
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