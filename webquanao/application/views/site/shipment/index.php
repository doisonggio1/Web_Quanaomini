<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-raty/2.7.1/jquery.raty.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-raty/2.7.1/jquery.raty.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> -->

<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 clearpaddingr">
    <div class="panel panel-info" style="margin-bottom: 15px">
        <div class="panel-heading">
            <h3 class="panel-title">Danh sách đơn hàng</h3>
        </div>
        
        <!-- Status Tabs Navigation -->
        <div class="status-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="<?php echo ($status_filter == 'all') ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('shipment'); ?>">
                        <i class="glyphicon glyphicon-list"></i> Tất cả
                        <?php if(isset($total_orders) && $total_orders > 0): ?>
                            <span class="badge badge-all"><?php echo $total_orders; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li role="presentation" class="<?php echo ($status_filter == '0') ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('shipment?status=0'); ?>">
                        <i class="glyphicon glyphicon-time"></i> Chờ xác nhận
                        <?php if(isset($status_counts[0]) && $status_counts[0] > 0): ?>
                            <span class="badge badge-pending"><?php echo $status_counts[0]; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li role="presentation" class="<?php echo ($status_filter == '1') ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('shipment?status=1'); ?>">
                        <i class="glyphicon glyphicon-ok"></i> Đã xác nhận
                        <?php if(isset($status_counts[1]) && $status_counts[1] > 0): ?>
                            <span class="badge badge-confirmed"><?php echo $status_counts[1]; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li role="presentation" class="<?php echo ($status_filter == '2') ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('shipment?status=2'); ?>">
                        <i class="glyphicon glyphicon-send"></i> Đang vận chuyển
                        <?php if(isset($status_counts[2]) && $status_counts[2] > 0): ?>
                            <span class="badge badge-shipping"><?php echo $status_counts[2]; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li role="presentation" class="<?php echo ($status_filter == '3') ? 'active' : ''; ?>">
                    <a href="<?php echo base_url('shipment?status=3'); ?>">
                        <i class="glyphicon glyphicon-check"></i> Hoàn thành
                        <?php if(isset($status_counts[3]) && $status_counts[3] > 0): ?>
                            <span class="badge badge-completed"><?php echo $status_counts[3]; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
				<li role="presentation" class="<?php echo ($status_filter == '4') ? 'active' : ''; ?>">
					<a href="<?php echo base_url('shipment?status=4'); ?>">
						<i class="glyphicon glyphicon-remove"></i> Đã hủy
						<?php if(isset($status_counts[4]) && $status_counts[4] > 0): ?>
							<span class="badge badge-danger"><?php echo $status_counts[4]; ?></span>
						<?php endif; ?>
					</a>
				</li>
            </ul>
        </div>
        
        <div class="panel-body">
            <?php if (!empty($orders)) { ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sản phẩm</th>
                            <th>Tổng tiền</th>
                            <th>Thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) { 
                            // Get the first image from the concatenated list
                            $images = explode('|', $order->product_images);
                            $first_image = !empty($images[0]) ? $images[0] : '';
                        ?>
                            <tr>
                                <td>#<?php echo $order->transaction_id; ?></td>
                                <td>
                                    <div class="order-products">
                                        <?php if(!empty($first_image)): ?>
                                        <img src="<?php echo base_url('upload/product/' . $first_image); ?>" 
                                            class="img-thumbnail" alt="Product Image" style="width: 50px; margin-right: 10px;">
                                        <?php endif; ?>
                                        <div>
                                            <div class="product-names"><?php echo $order->product_names; ?></div>
                                            <div class="product-count">Số lượng: <?php echo $order->total_items; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($order->total_amount); ?> VNĐ</td>
                                <td>
                                    <?php
                                    if ($order->payment == 'cash') {
                                        echo "<span class='label label-info'>Tiền mặt khi nhận hàng</span>";
                                    } else if ($order->payment == 'vnpay') {
                                        echo "<span class='label label-success'>VNPAY</span>";
                                    } else if ($order->payment == 'vietqr') {
                                        echo "<span class='label label-primary'>Chuyển khoản</span>";
                                    } else if ($order->payment == 'pos') {
                                        echo "<span class='label label-warning'>Quẹt thẻ POS</span>";
                                    } else {
                                        echo "<span class='label label-default'>Chưa xác định</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($order->status == 0) {
                                        echo "<span class='label label-warning'>Chờ xác nhận</span>";
                                    } else if ($order->status == 1) {
                                        echo "<span class='label label-primary'>Đã xác nhận</span>";
                                    } else if ($order->status == 2) {
                                        echo "<span class='label label-info'>Đang vận chuyển</span>";
                                    } else if ($order->status == 3) {
                                        echo "<span class='label label-success'>Hoàn thành</span>";
                                    } else if ($order->status == 4) {
										echo "<span class='label label-danger'>Đã hủy</span>";
									}
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Check if $order->created is numeric (timestamp)
                                    $timestamp = is_numeric($order->created) ? $order->created : strtotime($order->created);

                                    // If valid timestamp, format it, otherwise display an error or default message
                                    echo ($timestamp !== false) ? date('d/m/Y H:i', $timestamp) : 'Invalid Date';
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="#" class="btn btn-xs btn-info view-order-details" data-id="<?php echo $order->transaction_id; ?>" data-status="<?php echo $order->status; ?>">
                                            <i class="glyphicon glyphicon-eye-open"></i> Chi tiết
                                        </a>
                                        <?php if($order->status == 0): // Only show cancel button for pending orders ?>
                                        <a href="#" class="btn btn-xs btn-danger cancel-order" data-id="<?php echo $order->transaction_id; ?>">
                                            <i class="glyphicon glyphicon-remove"></i> Hủy
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                
                <div class="text-center">
                    <?php echo $pagination; ?>
                </div>
                
            <?php } else { ?>
                <div class="empty-state text-center">
                    <div class="empty-state-icon">
                        <?php 
                        $icon_class = 'glyphicon-shopping-cart';
                        $status_text = '';
                        
                        if ($status_filter == '0') {
                            $icon_class = 'glyphicon-time';
                            $status_text = 'chờ xác nhận';
                        } else if ($status_filter == '1') {
                            $icon_class = 'glyphicon-ok';
                            $status_text = 'đã xác nhận';
                        } else if ($status_filter == '2') {
                            $icon_class = 'glyphicon-send';
                            $status_text = 'đang vận chuyển';
                        } else if ($status_filter == '3') {
                            $icon_class = 'glyphicon-check';
                            $status_text = 'hoàn thành';
                        } else if ($status_filter == '4') {
							$icon_class = 'glyphicon-remove';
							$status_text = 'đã hủy';
						}
                        ?>
                        <i class="glyphicon <?php echo $icon_class; ?>"></i>
                    </div>
                    <h4 class="text-muted">Không có đơn hàng nào<?php echo ($status_filter != 'all') ? ' ' . $status_text : ''; ?>!</h4>
                    <p>Bạn chưa có đơn hàng nào<?php echo ($status_filter != 'all') ? ' ' . $status_text : ''; ?>.</p>
                    <a href="<?php echo base_url(); ?>" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            <?php } ?>

            <!-- Add the recommended products section here -->
            <div class="panel panel-info" style="margin-top: 20px;">
                <div class="panel-heading">
                    <h3 class="panel-title">Có thể bạn cũng thích</h3>
                </div>
                <div class="panel-body">
                    <div class="recommended-products-container row">
                        <?php if (isset($recommended_products) && !empty($recommended_products)) : ?>
                            <?php foreach ($recommended_products as $product) : ?>
                                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
                                    <div class="recommended-product">
                                        <a href="<?php echo $product['url']; ?>" class="product-image">
                                            <img src="<?php echo base_url('upload/product/' . $product['image']); ?>" alt="<?php echo $product['name']; ?>">
                                        </a>
                                        <h5><a href="<?php echo $product['url']; ?>"><?php echo $product['name']; ?></a></h5>
                                        <p class="price">
                                            <span class="current-price"><?php echo number_format($product['price']); ?> VNĐ</span>
                                            <?php if ($product['discount'] > 0) : ?>
                                                <br><del class="original-price"><?php echo number_format($product['original_price']); ?> VNĐ</del>
                                            <?php endif; ?>
                                        </p>
                                        <a href="<?php echo base_url('cart/add/' . $product['id']); ?>" class="btn btn-primary btn-add-cart">
                                            <i class="glyphicon glyphicon-shopping-cart"></i> Thêm vào giỏ
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="col-xs-12">
                                <p class="text-center text-muted">Không có sản phẩm đề xuất.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-labelledby="ratingModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="ratingModalLabel">Đánh giá sản phẩm</h4>
			</div>
			<div class="modal-body">
				<form id="ratingForm">
					<input type="hidden" id="ratingOrderId" name="order_id">
					<div class="form-group">
						<label for="ratingStars">Chọn số sao:</label>
						<div id="ratingStars" class="raty"></div>
						<input type="hidden" id="ratingScore" name="score">
					</div>
					<div class="form-group">
						<label for="ratingComment">Bình luận:</label>
						<textarea class="form-control" id="ratingComment" name="comment" rows="3" required></textarea>
					</div>
					<button type="submit" class="btn btn-primary">Gửi đánh giá</button>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Bootstrap Modal for Order Details -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="orderDetailsModalLabel">Chi tiết đơn hàng #<span id="order-id"></span></h4>
            </div>
            <div class="modal-body">
                <div id="order-details-content">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><strong>Thông tin người nhận</strong></h5>
                            <p><strong>Họ tên:</strong> <span id="customer-name"></span></p>
                            <p><strong>Email:</strong> <span id="customer-email"></span></p>
                            <p><strong>Điện thoại:</strong> <span id="customer-phone"></span></p>
                            <p><strong>Địa chỉ:</strong> <span id="customer-address"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h5><strong>Thông tin đơn hàng</strong></h5>
                            <p><strong>Mã đơn hàng:</strong> #<span id="order-number"></span></p>
                            <p><strong>Ngày đặt:</strong> <span id="order-date"></span></p>
                            <p><strong>Trạng thái:</strong> <span id="order-status"></span></p>
                            <p><strong>Thanh toán:</strong> <span id="order-payment"></span></p>
                        </div>
                    </div>
                    
                    <div class="order-products-list">
                        <h5><strong>Danh sách sản phẩm</strong></h5>
                        <div id="order-products"></div>
                    </div>
                    
                    <div class="order-total">
                        <h4 class="text-right">Tổng cộng: <strong><span id="order-total"></span> VNĐ</strong></h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript xử lý chọn đơn hàng -->
<script>
    $(document).ready(function () {
        // Define base URL
        var baseUrl = "<?php echo base_url(); ?>";
        
        // View order details
        $(".view-order-details").on("click", function(e) {
            e.preventDefault();
            
            const orderId = $(this).data("id");
			// console.log("Order ID:", orderId);
            
            // Show loading indicator in modal
			$("#order-details-content").html('<div class="text-center"><i class="glyphicon glyphicon-refresh"></i> Đang tải dữ liệu...</div>');
            $("#orderDetailsModal").modal("show");
            
            // Fetch order details via AJAX
            $.ajax({
                url: "<?php echo base_url('shipment/get_order_details/') ?>" + orderId,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    console.log("Order details response:", response);
                    
                    if (response && response.status === "success") {
                        const order = response.order;
                        console.log("Processing order data:", order);
                        
                        // Use our reusable render function
                        renderOrderDetails(order);
                    } else {
                        $("#order-details-content").html(`
                            <div class="alert alert-danger">
                                <h4>Không thể tải thông tin đơn hàng</h4>
                                <p>${response ? response.message : 'Không có dữ liệu phản hồi'}</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching order details:", error);
                    console.error("Response status:", status);
                    console.error("Response text:", xhr.responseText);
                    
                    // Try to parse the response in case it's JSON but not properly handled
                    try {
                        if (xhr.responseText) {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            console.log("Parsed error response:", jsonResponse);
                            
                            // If we have valid JSON, try to render it directly
                            if (jsonResponse && jsonResponse.status === "success" && jsonResponse.order) {
                                renderOrderDetails(jsonResponse.order);
                                return;
                            }
                        }
                    } catch (e) {
                        console.error("Failed to parse error response:", e);
                    }
                    
                    $("#order-details-content").html(`
                        <div class="alert alert-danger">
                            <h4>Có lỗi xảy ra khi tải thông tin đơn hàng</h4>
                            <p>Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</p>
                            <p><small>Chi tiết lỗi: ${error || 'Unknown error'}</small></p>
                        </div>
                    `);
                },
                complete: function() {
                    console.log("Request completed");
                }
            });

            // Fetch order details via AJAX
            $.ajax({
                url: "<?php echo base_url('shipment/get_order_details/') ?>" + orderId,
                type: "GET",
                dataType: "json",
                cache: false, // Disable caching
                timeout: 30000, // 30 second timeout
                success: function(response) {
                    console.log("Order details response:", response);
                    
                    if (response && response.status === "success") {
                        const order = response.order;
                        console.log("Processing order data:", order);
                        
                        // Use our reusable render function
                        renderOrderDetails(order);
                    } else {
                        $("#order-details-content").html(`
                            <div class="alert alert-danger">
                                <h4>Không thể tải thông tin đơn hàng</h4>
                                <p>${response ? response.message : 'Không có dữ liệu phản hồi'}</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching order details:", error);
                    console.error("Response status:", status);
                    console.error("Response text:", xhr.responseText);
                    
                    // Try to parse the response in case it's JSON but not properly handled
                    try {
                        if (xhr.responseText) {
                            var jsonResponse = JSON.parse(xhr.responseText);
                            console.log("Parsed error response:", jsonResponse);
                            
                            // If we have valid JSON, try to render it directly
                            if (jsonResponse && jsonResponse.status === "success" && jsonResponse.order) {
                                renderOrderDetails(jsonResponse.order);
                                return;
                            }
                        }
                    } catch (e) {
                        console.error("Failed to parse error response:", e);
                    }
                    
                    $("#order-details-content").html(`
                        <div class="alert alert-danger">
                            <h4>Có lỗi xảy ra khi tải thông tin đơn hàng</h4>
                            <p>Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</p>
                            <p><small>Chi tiết lỗi: ${error || 'Unknown error'}</small></p>
                        </div>
                    `);
                },
                complete: function() {
                    console.log("Request completed");
                }
            });
            
            // Create a backup link in case the AJAX fails
            const backupLink = $("<div class='text-center mt-3'><a href='" + 
                "<?php echo base_url('shipment/get_order_details/') ?>" + orderId + 
                "' target='_blank' class='btn btn-default btn-sm'>Mở chi tiết đơn hàng trong tab mới</a></div>");
            
            $("#order-details-content").append(backupLink);
        });

        // Individual cancel button
        $(".cancel-order").on("click", function(e) {
            e.preventDefault();
            
            let orderId = $(this).data("id");
            console.log("Cancelling order ID:", orderId);
            
            if (!confirm("Bạn có chắc muốn hủy đơn hàng này không?")) return;
            
            $.ajax({
                url: "<?php echo base_url('shipment/cancel_orders'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    order_ids: [orderId]
                },
                success: function (response) {
                    console.log("Cancel response:", response);
                    if (response && response.status === "success") {
                        alert(response.message || "Đã hủy đơn hàng thành công!");
                        window.location.reload();
                    } else {
                        alert(response.message || "Có lỗi xảy ra khi hủy đơn hàng.");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Cancel error:", error);
                    alert("Có lỗi xảy ra, vui lòng thử lại!");
                }
            });
        });
        
        // Helper function to render order details directly
        function renderOrderDetails(order) {
            console.log("Rendering order directly:", order);
            
            const detailsHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h5><strong>Thông tin người nhận</strong></h5>
                        <p><strong>Họ tên:</strong> ${order.customer_name || ''}</p>
                        <p><strong>Email:</strong> ${order.customer_email || ''}</p>
                        <p><strong>Điện thoại:</strong> ${order.customer_phone || ''}</p>
                        <p><strong>Địa chỉ:</strong> ${order.customer_address || ''}</p>
                    </div>
                    <div class="col-md-6">
                        <h5><strong>Thông tin đơn hàng</strong></h5>
                        <p><strong>Mã đơn hàng:</strong> #${order.id || ''}</p>
                        <p><strong>Ngày đặt:</strong> ${order.order_date || ''}</p>
                        <p><strong>Trạng thái:</strong> <span class="label ${
                            parseInt(order.status_code) === 0 ? 'label-warning' : 
                            parseInt(order.status_code) === 1 ? 'label-primary' : 
                            parseInt(order.status_code) === 2 ? 'label-info' : 
                            parseInt(order.status_code) === 3 ? 'label-success' : 
                            parseInt(order.status_code) === 4 ? 'label-danger' : 'label-default'
                        }">${order.status || ''}</span></p>
                        <p><strong>Thanh toán:</strong> ${order.payment_method || ''}</p>
                    </div>
                </div>
                
                <div class="order-products-list">
					<h5><strong>Danh sách sản phẩm</strong></h5>
					<table class="table table-hover">
						<thead>
							<tr>
								<th>ID</th>
								<th>Sản phẩm</th>
								<th>Số lượng</th>
								<th>Tổng giá</th>
								<th>Thao tác</th>
							</tr>
						</thead>
						<tbody id="order-products">
							${order.product_details.map(product => `
								<tr>
									<td>${product.id}</td>
									<td>
										<div class="order-products">
											<img src="${baseUrl + 'upload/product/' + product.image}" 
												class="img-thumbnail" alt="Product Image" style="width: 50px; margin-right: 10px;">
											<div>
												<div class="product-names">${product.name}</div>
											</div>
										</div>
									</td>
									<td>${product.quantity}</td>
									<td>${product.subtotal} VNĐ</td>
									<td>
										${(parseInt(order.status_code) === 3 && product.status == 0)
											? `<button class="btn btn-success btn-sm review-product" data-id="${product.id}"><i class="glyphicon glyphicon-star"></i> Đánh giá</button>`
											: (product.status == 1
												? `<span class="text-muted">Đã đánh giá</span>`
												: `<button class="btn btn-secondary btn-sm" disabled><i class="glyphicon glyphicon-star"></i> Đánh giá</button>`)}
									</td>
								</tr>`).join('')}
						</tbody>
					</table>
				</div>
                
                <div class="order-total">
                    <h4 class="text-right">Tổng cộng: <strong>${order.total_amount || '0'} VNĐ</strong></h4>
                </div>
            `;
            
            // Replace the entire content at once
            $("#order-details-content").html(detailsHTML);
            $("#orderDetailsModal .modal-title").text("Chi tiết đơn hàng #" + order.id);

			// Gắn sự kiện cho nút "Đánh giá"
			$(".review-product").on("click", function () {
				const productId = $(this).data("id");
				const productName = $(this).closest("tr").find(".product-names").text();

				$("#ratingModalLabel").text(`Đánh giá sản phẩm: ${productName}`);
				$("#ratingForm").data("product-id", productId);
				$("#ratingForm").data("order-id", order.id);

				$("#orderDetailsModal").modal("hide");
				$("#ratingModal").modal("show");

				$('#ratingForm').off('submit').on('submit', function (e) {
					e.preventDefault();
					const productId = $(this).data("product-id");
					const orderId = $(this).data("order-id");
					const score = $('#ratingScore').val();
					const comment = $('#ratingComment').val();
					
					console.log("Submitting rating for product ID:", productId);
					console.log("Order ID:", orderId);
					console.log("Rating score:", score);
					console.log("Comment:", comment);

					if (!score) {
						alert('Vui lòng chọn số sao!');
						return;
					}

					$.ajax({
						url: '<?php echo base_url('product/submit_rating'); ?>',
						type: 'POST',
						data: {
							id: productId,
							score: score,
							comment: comment,
							order_id: orderId
						},
						dataType: 'json',
						success: function (response) {
							console.log(response.message);
							if (response.success) {
								alert('Cảm ơn bạn đã đánh giá sản phẩm.');
								$("#ratingModal").modal("hide");
								
								const orderId = $("#ratingForm").data("order-id");
								$.ajax({
									url: "<?php echo base_url('shipment/get_order_details/') ?>" + orderId,
									type: "GET",
									dataType: "json",
									success: function (response) {
										if (response && response.status === "success") {
											const order = response.order;
											console.log("Updated order data:", order);

											// Cập nhật lại modal "Chi tiết đơn hàng"
											renderOrderDetails(order);
										} else {
											console.error("Không thể làm mới dữ liệu đơn hàng.");
										}
									},
									error: function (xhr, status, error) {
										console.error("Error refreshing order details:", error);
									}
								});
							} else {
								alert(response.message);
							}
						},
						error: function (xhr, status, error) {
							console.error('Status:', status);
							console.error('Error:', error);
							console.error('Response:', xhr.responseText);
							alert('Đã xảy ra lỗi, vui lòng thử lại.');
						}
					});
				});
			});

			$("#ratingModal").on("hidden.bs.modal", function () {
				$("#orderDetailsModal").modal("show");
			});

			$('#ratingStars').raty({
				score: 0,
				half: false,
				click: function (score, evt) {
					$('#ratingScore').val(score);
				}
			});
		}
    });
</script>

<style>
    .selected {
        background-color: #dcdcdc !important;
    }
    
    .status-tabs {
		display: flex;
		flex-wrap: nowrap;
		overflow-x: auto;
        margin: 15px 15px 0;
    }
    
    .status-tabs .nav-tabs {
		display: flex;
		flex-wrap: nowrap;
		justify-content: space-between;
        border-bottom: 2px solid #ddd;
    }
    
    .status-tabs .nav-tabs>li {
		flex: 1;
    	text-align: center;
        margin-bottom: -2px;
		margin-left: -3px;
		margin-right: -3px;
	}
    
    .status-tabs .nav-tabs>li>a {
        font-weight: 500;
        color: #555;
        padding: 10px 15px;
        border-radius: 4px 4px 0 0;
        transition: all 0.3s ease;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
    }
    
    .status-tabs .nav-tabs>li>a:hover {
        background-color: #f8f8f8;
        border-color: #eee #eee #ddd;
    }
    
    .status-tabs .nav-tabs>li.active>a, 
    .status-tabs .nav-tabs>li.active>a:hover, 
    .status-tabs .nav-tabs>li.active>a:focus {
        color: #337ab7;
        font-weight: 600;
        cursor: default;
        background-color: #fff;
        border: 1px solid #ddd;
        border-bottom-color: transparent;
        border-bottom: 2px solid #337ab7;
    }
    
    .badge {
        margin-left: 5px;
        padding: 3px 7px;
        font-size: 11px;
        font-weight: normal;
    }
    
    .badge-all {
        background-color: #777;
    }
    
    .badge-pending {
        background-color: #f39c12;
    }
    
    .badge-confirmed {
        background-color: #337ab7;
    }
    
    .badge-shipping {
        background-color: #5bc0de;
    }
    
    .badge-completed {
        background-color: #5cb85c;
    }
    
	.badge-danger {
		background-color: #d9534f;
	}

    .nav-tabs>li.active>a .badge {
        color: #fff;
        font-weight: bold;
    }
    
    .order-products {
        display: flex;
        align-items: center;
    }
    
    .product-names {
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .product-count {
        font-size: 12px;
        color: #777;
    }
    
    .empty-state {
        padding: 60px 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
        margin: 20px 0;
    }
    
    .empty-state-icon {
        margin-bottom: 20px;
    }
    
    .empty-state-icon i {
        font-size: 64px;
        color: #ddd;
        background-color: #fff;
        padding: 20px;
        border-radius: 50%;
        border: 1px solid #eee;
    }
    
    .empty-state h4 {
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .empty-state .btn {
        margin-top: 15px;
        padding: 8px 20px;
        font-weight: 500;
    }
    
    .order-products-list {
        margin-top: 20px;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }
    
    .order-total {
        margin-top: 20px;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }
    
    .panel-info {
        border-color: #ddd;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .panel-info > .panel-heading {
        color: #333;
        background-color: #f8f8f8;
        border-color: #ddd;
        font-weight: 600;
    }
    
    .table > thead > tr > th {
        background-color: #f9f9f9;
    }
    
    @media (max-width: 767px) {
        .status-tabs .nav-tabs > li {
            float: none;
            margin-bottom: 5px;
        }
        
        .status-tabs .nav-tabs > li > a {
            margin-right: 0;
        }
        
        .status-tabs .nav-tabs > li.active > a {
            border: 1px solid #ddd;
            border-left: 3px solid #337ab7;
        }
        
        .order-products {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .order-products img {
            margin-bottom: 10px;
        }
    }
    
    /* Recommended Products Styles */
    .recommended-products-section {
        margin-top: 30px;
        border-top: 1px solid #eee;
        padding-top: 20px;
        background-color: #f9f9f9;
        border-radius: 4px;
        padding: 15px;
    }
    
    .recommended-products-section h5 {
        margin-bottom: 20px;
        color: #333;
        font-weight: 600;
        position: relative;
        padding-bottom: 10px;
    }
    
    .recommended-products-section h5:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #337ab7;
    }
    
    .recommended-product {
        transition: all 0.3s ease;
        background-color: #fff;
        height: 100%;
    }
    
    .recommended-product:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    
    .recommended-product img {
        transition: all 0.3s ease;
    }
    
    .recommended-product:hover img {
        transform: scale(1.05);
    }
    
    .recommended-product h5 {
        font-size: 14px;
        line-height: 1.4;
    }
    
    .recommended-product .btn {
        border-radius: 3px;
        transition: all 0.2s ease;
    }
    
    .recommended-product .btn:hover {
        background-color: #265a88;
    }
    
    @media (max-width: 991px) {
        .recommended-product h5 {
            height: auto;
            max-height: 40px;
        }
    }
    
    @media (max-width: 767px) {
        #recommended-products .col-xs-12 {
            width: 50%;
        }
    }
    
    @media (max-width: 480px) {
        #recommended-products .col-xs-12 {
            width: 100%;
        }
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 5px;
        justify-content: flex-start;
        align-items: center;
    }
    
    .action-buttons .btn {
        padding: 4px 8px;
        min-width: 70px;
        text-align: center;
        margin-bottom: 3px;
    }
    
    .action-buttons .btn i {
        margin-right: 3px;
    }
    
    /* Main page recommended products styles */
    .recommended-products-container {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }
    
    .recommended-product {
        border: 1px solid #eee;
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
        border-radius: 4px;
        background-color: #fff;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .recommended-product:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    
    .recommended-product .product-image {
        display: block;
        margin-bottom: 15px;
        flex: 0 0 auto;
        overflow: hidden;
        border-radius: 3px;
    }
    
    .recommended-product img {
        max-width: 100%;
        height: 150px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .recommended-product:hover img {
        transform: scale(1.05);
    }
    
    .recommended-product h5 {
        height: 40px;
        overflow: hidden;
        margin-bottom: 10px;
        font-size: 14px;
        line-height: 1.4;
        flex: 0 0 auto;
    }
    
    .recommended-product h5 a {
        color: #333;
        text-decoration: none;
    }
    
    .recommended-product h5 a:hover {
        color: #337ab7;
    }
    
    .recommended-product .price {
        margin-bottom: 15px;
        flex: 1 0 auto;
    }
    
    .recommended-product .current-price {
        color: #e74c3c;
        font-weight: bold;
        font-size: 16px;
    }
    
    .recommended-product .original-price {
        color: #7f8c8d;
        font-size: 12px;
    }
    
    .recommended-product .btn-add-cart {
        width: 100%;
        border-radius: 3px;
        transition: all 0.2s ease;
        flex: 0 0 auto;
    }
    
    .recommended-product .btn-add-cart:hover {
        background-color: #265a88;
    }
    
    @media (max-width: 767px) {
        .recommended-products-container > div {
            width: 50%;
            padding: 0 10px;
        }
        
        .recommended-product h5 {
            height: auto;
            max-height: 40px;
        }
    }
    
    @media (max-width: 480px) {
        .recommended-products-container > div {
            width: 100%;
        }
    }
</style>