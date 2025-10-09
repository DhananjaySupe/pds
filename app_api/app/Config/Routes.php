<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->set404Override('App\Controllers\Errors::show404');

/* Ping to check internet connection */
$routes->group('auth', function ($routes) {
	$routes->post('login', 'Auth::login');
	$routes->post('verify_otp', 'Auth::verify_otp');
	$routes->post('register', 'Auth::register');
	$routes->delete('logout', 'Auth::logout');
	$routes->add('profile', 'Auth::profile');
	$routes->post('set_firebase_res_id', 'Auth::set_firebase_res_id');
	$routes->post('resetpasswordrequest', 'Auth::resetpasswordrequest');
	$routes->post('resetpassword', 'Auth::resetpassword');
});
