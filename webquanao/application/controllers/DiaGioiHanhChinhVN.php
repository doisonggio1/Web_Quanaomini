<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DiaGioiHanhChinhVN extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('Jwt_library');
    }
    public function index()
    {
        // Đường dẫn file
        $path = APPPATH . 'data/data.json';

        if (file_exists($path)) {
            $json = file_get_contents($path);

            // Trả về JSON response cho frontend
            $this->output
                ->set_content_type('application/json')
                ->set_output($json);
        } else {
            // Trả về lỗi JSON nếu file không tồn tại
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'File không tồn tại.']));
        }
    }
}
