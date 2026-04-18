<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"> </script>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php $user = $this->data['user_info']; ?>
<style>
    h2 {
        text-align: center;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        display: flex;
        justify-content: space-between;
        width: 90%;
        margin-right: 10px;
    }

    .info-label .label {
        font-weight: bold;
        flex: 0 0 150px;
        /* Cố định chiều rộng cho phần nhãn */
    }

    .info-label .content {
        flex: 1;
        overflow: hidden;
        /* ngăn nội dung tràn nếu cần */
        /* Phần nội dung chiếm phần còn lại */
    }

    .label {
        color: black;
        text-align: left;
        font-size: 100%;
    }

    .info-buttons {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 5px 10px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }

    .btn-edit {
        background-color: #4caf50;
        color: white;
    }

    .btn-delete {
        background-color: #f44336;
        color: white;
    }

    .btn-cancel {
        background-color: #f44336;
        color: white;
        display: none;
    }
    .btn-danger, .btn-success {
        color: #fff !important;
    }

    input[type="text"] {
        padding: 5px;
    }

    input[type="password"] {
        padding: 5px;
    }

    .sm-container {
        width: 100%;
    }

    .info-row {
        padding-right: 10px;

    }

    .btn-edit {
        width: 32px;
        height: 32px;
    }

    .fa-solid {
        font-size: smaller;
    }

    .changepassword {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        /* Màu xanh dương */
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        text-align: center;
        cursor: pointer;
        margin-top: 30px;
        transition: background-color 0.3s ease;
    }

    .address-edit {
        display: none;
    }

    .password {
        display: none;
    }

    .custom-toast {
        width: 90% !important;
        max-width: none !important;
        font-size: 1.1rem !important;
        padding: 1rem 1.5rem !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
    }

    .swal2-success-toast {
        background-color: #d4edda !important;
        color: #155724 !important;
        border: 1px solid #c3e6cb;
    }

    .swal2-error-toast {
        background-color: #f8d7da !important;
        color: #721c24 !important;
        border: 1px solid #f5c6cb;
    }

    /* Loại bỏ conflict tailwind */
    .collapse {
        visibility: unset !important;
    }

    a {
        color: #337ab7 !important;
        text-decoration: none !important;
    }

    a:hover {
        text-decoration: none !important;
    }

    .navbar-info .navbar-nav>.active>a,
    .navbar-info .navbar-nav>.active>a:hover,
    .navbar-info .navbar-nav>.active>a:focus {
        color: #fff !important;
        background-color: #4c66a4 !important;
    }

    .navbar-info .navbar-nav>li>a:hover,
    .navbar-info .navbar-nav>li>a:focus {
        color: #fff !important;
        background-color: #337ab7 !important;
        border-top-left-radius: 4px !important;
        border-top-right-radius: 4px !important;
    }

    a.product_title:hover {
        color: #337ab7 !important;
    }

    .dropdown-menu>li>a {
        color: #333 !important;
    }

    @media (min-width: 1200px) {
        .container {
            width: 1170px !important;
        }
    }

    /* Loại bỏ conflict tailwind */
</style>
<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 clearpaddingr">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 clearpadding">
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>#"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Trang chủ</a></li>
            <li class="active">Thông tin tài khoản</li>
        </ol>
        <div class="col-md-6 sm-container clearpadding">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Thông tin tài khoản</h3>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <div class="label">Họ Tên:</div>
                        <div class="content" id="name"><?php echo empty($user->name) ? '(Trống)' : $user->name; ?></div>
                    </div>
                    <div class="info-buttons">
                        <button class="btn btn-delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <button
                            class="btn btn-edit"
                            onclick="editContent(this, 'name')">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <div class="label">Số Điện Thoại:</div>
                        <div class="content" id="phone"><?php echo empty($user->phone) ? '(Trống)' : $user->phone; ?></div>
                    </div>
                    <div class="info-buttons">
                        <button class="btn btn-delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <button
                            class="btn btn-edit"
                            onclick="editContent(this, 'phone')">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <div class="label">Email:</div>
                        <div class="content" id="email"><?php echo empty($user->email) ? '(Trống)' : $user->email; ?></div>
                    </div>
                    <div class="info-buttons">
                        <button class="btn btn-cancel">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <button
                            class="btn btn-edit"
                            onclick="editContent(this, 'email')">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </div>
                <div class="info-row password">
                    <div class="info-label">
                        <div class="label">Mật khẩu:</div>
                        <div class="content" id="password">
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <div class="label">Địa Chỉ:</div>
                        <div class="content" id="address">
                            <?php
                            // Tạo một mảng chứa tất cả các giá trị
                            $values = [
                                $user->address,
                                $user->ward,
                                $user->district,
                                $user->city
                            ];
                            // Loại bỏ các giá trị trống
                            $filteredValues = array_filter($values, function ($value) {
                                return !empty(trim($value)); // Kiểm tra nếu phần tử không rỗng
                            });

                            // Kết hợp lại với dấu phẩy
                            $cleanAddress = empty($filteredValues) ? '(Trống)' : implode(", ", $filteredValues);

                            // Hiển thị kết quả
                            echo $cleanAddress;
                            ?>
                        </div>
                    </div>
                    <div class="info-buttons">
                        <button class="btn btn-cancel">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <button
                            class="btn btn-edit"
                            onclick="editContent(this, 'address-number')">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>
                </div>
                <div class="info-row address-number address-edit">
                    <div class="info-label">
                        <div class="label">Số nhà:</div>
                        <div class="content" id="address-number">
                        </div>
                    </div>
                </div>
                <div class="info-row address-info address-edit">
                    <div style="margin-left: 10px;">
                        <label class="block text-gray-700 mb-4" for="city">Tỉnh/Thành phố</label>
                        <select id="city" autocomplete="new-city" data-default='<?php echo $user->city ?>' name="city" style="font-size:85% !important; width: 200px;" class="w-full p-2 border border-gray-300 rounded">
                            <option value="" selected>Trống</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-4" for="district">Quận/Huyện</label>
                        <select id="district" autocomplete="new-district" data-default='<?php echo $user->district ?>' name="district" style="font-size:85% !important; width: 200px;" class="w-full p-2 border border-gray-300 rounded">
                            <option value="" selected>Trống</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-4" for="district">Phường/Xã</label>
                        <select id="ward" autocomplete="new-ward" data-default='<?php echo $user->ward ?>' name="ward" style="font-size:85% !important; width: 200px;" class="w-full p-2 border border-gray-300 rounded">
                            <option value="" selected>Trống</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: center;">
            <button class="changepassword" id="changepassword">Đổi mật khẩu</button>
        </div>
    </div>
</div>
<script src="<?php echo public_url('site/'); ?>js/info.js"></script>
