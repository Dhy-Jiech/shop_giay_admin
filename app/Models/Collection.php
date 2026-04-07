<?php
// app/Models/Collection.php
require_once dirname(__DIR__) . '/Core/Model.php';

class Collection extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll()
    {
        $query = "SELECT * FROM collections ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM collections WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO collections (name, slug, description, banner_image, start_date, end_date, status) 
                  VALUES (:name, :slug, :description, :banner_image, :start_date, :end_date, :status)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'],
            ':banner_image' => $data['banner_image'],
            ':start_date' => $data['start_date'] !== '' ? $data['start_date'] : null,
            ':end_date' => $data['end_date'] !== '' ? $data['end_date'] : null,
            ':status' => $data['status'] ?? 1
        ]);
    }

    public function update($id, $data)
    {
        $query = "UPDATE collections SET 
                  name = :name, 
                  slug = :slug, 
                  description = :description, 
                  banner_image = :banner_image,
                  start_date = :start_date,
                  end_date = :end_date,
                  status = :status 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'],
            ':banner_image' => $data['banner_image'],
            ':start_date' => $data['start_date'] !== '' ? $data['start_date'] : null,
            ':end_date' => $data['end_date'] !== '' ? $data['end_date'] : null,
            ':status' => $data['status'] ?? 1
        ]);
    }

    public function delete($id)
    {
        $query = "DELETE FROM collections WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function toggleStatus($id)
    {
        $query = "UPDATE collections SET status = NOT status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // --- Mối quan hệ với Product ---

    public function getProducts($collectionId)
    {
        $query = "SELECT p.id, p.name, p.slug, p.status, c.name as category_name
                  FROM products p
                  JOIN collection_products cp ON p.id = cp.product_id
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE cp.collection_id = :collection_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':collection_id', $collectionId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductIds($collectionId)
    {
        $query = "SELECT product_id FROM collection_products WHERE collection_id = :collection_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':collection_id', $collectionId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Trả về mảng 1 chiều chứa các id
    }

    public function syncProducts($collectionId, $productIds)
    {
        try {
            $this->db->beginTransaction();

            // 1. Xóa tất cả các liên kết cũ
            $queryDelete = "DELETE FROM collection_products WHERE collection_id = :collection_id";
            $stmtDelete = $this->db->prepare($queryDelete);
            $stmtDelete->bindParam(':collection_id', $collectionId);
            $stmtDelete->execute();

            // 2. Thêm các liên kết mới
            if (!empty($productIds)) {
                $queryInsert = "INSERT INTO collection_products (collection_id, product_id) VALUES (:collection_id, :product_id)";
                $stmtInsert = $this->db->prepare($queryInsert);

                foreach ($productIds as $productId) {
                    $stmtInsert->execute([
                        ':collection_id' => $collectionId,
                        ':product_id' => $productId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error syncing products for collection: " . $e->getMessage());
            return false;
        }
    }
}
