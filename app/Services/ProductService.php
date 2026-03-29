<?php
namespace App\Services;

use App\Models\ProductModel;
use Exception;

class ProductService
{

    private $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    /**
     * Menyimpan Produk + Opsi + Nilai Opsi sekaligus (Database Transaction)
     */
    // Ubah parameternya untuk menerima $fileGambar
    public function createCustomProduct($data, $fileGambar = null)
    {
        try {
            $this->productModel->beginTransaction();

            // LOGIKA UPLOAD GAMBAR
            $namaFileGambar = null;
            if ($fileGambar && $fileGambar['error'] === UPLOAD_ERR_OK) {
                // Ambil ekstensi asli (jpg/png)
                $ext = pathinfo($fileGambar['name'], PATHINFO_EXTENSION);
                // Buat nama unik (Contoh: prod_65a1b2c.jpg)
                $namaFileGambar = uniqid('prod_') . '.' . $ext;
                // Tentukan lokasi simpan
                $lokasiSimpan = __DIR__ . '/../../public/uploads/products/' . $namaFileGambar;

                // Pindahkan file dari memori sementara ke folder
                if (!move_uploaded_file($fileGambar['tmp_name'], $lokasiSimpan)) {
                    throw new Exception("Gagal memindahkan file gambar ke folder uploads.");
                }
            }

            // Simpan Data Utama ke tabel 'products' (Tambahkan $namaFileGambar di akhir)
            $productId = $this->productModel->insertProduct(
                $data['name'],
                $data['category'] ?? 'Uncategorized',
                $data['product_type'] ?? 'custom_full',
                $data['description'] ?? '',
                $data['base_price'],
                $namaFileGambar
            );

            // ... (Biarkan kode looping insertProductOption dan Values di bawahnya sama persis seperti sebelumnya) ...

            // 3. Jika ada Opsi Kustomisasi (Ukuran, Warna, dll)
            if (isset($data['options']) && is_array($data['options'])) {

                foreach ($data['options'] as $opt) {
                    // Simpan ke tabel 'product_options'
                    $optionId = $this->productModel->insertProductOption(
                        $productId,
                        $opt['option_name'],
                        $opt['option_type'] ?? 'single',
                        $opt['is_required'] ?? 1
                    );

                    // 4. Jika opsi tersebut punya nilai pilihan (Small, Large, dsb)
                    if (isset($opt['values']) && is_array($opt['values'])) {
                        foreach ($opt['values'] as $val) {
                            // Simpan ke tabel 'product_option_values'
                            $this->productModel->insertProductOptionValue(
                                $optionId,
                                $val['value_name'],
                                $val['additional_price'] ?? 0
                            );
                        }
                    }
                }
            }

            // 5. Jika semua baris di atas sukses, simpan secara permanen
            $this->productModel->commit();
            return $productId;

        } catch (Exception $e) {
            // Jika ada satu saja yang gagal, batalkan semua perubahan di database
            $this->productModel->rollBack();
            throw new Exception("Proses simpan gagal: " . $e->getMessage());
        }
    }
}