<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('coupon_model');
		$this->load->model('catalog_model');
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->library('upload');
		$this->load->library('upload_library');
	}
	public function index()
	{
		$message_success = $this->session->flashdata('message_success');
		$this->data['message_success'] = $message_success;

		$message_fail = $this->session->flashdata('message_fail');
		$this->data['message_fail'] = $message_fail;

		$input = array();
		$input['order'] = array('id' , 'ASC');
		$coupon = $this->coupon_model->get_coupons_with_catalog($input);
		$this->data['coupon']= $coupon;

		$catalog = $this->catalog_model->get_catalogs_with_parent();
		$this->data['catalog'] = $catalog;

		$this->data['temp']='admin/coupon/index.php';
		$this->load->view('admin/main',$this->data);
	}
	public function add()
	{
		$catalog = $this->catalog_model->get_catalogs_with_parent();
		$this->data['catalog'] = $catalog;

		$this->form_validation->set_error_delimiters('<div class="alert alert-danger" role="alert" style="padding:5px;border-bottom:0px;">', '</div>');

		if ($this->input->post()) {
			$this->form_validation->set_rules('value', 'Giá trị giảm giá', 'required|numeric');
			$this->form_validation->set_rules('start_date', 'Ngày bắt đầu', 'required');
			$this->form_validation->set_rules('end_date', 'Ngày kết thúc', 'required');
			if ($this->form_validation->run()) {
				$catalog_id = $this->input->post('catalog_id');
				$min_price = $this->input->post('min_price');
				$max_value = $this->input->post('max_value');
				$usage_limit = $this->input->post('usage_limit');
				$total_quantity = $this->input->post('total_quantity');

				$data = array(
					'catalog_id' => !empty($catalog_id) ? $catalog_id : null,
					'type_coupon' => $this->input->post('type_coupon'),
					'privilege' => $this->input->post('privilege'),
					'code' => $this->input->post('code'),
					'description' => $this->input->post('description'),
					'type' => $this->input->post('type'),
					'value' => $this->input->post('value'),
					'min_price' => !empty($min_price) ? $min_price : 0,
					'max_value' => !empty($max_value) ? $max_value : null,
					'usage_limit' => !empty($usage_limit) ? $usage_limit : null,
					'total_quantity' => !empty($total_quantity) ? $total_quantity : null,
					'start_date' => $this->input->post('start_date'),
					'end_date' => $this->input->post('end_date'),
					'status' => 1,
				);

				if ($this->coupon_model->create($data)) {
					$this->session->set_flashdata('message_success', 'Thêm mã giảm giá thành công');
				}else{
					$this->session->set_flashdata('message_fail', 'Thêm mã giảm giá thất bại');
				}
				redirect(admin_url('coupon'));
			}
		}

		$this->data['temp']='admin/coupon/add';
		$this->load->view('admin/main',$this->data);
	}

	public function edit()
	{
		$id = $this->uri->segment(4);
		$coupon = $this->coupon_model->get_info($id);
		if (empty($coupon)) {
			$this->session->set_flashdata('message_fail', 'Mã giảm giá không tồn tại');
			redirect(admin_url('coupon'));
		}
		$this->data['coupon'] = $coupon; 

		$catalog = $this->catalog_model->get_catalogs_with_parent();
		$this->data['catalog'] = $catalog;

		if ($this->input->post()) {
			$this->form_validation->set_rules('value', 'Giá trị giảm giá', 'required|numeric');
			$this->form_validation->set_rules('start_date', 'Ngày bắt đầu', 'required');
			$this->form_validation->set_rules('end_date', 'Ngày kết thúc', 'required');
			if ($this->form_validation->run()) {
				$catalog_id = $this->input->post('catalog_id');
				$min_price = $this->input->post('min_price');
				$max_value = $this->input->post('max_value');
				$usage_limit = $this->input->post('usage_limit');
				$total_quantity = $this->input->post('total_quantity');
		
				$data = array(
					'catalog_id' => !empty($catalog_id) ? $catalog_id : null,
					'type_coupon' => $this->input->post('type_coupon'),
					'privilege' => $this->input->post('privilege'),
					'code' => $this->input->post('code'),
					'description' => $this->input->post('description'),
					'type' => $this->input->post('type'),
					'value' => $this->input->post('value'),
					'min_price' => !empty($min_price) ? $min_price : 0,
					'max_value' => !empty($max_value) ? $max_value : null,
					'usage_limit' => !empty($usage_limit) ? $usage_limit : null,
					'total_quantity' => !empty($total_quantity) ? $total_quantity : null,
					'start_date' => $this->input->post('start_date'),
					'end_date' => $this->input->post('end_date'),
				);

				if ($this->coupon_model->update($id, $data)) {
					$this->session->set_flashdata('message_success', 'Thay đổi mã giảm giá thành công');
				} else {
					$this->session->set_flashdata('message_fail', 'Thay đổi mã giảm giá thất bại');
				}
				redirect(admin_url('coupon'));
			}
		}

		$this->data['temp']='admin/coupon/edit';
		$this->load->view('admin/main',$this->data);
	}
	public function del()
	{
		$id = $this->uri->segment(4);
		$coupon = $this->coupon_model->get_info($id);
		
		if (empty($coupon)) {
			$this->session->set_flashdata('message_fail', 'Mã giảm giá không tồn tại');
			redirect(admin_url('coupon'));
		}
		$this->data['coupon'] = $coupon;
		if ($this->coupon_model->delete($id)) {
			$this->session->set_flashdata('message_success', 'Xóa mã giảm giá thành công');
		}else{
			$this->session->set_flashdata('message_fail', 'Xóa mã giảm giá thất bại');
		}
		redirect(admin_url('coupon'));
	}
	public function status()
	{
		$id = $this->uri->segment(4);
		$coupon = $this->coupon_model->get_info($id);
		if (empty($coupon)) {
			$this->session->set_flashdata('message_fail', 'Mã giảm giá không tồn tại');
			redirect(admin_url('coupon'));
		}
		if ($coupon->status == 1) {
			$data = array('status' => 0);
		} else {
			$data = array('status' => 1);
		}
		if ($this->coupon_model->update($id, $data)) {
			$this->session->set_flashdata('message_success', 'Thay đổi trạng thái thành công');
		} else {
			$this->session->set_flashdata('message_fail', 'Thay đổi trạng thái thất bại');
		}
		redirect(admin_url('coupon'));
	}
}
