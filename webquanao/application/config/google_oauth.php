<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['google_oauth'] = array(
	'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
	'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
	'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:8080/dang-nhap/google/callback'
);
