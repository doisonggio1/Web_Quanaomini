<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends MY_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
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
		if ($this->input->post())
		{
			$checkbox = $this->input->post('checkbox');
			foreach ($checkbox as $value) {
				$product = $this->product_model->get_info($value);
				$image = './upload/product/'.$product->image_link;
				if (file_exists($image)) {
					unlink($image);
				}
				$image_list = array();
				$image_list = json_decode($product->image_list);
				if (is_array($image_list)) {
					foreach ($image_list as $value) {
						$image = './upload/product/'.$value;
						if (file_exists($image)) {
							unlink($image);
						}
					}
				}
			}
			$this->db->where_in('id',$checkbox);
			$this->db->delete('product');

			$flag = $this->db->affected_rows();
			if ($flag > 0) {
				$this->session->set_flashdata('message_success', 'Xóa'.$flag.'sản phẩm thành công');
			}else{
				$this->session->set_flashdata('message_fail', 'Xóa sản phẩm thất bại');
			}
			redirect(admin_url('product'));
		}

		$total = $this->product_model->get_total();
		$this->data['total']=$total;

		$this->load->library('pagination');
		$config = array();
		$base_url = admin_url('product/index');
		$per = 10;
		$uri = 4;
		$config = pagination($base_url,$total,$per,$uri);
		$this->pagination->initialize($config);

		$segment = isset($this->uri->segments['4'])?$this->uri->segments['4']:NULL;
		$segment = intval($segment);
		
		$input['limit'] = array($config['per_page'],$segment);

		$product = $this->product_model->get_products_with_discount_catalog();
		$this->data['product']= $product;

		$this->data['temp']='admin/product/index';
		$this->load->view('admin/main',$this->data);
	}
	public function add()
	{
		$this->data['catalog'] = $this->list_catalog();
		$this->form_validation->set_error_delimiters('<div class="alert alert-danger" role="alert" style="padding:5px;border-bottom:0px;">', '</div>');

		if ($this->input->post()) {
			$this->form_validation->set_rules('name','Tên sản phẩm','required');
			$this->form_validation->set_rules('catalog_id','sản phẩm','required');
			$this->form_validation->set_rules('price','Giá sản phẩm','required');
			
			if ($this->form_validation->run()) {
				$path = './upload/product/';
				$image_link ='';
				$image_link = $this->upload_library->upload($path,'image');

				$image_list = array();
				$image_list = $this->upload_library->upload_file($path,'list_image');
		        $image_list = json_encode($image_list);

				$data = array();
				$data = array(
					'name' => $this->input->post('name'),
					'image_link' => $image_link,
					'image_list' => $image_list,
					'content' => $this->input->post('content'),
					'catalog_id' => $this->input->post('catalog_id'),
					'origin_price' => $this->input->post('origin_price'),
					'price' => $this->input->post('price'),
					'created' => now()
					);
					if ($this->product_model->create($data)) {
						// Gửi danh sách hình ảnh lên server AI
						$ai_service_url = 'http://python_ai:5000/api/add_images';
					
						// Chuẩn bị dữ liệu để gửi
						$images = array();
						if (!empty($image_link)) {
							$images[] = $image_link; // Thêm ảnh chính
						}
						if (!empty($image_list)) {
							$image_list_array = json_decode($image_list, true); // Giải mã JSON thành mảng
							$images = array_merge($images, $image_list_array); // Gộp danh sách ảnh
						}
					
						// Gửi dữ liệu qua cURL
						$curl = curl_init();
						$post_data = array();

						// Thêm từng ảnh vào $post_data với key riêng biệt
						foreach ($images as $index => $image) {
							$file_path = FCPATH . '/upload/product/' . $image;
							if (file_exists($file_path)) {
								$post_data['images[' . $index . ']'] = new CURLFile($file_path, mime_content_type($file_path), basename($file_path));
							} else {
								error_log("File không tồn tại: " . $file_path);
							}
						}

						curl_setopt_array($curl, array(
							CURLOPT_URL => $ai_service_url,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_POST => true,
							CURLOPT_POSTFIELDS => $post_data, // Dữ liệu POST
						));

						$response = curl_exec($curl);
						$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
						curl_close($curl);
					
						// Kiểm tra phản hồi từ server AI
						if ($http_code == 200) {
							$response_data = json_decode($response, true);
							if (isset($response_data['success']) && $response_data['success']) {
								$this->session->set_flashdata('message_success', 'Thêm sản phẩm thành công');
							} else {
								$this->session->set_flashdata('message_fail', 'Thêm sản phẩm thành công nhưng gửi ảnh lên AI thất bại');
							}
						} else {
							$this->session->set_flashdata('message_fail', 'Thêm sản phẩm thành công nhưng không thể kết nối đến AI');
						}
					} else {
						$this->session->set_flashdata('message_fail', 'Thêm sản phẩm thất bại');
					}
				redirect(admin_url('product'));
			}
		}

		$this->data['temp']='admin/product/add';
		$this->load->view('admin/main',$this->data);
	}
	public function edit()
	{
		$this->data['catalog'] = $this->list_catalog();
		$id = $this->uri->segment(4);
		$product = $this->product_model->get_product_with_discount($id);
		
		if (empty($product)) {
			$this->session->set_flashdata('message_fail', 'Sản phẩm không tồn tại');
			redirect(admin_url('product'));
		}
		$this->data['product'] = $product; 
		if ($this->input->post()) {
			$this->form_validation->set_rules('name','Tên sản phẩm','required');
			$this->form_validation->set_rules('catalog_id','sản phẩm','required');
			$this->form_validation->set_rules('price','Giá sản phẩm','required');
			if ($this->form_validation->run()) {
				$price = $this->input->post('price');
				$origin_price = $this->input->post('origin_price');
				// $discount = $this->input->post('discount');
				$data = array();
				$data = array(
					'name' => $this->input->post('name'),
					'content' => $this->input->post('content'),
					'catalog_id' => $this->input->post('catalog_id'),
					'price' => str_replace(',','',$price),
					'origin_price' => str_replace(',','',$origin_price),
					// 'discount' => str_replace(',','',$discount)
					);
				$path = './upload/product/';
				$image_link = '';
				$image_link = $this->upload_library->upload($path,'image');
				if ($image_link != '') {
					$data['image_link'] = $image_link;
					$image = './upload/product/'.$product->image_link;
					if (file_exists($image)) {
						unlink($image);
					}
				}
				$image_list = array();
				$image_list = $this->upload_library->upload_file($path,'list_image');
				$image_list_json = json_encode($image_list);
				if (!empty($image_list)) {
					$data['image_list'] = $image_list_json;
					$image_list = json_decode($product->image_list);
					if (is_array($image_list)) {
						foreach ($image_list as $value) {
							$image = './upload/product/'.$value;
							if (file_exists($image)) {
								unlink($image);
							}
						}
					}
				}
				if ($this->product_model->update($id,$data)) {
					$this->session->set_flashdata('message_success', 'Thay đổi sản phẩm thành công');
				}else{
					$this->session->set_flashdata('message_fail', 'Thay đổi sản phẩm thất bại');
				}
				redirect(admin_url('product'));
			}
		}

		$this->data['temp']='admin/product/edit';
		$this->load->view('admin/main',$this->data);
	}
	public function del()
	{
		$id = isset($_POST['id'])?$_POST['id']:'NULL';
		$product = $this->product_model->get_product_with_discount($id);
		
		$this->data['product'] = $product;
		if ($this->product_model->delete($id)) {
			$image = './upload/product/'.$product->image_link;
			if (file_exists($image)) {
				unlink($image);
			}
			$image_list = array();
			$image_list = json_decode($product->image_list);
			if (is_array($image_list)) {
				foreach ($image_list as $value) {
					$image = './upload/product/'.$value;
					if (file_exists($image)) {
						unlink($image);
					}
				}
			}
			echo 'success';
		}else{
			echo 'failer';
		}
	}
	protected function list_catalog()
	{
		$input = array();
		$input['where'] = array('parent_id' => '1');
		$input['order'] = array('sort_order' , 'asc');
		$catalog = $this->catalog_model->get_list($input);
		foreach ($catalog as $value) {
			$input['where'] = array('parent_id' => $value->id);
			$subs = $this->catalog_model->get_list($input);
			$value->sub = $subs;
		}
		return $catalog;
	}
	public function apply_event()
	{
		$product_ids = $this->input->post('product_ids');
		$event_id = $this->input->post('event_id');

		if (empty($product_ids)) {
			echo json_encode(['success' => false, 'message' => 'Không có sản phẩm nào được chọn.']);
			return;
		}

		// Đặt thông tin cột discount_id của mỗi sản phẩm thành ID của sự kiện
		$this->db->where_in('id', $product_ids);
		$this->db->update('product', ['discount_id' => $event_id]);
		$affected_rows = $this->db->affected_rows();
		if ($affected_rows > 0) {
			// Nếu có sản phẩm nào được cập nhật thành công
			$this->session->set_flashdata('message_success', 'Áp dụng sự kiện thành công cho ' . $affected_rows . ' sản phẩm.');
		} else {
			// Nếu không có sản phẩm nào được cập nhật
			$this->session->set_flashdata('message_fail', 'Sản phẩm đang áp dụng sự kiện.');
		}		

		// Trả về phản hồi JSON
		echo json_encode(['success' => true, 'message' => 'Áp dụng sự kiện thành công cho ' . $affected_rows . ' sản phẩm.']);

	}
}
