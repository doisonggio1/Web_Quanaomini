<!DOCTYPE html>
<html>

<head>
	<?php $this->load->view('admin/head.php'); ?>
</head>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QPG3ZQV73K"></script>
<script>
	window.dataLayer = window.dataLayer || [];

	function gtag() {
		dataLayer.push(arguments);
	}
	gtag('js', new Date());

	gtag('config', 'G-QPG3ZQV73K');
</script>
<body>
	<?php $this->load->view('admin/style.php'); ?>
	<?php $this->load->view('admin/header.php'); ?>
	<?php $this->load->view('admin/sidebar.php'); ?>
	<div id="sidebar-collapse" class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
		<?php $this->load->view('admin/message.php'); ?>
		<?php $this->load->view($temp, $this->data); ?>
	</div>
	<?php $this->load->view('admin/footer.php'); ?>
</body>

</html>