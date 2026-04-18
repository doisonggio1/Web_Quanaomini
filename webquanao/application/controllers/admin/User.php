<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('order_model');
		$this->load->model('product_model');
		$this->load->model('transaction_model');
		$this->load->library('form_validation');
		$this->load->helper('form');
	}
	public function index()
	{
		$message_success = $this->session->flashdata('message_success');
		$this->data['message_success'] = $message_success;

		$message_fail = $this->session->flashdata('message_fail');
		$this->data['message_fail'] = $message_fail;

		$user = $this->user_model->get_list();
		$this->data['user']= $user;

		$this->data['temp']='admin/user/index.php';
		$this->load->view('admin/main',$this->data);
	}
	public function order()
	{
		$message_success = $this->session->flashdata('message_success');
		$this->data['message_success'] = $message_success;

		$message_fail = $this->session->flashdata('message_fail');
		$this->data['message_fail'] = $message_fail;

		

		$id = $this->uri->segment(4);
		$input['where'] = array('user_id' => $id);
		$order = $this->transaction_model->get_list($input);
		$this->data['order']= $order;

		$user = $this->user_model->get_info($id);
		$this->data['user']= $user;
		$this->data['temp']='admin/user/order';
		$this->load->view('admin/main',$this->data);
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
		$this->data['temp']='admin/user/detail';
		$this->load->view('admin/main',$this->data);
	}

	public function accept()
	{
		$id = $this->uri->segment(4);
		$data= array();
		$data['status'] = '1';
		$this->transaction_model->update($id,$data);
		$this->session->set_flashdata('message_success', 'Xác nhận đơn đặt hàng thành công');

		$input= array();
		$input['where']= array('transaction_id'=>$id);
		$order = $this->order_model->get_list($input);
		
		foreach ($order as $value) {
			$product = $this->product_model->get_info($value->product_id);
			
			$data= array();
			$data['buyed'] = $product->buyed + 1;
			$this->product_model->update($product->id,$data);
		}

		redirect(admin_url('user'));
	}
	public function del()
	{
		$id = $this->uri->segment(4);
		$where = array();
		$where = array('id' => $id);
		if (!$this->user_model->check_exists($where)) {
			$this->session->set_flashdata('message_fail', 'user không tồn tại');
			redirect(base_url("/dang-nhap"));
		}
		if ($this->user_model->delete($id)) {
			$this->session->set_flashdata('message_success', 'Xóa user thành công');
		}else{
			$this->session->set_flashdata('message_fail', 'Xóa user thất bại');
		}
		redirect(base_url("/dang-nhap"));
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
		if ($this->order_model->delete($id)) {
			$this->session->set_flashdata('message_success', 'Xóa thành công');
		}else{
			$this->session->set_flashdata('message_fail', 'Xóa thất bại');
		}
		redirect(admin_url('user'));
		
	}
}
