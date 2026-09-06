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

    public function exportIncome()
    {
        if (!isset($_SESSION['admin_logged_in'])) { http_response_code(403); exit('Akses ditolak.'); }
        $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]);
        $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2100]]);
        if ($month === false || $year === false || $month === null || $year === null) { $month = $year = null; }
        $format = $_GET['format'] ?? 'csv';
        if (!in_array($format, ['csv', 'pdf'], true)) { http_response_code(400); exit('Format tidak didukung.'); }
        $rows = (new \App\Services\OrderService())->getIncomeRows($month, $year);
        $period = ($month && $year) ? date('F Y', mktime(0, 0, 0, $month, 1, $year)) : 'Semua periode';
        $total = array_sum(array_map(static fn($row) => (int) $row['total_price'], $rows));
        $filename = 'penghasilan-' . date('Y-m-d');
        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8'); header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
            $csvSafe = static function ($value) {
                $value = (string) $value;
                return preg_match('/^[=+\\-@]/', $value) ? "'" . $value : $value;
            };
            fputcsv($out, ['Laporan Penghasilan', $period]); fputcsv($out, []); fputcsv($out, ['Order ID', 'Tanggal', 'Pelanggan', 'Status', 'Metode Pembayaran', 'Total']);
            foreach ($rows as $row) fputcsv($out, ['ORD-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT), $row['created_at'], $csvSafe($row['customer_name'] ?? 'Anonim'), $csvSafe($row['status']), $csvSafe($row['payment_method_name'] ?? '-'), (int) $row['total_price']]);
            fputcsv($out, []); fputcsv($out, ['TOTAL', '', '', '', '', $total]); fclose($out); exit;
        }
        header('Content-Type: text/html; charset=UTF-8'); header('Content-Disposition: attachment; filename="' . $filename . '.html"');
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><meta charset="utf-8"><title>Laporan Penghasilan</title><style>body{font:14px Arial;margin:30px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccc;padding:8px;text-align:left}</style><h1>Laporan Penghasilan</h1><p>Periode: ' . $e($period) . '</p><table><tr><th>Order ID</th><th>Tanggal</th><th>Pelanggan</th><th>Status</th><th>Pembayaran</th><th>Total</th></tr>';
        foreach ($rows as $row) echo '<tr><td>' . $e('ORD-' . str_pad($row['id'], 5, '0', STR_PAD_LEFT)) . '</td><td>' . $e($row['created_at']) . '</td><td>' . $e($row['customer_name'] ?? 'Anonim') . '</td><td>' . $e($row['status']) . '</td><td>' . $e($row['payment_method_name'] ?? '-') . '</td><td>Rp ' . number_format((int) $row['total_price'], 0, ',', '.') . '</td></tr>';
        echo '<tr><th colspan="5">TOTAL</th><th>Rp ' . number_format($total, 0, ',', '.') . '</th></tr></table>'; exit;
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