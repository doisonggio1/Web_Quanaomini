<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaction extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('transaction_model');
		$this->load->model('order_model');
		$this->load->model('product_model');

	}
	public function index()
	{
		$message_success = $this->session->flashdata('message_success');
		$this->data['message_success'] = $message_success;

		$message_fail = $this->session->flashdata('message_fail');
		$this->data['message_fail'] = $message_fail;

		$sort = $this->input->get('sort') ? $this->input->get('sort') : 'created';
		$order = $this->input->get('order') ? $this->input->get('order') : 'desc';

		$total = $this->transaction_model->get_total();
		$this->data['total'] = $total;

		$this->load->library('pagination');
		$config = array();
		$base_url = admin_url('transaction/index');
		$per = 10;
		$uri = 4;
		$config = pagination($base_url, $total, $per, $uri);

		$this->pagination->initialize($config);

		$segment = isset($this->uri->segments['4']) ? $this->uri->segments['4'] : NULL;
		$segment = intval($segment);

		$input['limit'] = array($config['per_page'], $segment);
		$input['order'] = array($sort, $order);
		
		$transaction = $this->transaction_model->get_list($input);
		$this->data['transaction'] = $transaction;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['temp'] = 'admin/transaction/index';
		$this->load->view('admin/main', $this->data);
	}

	public function del()
	{
		$id = $this->uri->segment(4);
		$transaction = $this->transaction_model->get_info($id);
		
		if (empty($transaction)) {
			$this->session->set_flashdata('message_fail', 'Đơn đặt hàng không tồn tại');
			redirect(admin_url('transaction'));
		}
		$this->data['transaction'] = $transaction;
		if ($this->transaction_model->delete($id)) {
			$this->session->set_flashdata('message_success', 'Xóa đơn đặt hàng thành công');
		}else{
			$this->session->set_flashdata('message_fail', 'Xóa đơn đặt hàng thất bại');
		}
		redirect(admin_url('transaction'));
	}
	public function detail()
	{
		$id = $this->uri->segment(4);
		$transaction = $this->transaction_model->get_info($id);
		$this->data['transaction'] = $transaction;
		
		$input =array();
		$input['where'] = array('transaction_id' => $transaction->id);
		$info = $this->order_model->get_list($input);
		
		$list_product = array();
		foreach ($info as $key => $value) {
			$this->db->select('product.id as id,product.name as name,image_link, order.qty as qty, order.amount as price, order.id as order_id');
			$this->db->join('order','order.product_id = product.id');
			$where = array();
			$where =array('product.id' => $value->product_id);
			$list_product[] = $this->product_model->get_info_rule($where);
		}
		$this->data['list_product'] = $list_product;
		$this->data['temp']='admin/transaction/detail';
		$this->load->view('admin/main',$this->data);
	}
	public function deldetail()
	{
		$id = $this->uri->segment(4);
		$where = array();
		$where = array('id' => $id);
		if (!$this->order_model->check_exists($where)) {
			$this->session->set_flashdata('message_fail', 'Danh mục không tồn tại');
			redirect(admin_url('transaction'));
		}
		$order = $this->order_model->get_info($id);
		if ($this->order_model->delete($id)) {			
			$transaction = $this->transaction_model->get_info($order->transaction_id);
			$data=array();
			$data['amount'] = $transaction->amount - $order->amount;
			$this->transaction_model->update($transaction->id,$data);
			$this->session->set_flashdata('message_success', 'Xóa thành công');
		}else{
			$this->session->set_flashdata('message_fail', 'Xóa thất bại');
		}
		redirect(admin_url('transaction'));
	}
	public function accept()
	{
		$id = $this->uri->segment(4);
		$data['status'] = '1';
		$this->transaction_model->update($id,$data);
		$this->session->set_flashdata('message_success', 'Xác nhận đơn đặt hàng thành công');

		redirect(admin_url('transaction'));
	}
	public function deliver()
	{
		$id = $this->uri->segment(4);
		log_message('info', "Starting deliver process for transaction ID: {$id}");

		$transaction = $this->transaction_model->get_info($id);
		if (!$transaction) {
			log_message('error', "Deliver failed: Transaction {$id} not found.");
			$this->session->set_flashdata('message_fail', 'Đơn hàng không tồn tại');
			redirect(admin_url('transaction'));
			return;
		}
		
		if ($transaction->status != '1') {
			log_message('warn', "Deliver failed: Transaction {$id} status is {$transaction->status}, not 1.");
			$this->session->set_flashdata('message_fail', 'Chỉ có thể vận chuyển đơn hàng đã xác nhận (Trạng thái hiện tại: ' . $transaction->status . ')');
			redirect(admin_url('transaction'));
			return;
		}

		// $tracking_id = 'TRACK-' . date('YmdHis') . '-' . $id;
		
		$this->db->trans_start();
		
		$input = array();
		$input['where'] = array('transaction_id' => $id);
		$orders = $this->order_model->get_list($input);
		
		foreach ($orders as $value) {
			$product = $this->product_model->get_info($value->product_id);
			if ($product) {
				// Sửa lỗi nhỏ: nên cộng với số lượng đặt mua (qty) chứ không phải +1
                $qty_ordered = (int)$value->qty;
                $new_stock = $product->stock - $qty_ordered;
                $this->product_model->update($product->id, ['stock' => $new_stock]);
			}
		}

		$data = array();
		$data['status'] = '2';
		$update_success = $this->transaction_model->update($id, $data);
		
		$shipping_data = array(
			'transaction_id' => $transaction->id,
			'status' => 'processing',
			'created_at' => date('Y-m-d H:i:s'),
			'estimated_delivery' => date('Y-m-d', strtotime('+3 days')),
			'callback_url' => site_url('api/shipping/webhook')
		);
		
		$this->db->insert('shipping_tracking', $shipping_data);
		
		$this->db->trans_complete();

		if ($update_success && $this->db->trans_status() !== FALSE) {
			log_message('info', "Transaction {$id} status updated to 2 and tracking info saved successfully.");
			// $this->session->set_flashdata('message_success', 'Đơn hàng đã được chuyển cho đơn vị vận chuyển với mã theo dõi: ' . $tracking_id);
			$this->session->set_flashdata('message_success', 'Đơn hàng đã được chuyển cho đơn vị vận chuyển.');
		} else {
			log_message('error', "Failed to update transaction {$id} status in database.");
			$this->session->set_flashdata('message_fail', 'Không thể cập nhật trạng thái đơn hàng.');
		}
		
		redirect(admin_url('transaction'));
	}
	public function done()
	{
		$id = $this->uri->segment(4);
		
		$transaction = $this->transaction_model->get_info($id);
		if (!$transaction) {
			$this->session->set_flashdata('message_fail', 'Đơn hàng không tồn tại');
			redirect(admin_url('transaction'));
			return;
		}

		$this->db->trans_start();

		$data = array();
		$data['status'] = '3';
		$update_success = $this->transaction_model->update($id, $data);

        $tracking_record = $this->db->get_where('shipping_tracking', array('transaction_id' => $id))->row();
		if ($tracking_record) {
			$this->db->where('transaction_id', $id); // Use transaction_id
			$this->db->update('shipping_tracking', array(
				'status' => 'delivered',
				'updated_at' => date('Y-m-d H:i:s')
			));
		}

		$input = array();
		$input['where'] = array('transaction_id' => $id);
		$orders = $this->order_model->get_list($input);
		
		foreach ($orders as $value) {
			$product = $this->product_model->get_info($value->product_id);
			if ($product) {
				// Sửa lỗi nhỏ: nên cộng với số lượng đặt mua (qty) chứ không phải +1
                $qty_ordered = (int)$value->qty;
                $new_buyed = $product->buyed + $qty_ordered;
                $this->product_model->update($product->id, ['buyed' => $new_buyed]);
			}
		}

		$this->db->trans_complete();

		if ($update_success && $this->db->trans_status() !== FALSE) {
			$this->session->set_flashdata('message_success', 'Đơn hàng đã hoàn thành');
		} else {
			$this->session->set_flashdata('message_fail', 'Không thể cập nhật trạng thái đơn hàng');
		}

		redirect(admin_url('transaction'));
	}
}