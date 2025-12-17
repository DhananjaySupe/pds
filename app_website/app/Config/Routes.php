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