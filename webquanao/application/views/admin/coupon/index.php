<div class="row">
	<ol class="breadcrumb">
		<li><a href="#"><svg class="glyph stroked home"><use xlink:href="#stroked-home"></use></svg></a></li>
		<li class="active">Mã giảm giá</li>
	</ol>
</div><!--/.row-->

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-info">
			<div class="panel-heading">
			<div class="col-md-8">Quản lý mã giảm giá</div>
			<div class="col-md-4 text-right"><a href="<?php echo admin_url('coupon/add'); ?>" class='btn btn-info'><svg class="glyph stroked plus sign"><use xlink:href="#stroked-plus-sign"/></svg> Thêm mã giảm giá</a></div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr class="info">	
								<th class="text-center">ID</th>	
								<th>Danh mục</th>			
								<th>Loại mã</th>
								<!-- <th>Quyền sử dụng</th> -->
								<th>Code</th>
								<!-- <th>Mô tả</th> -->
								<th>Loại ưu đãi</th>
								<th>Giá trị</th>
								<th>Giá tối thiểu áp dụng</th>
								<th>Giá trị tối đa</th>
								<th>Số lượng mỗi người dùng</th>
								<!-- <th>Tổng số lượng</th> -->
								<th>Ngày bắt đầu</th>
								<th>Ngày kết thúc</th>
								<th>Hành động</th>
								<th class="text-center">Trạng thái</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($coupon as $value) { ?>
								<tr>
									<td style="vertical-align: middle;text-align: center;"><strong><?php echo $value->id; ?></strong></td>
									<td>
										<strong>
										<?php 
											if ($value->catalog_id == null) {
												echo '';
											} elseif ($value->catalog_id != null) {
												echo $value->catalog_name;
											}
										?>
										</strong>
									</td>
									<td>
										<?php 
											if ($value->type_coupon == 0) {
												echo 'Voucher';
											} elseif ($value->type_coupon == 1) {
												echo 'Giftcode';
											}
										?>
									</td>
									<!-- <td>
										<?php 
											// if ($value->privilege == 0) {
											// 	echo 'Riêng tư';
											// } elseif ($value->privilege == 1) {
											// 	echo 'Công khai';
											// }
										?>
									</td> -->
									<td><?php echo $value->code; ?></td>
									<!-- <td><?php echo $value->description; ?></td> -->
									<td>
										<?php 
											if ($value->type == 0) {
												echo 'Vận chuyển';
											} elseif ($value->type == 1) {
												echo 'Đơn hàng';
											}
										?>
									</td>
									<td>
										<?php 
											if ($value->value > 100) {
												echo number_format($value->value) . ' VNĐ';
											} elseif ($value->value <= 100) {
												echo $value->value . '%';
											}
										?>
									</td>

									<td>
										<?php 
											if ($value->min_price != null) {
												echo number_format($value->min_price) . ' VNĐ';
											} elseif ($value->min_price == null) {
												echo '0 VNĐ';
											}
										?>
									</td>
									<td>
										<?php 
											if ($value->max_value != null) {
												echo number_format($value->max_value) . ' VNĐ';
											} elseif ($value->max_value == null) {
												echo 'Không giới hạn';
											}
										?>
									</td>
									<td>
										<?php 
											if ($value->usage_limit != null) {
												echo $value->usage_limit . ' lần';
											} elseif ($value->usage_limit == null) {
												echo 'Không giới hạn';
											}
										?>
									</td>
									<!-- <td>
										<?php 
											// if ($value->total_quantity != null) {
											// 	echo $value->total_quantity . ' lần';
											// } elseif ($value->total_quantity == null) {
											// 	echo 'Không giới hạn';
											// }
										?>
									</td> -->
									<td><?php echo $value->start_date; ?></td>
									<td><?php echo $value->end_date; ?></td>
									<td class="list_td aligncenter">
							            <a href="../admin/coupon/edit/<?php echo $value->id; ?>" title="Sửa"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;&nbsp;&nbsp;
							            <a href="../admin/coupon/del/<?php echo $value->id; ?>" title="Xóa"> <span class="glyphicon glyphicon-remove" onclick=" return confirm('Bạn chắc chắn muốn xóa')"></span> </a>
								    </td>    
									<td style="vertical-align: middle;text-align: center;">
										<?php if ($value->status == 1) { ?>
											<a href="../admin/coupon/status/<?php echo $value->id; ?>" title="Đang kích hoạt"><span class="glyphicon glyphicon-ok"></span></a>
										<?php } else { ?>
											<a href="../admin/coupon/status/<?php echo $value->id; ?>" title="Chưa kích hoạt"><span class="glyphicon glyphicon-remove" style="color: red;"></span></a>
										<?php } ?>
									</td>
				                </tr>
							<?php } ?>
			    		</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div><!--/.row-->
