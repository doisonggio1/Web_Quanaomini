<?php
defined('BASEPATH') or exit('No direct script access allowed');
class MY_Controller extends CI_Controller
{
	var $data = array();
	function __construct()
	{
		parent::__construct();
		$controller = $this->uri->segment(1);
		switch ($controller) {
			case 'admin':
				$this->load->helper('admin');
				$this->_checklogin();
				$login = $this->session->userdata("login");
				$this->data['login'] = $login;
				break;

			default:
				$this->load->model('catalog_model');
				$input = array();
				$input['where'] = array('parent_id' => '1');
				$input['order'] = array('sort_order', 'ASC');
				$catalog = $this->catalog_model->get_list($input);
				foreach ($catalog as $value) {
					$input = array();
					$input['where'] = array('parent_id' => $value->id);
					$input['order'] = array('sort_order', 'ASC');
					$sub = $this->catalog_model->get_list($input);
					$value->sub = $sub;
				}
				$this->data['catalog'] = $catalog;

				$user = $this->session->userdata('user');
				$this->data['user'] = $user;

				$this->load->model('cart_model');
				if (isset($user)) {
					$carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);
					$this->data['carts'] = $carts;
					$this->data['total_items'] = $this->cart_model->get_sum('qty', ['user_id' => $user->id]);
				} else {
					$this->data['total_items'] = NULL;
				}

				// Bật profiler
				if (ENVIRONMENT == "development") {
					$this->output->enable_profiler(TRUE); //Log_dev_code
				}
				break;
		}
	}
	protected function _checklogin()
	{
		$controller = $this->uri->segment(2);
		$login = $this->session->userdata("login");
		if (!isset($login) && $controller != 'login') {
			redirect(admin_url('login'));
		}
		if (isset($login) && $controller == 'login') {
			redirect(admin_url('home'));
		}
	}
}
