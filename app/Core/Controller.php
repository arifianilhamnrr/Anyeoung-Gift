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

    /**
     * Kirim response JSON ke browser SEKARANG (tanpa exit), supaya kode di
     * controller setelah ini bisa lanjut jalan di background. Berguna untuk
     * tugas yang lambat (kirim email SMTP, hit API eksternal) yang harusnya
     * tidak bikin user di admin menunggu.
     *
     * Pola sama seperti `flushResponseAndContinue()` di
     * `users/actions/email-helper.php`.
     */
    protected function respondAndContinue($data, $statusCode = 200) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode);
        }
        $body = json_encode($data);

        if (function_exists('fastcgi_finish_request')) {
            echo $body;
            // ob_get_level loop biar semua output buffer di-flush dulu
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @flush();
            fastcgi_finish_request();
            return;
        }

        if (!headers_sent()) {
            header('Connection: close');
            header('Content-Length: ' . strlen($body));
        }
        echo $body;
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        @flush();

        if (function_exists('session_write_close')) {
            session_write_close();
        }
        // ignore_user_abort biar PHP terus jalan meski browser disconnect
        ignore_user_abort(true);
    }

    // Helper untuk membaca input JSON dari Javascript (Fetch API)
    protected function getJsonInput() {
        $rawInput = file_get_contents('php://input');
        return json_decode($rawInput, true) ?? [];
    }
}