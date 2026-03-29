<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load konfigurasi
require_once __DIR__ . '/../config.php';

// SPL Autoloader (Otomatis meload class berdasarkan Namespace)
spl_autoload_register(function ($class) {
    // Contoh: App\Controllers\OrderController -> app/Controllers/OrderController.php
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Inisialisasi Router
$router = new App\Core\Router();

// Daftarkan Rute Web (Halaman HTML)
$router->get('/', 'DashboardController@index');
$router->get('/admin', 'DashboardController@index');

// Rute Autentikasi (Login/Logout)
$router->get('/login', 'AuthController@loginView');
$router->post('/api/login', 'AuthController@processLogin');
$router->get('/logout', 'AuthController@logout');
$router->post('/api/logout', 'AuthController@logout');

// Daftarkan Rute API (Untuk dipanggil oleh Fetch API Javascript)
$router->get('/api/dashboard/summary', 'DashboardController@getSummaryData');
$router->get('/api/products', 'ProductController@index');
$router->post('/api/products', 'ProductController@store');
$router->post('/api/products/toggle-status', 'ProductController@toggleStatus');
$router->get('/api/orders', 'OrderController@index');
$router->post('/api/orders/update-status', 'OrderController@updateStatus');
$router->get('/api/dev/generate-order', 'OrderController@generateDummy');
$router->post('/api/products/delete', 'ProductController@delete');
$router->get('/api/payment-methods', 'PaymentMethodController@index');
$router->post('/api/payment-methods', 'PaymentMethodController@store');
$router->get('/api/settings', 'StoreSettingController@index');
$router->post('/api/settings', 'StoreSettingController@update');
$router->get('/api/orders/details', 'OrderController@details');

$router->run();