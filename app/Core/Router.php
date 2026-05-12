<?php
namespace App\Core;

class Router
{
    protected $routes = [];

    // Mendaftarkan rute GET
    public function get($uri, $controllerAction)
    {
        $this->routes['GET'][$uri] = $controllerAction;
    }

    // Mendaftarkan rute POST
    public function post($uri, $controllerAction)
    {
        $this->routes['POST'][$uri] = $controllerAction;
    }

    // Menjalankan Router
    // Menjalankan Router
    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // 1. Ambil URL murni yang diketik pengunjung
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 2. BERSIHKAN BASE PATH (otomatis dari BASE_URL)
        $basePath = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && $basePath !== '/') {
            if (strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
        }

        // 3. Rapikan garis miring di belakang. Jika kosong, jadikan '/'
        $uri = rtrim($uri, '/') ?: '/';

        // 4. Cari rute yang cocok di daftar
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routeUri => $controllerAction) {
                // Cocokkan URI exact match
                if ($routeUri === $uri) {
                    $this->dispatch($controllerAction);
                    return;
                }
            }
        }

        // Jika rute benar-benar tidak terdaftar di index.php
        http_response_code(404);
        echo "404 - Halaman atau Endpoint Tidak Ditemukan (URL yang ditangkap: " . $uri . ")";
    }

    // Mengeksekusi Controller dan Method
    private function dispatch($controllerAction)
    {
        list($controllerName, $method) = explode('@', $controllerAction);

        $controllerClass = "App\\Controllers\\" . $controllerName;

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                die("Method $method tidak ditemukan di $controllerClass");
            }
        } else {
            die("Class $controllerClass tidak ditemukan.");
        }
    }
}
