<?php
namespace App\Models;

use App\Core\Model;

class ProductModel extends Model
{

    // Mengambil semua produk beserta jumlah opsinya (menggunakan LEFT JOIN & GROUP BY)
    public function getAllProducts()
    {
        $sql = "SELECT p.*, COUNT(DISTINCT po.id) as total_options 
                FROM products p 
                LEFT JOIN product_options po ON p.id = po.product_id 
                GROUP BY p.id 
                ORDER BY p.id DESC";

        $this->query($sql);
        return $this->resultSet();
    }

    public function insertProduct($name, $category, $productType, $description, $basePrice, $image = null)
    {
        $sql = "INSERT INTO products (name, category, product_type, description, base_price, image) 
                VALUES (:name, :category, :type, :desc, :price, :image)";

        $this->query($sql);
        $this->bind(':name', $name);
        $this->bind(':category', $category);
        $this->bind(':type', $productType);
        $this->bind(':desc', $description);
        $this->bind(':price', $basePrice);
        $this->bind(':image', $image); // Bind data gambar

        $this->execute();
        return $this->lastInsertId();
    }

    // 2. Insert ke tabel product_options
    public function insertProductOption($productId, $optionName, $optionType, $isRequired)
    {
        $sql = "INSERT INTO product_options (product_id, option_name, option_type, is_required) 
                VALUES (:pid, :name, :type, :req)";

        $this->query($sql);
        $this->bind(':pid', $productId);
        $this->bind(':name', $optionName);
        $this->bind(':type', $optionType); // enum: 'single', 'multiple', 'custom_input'
        $this->bind(':req', $isRequired);

        $this->execute();
        return $this->lastInsertId(); // Mengembalikan ID opsi
    }

    // 3. Insert ke tabel product_option_values
    public function insertProductOptionValue($optionId, $valueName, $additionalPrice)
    {
        $sql = "INSERT INTO product_option_values (option_id, value_name, additional_price) 
                VALUES (:oid, :vname, :price)";

        $this->query($sql);
        $this->bind(':oid', $optionId);
        $this->bind(':vname', $valueName);
        $this->bind(':price', $additionalPrice);

        $this->execute();
    }
    // Mengubah status aktif/nonaktif produk (Soft Delete)
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE products SET is_active = :status WHERE id = :id";
        $this->query($sql);
        $this->bind(':status', $status);
        $this->bind(':id', $id);
        return $this->execute();
    }

    public function deleteProduct($id)
    {
        try {
            // Hapus dari tabel products (Jika database Anda menggunakan ON DELETE CASCADE, 
            // maka data di tabel opsi akan otomatis terhapus juga)
            $this->query("DELETE FROM products WHERE id = :id");
            $this->bind(':id', $id);
            $this->execute();
            
            return true;
        } catch (\PDOException $e) {
            // Jika error karena produk ini sudah pernah dibeli (terikat Foreign Key di order_items)
            if ($e->getCode() == '23000') {
                throw new \Exception("Produk ini tidak bisa dihapus karena sudah ada di riwayat pesanan pelanggan. Silakan gunakan fitur 'Nonaktifkan' saja.");
            }
            throw new \Exception($e->getMessage());
        }
    }


    // detail on page product
    public function getProductDetails($id) {
        // Ambil data produk utama
        $this->query("SELECT * FROM products WHERE id = :id");
        $this->bind(':id', $id);
        $product = $this->single();
        
        if (!$product) return null;

        // Ambil nama grup opsinya
        $this->query("SELECT * FROM product_options WHERE product_id = :id");
        $this->bind(':id', $id);
        $options = $this->resultSet();

        // Ambil nilai pilihan harganya untuk setiap grup opsi
        foreach ($options as &$opt) {
            $this->query("SELECT * FROM product_option_values WHERE option_id = :opt_id");
            $this->bind(':opt_id', $opt['id']);
            $opt['values'] = $this->resultSet();
        }
        
        $product['options'] = $options;
        return $product;
    }

    // Fungsi Update Produk
    public function updateProduct($id, $data, $imageName = null) {
        // 1. Update data utama produk
        $sql = "UPDATE products SET name = :name, category = :category, base_price = :base_price";
        if ($imageName) $sql .= ", image = :image";
        $sql .= " WHERE id = :id";
        
        $this->query($sql);
        $this->bind(':name', $data['name']);
        $this->bind(':category', $data['category']);
        $this->bind(':base_price', $data['base_price']);
        $this->bind(':id', $id);
        if ($imageName) $this->bind(':image', $imageName);
        $this->execute();

        // 2. Trik Jitu Update Opsi: Hapus opsi lama, masukkan opsi baru (lebih aman & bersih)
        $this->query("SELECT id FROM product_options WHERE product_id = :id");
        $this->bind(':id', $id);
        $oldOptions = $this->resultSet();
        
        foreach($oldOptions as $opt) {
            $this->query("DELETE FROM product_option_values WHERE option_id = :opt_id");
            $this->bind(':opt_id', $opt['id']);
            $this->execute();
        }
        $this->query("DELETE FROM product_options WHERE product_id = :id");
        $this->bind(':id', $id);
        $this->execute();

        // 3. Masukkan opsi yang baru di-edit
        if (!empty($data['options'])) {
            foreach ($data['options'] as $opt) {
                $this->query("INSERT INTO product_options (product_id, option_name, option_type) VALUES (:pid, :name, 'single')");
                $this->bind(':pid', $id);
                $this->bind(':name', $opt['option_name']);
                $this->execute();
                $optionId = $this->lastInsertId();

                foreach ($opt['values'] as $val) {
                    $this->query("INSERT INTO product_option_values (option_id, value_name, additional_price) VALUES (:oid, :vname, :vprice)");
                    $this->bind(':oid', $optionId);
                    $this->bind(':vname', $val['value_name']);
                    $this->bind(':vprice', $val['additional_price'] ?? 0);
                    $this->execute();
                }
            }
        }
        return true;
    }
}