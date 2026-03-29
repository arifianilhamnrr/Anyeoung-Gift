<?php
namespace App\Controllers;
use App\Core\Controller;
use App\Models\PaymentMethodModel;

class PaymentMethodController extends Controller {
    public function index() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);
        
        try {
            $model = new PaymentMethodModel();
            return $this->jsonResponse(['status' => 'success', 'data' => $model->getAll()]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store() {
        if (!isset($_SESSION['admin_logged_in'])) return $this->jsonResponse(['status' => 'error'], 401);
        
        $data = $this->getJsonInput();
        if (empty($data['name']) || empty($data['type'])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Nama dan Tipe wajib diisi!'], 400);
        }

        try {
            $model = new PaymentMethodModel();
            $model->addMethod($data['name'], $data['type'], $data['account_info'] ?? '');
            return $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}