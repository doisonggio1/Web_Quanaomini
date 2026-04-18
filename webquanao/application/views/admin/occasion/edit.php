<div class="row">
	<ol class="breadcrumb">
		<li><a href="#"><svg class="glyph stroked home">
					<use xlink:href="#stroked-home"></use>
				</svg></a></li>
		<li class="active">Sự kiện</li>
	</ol>
</div><!--/.row-->

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-info">
			<div class="panel-heading">
				Chỉnh sửa thông tin sự kiện
			</div>
			<div class="panel-body">
				<form class="form-horizontal" name="" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Tên sự kiện</label>
						<div class="col-sm-5">
							<input type="text" name='name' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $occasion->name; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('name'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Mô tả</label>
						<div class="col-sm-5">
							<textarea name='description' class="form-control" id="inputEmail3" placeholder="Nhập mô tả"><?php echo $occasion->description; ?></textarea>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('description'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="discountValue" class="col-sm-2 control-label">Giá trị giảm giá</label>
						<div class="col-sm-5">
							<input type="text" name="value" class="form-control" id="discountValue" placeholder="Nhập giá trị giảm giá" value="<?php echo $occasion->value; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('value'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="measure" class="col-sm-2 control-label">Loại giảm giá</label>
						<div class="col-sm-5">
							<label class="radio-inline">
								<input type="radio" name="measure" value="0" <?php echo ($occasion->measure == 0) ? 'checked' : ''; ?>> VNĐ
							</label>
							<label class="radio-inline">
								<input type="radio" name="measure" value="1" <?php echo ($occasion->measure == 1) ? 'checked' : ''; ?>> %
							</label>
						</div>
						<div class="col-sm-4">
							<?php echo form_error('measure'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-2 control-label">Giá tối thiểu áp dụng</label>
						<div class="col-sm-5">
							<input type="text" name='min_price' class="form-control" id="inputEmail3" placeholder="" value="<?php echo $occasion->min_price; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('min_price'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="startDate" class="col-sm-2 control-label">Ngày bắt đầu</label>
						<div class="col-sm-5">
							<input type="datetime-local" name='start_date' class="form-control" id="startDate" step="1" value="<?php echo $occasion->start_date; ?>">
						</div>
						<div class="col-sm-4">
							<?php echo form_error('start_date'); ?>
						</div>
					</div>
					<div class="form-group">
						<label for="endDate" class="col-sm-2 control-label">Ngày kết thúc</label>
						<div class="col-sm-5">
							<input type="datetime-local" name='end_date' class="form-control" id="endDate" step="1" value="<?php echo $occasion->end_date; ?>">
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