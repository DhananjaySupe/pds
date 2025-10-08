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
$routes->post('login', 'Auth::login');
