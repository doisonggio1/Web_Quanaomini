<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['vnp_TmnCode'] = getenv('VNPAY_TMNCODE'); 
$config['vnp_HashSecret'] = getenv('VNPAY_HASHSECRET'); 
$config['vnp_Url'] = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'; 
$config['vnp_Returnurl'] = getenv('VNPAY_RETURN_URL') ?: 'http://localhost:8080/vnpay/callback'; 
$config['vnp_Command'] = 'pay';
$config['vnp_CurrCode'] = 'VND';
$config['vnp_Locale'] = 'vn';
$config['vnp_BankCode'] = getenv('VNPAY_BANKCODE') ?: '';