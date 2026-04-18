<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 clearpaddingr">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
		<style>
			/* Kiểu cho phần tổng hợp đánh giá AI */
			.ai-summary-container {
				padding: 15px;
				background-color: #f9f9f9;
				border-radius: 5px;
				margin-bottom: 15px;
			}
			.ai-summary-content {
				white-space: pre-line;
				line-height: 1.6;
			}
			.ai-summary-header {
				display: flex;
				align-items: center;
				margin-bottom: 10px;
			}
			.ai-summary-header i {
				margin-right: 8px;
				color: #3498db;
			}
			.ai-summary-section {
				margin-bottom: 10px;
			}
			.ai-summary-section h4 {
				font-weight: bold;
				margin-top: 15px;
				margin-bottom: 8px;
			}
			/* Hiệu ứng quay cho biểu tượng loading */
			@keyframes spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}
			/* Kiểu cho danh sách trong tổng hợp */
			.ai-summary-content li {
				margin-bottom: 5px;
			}
		</style>
		<ol class="breadcrumb">
			<li><a href="<?php echo base_url(); ?>"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Trang chủ</a></li>
			<li><a href="<?php echo base_url('product/catalog/' . $catalog_product->id); ?>"><?php echo $catalog_product->name; ?></a></li>
			<li class="active"><?php echo $product->name; ?></li>
		</ol>

		<!-- zoom image -->
		<script src="<?php echo public_url('js'); ?>/jqzoom_ev/js/jquery.jqzoom-core.js" type="text/javascript"></script>
		<link rel="stylesheet" href="<?php echo public_url('js'); ?>/jqzoom_ev/css/jquery.jqzoom.css" type="text/css">
		<script type="text/javascript">
			$(document).ready(function() {
				$('.jqzoom').jqzoom({
					zoomType: 'standard',
				});
			});
		</script>
		<!-- end zoom image -->
		<script type="text/javascript">
			$(document).ready(function() {
				//raty
				$('.raty_detailt').raty({
					score: function() {
						return $(this).attr('data-score');
					},
					half: true,
					click: function(score, evt) {
						var rate_count = $('.rate_count');
						var rate_count_total = rate_count.text();
						$.ajax({
							url: '<?php echo base_url('product/raty'); ?>',
							type: 'POST',
							data: {
								'id': '<?php echo $product->id; ?>',
								'score': score
							},
							dataType: 'json',
							success: function(data) {
								if (data.complete) {
									var total = parseInt(rate_count_total) + 1;
									rate_count.html(parseInt(total));
								}
								alert(data.msg);
							}
						});
					}
				});
			});
		</script>
		<!--End Raty -->


		<div class="panel panel-info ">
			<div class="panel-heading">
				<h3 class="panel-title">Xem chi tiết sản phẩm</h3>
			</div>
			<div class="panel-body">
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<div class="text-center">
						<a href="<?php echo base_url(); ?>upload/product/<?php echo $product->image_link; ?>" class="jqzoom" rel="gal1" title="triumph">
							<img src="<?php echo base_url(); ?>upload/product/<?php echo $product->image_link; ?>" alt="" style="max-width:380px;max-height: 500px">
						</a>
						<div class="clearfix"></div>
						<ul id="thumblist" style="margin-top: 20px;">
							<li>
								<a class="zoomThumbActive" href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: '<?php echo base_url(); ?>/upload/product/<?php echo $product->image_link ?>',largeimage: '<?php echo base_url(); ?>/upload/product/<?php echo $product->image_link ?>'}">
									<img src='<?php echo base_url(); ?>/upload/product/<?php echo $product->image_link ?>'>
								</a>
							</li>
							<?php if (is_array($image_list)): ?>
								<?php foreach ($image_list as $value) { ?>
									<li>
										<a href='javascript:void(0);' rel="{gallery: 'gal1', smallimage: '<?php echo base_url(); ?>/upload/product/<?php echo $value ?>',largeimage: '<?php echo base_url(); ?>/upload/product/<?php echo $value ?>'}">
											<img src='<?php echo base_url(); ?>/upload/product/<?php echo $value; ?>'>
										</a>
									</li>
								<?php } ?> <?php endif; ?>
						</ul>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
					<h1 style="font-size: 25px;text-transform:uppercase;color: red;font-weight:bold;"><?php $product->name; ?></h1>
					<p><?php echo $product->content; ?></p>
					<?php
					if ($product->discount > 0 || $product->price < $product->origin_price) {
						$price_new = $product->price - $product->discount;
					?><p>Giá cũ: <strong><del><?php echo number_format($product->origin_price) ?> VNĐ</del></strong></p>
						<p>Giá khuyến mại: <span style="font-weight: bold;color: green"><?php echo number_format($price_new); ?> VNĐ</span></p>
					<?php } else { ?>
						<p>Giá: <span style="font-weight: bold;color: green"><?php echo number_format($product->origin_price); ?> VNĐ</span></p> <?php
																																		}
																																			?>
					<p>Số lượt xem: <?php echo $product->view; ?></p>
					<p>Số lượt đã mua: <?php echo $product->buyed; ?></p>
					<p> Đánh giá &nbsp;
						<?php 
							$raty_tb = ($product->rate_count > 0) ? ($product->rate_total / $product->rate_count) : 0;
						?>
						<span class='raty_detailt' style='margin:5px' id='<?php echo $product->id; ?>' data-score='<?php echo round($raty_tb, 2); ?>'></span>
						| Tổng số: <b class='rate_count'><?php echo $product->rate_count; ?></b>
					</p>

					<script type="text/javascript">
						$(document).ready(function() {
							$('.raty_detailt').raty({
								score: function() {
									return $(this).attr('data-score');
								},
								readOnly: true,
								half: true,
								precision: true
							});
						});
					</script>

					<a href="<?php echo base_url('cart/add/' . $product->id); ?>" class="btn btn-info"> Thêm vào giỏ hàng</a>

				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<img src="<?php echo base_url(); ?>upload/icon/services.png" alt="">
						<p style="color:red">Phục vụ chu đáo</p>
					</div>
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<img src="<?php echo base_url(); ?>upload/icon/ship.png" alt="">
						<p style="color:red">Trao hàng đúng hẹn</p>
					</div>
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
						<img src="<?php echo base_url(); ?>upload/icon/services.png" alt="">
						<p style="color:red">Đổi hàng trong 24h</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="panel panel-info" style="margin-bottom: 15px">
			<div class="panel-heading">
				<h3 class="panel-title">Bình luận</h3>
			</div>
			<div class="panel-body comment-panel-body">
				<?php if (!empty($comments)): ?>
					<ul class="comment-list" id="comment-list">
						<?php foreach ($comments as $comment): ?>
							<li class="comment-item">
								<div class="product-rating">
									<div class="product-rating__avatar">
										<div class="avatar">
											<img src="<?php echo base_url('upload/avatar/default-avatar.jpg'); ?>" 
												alt="Avatar" class="avatar__img">
										</div>
									</div>

									<div class="product-rating__main">
										<div class="product-rating__author-name">
											<?php echo $comment->user_name; ?>
										</div>
										<div class="product-rating__rating">
											<?php for ($i = 1; $i <= 5; $i++): ?>
												<svg enable-background="new 0 0 15 15" viewBox="0 0 15 15" x="0" y="0" 
													class="svg-icon icon-rating-solid<?php echo ($i <= $comment->rate) ? ' svg-icon--active' : ''; ?>">
													<polygon points="7.5 .8 9.7 5.4 14.5 5.9 10.7 9.1 11.8 14.2 7.5 11.6 3.2 14.2 4.3 9.1 .5 5.9 5.3 5.4" 
															stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"></polygon>
												</svg>
											<?php endfor; ?>
										</div>

										<div class="product-rating__time">
											<?php echo date('Y-m-d H:i', $comment->created); ?>
										</div>

										<div class="product-rating__content">
											<?php echo nl2br(htmlspecialchars($comment->comment_content)); ?>
										</div>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<p>Sản phẩm chưa có bình luận nào.</p>
				<?php endif; ?>
			</div>
		</div>

<!-- Phần tổng hợp đánh giá bằng AI -->
<div class="panel panel-info" style="margin-bottom: 15px">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="glyphicon glyphicon-stats"></i> Tổng hợp đánh giá bằng AI</h3>
			</div>
			<div class="panel-body">
				<div id="ai-summary-container" style="display: none;">
						<div id="ai-summary-loading" style="display: none;" class="text-center">
							<p><i class="glyphicon glyphicon-refresh" style="animation: spin 2s linear infinite;"></i> Đang tổng hợp đánh giá...</p>
							<p class="text-muted">Quá trình này có thể mất vài giây, vui lòng đợi...</p>
						</div>
						<div id="ai-summary-content" style="display: none;" class="ai-summary-container">
							<div class="ai-summary-header">
								<i class="glyphicon glyphicon-check"></i>
								<h4>Phân tích đánh giá từ khách hàng</h4>
							</div>
							<div class="ai-summary-content">
								<!-- Nội dung tổng hợp sẽ được hiển thị ở đây -->
							</div>
						</div>
						<div id="ai-summary-error" style="display: none;" class="alert alert-danger">
							<i class="glyphicon glyphicon-exclamation-sign"></i> Không thể tổng hợp đánh giá. <span id="ai-summary-error-message"></span>
						</div>
						<div id="ai-summary-empty" style="display: none;" class="alert alert-info">
							<i class="glyphicon glyphicon-info-sign"></i> Chưa đủ đánh giá để tổng hợp. Hãy là người đầu tiên đánh giá sản phẩm này!
						</div>
				</div>
				<div class="text-center">
						<button id="generate-ai-summary" class="btn btn-primary">
							<i class="glyphicon glyphicon-flash"></i> Tổng hợp đánh giá bằng AI
						</button>
				</div>
			</div>
		</div>

		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title">Sản phẩm liên quan</h3>
			</div>
			<div class="panel-body">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
					<?php foreach ($productsub as $value) {
						$name = covert_vi_to_en($value->name);
						$name = strtolower($name);
					?>
						<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 re-padding">
							<div class="product_item">
								<p class="product_name"><a href="<?php echo base_url($name . '-p' . $value->id); ?>"><?php echo $value->name; ?></a></p>
								<div class="product-image">
									<a href="<?php echo base_url($name . '-p' . $value->id); ?>"><img src="<?php echo base_url(); ?>upload/product/<?php echo $value->image_link; ?>" alt="" class=""></a>
								</div>
								<?php if ($value->discount > 0 || $value->price < $value->origin_price) {
									$new_price = $value->price - $value->discount; ?>
									<p><span class='price text-right'><?php echo number_format($new_price); ?> VNĐ</span> <del class="product-discount"><?php echo number_format($value->origin_price); ?> VNĐ</del></p>
								<?php } else { ?>
									<p><span class='price text-right'><?php echo number_format($value->origin_price); ?> VNĐ</span></p>
								<?php	} ?>
								<p><span class="glyphicon glyphicon-eye-open" aria-hidden="true" title="Số lượt xem"></span> <?php echo $value->view; ?> <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true" title="Số lượng đặt mua"><?php echo $value->buyed; ?></p>
								<a href="<?php echo base_url('cart/add/' . $value->id); ?>"><button class='btn btn-info'><span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Thêm giỏ hàng</button></a>
							</div>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
		<div class="panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title">Có thể bạn thích</h3>
			</div>
			<div class="panel-body">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
					<?php foreach ($productview as $value) {
						$name = covert_vi_to_en($value->name);
						$name = strtolower($name);
					?>
						<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 re-padding">
							<div class="product_item">
								<p class="product_name"><a href="<?php echo base_url($name . '-p' . $value->id); ?>"><?php echo $value->name; ?></a></p>
								<div class="product-image">
									<a href="<?php echo base_url($name . '-p' . $value->id); ?>"><img src="<?php echo base_url(); ?>upload/product/<?php echo $value->image_link; ?>" alt="" class=""></a>
								</div>
								<?php if ($value->discount > 0 || $value->price < $value->origin_price) {
									$new_price = $value->price - $value->discount; ?>
									<p><span class='price text-right'><?php echo number_format($new_price); ?> VNĐ</span> <del class="product-discount"><?php echo number_format($value->price); ?> VNĐ</del></p>
								<?php } else { ?>
									<p><span class='price text-right'><?php echo number_format($value->origin_price); ?> VNĐ</span></p>
								<?php	} ?>
								<p><span class="glyphicon glyphicon-eye-open" aria-hidden="true" title="Số lượt xem"></span> <?php echo $value->view; ?> <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true" title="Số lượng đặt mua"><?php echo $value->buyed; ?></p>
								<a href="<?php echo base_url('cart/add/' . $value->id); ?>"><button class='btn btn-info'><span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span> Thêm giỏ hàng</button></a>
							</div>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>
</div>


<script>
    $(document).ready(function () {

        // Xử lý nút tổng hợp đánh giá bằng AI
			$('#generate-ai-summary').on('click', function() {
				const productId = <?php echo $product->id; ?>;
				
				// Hiển thị container tổng hợp
				$('#ai-summary-container').show();
				
				// Hiển thị trạng thái đang tải
				$('#ai-summary-loading').show();
				$('#ai-summary-content').hide();
				$('#ai-summary-error').hide();
				$('#ai-summary-empty').hide();
				$(this).prop('disabled', true);
				
				// Gọi API để lấy tổng hợp đánh giá
				$.ajax({
					url: '<?php echo base_url('product/get_review_summary'); ?>',
					type: 'POST',
					data: {
							product_id: productId
					},
					dataType: 'json',
					success: function(response) {
							$('#ai-summary-loading').hide();
							$('#generate-ai-summary').prop('disabled', false);
							
							if (response.success) {
								// Hiển thị kết quả tổng hợp
								$('.ai-summary-content').html(formatSummary(response.summary));
								$('#ai-summary-content').show();
								// Ẩn nút sau khi đã tổng hợp thành công
								$('#generate-ai-summary').hide();
							} else {
								// Hiển thị thông báo lỗi hoặc không có đánh giá
								if (response.message.includes('chưa có bình luận')) {
									$('#ai-summary-empty').show();
								} else {
									$('#ai-summary-error-message').text(response.message);
									$('#ai-summary-error').show();
								}
								$('#generate-ai-summary').prop('disabled', false);
							}
					},
					error: function(xhr, status, error) {
							$('#ai-summary-loading').hide();
							$('#ai-summary-error-message').text('Đã xảy ra lỗi khi kết nối đến máy chủ.');
							$('#ai-summary-error').show();
							$('#generate-ai-summary').prop('disabled', false);
							console.error('Error:', error);
							console.error('Response:', xhr.responseText);
					}
				});
			});
        
        // Hàm định dạng nội dung tổng hợp để hiển thị đẹp hơn
        function formatSummary(summary) {
            // Xử lý các tiêu đề và phần nội dung
            let formattedSummary = summary;
            
            // Định dạng các tiêu đề thường gặp trong bản tổng hợp
            const headings = [
                'Tóm tắt', 'Điểm mạnh', 'Điểm yếu', 'Đánh giá tổng thể', 
                'Đề xuất', 'Kết luận', 'Ưu điểm', 'Nhược điểm', 'Đề xuất cải tiến'
            ];
            
            // Thay thế các tiêu đề bằng HTML có định dạng
            headings.forEach(heading => {
                const regex = new RegExp(`(^|\\n)(${heading}:)`, 'g');
                formattedSummary = formattedSummary.replace(regex, '$1<div class="ai-summary-section"><h4>$2</h4>');
            });
            
            // Thêm thẻ đóng cho các phần
            formattedSummary = formattedSummary.replace(/\n(\d+\.|\*|\-)/g, '</div>\n$1');
            
            // Xử lý danh sách (dòng bắt đầu bằng số, dấu gạch ngang hoặc dấu sao)
            formattedSummary = formattedSummary.replace(/(^|\n)(\d+\.|\*|\-)(.*)/g, '$1<li>$3</li>');
            
            // Thay thế xuống dòng còn lại bằng thẻ <br>
            formattedSummary = formattedSummary.replace(/\n\n/g, '<br><br>').replace(/\n/g, '<br>');
            
            // Đảm bảo tất cả các phần đều được đóng đúng
            if (formattedSummary.includes('<div class="ai-summary-section">') && 
                !formattedSummary.endsWith('</div>')) {
                formattedSummary += '</div>';
            }
            
            return formattedSummary;
        }
    });
</script>
