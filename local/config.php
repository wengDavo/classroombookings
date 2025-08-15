<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return array(

	'config' => array(
		'base_url' => 'http://localhost/crbs/',
		'log_threshold' => 2,
		'index_page' => 'index.php',
		'uri_protocol' => 'REQUEST_URI',
	),

	'database' => array(
		'hostname' => 'localhost',
		'port' => '3306',
		'username' => 'catss',
		'password' => 'catss',
		'database' => 'catss',
		'dbdriver' => 'mysqli',
	),

);
