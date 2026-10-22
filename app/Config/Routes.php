<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Halaman Publik ────────────────────────────────────────────────────────
$routes->get('/', 'Home::index');
$routes->get('track/(:num)', 'Home::track/$1');

// ── Auth (Web) ────────────────────────────────────────────────────────────
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::processLogin');
$routes->get('logout', 'Auth::logout');

// ── Dasbor Admin (Protected by adminAuth Filter) ──────────────────────────
$routes->group('admin', ['filter' => 'adminAuth'], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->post('orders/update-status', 'Admin\Dashboard::updateStatus');
    $routes->post('orders/delete', 'Admin\Dashboard::deleteOrder');
    $routes->post('orders/delete-batch', 'Admin\Dashboard::deleteBatchOrders');
    $routes->post('store-status', 'Admin\Dashboard::toggleStoreStatus');

    // CRUD Item Katalog
    $routes->post('items/create', 'Admin\Item::create');
    $routes->post('items/update/(:num)', 'Admin\Item::update/$1');
    $routes->post('items/toggle-status/(:num)', 'Admin\Item::toggleStatus/$1');
    $routes->post('items/delete/(:num)', 'Admin\Item::delete/$1');
});

// ── API Routes ────────────────────────────────────────────────────────────
$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes): void {

    // ── Catalog ──────────────────────────────────────────────
    $routes->get('catalog', 'Catalog::index');
    $routes->get('catalog/items', 'Catalog::items');
    $routes->get('catalog/search', 'Catalog::search');
    $routes->get('catalog/category/(:num)', 'Catalog::byCategory/$1');

    // ── Booking & Store Status ───────────────────────────────
    $routes->post('booking/create', 'Booking::create');
    $routes->get('booking/slots', 'Booking::slots');
    $routes->get('store-status', 'Booking::storeStatus');

    // ── Webhook Worker (Pseudo-Cron) ─────────────────────────
    $routes->get('webhook/worker', 'WebhookWorker::processPending');

});
