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
$routes->get('ping', 'Pages::ping');
$routes->get('captcha', 'Captcha::generate');
$routes->get('terms-of-use', 'Pages::termsOfUse');

$routes->get('/', 'Dashboard::index');
$routes->get('/get/centers', 'Ajax::centers');
$routes->get('/get/state', 'Ajax::state');
$routes->get('/get/district', 'Ajax::district');
$routes->post('/get/lostpeopledetails', 'Ajax::lostpeopledetails');
$routes->post('/get/foundpeopledetails', 'Ajax::foundpeopledetails');

// Export and Download routes
$routes->post('/ajax/downloadExcel', 'Ajax::downloadExcel');
$routes->post('/ajax/downloadCsv', 'Ajax::downloadCsv');
$routes->get('/download/(:any)', 'Download::index/$1');

$routes->group('api', function($routes) {
    $routes->post('detect-language', 'Api::detectLanguage');
    $routes->post('convert-to-text', 'Api::convertToText');
    $routes->post('translate-text', 'Api::translateText');
});

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

// Auth Lost
$routes->group('lost-people', function($routes) {
	$routes->add('/', 'LostPeople::index');
	$routes->add('new', 'LostPeople::new');
	$routes->add('edit/(:any)', 'LostPeople::edit/$1');
	$routes->add('view/(:any)', 'LostPeople::view/$1');
	$routes->add('save-photo', 'LostPeople::savePhoto');
	$routes->add('mind-match-found-profile', 'LostPeople::mindMatchFoundProfile');
});

// Auth Found
$routes->group('found-people', function($routes) {
	$routes->add('/', 'FoundPeople::index');
	$routes->add('new', 'FoundPeople::new');
	$routes->add('edit/(:any)', 'FoundPeople::edit/$1');
	$routes->add('view/(:any)', 'FoundPeople::view/$1');
	$routes->add('save-photo', 'FoundPeople::savePhoto');
	$routes->add('mind-match-lost-profile', 'FoundPeople::mindMatchLostProfile');
});

$routes->group('reports', function($routes) {
	$routes->add('master', 'Reports::master');
	$routes->add('lost-people', 'LostPeopleReports::index');
	$routes->add('found-people', 'FoundPeopleReports::index');
	$routes->add('calling', 'CallingReports::index');
	$routes->add('reunite', 'ReuniteReports::index');
	$routes->add('handover', 'HandoverReports::index');
	$routes->add('other', 'OtherReports::index');
	$routes->add('other/sla-compliance', 'OtherReports::slaCompliance');
	$routes->add('other/attendance-report', 'OtherReports::attendanceReport');
});

$routes->group('reunite', function($routes) {
	$routes->add('/', 'Reunite::index');
	$routes->add('save-reunite', 'Reunite::saveReunite');
	$routes->add('generate-pdf', 'Reunite::generatePDF');
});

$routes->group('handover', function($routes) {
	$routes->add('/', 'Handover::index');
	$routes->add('save-handover', 'Handover::saveHandover');
});

$routes->group('calling', function($routes) {
	$routes->add('/', 'Calling::index');
	$routes->add('make-call', 'Calling::makeCall');
	$routes->add('save-call-log', 'Calling::saveCallLog');
	$routes->add('get-call-history', 'Calling::getCallHistory');
	$routes->add('update-status', 'Calling::updateStatus');
	$routes->add('get-statistics', 'Calling::getStatistics');
});
// User Reports
$routes->group('users', function($routes) {
	$routes->add('/', 'User::index');
	$routes->add('new', 'User::new');
	$routes->add('edit/(:any)', 'User::edit/$1');
	$routes->add('view/(:any)', 'User::view/$1');
	$routes->add('save-photo', 'User::savePhoto');
	$routes->add('reset-password', 'User::resetPassword');
	$routes->add('logout-user', 'User::logoutUser');
	$routes->add('delete', 'User::delete');
	$routes->add('export-excel', 'User::exportExcel');
});

$routes->group('tv-configuration', function($routes) {
	$routes->add('/', 'TvConfiguration::index');
	$routes->add('new', 'TvConfiguration::new');
	$routes->add('edit/(:any)', 'TvConfiguration::edit/$1');
	$routes->add('view/(:any)', 'TvConfiguration::view/$1');
	$routes->add('delete/(:any)', 'TvConfiguration::delete/$1');
	$routes->add('change-status/(:any)', 'TvConfiguration::changeStatus/$1');
});

$routes->get('announcement', 'Announcement::index');
$routes->get('announcement/get-lost-people', 'Announcement::getLostPeople');
$routes->get('announcement/get-found-people', 'Announcement::getFoundPeople');
$routes->get('announcement/get-lost-people-sidebar', 'Announcement::getLostPeopleSidebar');
$routes->get('announcement/get-found-people-sidebar', 'Announcement::getFoundPeopleSidebar');