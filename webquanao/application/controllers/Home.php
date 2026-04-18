<?php
defined('BASEPATH') or exit('No direct script access allowed'); //Dòng này giúp ngăn chặn truy cập trực tiếp  vào file Home.php mà không thông qua CodeIgniter.

class Home extends MY_Controller
{ // Home là một Controller kế thừa từ MY_Controller, có nghĩa là nó mở rộng các chức năng từ MY_Controller (một Controller gốc do bạn tạo ra thay vì CI_Controller).
	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}

	public function index() //Khi người dùng truy cập trang chủ (http://yourdomain.com/), hệ thống sẽ gọi Controller mặc định được cấu hình trong routes.php:
	{
		$this->load->model('slider_model');
		$input = array();
		$input['order'] = array('sort_order', 'DESC');
		$slider = $this->slider_model->get_list($input);
		$this->data['slider'] = $slider;

		$this->load->model('product_model');
		$input = array();
		$input['order'] = array('id', 'DESC');
		$input['limit'] = array('12', '0');
		$new_product = $this->product_model->get_products_with_discount($input);
		$this->data['new_product'] = $new_product;

		$input['order'] = array('buyed', 'DESC');
		$input['limit'] = array('12', '0');
		$hot_product = $this->product_model->get_products_with_discount($input);
		$this->data['hot_product'] = $hot_product;

		$input['order'] = array('view', 'DESC');
		$input['limit'] = array('12', '0');
		$view_product = $this->product_model->get_products_with_discount($input);
		$this->data['view_product'] = $view_product;

		$this->data['temp'] = 'site/home/index.php';
		$this->load->view('site/layout', $this->data);
	}
}