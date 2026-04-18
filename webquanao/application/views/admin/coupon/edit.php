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
				Chỉnh sửa thông tin mã giảm giá
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
									<option value="<?php echo $value->id; ?>" <?php echo ($value->id == $coupon->catalog_id) ? 'selected' : ''; ?>><?php echo $value->name; ?></option>
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
								<input type="radio" name="type_coupon" value="0" <?php echo ($coupon->type_coupon == 0) ? 'checked' : ''; ?>> Voucher
							</label>
							<label class="radio-inline">
								<input type="radio" name="type_coupon" value="1" <?php echo ($coupon->type_coupon == 1) ? 'checked' : ''; ?>> Giftcode
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
								<input type="radio" name="privilege" value="0" <?php echo ($coupon->privilege == 0) ? 'checked' : ''; ?>> Riêng tư
							</label>
							<label class="radio-inline">
								<input type="radio" name="privilege" value="1" <?php echo ($coupon->privilege == 1) ? 'checked' : ''; ?>> Công khai
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
							<input type="text" name='code' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $coupon->code; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('code'); ?>
						</div>
					</div>
					<!-- Mô tả -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Mô tả</label>
						<div class="col-sm-5">
							<textarea name='description' class="form-control" id="inputEmail3" placeholder="Nhập mô tả"><?php echo $coupon->description; ?></textarea>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('description'); ?>
						</div>
					</div>
					<!-- Loại ưu đãi -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Loại ưu đãi</label>
						<div class="col-sm-5">
							<label class="radio-inline">
								<input type="radio" name="type" value="0" <?php echo ($coupon->type == 0) ? 'checked' : ''; ?>> Vận chuyển
							</label>
							<label class="radio-inline">
								<input type="radio" name="type" value="1" <?php echo ($coupon->type == 1) ? 'checked' : ''; ?>> Đơn hàng
							</label>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('type'); ?>
						</div>
					</div>
					<!-- Giá trị -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Giá trị</label>
						<div class="col-sm-5">
							<input type="text" name='value' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $coupon->value; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('value'); ?>
						</div>
					</div>
					<!-- Giá trị tối thiểu áp dụng -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Giá trị tối thiểu áp dụng</label>
						<div class="col-sm-5">
							<input type="text" name='min_price' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $coupon->min_price; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('min_price'); ?>
						</div>
					</div>
					<!-- Giá trị tối đa của mã -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Giá trị tối đa</label>
						<div class="col-sm-5">
							<input type="text" name='max_value' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $coupon->max_value; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('max_value'); ?>
						</div>
					</div>
					<!-- Số lượng mỗi người dùng -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Số lượng mỗi người dùng</label>
						<div class="col-sm-5">
							<input type="text" name='usage_limit' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $coupon->usage_limit; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('usage_limit'); ?>
						</div>
					</div>
					<!-- Số lượng -->
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Tổng số lượng</label>
						<div class="col-sm-5">
							<input type="text" name='total_quantity' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $coupon->total_quantity; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('total_quantity'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="startDate" class="col-sm-2 control-label">Ngày bắt đầu</label>
						<div class="col-sm-5">
							<input type="datetime-local" name='start_date' class="form-control" id="startDate" step="1" value="<?php echo $coupon->start_date; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('start_date'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="endDate" class="col-sm-2 control-label">Ngày kết thúc</label>
						<div class="col-sm-5">
							<input type="datetime-local" name='end_date' class="form-control" id="endDate" step="1" value="<?php echo $coupon->end_date; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('end_date'); ?>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-5">
							<button type="submit" class="btn btn-primary">Lưu thay đổi</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div><!--/.row-->