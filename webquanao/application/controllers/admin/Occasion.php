<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Occasion extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('discount_model');
		$this->load->model('catalog_model');
		$this->load->model('product_model');
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
		$occasion = $this->discount_model->get_list($input);
		$this->data['occasion']= $occasion;

		$product = $this->product_model->get_products_with_discount_catalog();
		$this->data['product']= $product;

		$this->data['temp']='admin/occasion/index.php';
		$this->load->view('admin/main',$this->data);
	}
	public function add()
	{
		$this->form_validation->set_error_delimiters('<div class="alert alert-danger" role="alert" style="padding:5px;border-bottom:0px;">', '</div>');

		if ($this->input->post()) {
			$this->form_validation->set_rules('name', 'Tên sự kiện', 'required');
			$this->form_validation->set_rules('value', 'Giá trị giảm giá', 'required|numeric');
			$this->form_validation->set_rules('measure', 'Loại giảm giá', 'required');
			$this->form_validation->set_rules('start_date', 'Ngày bắt đầu', 'required');
			$this->form_validation->set_rules('end_date', 'Ngày kết thúc', 'required');
			if ($this->form_validation->run()) {
				$data = array();
				$data = array(
					'name' => $this->input->post('name'),
					'description' => $this->input->post('description'),
					'measure' => $this->input->post('measure'),
					'value' => $this->input->post('value'),
					'min_price' => $this->input->post('min_price'),
					'start_date' => $this->input->post('start_date'),
					'end_date' => $this->input->post('end_date'),
					'status' => 0
					);
				if ($this->discount_model->create($data)) {
					$this->session->set_flashdata('message_success', 'Thêm sự kiện thành công');
				}else{
					$this->session->set_flashdata('message_fail', 'Thêm sự kiện thất bại');
				}
				redirect(admin_url('occasion'));
			}
		}

		$this->data['temp']='admin/occasion/add';
		$this->load->view('admin/main',$this->data);
	}
	public function edit()
	{
		$id = $this->uri->segment(4);
		$occasion = $this->discount_model->get_info($id);
		if (empty($occasion)) {
			$this->session->set_flashdata('message_fail', 'Sự kiện không tồn tại');
			redirect(admin_url('occasion'));
		}
		$this->data['occasion'] = $occasion; 
		if ($this->input->post()) {
			$this->form_validation->set_rules('name', 'Tên sự kiện', 'required');
			$this->form_validation->set_rules('value', 'Giá trị giảm giá', 'required|numeric');
			$this->form_validation->set_rules('measure', 'Loại giảm giá', 'required');
			$this->form_validation->set_rules('start_date', 'Ngày bắt đầu', 'required');
			$this->form_validation->set_rules('end_date', 'Ngày kết thúc', 'required');
			if ($this->form_validation->run()) {
				$data = array();
				$data = array(
					'name' => $this->input->post('name'),
					'description' => $this->input->post('description'),
					'measure' => $this->input->post('measure'),
					'value' => $this->input->post('value'),
					'min_price' => $this->input->post('min_price'),
					'start_date' => $this->input->post('start_date'),
					'end_date' => $this->input->post('end_date'),
					'status' => 0
					);
				if ($this->discount_model->update($id,$data)) {
					$this->session->set_flashdata('message_success', 'Thay đổi sự kiện thành công');
				}else{
					$this->session->set_flashdata('message_fail', 'Thay đổi sự kiện thất bại');
				}
				redirect(admin_url('occasion'));
			}
		}

		$this->data['temp']='admin/occasion/edit';
		$this->load->view('admin/main',$this->data);
	}
	public function del()
	{
		$id = $this->uri->segment(4);
		$occasion = $this->discount_model->get_info($id);
		
		if (empty($occasion)) {
			$this->session->set_flashdata('message_fail', 'Sự kiện không tồn tại');
			redirect(admin_url('occasion'));
		}
		$this->data['occasion'] = $occasion;
		if ($this->discount_model->delete($id)) {
			$this->session->set_flashdata('message_success', 'Xóa sự kiện thành công');
		}else{
			$this->session->set_flashdata('message_fail', 'Xóa sự kiện thất bại');
		}
		redirect(admin_url('occasion'));
	}
	public function status()
	{
		$id = $this->uri->segment(4);
		$occasion = $this->discount_model->get_info($id);
		if (empty($occasion)) {
			$this->session->set_flashdata('message_fail', 'Sự kiện không tồn tại');
			redirect(admin_url('occasion'));
		}
		if ($occasion->status == 1) {
			$data = array('status' => 0);
		} else {
			$data = array('status' => 1);
		}
		if ($this->discount_model->update($id, $data)) {
			$this->session->set_flashdata('message_success', 'Thay đổi trạng thái thành công');
		} else {
			$this->session->set_flashdata('message_fail', 'Thay đổi trạng thái thất bại');
		}
		redirect(admin_url('occasion'));
	}
}
