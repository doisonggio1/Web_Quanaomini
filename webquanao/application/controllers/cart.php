<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('product_model');
        $this->load->model('cart_model');
    }

    public function index()
    {
        $user = $this->session->userdata('user');
        if (!isset($user)) {
            redirect(base_url('/dang-nhap'));
        }
        $message = $this->session->flashdata('message');
        $this->data['message'] = $message;
        $carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);
        $formatted_cart = [];

        foreach ($carts as $item) {
            $formatted_cart[] = [
                'id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'name' => $item->name,
                'image_link' => $item->image_link,
                'rowid' => $item->rowid,
                'subtotal' => $item->price * $item->qty,
            ];
        }
        $this->data['carts'] = $formatted_cart;

        $this->data['total_items'] = $this->cart_model->get_sum('qty', ['user_id' => $user->id]);

        $this->data['temp'] = 'site/cart/index';
        $this->load->view('site/layoutsub', $this->data);
    }
    public function add()
    {
        $user = $this->session->userdata('user');
        if (!isset($user)) {
            redirect(base_url('/dang-nhap'));
        }
        $id = $this->uri->rsegment(3);
        $id = intval($id);
        $product = $this->product_model->get_product_with_discount($id);
        $data = array();
        $qty = 1;
        $price = $product->price;
        if ($product->discount > 0) {
            $price = $product->price - $product->discount;
        }
        $data['user_id'] = $user->id;
        $data['product_id'] = $id;
        $data['qty'] = $qty;
        $data['price'] = $price;
        $data['name'] = $product->name;
        $data['image_link'] = $product->image_link;
        $data['rowid'] = md5(mt_rand(1, 1000));

        $qty_ex = $this->cart_model->get_info_rule(['user_id' => $user->id, 'product_id' => $id], 'qty');
        if ($qty_ex) {
            $qty_ex = array(
                'qty' => $qty_ex->qty + 1
            );
            $this->cart_model->update_rule(['user_id' => $user->id, 'product_id' => $id], $qty_ex);
            redirect(base_url('cart'));
            return;
        }

        $this->cart_model->create($data);
        redirect(base_url('cart'));
    }
    public function update_ajax($id)
    {
        $user = $this->session->userdata('user');
        if (!isset($user)) {
            redirect(base_url('/dang-nhap'));
        }
        header('Content-Type: application/json'); // Thêm header để chỉ định response type

        $carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);
        $qty = $this->input->post('qty');
        if ($qty < 1) {
            $response = array(
                'status' => 'error',
                'message' => 'Số lượng sản phẩm không hợp lệ'
            );
            exit(json_encode($response, JSON_UNESCAPED_UNICODE));
            return;
        }
        $response = array(
            'status' => 'error',
            'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
        );
        foreach ($carts as $key => $value) {
            if ($value->product_id == $id) {
                $data = array();
                $data['qty'] = $qty;
                $data['rowid'] = md5(mt_rand(1, 1000));
                $this->cart_model->update_rule(['user_id' => $user->id, 'product_id' => $id], $data);

                // Get updated cart info
                $updated_cart = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);
                foreach ($updated_cart as $item) {
                    if ($item->rowid == $data['rowid']) {
                        $response = array(
                            'status' => 'success',
                            'message' => 'Cập nhật cart thành công!',
                        );
                        break;
                    }
                }
                exit(json_encode($response, JSON_UNESCAPED_UNICODE));
            }
        }

        // Trường hợp không tìm thấy sản phẩm
        exit(json_encode(array('status' => 'error')));
    }
    public function del()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json;');

            $user = $this->session->userdata('user');
			if (!$user) {
				echo json_encode(["status" => "null_user", "message" => "Người dùng không tồn tại!"], JSON_UNESCAPED_UNICODE);
				return;
			}
            // Nhận dữ liệu JSON từ request
            $data = json_decode(file_get_contents("php://input"), true);
            // Kiểm tra nếu không có dữ liệu
            if (!$data) {
                echo json_encode(["status" => "error", "message" => "Không nhận được dữ liệu"], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $carts = $this->cart_model->get_list(['where' => ['user_id' => $user->id]]);
            $id = $data['id'];
            $id = intval($id);
            if ($id > 0) {
                foreach ($carts as $key => $value) {
                    if ($value->product_id == $id) {  // dùng -> và so sánh ==
                        $this->cart_model->del_rule(['user_id' => $user->id, 'product_id' => $id]);
                        echo json_encode(["status" => "success", "message" => "Xóa sản phẩm thành công"], JSON_UNESCAPED_UNICODE);
                        return;
                    }
                }
            } else {
                $this->cart_model->del_rule(['user_id' => $user->id]);
                echo json_encode(["status" => "success", "message" => "Xóa giỏ hàng thành công"], JSON_UNESCAPED_UNICODE);
                return;
            }
        }
    }
}
