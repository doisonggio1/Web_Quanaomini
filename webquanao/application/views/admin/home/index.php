<style>
	.chart-legend {
		font-size: 14px;
		font-weight: 500;
	}

	.chart-legend span {
		display: inline-block;
		margin-right: 8px;
	}

	.stats-list {
		padding: 15px;
		background-color: #f9f9f9;
		border-radius: 8px;
		list-style: none;
		box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
		font-size: 15px;
		line-height: 1.6;
		margin-bottom: 20px;
	}

	.stats-list li {
		padding: 8px 12px;
		border-left: 4px solid #007bff;
		background-color: #fff;
		margin-bottom: 8px;
		border-radius: 4px;
	}

	.stats-list li strong {
		color: #333;
	}
</style>
<div class="row">
	<ol class="breadcrumb">
		<li><a href="#"><svg class="glyph stroked home">
					<use xlink:href="#stroked-home"></use>
				</svg></a></li>
		<li class="active">Quản trị</li>
	</ol>
</div><!--/.row-->

<div class="row">
	<div class="col-lg-12">
		<h1 class="page-header">Trang quản trị</h1>
	</div>
</div><!--/.row-->




<div class="row">
	<div class="col-xs-12 col-md-6 col-lg-3">
		<div class="panel panel-blue panel-widget ">
			<div class="row no-padding">
				<div class="col-sm-3 col-lg-5 widget-left">
					<svg class="glyph stroked bag">
						<use xlink:href="#stroked-bag"></use>
					</svg>
				</div>
				<div class="col-sm-9 col-lg-7 widget-right">
					<div class="large"><?php echo $total_order; ?></div>
					<div class="text-muted">Đơn hàng mới</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-6 col-lg-3">
		<div class="panel panel-orange panel-widget">
			<div class="row no-padding">
				<div class="col-sm-3 col-lg-5 widget-left">
					<svg class="glyph stroked empty-message">
						<use xlink:href="#stroked-empty-message"></use>
					</svg>
				</div>
				<div class="col-sm-9 col-lg-7 widget-right">
					<div class="large"><?php echo $total_comments; ?></div>
					<div class="text-muted">Bình luận</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-6 col-lg-3">
		<div class="panel panel-teal panel-widget">
			<div class="row no-padding">
				<div class="col-sm-3 col-lg-5 widget-left">
					<svg class="glyph stroked male-user">
						<use xlink:href="#stroked-male-user"></use>
					</svg>
				</div>
				<div class="col-sm-9 col-lg-7 widget-right">
					<div class="large"><?php echo $new_customers; ?></div>
					<div class="text-muted">Khách hàng mới</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-6 col-lg-3">
		<div class="panel panel-red panel-widget">
			<div class="row no-padding">
				<div class="col-sm-3 col-lg-5 widget-left">
					<svg class="glyph stroked app-window-with-content">
						<use xlink:href="#stroked-app-window-with-content"></use>
					</svg>
				</div>
				<div class="col-sm-9 col-lg-7 widget-right">
					<div class="large"><?php echo number_format($total_views); ?></div>
					<div class="text-muted">Lượt xem</div>
				</div>
			</div>
		</div>
	</div>
</div><!--/.row-->

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">Site Traffic Overview</div>
			<div class="panel-body">

				<!-- 🔼 Thêm thống kê 7 ngày qua -->
				<h3>📊 Thống kê lưu lượng truy cập trong 7 ngày qua:</h3>
				<ul class="stats-list">
					<li><strong>Người dùng đang hoạt động:</strong> <?= $data['activeUsers'] ?? 'N/A' ?></li>
					<li><strong>Người dùng mới:</strong> <?= $data['newUsers'] ?? 'N/A' ?></li>
					<li><strong>Số phiên truy cập:</strong> <?= $data['sessions'] ?? 'N/A' ?></li>
					<li><strong>Phiên có tương tác:</strong> <?= $data['engagedSessions'] ?? 'N/A' ?></li>
					<li><strong>Tỷ lệ thoát:</strong> <?= isset($data['bounceRate']) ? number_format($data['bounceRate'], 2) . '%' : 'N/A' ?></li>
					<li><strong>Thời lượng trung bình mỗi phiên:</strong> <?= isset($data['averageSessionDuration']) ? round($data['averageSessionDuration'], 2) . 's' : 'N/A' ?></li>
				</ul>

				<!-- 🔽 Biểu đồ + legend -->
				<div class="canvas-wrapper" style="margin-top: 20px;">
					<canvas class="main-chart" id="line-chart" height="200" width="600"></canvas>

					<!-- Legend -->
					<div id="chart-legend" class="chart-legend" style="margin-top: 10px;">
						<span style="color: rgba(220,220,220,1); font-size: 18px;">■</span> Tổng người dùng trong tháng &nbsp;&nbsp;
						<span style="color: rgba(48, 164, 255, 1); font-size: 18px;">■</span> Người dùng mới trong tháng
					</div>
				</div>

			</div>
		</div>
	</div>
</div><!--/.row-->

<div id="userStats"
	data-info='<?= json_encode([
					"totalUsers" => $dataMonth["totalUsers"] ?? 0,
					"newUsers" => $dataMonth["newUsers"] ?? 0
				]) ?>'>
</div>

<!-- Thống kê sản phẩm -->
<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-default">
			<div class="panel-heading">Thống kê sản phẩm</div>
			<div class="panel-body">
				<div class="row">
					<!-- 5 sản phẩm bán chạy nhất -->
					<div class="col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">5 sản phẩm bán chạy nhất</div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th>Tên sản phẩm</th>
												<th>Giá</th>
												<th>Lượt xem</th>
												<th>Đã bán</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($best_selling as $product): ?>
												<tr>
													<td><?php echo $product->name; ?></td>
													<td><?php echo number_format($product->price); ?>đ</td>
													<td><?php echo $product->view; ?></td>
													<td><?php echo $product->sold_count; ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

					<!-- 5 sản phẩm bán ít nhất -->
					<div class="col-md-6">
						<div class="panel panel-danger">
							<div class="panel-heading">5 sản phẩm bán ít nhất</div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th>Tên sản phẩm</th>
												<th>Giá</th>
												<th>Lượt xem</th>
												<th>Đã bán</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($worst_selling as $product): ?>
												<tr>
													<td><?php echo $product->name; ?></td>
													<td><?php echo number_format($product->price); ?>đ</td>
													<td><?php echo $product->view; ?></td>
													<td><?php echo $product->sold_count; ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<!-- 5 sản phẩm được đánh giá tốt nhất -->
					<div class="col-md-6">
						<div class="panel panel-success">
							<div class="panel-heading">5 sản phẩm được đánh giá tốt nhất</div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th>Tên sản phẩm</th>
												<th>Giá</th>
												<th>Lượt xem</th>
												<th>Đánh giá</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($best_rated as $product): ?>
												<tr>
													<td><?php echo $product->name; ?></td>
													<td><?php echo number_format($product->price); ?>đ</td>
													<td><?php echo $product->view; ?></td>
													<td><?php echo number_format($product->avg_rating, 1); ?>/5 (<?php echo $product->rating_count; ?> đánh giá)</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

					<!-- 5 sản phẩm được đánh giá kém nhất -->
					<div class="col-md-6">
						<div class="panel panel-warning">
							<div class="panel-heading">5 sản phẩm được đánh giá kém nhất</div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th>Tên sản phẩm</th>
												<th>Giá</th>
												<th>Lượt xem</th>
												<th>Đánh giá</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($worst_rated as $product): ?>
												<tr>
													<td><?php echo $product->name; ?></td>
													<td><?php echo number_format($product->price); ?>đ</td>
													<td><?php echo $product->view; ?></td>
													<td><?php echo number_format($product->avg_rating, 1); ?>/5 (<?php echo $product->rating_count; ?> đánh giá)</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<!-- 5 sản phẩm được cho vào giỏ hàng nhiều nhất -->
					<div class="col-md-6">
						<div class="panel panel-info">
							<div class="panel-heading">5 sản phẩm được cho vào giỏ hàng nhiều nhất</div>
							<div class="panel-body">
								<div class="table-responsive">
									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>
												<th>Tên sản phẩm</th>
												<th>Giá</th>
												<th>Lượt xem</th>
												<th>Số lần thêm vào giỏ</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($most_carted as $product): ?>
												<tr>
													<td><?php echo $product->name; ?></td>
													<td><?php echo number_format($product->price); ?>đ</td>
													<td><?php echo $product->view; ?></td>
													<td><?php echo $product->cart_count; ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!--/.row-->


<div class="row">
	<div class="col-xs-6 col-md-3">
		<div class="panel panel-default">
			<div class="panel-body easypiechart-panel">
				<h4>Đơn hàng mới</h4>
				<div class="easypiechart" id="easypiechart-blue" data-percent="<?php echo $order_percent; ?>"><span class="percent"><?php echo $order_percent; ?>%</span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-6 col-md-3">
		<div class="panel panel-default">
			<div class="panel-body easypiechart-panel">
				<h4>Bình luận mới</h4>
				<div class="easypiechart" id="easypiechart-orange" data-percent="<?php echo $comment_percent; ?>"><span class="percent"><?php echo $comment_percent; ?>%</span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-6 col-md-3">
		<div class="panel panel-default">
			<div class="panel-body easypiechart-panel">
				<h4>Người dùng mới</h4>
				<div class="easypiechart" id="easypiechart-teal" data-percent="<?php echo $user_percent; ?>"><span class="percent"><?php echo $user_percent; ?>%</span>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-6 col-md-3">
		<div class="panel panel-default">
			<div class="panel-body easypiechart-panel">
				<h4>Lượt truy cập mới</h4>
				<div class="easypiechart" id="easypiechart-red" data-percent="<?php echo $visitor_percent; ?>"><span class="percent"><?php echo $visitor_percent; ?>%</span>
				</div>
			</div>
		</div>
	</div>
</div><!--/.row-->

<!-- Nút Phân tích AI và kết quả (Đã tối ưu) -->
<div class="row">
	<div class="col-lg-12">
		<button id="btnAnalyzeAI-1" class="btn-analyze-ai btn btn-primary btn-lg" style="margin-bottom: 20px;">
			<i class="fa fa-bar-chart"></i> Phân tích số liệu kinh doanh
		</button>

		<!-- Ô hiển thị kết quả phân tích -->
		<div class="panel panel-default analysis-result-panel" style="display: none;">
			<div class="panel-heading">
				<h3 class="panel-title">Phân tích ngắn gọn số liệu kinh doanh</h3>
			</div>
			<div class="panel-body">
				<div class="loading-analysis" style="text-align: center; display: none;">
					<i class="fa fa-spinner fa-spin fa-3x"></i>
					<p>Đang phân tích dữ liệu...</p>
				</div>
				<div class="analysis-result markdown-content" style="white-space: normal;"></div>
			</div>
		</div>

		<!-- CSS cho định dạng Markdown (đã tối ưu) -->
		<style>
			.markdown-content h1,
			.markdown-content h2,
			.markdown-content h3 {
				margin-top: 15px;
				margin-bottom: 10px;
				font-weight: 600;
			}

			.markdown-content h1 {
				font-size: 20px;
			}

			.markdown-content h2 {
				font-size: 18px;
			}

			.markdown-content h3 {
				font-size: 16px;
			}

			.markdown-content ul,
			.markdown-content ol {
				margin-left: 15px;
				margin-bottom: 10px;
			}

			.markdown-content p {
				margin-bottom: 10px;
			}

			.markdown-content table {
				width: 100%;
				max-width: 100%;
				margin-bottom: 15px;
				border-collapse: collapse;
			}

			.markdown-content table th,
			.markdown-content table td {
				padding: 6px;
				line-height: 1.3;
				border: 1px solid #ddd;
			}

			.markdown-content table th {
				background-color: #f5f5f5;
			}
		</style>
	</div>
</div>