<?php
namespace App\Controllers;

use App\Core\Controller;

class DashboardController extends Controller
{

    /**
     * Menampilkan kerangka utama web (SPA Shell)
     * Method: GET /admin
     */
    public function index()
    {
        // 🔒 1. GEMBOK KEAMANAN HALAMAN WEB
        if (!isset($_SESSION['admin_logged_in'])) {
            header('Location: /anyeong-gift/public/login');
            exit;
        }

        // Data dasar untuk layout
        $data = [
            'title' => 'Dashboard - Anyeong Gift',
            // Mengambil nama asli admin yang sedang login dari Session
            'admin_name' => $_SESSION['admin_name'] ?? 'Admin'
        ];

        // Memanggil view utama
        $this->view('admin/index', $data);
    }

    /**
     * Endpoint API untuk memberikan data statistik
     * Method: GET /api/dashboard/summary
     */
    public function getSummaryData()
    {
        // 🔒 2. GEMBOK KEAMANAN API (Agar data tidak bisa ditembak lewat Postman/Hacker)
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Akses ditolak. Silakan login.'], 403);
        }

        // Gunakan Service Layer untuk mengambil data dari Database Anda
        $orderService = new \App\Services\OrderService();

        try {
            // Ambil summary angka
            $summary = $orderService->getSummary();

            // Ambil data tabel
            $allOrders = $orderService->getAllOrders();
            // Ambil 5 pesanan terbaru saja untuk di dashboard
            $recentOrders = array_slice($allOrders, 0, 5);

            $data = [
                'total_revenue' => (int) $summary['total_revenue'],
                'active_orders' => (int) $summary['active_orders'],
                'pending_payments' => (int) $summary['pending_payments'],
                'recent_orders' => $recentOrders
            ];

            return $this->jsonResponse([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}