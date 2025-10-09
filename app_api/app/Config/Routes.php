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

/* Users API Routes */
$routes->group('users', function ($routes) {
	$routes->get('index', 'Users::index');
	$routes->get('details/(:num)', 'Users::details/$1');
	$routes->post('new', 'Users::new');
	$routes->post('edit/(:num)', 'Users::edit/$1');
	$routes->delete('delete/(:num)', 'Users::delete/$1');
});

/* Products API Routes */
$routes->group('products', function ($routes) {
	$routes->get('index', 'Products::index');
	$routes->get('details/(:num)', 'Products::details/$1');
	$routes->post('new', 'Products::new');
	$routes->post('edit/(:num)', 'Products::edit/$1');
	$routes->delete('delete/(:num)', 'Products::delete/$1');
});
