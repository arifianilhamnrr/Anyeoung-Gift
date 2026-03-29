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
}