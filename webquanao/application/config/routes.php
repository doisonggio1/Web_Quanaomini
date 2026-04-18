<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

$route['dang-nhap'] = 'user/login';
$route['dang-nhap/google'] = 'user/google_login';
$route['dang-nhap/google/callback'] = 'user/google_callback';
$route['dang-ky'] = 'user/register';
$route['ban-chay'] = 'product/hot';
$route['moi'] = 'product/news';
$route['khuyen-mai'] = 'product/discount';
$route['(:any)-c(:num)'] = 'product/catalog/$2';
$route['(:any)-p(:num)'] = 'product/view/$2';
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//Update
$route['xac-thuc-mail/(:any)'] = 'validation/index/$1';
$route['image-search'] = 'product/image_search';
$route['text-search'] = 'product/text_search';
$route['tim-kiem-ket-qua'] = 'product/tim_kiem_ket_qua';
$route['quen-mat-khau'] = 'user/forgotpassword';
$route['doi-mat-khau'] = 'user/changepassword';
$route['verify-recaptcha'] = 'validation/verify_recaptcha';
$route['doi-mat-khau/(:any)'] = 'validation/changepassword/$1';

// Payment routes
$route['vnpay/payment'] = 'order/payment';
$route['vnpay/callback'] = 'order/callback';
$route['hook/sepay-payment'] = 'order/sepay';

// Shipping API routes
$route['api/shipping2/register'] = 'api/Shipping2/register';
$route['api/shipping2/status/(:any)'] = 'api/Shipping2/status/$1';
$route['api/shipping2/update_status/(:any)'] = 'api/Shipping2/update_status/$1';
$route['api/shipping2/webhook'] = 'api/Shipping2/webhook';
$route['api/shipping2/confirm_delivery/(:any)'] = 'api/Shipping2/confirm_delivery/$1';

// Webhook endpoint
$route['api/shipping/webhook'] = 'api/Shipping/webhook';

// Admin shipping routes
$route['admin/shipping/confirm_delivery/(:any)'] = 'admin/shipping/confirm_delivery/$1';
//update-delete-infomation-user
$route['update-info'] = 'user/update_info';
$route['delete-info'] = 'user/delete_info';

//Read address info file
$route['api/read-json'] = 'DiaGioiHanhChinhVN';

// Check password
$route['check-password'] = 'user/checkpassword';

//shipping fee rule
$route['shipping-fee'] = 'order/shipping_fee_rule';

//Voucher
$route['get-voucher'] = 'order/get_voucher';
$route['check-gift-code'] = 'order/check_gift_code';
