<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->set404Override('App\Controllers\Errors::show404');

/* Ping to check internet connection */
$routes->get('ping', 'Pages::ping');
$routes->get('captcha', 'Captcha::generate');
$routes->get('terms-of-use', 'Pages::termsOfUse');

$routes->get('/', 'Home::index');
$routes->get('/dashboard', 'Dashboard::index');

// Language routes
$routes->post('/language/change', 'Language::change');
$routes->get('/language/current', 'Language::getCurrent');

$routes->add('/login', 'Auth::login');
$routes->add('/register', 'Auth::register');
$routes->add('/forgot-password', 'Auth::forgotPassword');
$routes->add('/forgot-password/(:any)', 'Auth::forgotPassword/$1');
$routes->add('/logout', 'Auth::logout');
$routes->add('/welcome/(:any)', 'Auth::welcome/$1');
$routes->add('/verify-otp/(:any)', 'Auth::verifyOtp/$1');
$routes->add('/profile', 'Auth::profile');


$routes->group('users', function($routes) {
	$routes->add('/', 'Users::index');
	$routes->get('export', 'Users::export');
	$routes->add('new', 'Users::new');
	$routes->add('edit/(:num)', 'Users::edit/$1');
	$routes->add('delete/(:num)', 'Users::delete/$1');
	$routes->add('view/(:num)', 'Users::view/$1');
});

$routes->group('products', function($routes) {
	$routes->add('/', 'Products::index');
	$routes->get('export', 'Products::export');
	$routes->get('search', 'Products::search');
	$routes->add('new', 'Products::new');
	$routes->add('edit/(:num)', 'Products::edit/$1');
	$routes->add('delete/(:num)', 'Products::delete/$1');
	$routes->add('view/(:num)', 'Products::view/$1');
});

$routes->group('purchase-orders', function($routes) {
	$routes->add('/', 'PurchaseOrders::index');
	$routes->get('export', 'PurchaseOrders::export');
	$routes->add('new', 'PurchaseOrders::new');
	$routes->add('edit/(:num)', 'PurchaseOrders::edit/$1');
	$routes->add('delete/(:num)', 'PurchaseOrders::delete/$1');
	$routes->add('view/(:num)', 'PurchaseOrders::view/$1');
});

$routes->group('customers', function($routes) {
	$routes->get('search', 'Customers::search');
});

$routes->group('sales', function($routes) {
	$routes->add('/', 'Sales::index');
	$routes->get('export', 'Sales::export');
	$routes->get('search-qr', 'Sales::searchQr');
	$routes->get('qr-info', 'Sales::qrInfo');
	$routes->add('new', 'Sales::new');
	$routes->add('edit/(:num)', 'Sales::edit/$1');
	$routes->add('delete/(:num)', 'Sales::delete/$1');
	$routes->add('view/(:num)', 'Sales::view/$1');
});

$routes->group('stock-transfers', function($routes) {
	$routes->add('/', 'StockTransfers::index');
	$routes->get('export', 'StockTransfers::export');
	$routes->get('search-qr', 'StockTransfers::searchQr');
	$routes->get('qr-info', 'StockTransfers::qrInfo');
	$routes->add('new', 'StockTransfers::new');
	$routes->add('edit/(:num)', 'StockTransfers::edit/$1');
	$routes->add('delete/(:num)', 'StockTransfers::delete/$1');
	$routes->add('view/(:num)', 'StockTransfers::view/$1');
});

$routes->group('shop-requests', function($routes) {
	$routes->add('/', 'ShopRequests::index');
	$routes->get('export', 'ShopRequests::export');
	$routes->add('new', 'ShopRequests::new');
	$routes->add('edit/(:num)', 'ShopRequests::edit/$1');
	$routes->add('delete/(:num)', 'ShopRequests::delete/$1');
	$routes->add('view/(:num)', 'ShopRequests::view/$1');
});

$routes->group('reports', function($routes) {
	$routes->add('/', 'Reports::index');
	$routes->get('vendor', 'Reports::vendor');
	$routes->get('godown', 'Reports::godown');
	$routes->get('shop', 'Reports::shop');
	$routes->get('export', 'Reports::export');
	$routes->get('pdf-export', 'Reports::pdfExport');
});

// Alias for sales
$routes->group('sell', function($routes) {
	$routes->add('/', 'Sales::index');
	$routes->get('export', 'Sales::export');
	$routes->add('new', 'Sales::new');
	$routes->add('edit/(:num)', 'Sales::edit/$1');
	$routes->add('delete/(:num)', 'Sales::delete/$1');
	$routes->add('view/(:num)', 'Sales::view/$1');
});
