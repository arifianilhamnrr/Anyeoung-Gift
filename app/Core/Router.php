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

        // 2. BERSIHKAN NAMA FOLDER (Mantra Anti-404)
        // Kita paksa buang nama folder 'anyeong-gift' dan 'public' jika terbawa
        $uri = str_replace('/anyeong-gift/public', '', $uri);
        $uri = str_replace('/anyeong-gift', '', $uri);

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