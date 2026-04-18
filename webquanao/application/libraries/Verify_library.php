<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Verify_library
{
    protected $CI;

    public function __construct()
    {
        // &get_instance() trả về một đối tượng của CI_controller
        $this->CI = &get_instance();
        $this->CI->load->model('user_model');
        $this->CI->load->library('Jwt_library');
    }

    public function generate_verification_token($info)
    {
        $token = $this->CI->jwt_library->encode($info);
        return $token;
    }

    public function verify_recaptcha($recaptchaResponse)
    {
        // Put secret key here, which we get
        // from google console
        $secret_key = getenv('PRIVATE_KEY');

        // Hitting request to the URL, Google will
        // respond with success or error scenario
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret='
            . $secret_key . '&response=' . $recaptchaResponse;

        // Making request to verify captcha
        // Thiết lập thời gian timeout
        $context = stream_context_create([
            'http' => [
                'timeout' => 5, // Timeout sau 5 giây
            ],
        ]);
        // Gửi request để xác thực captcha
        $response = @file_get_contents($url, false, $context);

        // Nếu request thất bại hoặc quá thời gian, trả về false
        if ($response === false) {
            return false;
        }

        // Response return by google is in JSON format, so we have to parse that json
        $response = json_decode($response);

        // Checking, if response is true or not
        if ($response->success == true) {
            return true;
        } else {
            return false;
        }
    }
}
