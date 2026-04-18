<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 clearpaddingr">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
		<ol class="breadcrumb">
			<li><a href="#"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Trang chủ</a></li>
			<li class="active">Chi tiết giỏ hàng</li>
		</ol>
		<?php if (isset($message) && !empty($message)) { ?>
			<h4 style="color:red;margin-top: 20px"><?php echo $message; ?></h4>
		<?php }
		if ($total_items > 0) { ?>
			<div class="panel panel-info " style="margin-bottom: 15px">
				<div class="panel-heading">
					<h3 class="panel-title title-bar">GIỎ HÀNG ( <?php echo $total_items; ?> sản phẩm )</h3>
				</div>
				<div class="panel-body">
					<table class="table table-hover">
						<thead>
							<th>STT</th>
							<th>Tên sản phẩm</th>
							<th>Hình ảnh</th>
							<th>Số lượng</th>
							<th>Thành tiền</th>
							<th>Xóa</th>
						</thead>
						<tbody>
							<?php
							$i = 0;
							$total_price = 0;
							foreach ($carts as $items) {
								$total_price = $total_price + $items['subtotal']; ?>
								<tr>
									<td><?php echo $i = $i + 1 ?></td>
									<td><?php echo $items['name']; ?></td>
									<td><img src="<?php echo base_url('upload/product/' . $items['image_link']); ?>" class="img-thumbnail" alt="" style="width: 50px;"></td>
									<td>
										<button class="cart-sub" data-id="<?php echo $items['id']; ?>">-</button>
										<input type="text" class="qty-input" value="<?php echo $items['qty']; ?>" style="width: 30px;text-align: center;" readonly>
										<button class="cart-sum" data-id="<?php echo $items['id']; ?>">+</button>
									</td>
									<td><?php echo number_format($items['subtotal']); ?> VNĐ</td>
									<td><a class="del-item" href="#" data-id="<?php echo $items['id']; ?>"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></td>
								</tr>
							<?php	}
							?>

							<tr>
								<td colspan="4">Tổng tiền</td>
								<td style="font-weight: bold;color:green" id="total_price"><?php echo number_format($total_price); ?> VNĐ</td>
								<td><a style="font-weight: bold;color: red" class="del-all" href="#" data-id="-1">Xóa toàn bộ</a></td>
							</tr>
							<tr>
								<td colspan="6">
									<a onclick="checkLoginBeforeOrder()" class="btn btn-success" style="cursor:pointer">Đặt mua</a>
								</td>
							</tr>
						</tbody>
					</table>

				</div>
			</div>
		<?php } else { ?>
			<div class="panel panel-info " style="margin-bottom: 15px">
				<div class="panel-heading">
					<h3 class="panel-title">GIỎ HÀNG ( 0 sản phẩm )</h3>
				</div>
				<div class="panel-body">
					<div class="text-center">
						<img src="<?php echo base_url('upload/cart-empty.png') ?>" alt="">
						<h4 style="color:red">Không có sản phẩm trong giỏ hàng</h4>
						<a href="<?php echo base_url('product/hot'); ?>" class="btn btn-success">Mua sắm</a>
					</div>

				</div>
			</div>

		<?php }
		?>



	</div>
</div>

<script>
	// Lấy phần số trong subtotal để tính toán
	function extractNumber(text) {
		return parseFloat(text.replace(/[^0-9]/g, "")); // Loại bỏ ký tự không phải số và chuyển thành số
	}

	document.addEventListener("DOMContentLoaded", function() {
		//xoá từng dòng
		document.querySelectorAll('.del-item').forEach(el => {
			let isLoading = false; // cờ trạng thái riêng cho từng nút

			el.addEventListener('click', function(e) {
				e.preventDefault(); // Ngăn chặn link chuyển trang

				if (isLoading) {
					// Đang xử lý request trước, không làm gì nữa
					return;
				}

				isLoading = true; // Đánh dấu đang xử lý

				const itemId = this.getAttribute('data-id');

				fetch(`<?php echo base_url('cart/del'); ?>`, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-Requested-With': 'XMLHttpRequest'
						},
						body: JSON.stringify({
							id: itemId
						})
					})
					.then(res => res.text())
					.then(text => {
						let data;
						try {
							data = JSON.parse(text);
						} catch (e) {
							console.error('Response không phải JSON:', e);
							alert('Phản hồi từ server không hợp lệ!');
							return;
						}

						if (data.status) {
							const row = this.closest('tr');
							const tbody = row.closest('tbody');
							if (tbody) {
								const remainingRows = tbody.querySelectorAll('tr');
								if (remainingRows.length <= 3) {
									// Nếu không còn hàng nào trong tbody thì reload trang
									location.reload();
								} else {
									if (row) row.remove();
								}
							}
						} else {
							alert('Xóa không thành công!');
						}
					})
					.catch(err => {
						console.error(err);
						alert('Có lỗi xảy ra khi xóa.');
					})
					.finally(() => {
						isLoading = false; // Cho phép bấm lại khi fetch hoàn thành
					});
			});
		});
		let isLoading2 = false; // cờ trạng thái riêng cho từng nút

		document.querySelector('.del-all').addEventListener('click', function(e) {
			if (isLoading2) {
				// Đang xử lý request trước, không làm gì nữa
				return;
			}

			isLoading2 = true; // Đánh dấu đang xử lý

			const itemId = this.getAttribute('data-id');

			fetch(`<?php echo base_url('cart/del'); ?>`, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest'
					},
					body: JSON.stringify({
						id: itemId
					})
				})
				.then(res => res.text())
				.then(text => {
					let data;
					try {
						data = JSON.parse(text);
					} catch (e) {
						console.error('Response không phải JSON:', e);
						alert('Phản hồi từ server không hợp lệ!');
						return;
					}

					if (data.status) {
						location.reload();
					} else {
						alert('Xóa không thành công!');
					}
				})
				.catch(err => {
					console.error(err);
					alert('Có lỗi xảy ra khi xóa.');
				})
				.finally(() => {
					isLoading2 = false; // Cho phép bấm lại khi fetch hoàn thành
				});
		});

		// Ngăn request được gửi đi quá nhiều lần 
		let controller = new AbortController(); // Khởi tạo 1 controller duy nhất
		let allow = true;
		let count = 0; // Biến đếm
		let server_count = 0;

		let panelTitle = document.querySelector(".title-bar").innerText;
		let totalItems = panelTitle.match(/\d+/); // Lấy số đầu tiên trong chuỗi
		totalItems = totalItems ? parseInt(totalItems[0], 10) : 0; // Chuyển thành số nguyên

		document.querySelectorAll(".cart-sum, .cart-sub").forEach(function(button) {
			button.addEventListener("click", function(e) {
				e.preventDefault();

				var btn = this;
				var qtyInput = btn.parentElement.querySelector(".qty-input");

				let row = this.closest('tr'); // Lấy dòng <tr> chứa nút được bấm
				let subtotalTd = row.querySelector('td:nth-child(5)'); // Lấy cột subtotal



				current_price = extractNumber(subtotalTd.textContent.trim());
				unit_price = current_price / parseInt(qtyInput.value);

				// Chỉ được request nếu như không có request nào trước đó
				if (!allow) {
					controller.abort(); // Hủy request nếu có
				}

				controller = new AbortController(); // Chỉ tạo mới khi gửi request hợp lệ
				const signal = controller.signal;

				var id = this.getAttribute("data-id");
				var action = this.classList.contains("cart-sum") ? "sum" : "sub";
				if (action == "sub" && (parseInt(qtyInput.value) - 1 < 1)) {
					return;
				}

				if (action == "sum") {
					qtyInput.value = parseInt(qtyInput.value) + 1;
					current_price = current_price + unit_price;
					subtotalTd.textContent = current_price.toLocaleString() + " VNĐ";
					totalItems += 1;
					document.querySelector('.title-bar').innerHTML = 'GIỎ HÀNG ( ' + totalItems + ' sản phẩm )';
				} else if (action == "sub") {
					qtyInput.value = parseInt(qtyInput.value) - 1;
					current_price = current_price - unit_price;
					subtotalTd.textContent = current_price.toLocaleString() + " VNĐ";
					totalItems -= 1;
					document.querySelector('.title-bar').innerHTML = 'GIỎ HÀNG ( ' + totalItems + ' sản phẩm )';
				}

				let totalPrice = 0;
				document.querySelectorAll("tbody tr").forEach(row => {
					let priceCell = row.querySelector("td:nth-child(5)"); // Ô chứa tổng tiền sản phẩm
					let qtyInput = row.querySelector(".qty-input");

					if (priceCell && qtyInput) {
						let subtotal = parseInt(priceCell.textContent.replace(/\D/g, ""), 10) || 0; // Lấy tổng tiền, loại bỏ ký tự không phải số
						let qty = parseInt(qtyInput.value, 10) || 1; // Lấy số lượng (mặc định là 1 để tránh lỗi chia cho 0)
						let price = Math.floor(subtotal / qty); // Tính giá gốc của 1 sản phẩm
						let newSubtotal = price * qty; // Tính lại tổng tiền
						totalPrice += newSubtotal;
					}
				});

				// Cập nhật tổng tiền của toàn bộ giỏ hàng
				document.getElementById("total_price").textContent = totalPrice.toLocaleString() + " VNĐ";

				allow = false;
				fetch("<?php echo base_url('cart/update_ajax'); ?>/" + id, {
						method: "POST",
						headers: {
							"Content-Type": "application/x-www-form-urlencoded"
						},
						body: "qty=" + parseInt(qtyInput.value),
						signal: signal // Thêm signal vào fetch request
					})
					.then(response => response.text()) // Đọc response dưới dạng text trước
					.then(text => {
						let data = JSON.parse(text); // Thử parse JSON
						if (data.status === "success") {
							allow = true;
						}
					})
					.catch(error => {
						if (error.name === "AbortError") {
							console.warn("Request đã bị hủy:", error);
						} else {
							console.error("Ajax Error:", error);
						}
					});
			});
		});
	});
</script>
<style>
	.cart-sum,
	.cart-sub {
		padding: 2px 8px;
		margin: 0 3px;
		cursor: pointer;
	}
</style>

<!-- Thêm modal thông báo đăng nhập -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Thông báo</h4>
			</div>
			<div class="modal-body">
				<p>Bạn cần đăng nhập để đặt hàng</p>
			</div>
			<div class="modal-footer">
				<a href="<?php echo base_url('dang-nhap'); ?>" class="btn btn-primary">Đăng nhập</a>
				<button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
			</div>
		</div>
	</div>
</div>

<!-- Thêm script kiểm tra đăng nhập -->
<script>
	function checkLoginBeforeOrder() {
		<?php if (!$this->session->userdata('user')): ?>
			$('#loginModal').modal('show');
		<?php else: ?>
			window.location.href = '<?php echo base_url('order'); ?>';
		<?php endif; ?>
	}
</script>