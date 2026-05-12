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
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Data dasar untuk layout
        $data = [
            'title' => 'Dashboard - Anyeong Gift',
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
        // 🔒 2. GEMBOK KEAMANAN API
        if (!isset($_SESSION['admin_logged_in'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Akses ditolak. Silakan login.'], 403);
        }

        $orderService = new \App\Services\OrderService();

        try {
            $summary = $orderService->getSummary();
            $allOrders = $orderService->getAllOrders();
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