<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shipment extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('transaction_model');
        $this->load->library('pagination');
		$this->load->model('product_model');
    }

    public function index()
    {
        $user = $this->session->userdata('user');
        $orders = [];
        $status_filter = $this->input->get('status');
        
        // If status is not provided or invalid, set default to 'all'
        if ($status_filter === null || !in_array($status_filter, ['0', '1', '2', '3', '4'])) {
            $status_filter = 'all';
        }

        if ($user) {
            $user_email = $user->email;

            // Base query for counting total orders
            $this->db->select('COUNT(*) as total')
                ->from('transaction')
                ->where('transaction.delivery_email', $user_email);
            
            // Apply status filter for counting if not 'all'
            if ($status_filter !== 'all') {
                $this->db->where('transaction.status', $status_filter);
            }
            
            $count_result = $this->db->get()->row();
            $total_rows = $count_result->total;

            // Cấu hình Pagination
            $config = array();
            $config['base_url'] = base_url('shipment/index');
            $config['total_rows'] = $total_rows;
            $config['per_page'] = 10; // Số đơn hàng trên mỗi trang
            $config['uri_segment'] = 3;
            $config['num_links'] = 2;
            $config['page_query_string'] = TRUE;
            $config['query_string_segment'] = 'page';
            $config['reuse_query_string'] = TRUE;

            // Cấu hình giao diện Pagination Bootstrap
            $config['full_tag_open'] = '<ul class="pagination">';
            $config['full_tag_close'] = '</ul>';
            $config['first_tag_open'] = '<li>';
            $config['first_tag_close'] = '</li>';
            $config['last_tag_open'] = '<li>';
            $config['last_tag_close'] = '</li>';
            $config['next_tag_open'] = '<li>';
            $config['next_tag_close'] = '</li>';
            $config['prev_tag_open'] = '<li>';
            $config['prev_tag_close'] = '</li>';
            $config['cur_tag_open'] = '<li class="active"><a>';
            $config['cur_tag_close'] = '</a></li>';
            $config['num_tag_open'] = '<li>';
            $config['num_tag_close'] = '</li>';

            // Load thư viện Pagination và khởi tạo
            $this->pagination->initialize($config);

            // Lấy số trang hiện tại từ query string
            $page = $this->input->get('page') ? $this->input->get('page') : 0;

            // Truy vấn đơn hàng theo trang và filter
            $this->db->select('
                transaction.id as transaction_id,
                transaction.status,
                transaction.payment,
                transaction.created,
                transaction.delivery_name,
                transaction.delivery_email,
                transaction.delivery_address,
                transaction.delivery_phone,
                transaction.amount as total_amount,
                GROUP_CONCAT(DISTINCT product.name SEPARATOR ", ") AS product_names,
                GROUP_CONCAT(DISTINCT product.image_link SEPARATOR "|") AS product_images,
                GROUP_CONCAT(DISTINCT product.id SEPARATOR ",") AS product_ids,
                SUM(order.qty) AS total_items
            ');
            $this->db->from('transaction');
            $this->db->join('order', 'transaction.id = order.transaction_id', 'left');
            $this->db->join('product', 'order.product_id = product.id', 'left');
            $this->db->where('transaction.delivery_email', $user_email);
            
            // Apply status filter if not 'all'
            if ($status_filter !== 'all') {
                $this->db->where('transaction.status', $status_filter);
            }
            
            $this->db->group_by('transaction.id');
            $this->db->order_by('transaction.created', 'DESC');
            $this->db->limit($config['per_page'], $page);

            $orders = $this->db->get()->result();
            
            // Get the counts for each status for the tab counters
            $status_counts = [];
            $this->db->select('status, COUNT(*) as count');
            $this->db->from('transaction');
            $this->db->where('delivery_email', $user_email);
            $this->db->group_by('status');
            $status_results = $this->db->get()->result();
            
            foreach($status_results as $result) {
                $status_counts[$result->status] = $result->count;
            }
            
            // Make sure all statuses have a count
            for($i = 0; $i <= 4; $i++) {
                if(!isset($status_counts[$i])) {
                    $status_counts[$i] = 0;
                }
            }
            
            $this->data['status_counts'] = $status_counts;
            
            // Get products that the user has purchased
            $purchased_product_ids = array();
            foreach ($orders as $order) {
                if (!empty($order->product_ids)) {
                    $product_ids = explode(',', $order->product_ids);
                    $purchased_product_ids = array_merge($purchased_product_ids, $product_ids);
                }
            }
            
            // Get recommended products (products from same category but not purchased yet)
            $recommended_products = array();
            
            if (!empty($purchased_product_ids)) {
                // Get unique purchased product IDs
                $purchased_product_ids = array_unique($purchased_product_ids);
                
                // Get popular products excluding what the user has already purchased
                $this->db->select('product.*', false);
				$this->db->select('
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
					END AS discount
				', false);
				$this->db->from('product');
				$this->db->join('discount', 'product.discount_id = discount.id', 'left');
                $this->db->where_not_in('product.id', $purchased_product_ids);
                $this->db->order_by('product.buyed', 'DESC');
                $this->db->limit(5);
                
                $query = $this->db->get();
                
                if ($query->num_rows() > 0) {
                    foreach ($query->result() as $row) {
                        $price = $row->price;
                        if ($row->discount > 0) {
                            $price = $row->price - $row->discount;
                        }
                        
                        $recommended_products[] = [
                            'id' => $row->id,
                            'name' => $row->name,
                            'price' => $price,
                            'original_price' => $row->price,
                            'discount' => $row->discount,
                            'image' => $row->image_link,
                            'url' => base_url(covert_vi_to_en($row->name) . '-p' . $row->id)
                        ];
                    }
                }
            } else {
                // If no purchase history, just show popular products
                $this->db->select('product.*', false);
				$this->db->select('
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
					END AS discount
				', false);
				$this->db->from('product');
				$this->db->join('discount', 'product.discount_id = discount.id', 'left');
                $this->db->order_by('product.buyed', 'DESC');
                $this->db->limit(5);
                
                $query = $this->db->get();
                
                if ($query->num_rows() > 0) {
                    foreach ($query->result() as $row) {
                        $price = $row->price;
                        if ($row->discount > 0) {
                            $price = $row->price - $row->discount;
                        }
                        
                        $recommended_products[] = [
                            'id' => $row->id,
                            'name' => $row->name,
                            'price' => $price,
                            'original_price' => $row->price,
                            'discount' => $row->discount,
                            'image' => $row->image_link,
                            'url' => base_url(covert_vi_to_en($row->name) . '-p' . $row->id)
                        ];
                    }
                }
            }
            
            $this->data['recommended_products'] = $recommended_products;
        }
        $this->data['orders'] = $orders;
        $this->data['status_filter'] = $status_filter;
        $this->data['pagination'] = $this->pagination->create_links();
        $this->data['temp'] = 'site/shipment/index';
        $this->load->view('site/layoutsub', $this->data);
    }

    public function cancel_orders()
    {
        // Get the order IDs from POST data
        $order_ids = $this->input->post('order_ids');

        if (empty($order_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'Không có đơn hàng nào được chọn!']);
            return;
        }

        // Start a transaction to ensure data integrity
        $this->db->trans_start();
        
        try {
            // For each transaction ID directly (which is what we're receiving), update its status to canceled
            // or delete the transaction based on your business logic
            foreach ($order_ids as $transaction_id) {
                // Update the transaction status to 'Canceled' (4) without deleting the record
                $this->db->where('id', $transaction_id);
                $this->db->update('transaction', ['status' => 4]);
            }
            
            // Complete the transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                // Transaction failed
                echo json_encode(['status' => 'error', 'message' => 'Hủy đơn hàng thất bại. Vui lòng thử lại!']);
                return;
            }
            
            // Success
            echo json_encode(['status' => 'success', 'message' => 'Đã hủy đơn hàng thành công!']);
            
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $this->db->trans_rollback();
            echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function get_order_details()
    {
        // Get the transaction ID from POST data or URL segment
        $transaction_id = $this->input->post('transaction_id');
        
        // If not in POST, check URL segment
        if (empty($transaction_id)) {
            $transaction_id = $this->uri->segment(3);
        }
        
        if (empty($transaction_id)) {
            echo json_encode(['status' => 'error', 'message' => 'No order ID provided']);
            return;
        }
        
        // Get user info for security check
        $user = $this->session->userdata('user');
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
            return;
        }

        try {
            // Get transaction details
            $this->db->select('
				transaction.*,
				GROUP_CONCAT(DISTINCT product.name SEPARATOR ", ") AS product_names,
				GROUP_CONCAT(DISTINCT product.id SEPARATOR ",") AS product_ids,
				GROUP_CONCAT(DISTINCT CONCAT(product.id, ":", product.name, ":", product.image_link, ":", order.qty, ":", product.price, ":", 
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
				, ":", order.status) SEPARATOR "|") AS product_details
			', false);
			$this->db->from('transaction');
			$this->db->join('order', 'transaction.id = order.transaction_id', 'left');
			$this->db->join('product', 'order.product_id = product.id', 'left');
			$this->db->join('discount', 'product.discount_id = discount.id', 'left');
			$this->db->where('transaction.id', $transaction_id);
			$this->db->where('transaction.delivery_email', $user->email); // Security: ensure user owns this order
			$this->db->group_by('transaction.id');
            
            $order = $this->db->get()->row();

            if (!$order) {
                echo json_encode(['status' => 'error', 'message' => 'Order not found']);
                return;
            }
            
            // Format the order details
            $payment_method = '';
            if ($order->payment == 'cash') {
                $payment_method = 'Tiền mặt khi nhận hàng';
            } else if ($order->payment == 'vnpay') {
                $payment_method = 'VNPAY';
            } else if ($order->payment == 'vietqr') {
                $payment_method = 'Chuyển khoản';
            } else if ($order->payment == 'pos') {
                $payment_method = 'Quẹt thẻ POS';
            } else {
                $payment_method = 'Chưa xác định';
            }
            
            $status_text = '';
            if ($order->status == 0) {
                $status_text = 'Chờ xác nhận';
            } else if ($order->status == 1) {
                $status_text = 'Đã xác nhận';
            } else if ($order->status == 2) {
                $status_text = 'Đang vận chuyển';
            } else if ($order->status == 3) {
                $status_text = 'Hoàn thành';
            } else if ($order->status == 4) {
                $status_text = 'Đã hủy';
            }
            // Parse product details to create detailed product list
            $products = [];
            $product_ids_array = [];
            if (!empty($order->product_details)) {
                $product_items = explode('|', $order->product_details);
                foreach ($product_items as $item) {
                    $parts = explode(':', $item);
                    if (count($parts) >= 4) {
                        $products[] = [
                            'id' => $parts[0],
							'name' => $parts[1],
                            'image' => $parts[2],
                            'quantity' => $parts[3],
                            'price' => $parts[4],
							'discount' => $parts[5],
							'status' => $parts[6],
                            'subtotal' => $parts[3] * ($parts[4] - $parts[5])
                        ];
                        $product_ids_array[] = $parts[0];
                    }
                }
            }
            // Get recommended products based on current order
            $recommended_products = [];
            if (!empty($product_ids_array)) {
                // Get popular products excluding what's in the current order
                $input = array();
				$input['order'] = array('buyed', 'DESC');
				$input['limit'] = array('4','0');
				$input['where_not_in'] = array('id', $product_ids_array);
				$products_recommended = $this->product_model->get_products_with_discount($input);
                
                if (!empty($products_recommended)) {
                    foreach ($products_recommended as $row) {
                        $price = $row->price;
                        if ($row->discount > 0) {
                            $price = $row->price - $row->discount;
                        }
                        
                        $recommended_products[] = [
                            'id' => $row->id,
                            'name' => $row->name,
                            'price' => $price,
                            'original_price' => $row->price,
                            'discount' => $row->discount,
                            'image' => $row->image_link,
                            'url' => base_url(covert_vi_to_en($row->name) . '-p' . $row->id)
                        ];
                    }
                }
            }
            // Format the response
            $response = [
                'status' => 'success',
                'order' => [
                    'id' => $order->id,
                    'customer_name' => $order->delivery_name,
                    'customer_email' => $order->delivery_email,
                    'customer_phone' => $order->delivery_phone,
                    'customer_address' => $order->delivery_address,
                    'order_date' => date('d/m/Y H:i', strtotime($order->created)),
                    'status' => $status_text,
                    'status_code' => intval($order->status),
                    'payment_method' => $payment_method,
                    'total_amount' => number_format($order->amount),
                    'products' => $order->product_names,
                    'product_details' => $products,
                    'recommended_products' => $recommended_products
                ]
            ];
            
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
