<div class="row">
	<ol class="breadcrumb">
		<li><a href="#"><svg class="glyph stroked home"><use xlink:href="#stroked-home"></use></svg></a></li>
		<li class="active">Sự kiện</li>
	</ol>
</div><!--/.row-->

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-info">
			<div class="panel-heading">
			<div class="col-md-8">Quản lý sự kiện</div>
			<div class="col-md-4 text-right"><a href="<?php echo admin_url('occasion/add'); ?>" class='btn btn-info'><svg class="glyph stroked plus sign"><use xlink:href="#stroked-plus-sign"/></svg> Thêm sự kiện</a></div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr class="info">	
								<th class="text-center">ID</th>				
								<th>Tên sự kiện</th>
								<th>Giá trị</th>
								<th>Giá tối thiểu áp dụng</th>
								<th>Ngày bắt đầu</th>
								<th>Ngày kết thúc</th>
								<th>Hành động</th>
								<th class="text-center">Trạng thái</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($occasion as $value) { ?>
								<tr>
									<td style="vertical-align: middle;text-align: center;"><strong><?php echo $value->id; ?></strong></td>
									<td><strong><?php echo $value->name; ?></strong></td>
									<td>
										<?php 
											if ($value->measure == 0) {
												echo number_format($value->value) . ' VNĐ';
											} elseif ($value->measure == 1) {
												echo $value->value . '%';
											}
										?>
									</td>
									<td><?php echo $value->min_price . ' VNĐ'; ?></td>
									<td><?php echo $value->start_date; ?></td>
									<td><?php echo $value->end_date; ?></td>
									<td class="list_td aligncenter">
							            <a href="../admin/occasion/edit/<?php echo $value->id; ?>" title="Sửa"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;&nbsp;&nbsp;
							            <a href="../admin/occasion/del/<?php echo $value->id; ?>" title="Xóa"> <span class="glyphicon glyphicon-remove" onclick=" return confirm('Bạn chắc chắn muốn xóa')"></span> </a>
								    </td>    
									<td style="vertical-align: middle;text-align: center;">
										<?php if ($value->status == 1) { ?>
											<a href="../admin/occasion/status/<?php echo $value->id; ?>" title="Đang kích hoạt"><span class="glyphicon glyphicon-ok"></span></a>
										<?php } else { ?>
											<a href="../admin/occasion/status/<?php echo $value->id; ?>" title="Chưa kích hoạt"><span class="glyphicon glyphicon-remove" style="color: red;"></span></a>
										<?php } ?>
									</td>
									<td>
										<button class="btn btn-primary btn-sm apply-event"
											data-id="<?php echo $value->id; ?>"
											data-name="<?php echo $value->name; ?>"
											data-value="<?php echo $value->value; ?>"
											data-measure="<?php echo $value->measure; ?>"
											data-min-price="<?php echo $value->min_price; ?>">
											Áp dụng
										</button>
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

<!-- Modal Áp dụng sự kiện -->
<div class="modal fade" id="applyEventModal" tabindex="-1" role="dialog" aria-labelledby="applyEventModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="applyEventModalLabel">Áp dụng sự kiện</h4>
            </div>
            <div class="modal-body">
                <form id="applyEventForm">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th class="text-center">ID</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá hiện tại</th>
								<th>Giá khuyến mãi</th>
                            </tr>
                        </thead>
                        <tbody id="productList">
							<?php foreach ($product as $value) { ?>
								<tr>
									<td style="vertical-align: middle"><input type="checkbox" class="product-checkbox" value="<?php echo $value->id; ?>" /></td>
									<td style="vertical-align: middle;text-align: center;"><strong><?php echo $value->id; ?></strong></td>
									<td>
										<img src="<?php echo base_url(); ?>upload/product/<?php echo $value->image_link; ?>" alt="" style="width: 50px;float:left;margin-right: 10px;">
										<strong><?php echo $value->name; ?></strong>
										<p style="font-size: 12px;margin-top: 4px;">View: <?php echo $value->view; ?> <span> | Đã bán: <?php echo $value->buyed; ?></span></p>
									</td>
									<td style="vertical-align: middle"><strong><?php echo $value->namecatalog; ?></strong></td>
									<td class="product-price" data-price="<?php echo $value->price; ?>" style="vertical-align: middle">
										<strong><?php echo number_format($value->price); ?> VNĐ</strong>
									</td>
									<td class="discount-price" style="vertical-align: middle">
									</td>
								</tr>
							<?php } ?>
						</tbody>
                    </table>
                    <button type="submit" class="btn btn-success" data-id="<?php echo $value->id; ?>">Áp dụng</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
	$(document).ready(function () {
		$(".apply-event").on("click", function () {
			const eventId = $(this).data("id");
			const eventName = $(this).data("name");
			const eventValue = $(this).data("value"); // Giá trị giảm giá
			const eventMeasure = $(this).data("measure"); // Loại giảm giá (0: VNĐ, 1: %)
			const eventMinPrice = $(this).data("min-price"); // Giá tối thiểu áp dụng

			$("#applyEventModalLabel").text("Áp dụng sự kiện: " + eventName);
			$("#applyEventForm").data("id", eventId);

			$("#productList tr").each(function () {
				const price = parseFloat($(this).find(".product-price").data("price")); // Giá hiện tại
				let discountPrice = price;

				if (price >= eventMinPrice) {
					if (eventMeasure == 0) {
						discountPrice = price - eventValue;
					} else if (eventMeasure == 1) {
						discountPrice = price - (price * (eventValue / 100));
					}
				}

				$(this).find(".discount-price").html(discountPrice > 0 
					? `<strong>${discountPrice.toLocaleString()} VNĐ</strong>` 
					: `<strong>Không áp dụng</strong>`);
							});

			$("#applyEventModal").modal("show");
		});

		$("#selectAll").on("change", function () {
			$(".product-checkbox").prop("checked", $(this).prop("checked"));
		});

		$("#applyEventForm").on("submit", function (e) {
			e.preventDefault();

			const selectedProducts = [];
			$(".product-checkbox:checked").each(function () {
				selectedProducts.push($(this).val());
			});

			if (selectedProducts.length === 0) {
				alert("Vui lòng chọn ít nhất một sản phẩm.");
				return;
			}

			const eventId = $(this).data("id");

			$.ajax({
				url: "<?php echo admin_url('product/apply_event'); ?>",
				type: "POST",
				data: { 
					product_ids: selectedProducts,
					event_id: eventId 
				},
				dataType: "json",
				success: function (response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.message);
					}
				},
				error: function () {
					alert("Đã xảy ra lỗi, vui lòng thử lại.");
				}
			});
		});
	});
</script>