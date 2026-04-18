<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('product_model');
		$this->load->model('catalog_model');
		$this->load->model('comment_model');
		$this->load->model('user_model');
		$this->load->model('discount_model');
		$this->load->model('order_model');
	}

	public function index()
	{
		$this->data['temp']='site/product/index';
		$this->load->view('site/layoutsub',$this->data);
	}
	public function view()
	{
		$id = $this->uri->rsegment(3);
		$product = $this->product_model->get_product_with_discount($id);
		if (empty($product)) {
			$this->session->set_flashdata('message_fail', 'Sản phẩm không tồn tại');
			redirect(base_url());
		}
		$this->data['product']=$product;
		$catalog_product = $this->catalog_model->get_info($product->catalog_id);
		$this->data['catalog_product']=$catalog_product;

		$view = $this->session->userdata('session_view');
		$view = (!is_array($view)) ? array() : $view;
		if (!isset($view[$id])) {
			$view[$id]=TRUE;
			$this->session->set_userdata('session_view',$view);
			$data = array();
			$data['view'] = $product->view + 1;
			$this->product_model->update($id,$data);
		}
		

		$image_list = json_decode($product->image_list);
		$this->data['image_list'] = $image_list;
		
		$input = array();
		$input['where'] = array('catalog_id' => $product->catalog_id);
		$input['limit'] = array('4','0');
		$productsub = $this->product_model->get_products_with_discount($input);
		$this->data['productsub']=$productsub;
		
		$input = array();
		$input['order'] = array('buyed', 'DESC');
		$input['limit'] = array('4','0');
		$productview = $this->product_model->get_products_with_discount($input);
		$this->data['productview']=$productview;
		
		// Lấy danh sách bình luận theo sản phẩm
		$sql = "
			SELECT comments.*, user.name AS user_name
			FROM comments
			LEFT JOIN user ON comments.user_id = user.id
			WHERE comments.product_id = $id
			ORDER BY comments.created DESC
		";
		$comments = $this->comment_model->query($sql);

		$this->data['comments'] = $comments;

		// Kiểm tra dữ liệu lấy được
		// echo '<pre>';
		// print_r($comments);
		// echo '</pre>';
		// exit();

		$this->data['temp']='site/product/view';
		$this->load->view('site/layoutsub',$this->data);
	}
	public function catalog()
	{
		$id = $this->uri->rsegment(3);
		$catalog = $this->catalog_model->get_info($id);
		$this->data['catalog_p'] = $catalog;
		if(empty($catalog))
		{
			redirect(base_url());
		}
		$input = array();
		if ($catalog->parent_id == '1') {
			$input_cat = array();
			$input_cat['where'] = array('parent_id' => $catalog->id);
			$input_cat['order'] = array('sort_order', 'ASC');
			$catalog_child = $this->catalog_model->get_list($input_cat);
			if (!empty($catalog_child)) {
				$cat_list_id = array();
				foreach ($catalog_child as $value) {
					$cat_list_id[] = $value->id;
				}
				$this->db->where_in('catalog_id',$cat_list_id);
			}else{
				$input['where'] = array('catalog_id'=>$id);
			}
		}else{
			$input['where'] = array('catalog_id'=>$id);
		}
		$total = $this->product_model->get_total($input);
		$this->data['total']=$total;

		$this->load->library('pagination');
		$config = array();
		$base_url = base_url('product/catalog/'.$id);
		$per = 8;
		$uri = 4;
		$config = pagination($base_url,$total,$per,$uri);		
		$this->pagination->initialize($config);

		$segment = $this->uri->segment(4);
		$segment = intval($segment);

		$input['limit'] = array($config['per_page'],$segment);
		if(isset($cat_list_id))
		{   
		    $this->db->where_in('catalog_id', $cat_list_id);
		}

		$product_list = $this->product_model->get_products_with_discount($input);
		$this->data['product_list'] = $product_list;

		$this->data['temp']='site/product/catalog';
		$this->load->view('site/layoutsub',$this->data);

	}
	public function hot()
	{
		$input = array();
		$input['order'] = array('buyed','DESC');

		$this->load->library('pagination');
		$config = array();
		$base_url = base_url('product/hot');
		$total =20;
		$per = 8;
		$uri = 3;
		$config = pagination($base_url,$total,$per,$uri);
		$this->pagination->initialize($config);

		$segment = $this->uri->segment(3);
		$segment = intval($segment);

		$input['limit'] = array($config['per_page'],$segment);

		$product_list = $this->product_model->get_products_with_discount($input);
		$this->data['product_list'] = $product_list;
		$this->data['temp']='site/product/hot';
		$this->load->view('site/layoutsub',$this->data);

	}
	public function views()
	{
		$input = array();
		$input['order'] = array('view','DESC');

		$this->load->library('pagination');
		$config = array();
		$base_url = base_url('product/views');
		$total =20;
		$per = 8;
		$uri = 3;
		$config = pagination($base_url,$total,$per,$uri);
		$this->pagination->initialize($config);

		$segment = $this->uri->segment(3);
		$segment = intval($segment);

		$input['limit'] = array($config['per_page'],$segment);

		$product_list = $this->product_model->get_products_with_discount($input);
		$this->data['product_list'] = $product_list;
		$this->data['temp']='site/product/views';
		$this->load->view('site/layoutsub',$this->data);

	}
	public function news()
	{
		$input = array();
		$input['order'] = array('id','DESC');

		$this->load->library('pagination');
		$config = array();
		$base_url = base_url('product/news');
		$total =20;
		$per = 8;
		$uri = 3;
		$config = pagination($base_url,$total,$per,$uri);
		$this->pagination->initialize($config);

		$segment = $this->uri->segment(3);
		$segment = intval($segment);

		$input['limit'] = array($config['per_page'],$segment);

		$product_list = $this->product_model->get_products_with_discount($input);
		$this->data['product_list'] = $product_list;
		$this->data['temp']='site/product/new';
		$this->load->view('site/layoutsub',$this->data);

	}
	public function discount()
	{
		$input = array();
		$input['order'] = array('discount','DESC');

		$this->load->library('pagination');
		$config = array();
		$base_url = base_url('product/discount');
		$total =20;
		$per = 8;
		$uri = 3;
		$config = pagination($base_url,$total,$per,$uri);
		$this->pagination->initialize($config);

		$segment = $this->uri->segment(3);
		$segment = intval($segment);

		$input['limit'] = array($config['per_page'],$segment);

		$product_list = $this->product_model->get_products_with_discount($input);
		$this->data['product_list'] = $product_list;
		$this->data['temp']='site/product/discount';
		$this->load->view('site/layoutsub',$this->data);

	}
	public function search()
	{
		
		$catalog_id = $this->input->post('catalog_id');
		$price_from = $this->input->post('price_from');
		$price_to = $this->input->post('price_to');

		$this->data['price_from'] = $price_from;
		$this->data['price_to'] = $price_to;
		$this->data['catalog_id'] = $catalog_id;
		$input = array();
		

		$list = $this->catalog_model->get_info($catalog_id);
		if ($list->parent_id == '1') {
			$inputt=array();
			$inputt['where'] = array('parent_id' => $list->id);
			$list_child = $this->catalog_model->get_list($inputt);
			$list_id = array();
			foreach ($list_child as $value) {
				$list_id[] = $value->id;
			}
			$this->db->where_in('catalog_id', $list_id);
			$input['where'] = "product.price - (
				CASE
					WHEN discount.status = 1 
						AND product.price >= discount.min_price 
						AND NOW() BETWEEN discount.start_date AND discount.end_date THEN
						CASE
							WHEN discount.measure = 0 THEN discount.value
							WHEN discount.measure = 1 THEN product.price * (discount.value / 100)
							ELSE 0
						END
					ELSE 0
				END
			) BETWEEN $price_from AND $price_to";
		}else{
			$input['where'] = "product.price - (
				CASE
					WHEN discount.status = 1 
						AND product.price >= discount.min_price 
						AND NOW() BETWEEN discount.start_date AND discount.end_date THEN
						CASE
							WHEN discount.measure = 0 THEN discount.value
							WHEN discount.measure = 1 THEN product.price * (discount.value / 100)
							ELSE 0
						END
					ELSE 0
				END
			) BETWEEN $price_from AND $price_to AND catalog_id = $catalog_id";
		}
		$input['order'] = array('price','ASC');
		$product_list = $this->product_model->get_products_with_discount($input);
		$total =  count($product_list);
		$this->data['total'] = $total;
		$this->data['product_list'] = $product_list;
		$this->data['temp']='site/product/search';
		$this->load->view('site/layoutsub',$this->data);
	}
	public function raty()
	{

		
		$id = $this->input->post('id');
		$product = $this->product_model->get_product_with_discount($id);
		if (!$product) {
			exit();
		}
		$result = array();
		$raty = $this->session->userdata('session_raty');
		$raty = (!is_array($raty)) ? array() : $raty;
		if (isset($raty[$id])) {
			$result['msg'] = 'Bạn đã bình chọn cho sản phẩm này rồi';
			$output = json_encode($result);
			echo $output;
			exit();
		}
		$raty[$id] = TRUE;
		$this->session->set_userdata('session_raty',$raty);
		$score = $this->input->post('score');
		$data=array();
		$data['rate_count'] = $product->rate_count + 1;
		$data['rate_total'] = $product->rate_total + $score;
		$this->product_model->update($id,$data);
		$result['complete'] = TRUE;
		$result['msg'] = 'Cảm ơn bạn đã đánh giá';
		$output = json_encode($result);
		echo $output;
		exit();
	}

	public function submit_rating()
	{
		$id = $this->input->post('id');
		$score = $this->input->post('score');
		$comment = $this->input->post('comment');
		$transaction_id = $this->input->post('order_id');

		$product = $this->product_model->get_product_with_discount($id);
		if (!$product) {
			if ($this->input->is_ajax_request()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
				exit();
			} else {
				$this->session->set_flashdata('message_fail', 'Sản phẩm không tồn tại');
				redirect(base_url());
			}
			return;
		}

		// Cập nhật status của bảng Order theo transaction_id và product_id
		$this->db->where('transaction_id', $transaction_id);
		$this->db->where('product_id', $id);
		$this->db->update('order', ['status' => 1]);
		

		// Cập nhật điểm đánh giá và số lượng đánh giá
		$data = array();
		$data['rate_count'] = $product->rate_count + 1;
		$data['rate_total'] = $product->rate_total + $score;
		$this->product_model->update($id, $data);

		$user = $this->session->userdata('user');

		// Lưu bình luận vào bảng comments
		$comment_data = array(
			'product_id' => $id,
			'user_id' => $user->id, // ID người dùng (nếu có)
			'comment_content' => $comment,
			'rate' => $score,
			'created' => now()
		);

		if ($this->comment_model->create($comment_data)) {
			if ($this->input->is_ajax_request()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => true]);
				exit();
			} else {
				redirect(base_url('product/view/' . $id));
			}
		} else {
			if ($this->input->is_ajax_request()) {
				header('Content-Type: application/json');
				echo json_encode(['success' => false]);
				exit();
			} else {
				redirect(base_url('product/view/' . $id));
			}
		}
	}

	public function text_search()
	{
		$keyword = $this->input->post('key');
		if (empty($keyword)) {
			echo json_encode(['success' => false, 'message' => 'Vui lòng nhập từ khóa tìm kiếm.']);
			exit();
		}
		
		$keyword_parts = explode(' ', strtolower($keyword));

		$catalogs = $this->catalog_model->get_list();
		$catalog_ids = [];

		foreach ($catalogs as $catalog) {
			foreach ($keyword_parts as $keyword_part) {

				// echo '<pre>';
				// print_r($keyword_part);
				// print_r('----------------');
				// print_r(mb_strtolower($catalog->name));
				// print_r('----------------');
				// print_r(stripos(mb_strtolower($catalog->name), $keyword_part));
				// echo '</pre>';

				if (stripos(mb_strtolower($catalog->name), $keyword_part) !== false) {
					$catalog_ids[] = $catalog->id;
					break;
				}
			}
		}

		// echo '<pre>';
		// print_r($catalog_ids);
		// echo '</pre>';
		// exit();

		if (!empty($catalog_ids)) {
			$input = array();
			$input['where_in'] = array(
				'catalog_id' => $catalog_ids
			);
			$input['order'] = array('price', 'ASC');
			$products = $this->product_model->get_products_with_discount($input);
			
			// echo '<pre>';
			// print_r($products);
			// echo '</pre>';
			// exit();
		} else {
			$products = $this->product_model->get_products_with_discount();
		}

		$product_list = $this->product_model->fuzzy_search($keyword, $products);

		$this->session->unset_userdata('search_results');
		$this->session->set_userdata('search_results', $product_list);

		redirect(base_url('tim-kiem-ket-qua'));
	}

	public function image_search() {
		if (!isset($_FILES['image'])) {
			echo json_encode(['success' => false, 'message' => 'Không có ảnh được tải lên']);
			exit();
			return;
		}
	
		$config['upload_path'] = './upload/search/';
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size'] = 2048;
		$this->load->library('upload', $config);
	
		if (!$this->upload->do_upload('image')) {
			echo json_encode(['success' => false, 'message' => $this->upload->display_errors()]);
			exit();
			return;
		}
	
		$upload_data = $this->upload->data();
		$image_path = FCPATH . 'upload/search/' . $upload_data['file_name'];
	
		// Kiểm tra file tồn tại
		if (!file_exists($image_path)) {
			echo json_encode(['success' => false, 'message' => 'File ảnh không tồn tại.']);
			exit();
		}
	
		// URL của dịch vụ AI
		$ai_service_url = 'http://python_ai:5000/api/image_search';
	
		// Đọc nội dung file ảnh
		$cfile = new CURLFile($image_path, mime_content_type($image_path), basename($image_path));
	
		// Cấu hình cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $ai_service_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: multipart/form-data"]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $cfile]);
	
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	
		if ($http_code !== 200) {
			echo json_encode(['success' => false, 'message' => 'Lỗi kết nối AI server.', 'http_code' => $http_code]);
			exit();
		}
	
		$response_data = json_decode($response, true);
		if (!$response_data || !isset($response_data['image_names'])) {
			echo json_encode(['success' => false, 'message' => 'Dịch vụ AI không trả về kết quả hợp lệ.']);
			exit();
		}
	
		$image_names = $response_data['image_names'];
	
		$product_list = $this->product_model->get_products_by_images_with_discount($image_names);
	
		// Xóa file ảnh sau khi xử lý xong
		if (file_exists($image_path)) {
			unlink($image_path);
		}
	
		if (empty($product_list)) {
			echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm nào.']);
			exit();
			return;
		}
	
		// Lưu danh sách sản phẩm vào session
		$this->session->unset_userdata('search_results');
		$this->session->set_userdata('search_results', $product_list);
	
		// Trả về phản hồi JSON thành công
		echo json_encode(['success' => true, 'product_list' => $product_list]);
		exit();
	}
 
 	public function tim_kiem_ket_qua() {
 		// Lấy danh sách sản phẩm từ session
 		$product_list = $this->session->userdata('search_results');
 	
 		if (empty($product_list)) {
 			$this->data['message'] = 'Không tìm thấy sản phẩm nào.';
 			$this->data['product_list'] = [];
 		} else {
 			$this->data['message'] = null;
 			$this->data['product_list'] = $product_list;
 		}
 		
 		$total =  count($product_list);
 		$this->data['total'] = $total;
 		$this->data['temp'] = 'site/product/search';
 		$this->load->view('site/layoutsub', $this->data);
		
 	}

	/**
	 * Lấy tổng hợp đánh giá sản phẩm từ OpenAI
	 */
	public function get_review_summary() {
		// Kiểm tra yêu cầu AJAX
		if (!$this->input->is_ajax_request()) {
			show_error('Không được phép truy cập trực tiếp');
			return;
		}

		// Lấy ID sản phẩm từ request
		$product_id = $this->input->post('product_id');
		if (!$product_id) {
			echo json_encode(['success' => false, 'message' => 'Thiếu ID sản phẩm']);
			exit();
		}

		// Lấy thông tin sản phẩm
		$product = $this->product_model->get_info($product_id);
		if (!$product) {
			echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
			exit();
		}

		// Lấy danh sách bình luận theo sản phẩm
		$sql = "
			SELECT comments.*, user.name AS user_name
			FROM comments
			LEFT JOIN user ON comments.user_id = user.id
			WHERE comments.product_id = $product_id
			ORDER BY comments.created DESC
		";
		$comments = $this->comment_model->query($sql);

		if (empty($comments)) {
			echo json_encode(['success' => false, 'message' => 'Sản phẩm chưa có bình luận nào']);
			exit();
		}

		// Chuẩn bị dữ liệu để gửi đến API OpenAI
		$reviews = [];
		foreach ($comments as $comment) {
			$reviews[] = "Đánh giá {$comment->rate}/5 sao: {$comment->comment_content}";
		}

		$data = [
			'product_name' => $product->name,
			'reviews' => $reviews
		];

		// Gọi API OpenAI để tổng hợp đánh giá
		$openai_url = 'http://openai:5002/api/product/review-summary';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $openai_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($http_code !== 200) {
			echo json_encode(['success' => false, 'message' => 'Lỗi kết nối đến dịch vụ AI', 'http_code' => $http_code]);
			exit();
		}

		$response_data = json_decode($response, true);
		if (!$response_data || !isset($response_data['data']['summary'])) {
			echo json_encode(['success' => false, 'message' => 'Dịch vụ AI không trả về kết quả hợp lệ']);
			exit();
		}

		// Trả về kết quả tổng hợp
		echo json_encode([
			'success' => true, 
			'summary' => $response_data['data']['summary'],
			'review_count' => count($reviews)
		]);
		exit();
	}
}
