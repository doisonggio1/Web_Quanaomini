<div class="row" style="margin-top: 8px; height: 110px">
    <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 clearpadding">
        <a href="<?php echo base_url(); ?>">
            <img src="<?php echo base_url(); ?>upload/logo.png" alt="Logo" class="img-responsive" style="height: 100px;">
        </a>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 clearpadding text-center search-container">
        <div class="row">
            <!-- Form tìm kiếm bằng text -->
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
                <form action="<?php echo base_url('text-search'); ?>" method="POST" class="navbar-form">
                    <div class="input-group" style="width: 100%;">
                        <input type="text" name="key" class="form-control search-input" placeholder="Tìm kiếm sản phẩm..." required>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </form>
            </div>
            <!-- Nút tìm kiếm bằng hình ảnh -->
            <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                <span class="input-group-btn">
                    <label for="image-upload" class="btn btn-secondary" id="image-search-button" style="width: 100%; margin-top: 8px; margin-left: -20px;">
                        <span class="glyphicon glyphicon-camera"></span>
                    </label>
                    <input type="file" id="image-upload" name="image" accept="image/*" style="display: none;">
                </span>
            </div>
        </div>
    </div>
</div>
<div class="row">
        <div class="row">
            <nav class="navbar navbar-info re-navbar">
                <div class="container-fluid re-container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#">--- Menu ---</a>
                    </div>
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse re-navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <li class="active"><a href="<?php echo base_url(); ?>"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> HOME<span class="sr-only">(current)</span></a></li>

                            <li><a href="<?php echo base_url('moi'); ?>">Mới</a></li>
                            <li><a href="<?php echo base_url('ban-chay'); ?>">Bán chạy</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Thời trang<span class="caret"></span></a>
                                <ul class="dropdown-menu" id="re-dropdown-menu">
                                    <?php foreach ($catalog as $value) {
                                        $name = covert_vi_to_en($value->name);
                                        $name = strtolower($name);
                                    ?>
                                        <li><a style="color: #337ab7;padding: 10px 20px;" href="<?php echo base_url($name . '-c' . $value->id); ?>"><?php echo $value->name; ?></a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li><a href="<?php echo base_url('khuyen-mai'); ?>">Khuyến mại</a></li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li>
                                <a href="<?php echo base_url('shipment'); ?>">
                                    <span class="glyphicon glyphicon-list-alt"></span> Đơn Mua
                                </a>
                            </li>
                            <?php $this->load->view('site/cart/cart_sh'); ?>

                            <?php if (!isset($user)) { ?>
                                <li><a href="<?php echo base_url('dang-nhap'); ?>">Đăng nhập</a></li>
                            <?php } else { ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Xin chào: <?php echo $user->name; ?><span class="caret"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a href="<?php echo base_url('user'); ?>">Tài khoản</a></li>
                                        <li role="separator" class="divider"></li>
                                        <li><a href="<?php echo base_url('user/logout'); ?>">Đăng xuất</a></li>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </div><!-- /.navbar-collapse -->

                </div><!-- /.container-fluid -->
            </nav>
        </div>

        <script>
            var imageUploadElement = document.getElementById('image-upload');
            if (imageUploadElement) {
                imageUploadElement.addEventListener('change', function() {
                    // Thay đổi giao diện nút thành dấu ba chấm nhấp nháy
                    var searchButton = document.getElementById('image-search-button');
                    searchButton.innerHTML = '<div class="loading-dots"><span>.</span><span>.</span><span>.</span></div>';

                    var formData = new FormData();
                    formData.append('image', this.files[0]);

                    fetch('<?php echo base_url("image-search"); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Khôi phục lại giao diện nút sau khi xử lý xong
                            searchButton.innerHTML = '<span class="glyphicon glyphicon-camera"></span>';

                            if (data.success) {
                                sessionStorage.setItem('product_list', JSON.stringify(data.product_list));
                                window.location.href = '<?php echo base_url("tim-kiem-ket-qua"); ?>';
                            } else {
                                alert(data.message || 'Tìm kiếm không thành công!');
                            }
                        })
                        .catch(error => {
                            // Khôi phục lại giao diện nút nếu có lỗi
                            searchButton.innerHTML = '<span class="glyphicon glyphicon-camera"></span>';

                            console.error('Lỗi:', error);
                            alert('Đã xảy ra lỗi trong quá trình tìm kiếm. Vui lòng thử lại sau!');
                        });
                });
            }
        </script>
</div>
