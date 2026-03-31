<?php
namespace App\Core;

class Controller {
    
    // Helper untuk merender halaman HTML/View
    protected function view($view, $data = []) {
        extract($data);
        $file = __DIR__ . '/../../views/' . $view . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("View '$view' tidak ditemukan.");
        }
    }

    // Helper untuk merespon API (Digunakan untuk SPA/Fetch)
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Helper untuk membaca input JSON dari Javascript (Fetch API)
    protected function getJsonInput() {
        $rawInput = file_get_contents('php://input');
        return json_decode($rawInput, true) ?? [];
    }
}