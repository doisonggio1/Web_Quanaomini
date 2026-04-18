<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jwt_library {
    public function __construct()
    {
        // &get_instance() trả về một đối tượng của CI_controller
        $this->CI = &get_instance();

        $this->privateKey = getenv('JWT_PRIVATE_KEY') ?: 'dev_fallback_jwt_key_change_me';
    }
    private $privateKey;
    private $algorithm = 'HS256';

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function encode($data) {
        $issuedAt = time();
        $expire = (int) getenv('JWT_EXPIRE');
        if ($expire <= 0) {
            $expire = 900;
        }

        $payload = [
            'iat' => $issuedAt,
            'exp' => $issuedAt + $expire,
            'data' => $data
        ];

        $header = [
            'alg' => $this->algorithm,
            'typ' => 'JWT'
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->privateKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public function decode($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $header = json_decode($this->base64UrlDecode($parts[0]), true);
            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            $signature = $this->base64UrlDecode($parts[2]);

            if (!is_array($header) || !is_array($payload) || empty($header['alg']) || $header['alg'] !== $this->algorithm) {
                return null;
            }

            $expectedSignature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $this->privateKey, true);
            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }

            if (isset($payload['exp']) && time() > (int) $payload['exp']) {
                return null;
            }

            return $payload;
        } catch (Exception $e) {
            return null; // Xử lý lỗi nếu token không hợp lệ
        }
    }
}
