<?php
$message = $this->session->flashdata('message');
if ($message) {
    echo '<div class="alert alert-' . ($this->session->flashdata('message_type') ?: 'info') . '">' . $message . '</div>';
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="<?php echo admin_url(); ?>"><svg class="glyph stroked home"><use xlink:href="#stroked-home"></use></svg></a></li>
        <li class="active">Xác nhận giao hàng</li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="col-md-8">Danh sách đơn hàng đang vận chuyển</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr class="info">
                                <th class="text-center">STT</th>
                                <th>Mã đơn hàng</th>
                                <th>Tên khách hàng</th>
                                <th>Ngày đặt</th>
                                <th>Số ĐT</th>
                                <th>Địa chỉ</th>
                                <th>Tổng tiền</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stt = 0;
                            foreach ($delivering_orders as $order) { 
                                $stt++;
                            ?>
                                <tr>
                                    <td style="vertical-align: middle;text-align: center;"><strong><?php echo $stt; ?></strong></td>
                                    <td><strong>#<?php echo $order->id; ?></strong></td>
                                    <td><strong><?php echo $order->delivery_name; ?></strong></td>
                                    <td><strong><?php echo mdate('%H:%i:%s %d/%m/%Y', strtotime($order->created)); ?></strong></td>
                                    <td><strong><?php echo $order->delivery_phone; ?></strong></td>
                                    <td><strong><?php echo $order->delivery_address; ?></strong></td>
                                    <td><strong><?php echo number_format($order->amount); ?></strong> VNĐ</td>
                                    <td>
                                        <a href="<?php echo admin_url('shipping/confirm_delivery/'.$order->id); ?>" 
                                           class="btn btn-success btn-sm" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xác nhận đã giao hàng?');">
                                            <i class="fa fa-check"></i> Xác nhận đã giao
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if (empty($delivering_orders)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Không có đơn hàng nào đang vận chuyển</td> <!-- Adjusted colspan from 9 to 8 -->
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>