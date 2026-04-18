<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php'; // Đường dẫn tới autoload Composer

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;

class Home extends MY_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('transaction_model');
		$this->load->model('comment_model');
		$this->load->model('user_model');
		$this->load->model('product_model');
		$this->load->model('order_model');
		$this->load->model('cart_model');
	}
	public function index()
	{
		// Đơn hàng mới (chưa xử lý)
		$input = array();
		$input['where'] = array('status' => '0');
		$total_order = $this->transaction_model->get_total($input);
		$this->data['total_order'] = $total_order;

		// Tổng số đơn hàng
		$total_all_orders = $this->transaction_model->get_total();
		$this->data['total_all_orders'] = $total_all_orders;
		// Tính phần trăm đơn hàng mới
		$order_percent = ($total_all_orders > 0) ? round(($total_order / $total_all_orders) * 100) : 0;
		$this->data['order_percent'] = $order_percent;

		// Tổng số bình luận
		$total_comments = $this->comment_model->get_total();
		$this->data['total_comments'] = $total_comments;

		// Bình luận mới (trong 30 ngày qua)
		$date = new DateTime();
		$date->modify('-30 days');
		$thirty_days_ago = $date->format('Y-m-d');
		$input_comment = array();
		$input_comment['where'] = "created >= '$thirty_days_ago'";
		$new_comments = $this->comment_model->get_total($input_comment);
		// Tính phần trăm bình luận mới
		$comment_percent = ($total_comments > 0) ? round(($new_comments / $total_comments) * 100) : 0;
		$this->data['comment_percent'] = $comment_percent;

		// Khách hàng mới (đăng ký trong 30 ngày qua)
		$input_user = array();
		$input_user['where'] = "created >= '$thirty_days_ago'";
		$new_customers = $this->user_model->get_total($input_user);
		$this->data['new_customers'] = $new_customers;

		// Tổng số khách hàng
		$total_customers = $this->user_model->get_total();
		// Tính phần trăm khách hàng mới
		$user_percent = ($total_customers > 0) ? round(($new_customers / $total_customers) * 100) : 0;
		$this->data['user_percent'] = $user_percent;

		// Tổng lượt xem sản phẩm
		$total_views = 0;
		$products = $this->product_model->get_list();

		foreach ($products as $product) {
			if (isset($product->view)) {
				$total_views += $product->view;
			}
		}
		$this->data['total_views'] = $total_views;

		// Lượt xem mới (ước tính 30% tổng lượt xem là mới)
		$visitor_percent = 27; // Giữ nguyên giá trị này vì không có dữ liệu thời gian cho lượt xem
		$this->data['visitor_percent'] = $visitor_percent;

		// 5 sản phẩm bán chạy nhất
		$this->db->select('product.*, COUNT(order.product_id) as sold_count');
		$this->db->from('product');
		$this->db->join('order', 'product.id = order.product_id', 'left');
		$this->db->group_by('product.id');
		$this->db->order_by('sold_count', 'DESC');
		$this->db->limit(5);
		$best_selling = $this->db->get()->result();
		$this->data['best_selling'] = $best_selling;

		// 5 sản phẩm bán ít nhất
		$this->db->select('product.*, COUNT(order.product_id) as sold_count');
		$this->db->from('product');
		$this->db->join('order', 'product.id = order.product_id', 'left');
		$this->db->group_by('product.id');
		$this->db->order_by('sold_count', 'ASC');
		$this->db->limit(5);
		$worst_selling = $this->db->get()->result();
		$this->data['worst_selling'] = $worst_selling;

		// 5 sản phẩm được đánh giá tốt nhất
		$this->db->select('product.*, AVG(comments.rate) as avg_rating, COUNT(comments.id) as rating_count');
		$this->db->from('product');
		$this->db->join('comments', 'product.id = comments.product_id', 'left');
		$this->db->where('comments.rate IS NOT NULL');
		$this->db->group_by('product.id');
		$this->db->having('rating_count > 0');
		$this->db->order_by('avg_rating', 'DESC');
		$this->db->limit(5);
		$best_rated = $this->db->get()->result();
		$this->data['best_rated'] = $best_rated;

		// 5 sản phẩm được đánh giá kém nhất
		$this->db->select('product.*, AVG(comments.rate) as avg_rating, COUNT(comments.id) as rating_count');
		$this->db->from('product');
		$this->db->join('comments', 'product.id = comments.product_id', 'left');
		$this->db->where('comments.rate IS NOT NULL');
		$this->db->group_by('product.id');
		$this->db->having('rating_count > 0');
		$this->db->order_by('avg_rating', 'ASC');
		$this->db->limit(5);
		$worst_rated = $this->db->get()->result();
		$this->data['worst_rated'] = $worst_rated;

		// 5 sản phẩm được cho vào giỏ hàng nhiều nhất
		$this->db->select('product.*, COUNT(cart.product_id) as cart_count');
		$this->db->from('product');
		$this->db->join('cart', 'product.id = cart.product_id', 'left');
		$this->db->group_by('product.id');
		$this->db->order_by('cart_count', 'DESC');
		$this->db->limit(5);
		$most_carted = $this->db->get()->result();
		$this->data['most_carted'] = $most_carted;

		// Thống kê lưu lượng truy cập GA4 (không làm hỏng trang nếu thiếu key)
		$KEY_FILE_PATH = APPPATH . 'third_party/ga4-key.json';
		$property_id = '492315679';
		$dataWeek = array();
		$dataMonth = array();

		try {
			if (!file_exists($KEY_FILE_PATH) || !is_readable($KEY_FILE_PATH)) {
				throw new RuntimeException('Missing or unreadable GA4 key file: ' . $KEY_FILE_PATH);
			}

			$client = new BetaAnalyticsDataClient([
				'credentials' => $KEY_FILE_PATH
			]);

			// ===================== THỐNG KÊ 7 NGÀY QUA =====================
			$responseWeek = $client->runReport([
				'property' => 'properties/' . $property_id,
				'dateRanges' => [
					new DateRange(['start_date' => '7daysAgo', 'end_date' => 'today'])
				],
				'metrics' => [
					new Metric(['name' => 'activeUsers']),
					new Metric(['name' => 'sessions']),
					new Metric(['name' => 'newUsers']),
					new Metric(['name' => 'engagedSessions']),
					new Metric(['name' => 'bounceRate']),
					new Metric(['name' => 'averageSessionDuration']),
				]
			]);

			$dataWeek = array();
			foreach ($responseWeek->getRows() as $row) {
				foreach ($row->getMetricValues() as $i => $metric) {
					$name = $responseWeek->getMetricHeaders()[$i]->getName();
					$dataWeek[$name] = $metric->getValue();
				}
			}

			// ===================== NGƯỜI DÙNG TRONG THÁNG HIỆN TẠI =====================
			$startOfMonth = date('Y-m-01');
			$today = date('Y-m-d');

			$responseMonth = $client->runReport([
				'property' => 'properties/' . $property_id,
				'dateRanges' => [
					new DateRange(['start_date' => $startOfMonth, 'end_date' => $today])
				],
				'metrics' => [
					new Metric(['name' => 'totalUsers']),
					new Metric(['name' => 'newUsers']), // 👈 Thêm dòng này
				]
			]);

			$dataMonth = array();
			foreach ($responseMonth->getRows() as $row) {
				foreach ($row->getMetricValues() as $i => $metric) {
					$name = $responseMonth->getMetricHeaders()[$i]->getName();
					$dataMonth[$name] = $metric->getValue();
				}
			}
		} catch (Throwable $e) {
			log_message('error', 'GA4 dashboard error: ' . $e->getMessage());
		}

		$this->data['data'] = $dataWeek;
		$this->data['dataMonth'] = $dataMonth;

		$this->data['temp'] = 'admin/home/index';
		$this->load->view('admin/main', $this->data);
	}
}