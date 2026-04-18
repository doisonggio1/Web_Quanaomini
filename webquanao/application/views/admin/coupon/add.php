<div class="row">
	<ol class="breadcrumb">
		<li><a href="#"><svg class="glyph stroked home">
					<use xlink:href="#stroked-home"></use>
				</svg></a></li>
		<li class="active">Mã giảm giá</li>
	</ol>
</div><!--/.row-->

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-info">
			<div class="panel-heading">
				Thêm mã giảm giá
			</div>
			<div class="panel-body">
				<form class="form-horizontal" name="" method="post" enctype="multipart/form-data">
					<!-- Chọn danh mục -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Danh mục</label>
						<div class="col-sm-5">
							<select name='catalog_id' class="form-control">
								<option value=''>Áp dụng tất cả</option>
								<?php foreach ($catalog as $value) { ?>
									<option value="<?php echo $value->id; ?>" <?php echo set_select('catalog_id', $value->id); ?>><?php echo $value->name; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('catalog_id'); ?>
						</div>
					</div>
					<!-- Chọn loại mã -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Loại mã</label>
						<div class="col-sm-5">
							<label class="radio-inline">
								<input type="radio" name="type_coupon" value="0" <?php echo set_radio('type_coupon', '0', true); ?>> Voucher
							</label>
							<label class="radio-inline">
								<input type="radio" name="type_coupon" value="1" <?php echo set_radio('type_coupon', '1'); ?>> Giftcode
							</label>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('type_coupon'); ?>
						</div>
					</div>
					<!-- Chọn loại quyền sử dụng -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Quyền sử dụng</label>
						<div class="col-sm-5">
							<label class="radio-inline">
								<input type="radio" name="privilege" value="0" <?php echo set_radio('privilege', '0', true); ?>> Riêng tư
							</label>
							<label class="radio-inline">
								<input type="radio" name="privilege" value="1" <?php echo set_radio('privilege', '1'); ?>> Công khai
							</label>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('privilege'); ?>
						</div>
					</div>
					<!-- Nhập mã giảm giá -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Mã giảm giá</label>
						<div class="col-sm-5">
							<input type="text" name='code' class="form-control" id="inputEmail3" placeholder="" value="<?php echo set_value('code'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('code'); ?>
						</div>
					</div>
					<!-- Mô tả -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Mô tả</label>
						<div class="col-sm-5">
							<textarea name='description' class="form-control" id="inputEmail3" placeholder="Nhập mô tả"><?php echo set_value('description'); ?></textarea>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('description'); ?>
						</div>
					</div>
					<!-- Chọn loại ưu đãi -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Loại ưu đãi</label>
						<div class="col-sm-5">
							<label class="radio-inline">
								<input type="radio" name="type" value="0" <?php echo set_radio('type', '0', true); ?>> Vận chuyển
							</label>
							<label class="radio-inline">
								<input type="radio" name="type" value="1" <?php echo set_radio('type', '1'); ?>> Đơn hàng
							</label>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('type'); ?>
						</div>
					</div>
					<!-- Giá trị giảm giá -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Giá trị</label>
						<div class="col-sm-5">
							<input type="text" name='value' class="form-control" id="inputEmail3" placeholder="Nhập giá trị giảm giá" value="<?php echo set_value('value'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('value'); ?>
						</div>
					</div>
					<!-- Giá trị tối thiểu áp dụng -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Giá tối thiểu áp dụng</label>
						<div class="col-sm-5">
							<input type="text" name='min_price' class="form-control" id="inputEmail3" placeholder="" value="<?php echo set_value('min_price'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('min_price'); ?>
						</div>
					</div>
					<!-- Giá trị tối đa -->
					<div class="form-group">
						<label for="maxValue" class="col-sm-2 control-label">Giá trị tối đa</label>
						<div class="col-sm-5">
							<input type="text" name='max_value' class="form-control" id="maxValue" placeholder="Nhập giá trị tối đa" value="<?php echo set_value('max_value'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('max_value'); ?>
						</div>
					</div>
					<!-- Số lượng mỗi người dùng -->
					<div class="form-group">
						<label for="usageLimit" class="col-sm-2 control-label">Số lượng mỗi người dùng</label>
						<div class="col-sm-5">
							<input type="text" name='usage_limit' class="form-control" id="usageLimit" placeholder="Nhập số lượng mỗi người dùng" value="<?php echo set_value('usage_limit'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('usage_limit'); ?>
						</div>
					</div>
					<!-- Số lượng mã -->
					<div class="form-group">
						<label for="totalQuantity" class="col-sm-2 control-label">Số lượng mã</label>
						<div class="col-sm-5">
							<input type="text" name='total_quantity' class="form-control" id="totalQuantity" placeholder="Nhập số lượng mã" value="<?php echo set_value('total_quantity'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('total_quantity'); ?>
						</div>
					</div>
					<!-- Ngày bắt đầu -->
					<div class="form-group">
						<label for="startDate" class="col-sm-2 control-label">Ngày bắt đầu</label>
						<div class="col-sm-5">
							<input type="datetime-local" name='start_date' class="form-control" id="startDate" step="1" value="<?php echo set_value('start_date'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('start_date'); ?>
						</div>
					</div>
					<!-- Ngày kết thúc -->
					<div class="form-group">
						<label for="endDate" class="col-sm-2 control-label">Ngày kết thúc</label>
						<div class="col-sm-5">
							<input type="datetime-local" name='end_date' class="form-control" id="endDate" step="1" value="<?php echo set_value('end_date'); ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('end_date'); ?>
						</div>
					</div>
					<!-- Nút thêm mới -->
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-5">
							<button type="submit" class="btn btn-primary">Thêm mới</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div><!--/.row-->