<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('games', 'Games::index');
$routes->get('stats', 'Games::stats');
$routes->get('library/toggle/(:num)', 'Home::toggleLibrary/$1');

// Game Details route with two parameters (ID and slug) for SEO and assignment requirement
$routes->get('games/show/(:num)/(:any)', 'Games::show/$1/$2');

// Auth routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// CRUD routes
$routes->get('games/add', 'Games::add');
$routes->post('games/create', 'Games::create');
$routes->get('games/edit/(:num)', 'Games::edit/$1');
$routes->post('games/update/(:num)', 'Games::update/$1');
$routes->post('games/delete/(:num)', 'Games::delete/$1');
