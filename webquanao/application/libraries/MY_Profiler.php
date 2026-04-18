<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Profiler extends CI_Profiler {
    public function run()
    {
        $output = parent::run(); // Gọi profiler gốc của CodeIgniter

        // Ghi log dữ liệu profiler
        log_message('debug', "Profiler Output:\n" . $output); // Nếu muốn dùng profiler: chuyển threshold về bằng 4 hoặc sửa 'debug' thành 'error'

        // return $output; // Nếu muốn hiển thị profiler trên trình duyệt
    }
}
