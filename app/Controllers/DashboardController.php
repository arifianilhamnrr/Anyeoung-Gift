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

        // Data dasar untuk layout. Nama toko diambil dari pengaturan
        // (fallback "Anyeong Gift") supaya konsisten di seluruh tampilan.
        $storeName = storeNameRaw();
        $data = [
            'title' => 'Dashboard - ' . $storeName,
            'admin_name' => $_SESSION['admin_name'] ?? 'Admin',
            'store_name' => $storeName,
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

        // Filter bulan / tahun untuk rekap. Kalau salah satu tidak valid,
        // tetap fallback ke total keseluruhan (perilaku lama).
        $month = isset($_GET['month']) && ctype_digit((string) $_GET['month']) ? (int) $_GET['month'] : null;
        $year = isset($_GET['year']) && ctype_digit((string) $_GET['year']) ? (int) $_GET['year'] : null;
        if ($month !== null && ($month < 1 || $month > 12)) {
            $month = null;
        }
        if ($year !== null && ($year < 2000 || $year > 2100)) {
            $year = null;
        }
        // Wajib pasangan: kalau cuma satu yang valid, abaikan keduanya
        // supaya query SQL konsisten.
        if ($month === null || $year === null) {
            $month = null;
            $year = null;
        }

        try {
            $summary = $orderService->getSummary($month, $year);
            $recentOrders = $orderService->getRecentOrdersForDashboard($month, $year, 5);

            $data = [
                'total_revenue' => (int) $summary['total_revenue'],
                'active_orders' => (int) $summary['active_orders'],
                'pending_payments' => (int) $summary['pending_payments'],
                'recent_orders' => $recentOrders,
                'period' => [
                    'month' => $month,
                    'year' => $year,
                ]
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