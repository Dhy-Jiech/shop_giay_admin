<?php
// app/Models/Product.php

class Product extends Model
{
    protected $table = 'products';

    // Lấy danh sách sản phẩm kèm tổng tồn kho từ tất cả biến thể
    public function getProductsWithDetails($limit = 10, $offset = 0)
    {
        $sql = "SELECT p.*, c.name as category_name,
                       (SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = p.id) as total_stock,
                       (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image_url,
                       (SELECT MIN(sale_price) FROM product_variants WHERE product_id = p.id) as min_price
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Đặt lại tất cả ảnh chính của sản phẩm về 0
    public function resetPrimaryImages($product_id)
    {
        $stmt = $this->db->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        return $stmt->execute([$product_id]);
    }

    // Thêm hoặc cập nhật ảnh chính
    public function setPrimaryImage($product_id, $url)
    {
        $this->resetPrimaryImages($product_id);
        return $this->addImage($product_id, $url, 1);
    }

    // Thêm ảnh vào thư viện
    public function addImage($product_id, $url, $is_primary = 0)
    {
        $stmt = $this->db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
        return $stmt->execute([$product_id, $url, $is_primary]);
    }

    // Thêm biến thể chuyên sâu
    public function addVariant($data)
    {
        $stmt = $this->db->prepare("INSERT INTO product_variants (product_id, size, color, import_price, sale_price, stock_quantity, sku, image_url) 
                                   VALUES (:pid, :size, :color, :iprice, :sprice, :stock, :sku, :img)");
        return $stmt->execute([
            ':pid' => $data['product_id'],
            ':size' => $data['size'],
            ':color' => $data['color'],
            ':iprice' => $data['import_price'],
            ':sprice' => $data['sale_price'],
            ':stock' => $data['stock_quantity'],
            ':sku' => $data['sku'] ?? null,
            ':img' => $data['image_url'] ?? null
        ]);
    }

    // Thêm sản phẩm mới kèm danh sách biến thể
    public function createWithVariants($productData, $variants)
    {
        try {
            $this->db->beginTransaction();

            $slug = $this->createSlug($productData['name']);
            $productData['slug'] = $slug;

            $productId = $this->insert($productData);

            if ($productId) {
                $totalStock = 0;
                foreach ($variants as $variant) {
                    $variant['product_id'] = $productId;
                    $this->addVariant($variant);
                    $totalStock += ($variant['stock_quantity'] ?? 0);
                }

                // Tự động cập nhật trạng thái dựa trên kho
                if ($totalStock <= 0) {
                    $this->update($productId, ['status' => 'Out of Stock']);
                }
                else {
                    $this->update($productId, ['status' => 'In Stock']);
                }
            }

            $this->db->commit();
            return $productId;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Lấy chi tiết sản phẩm và danh sách biến thể
    public function getFullDetail($id)
    {
        $product = $this->findById($id);
        if (!$product)
            return null;

        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = ?");
        $stmt->execute([$id]);
        $product['variants'] = $stmt->fetchAll();

        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $product['images'] = $stmt->fetchAll();

        return $product;
    }

    // Cập nhật sản phẩm và biến thể
    public function updateWithVariants($id, $productData, $variants)
    {
        try {
            $this->db->beginTransaction();

            // 1. Cập nhật thông tin chính (Tên, danh mục...)
            if (isset($productData['name'])) {
                $productData['slug'] = $this->createSlug($productData['name']);
            }
            $this->update($id, $productData);

            // 2. Xử lý biến thể
            $stmt = $this->db->prepare("SELECT id FROM product_variants WHERE product_id = ?");
            $stmt->execute([$id]);
            $currentVariantIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $newVariantIds = [];

            foreach ($variants as $v) {
                if (!empty($v['id'])) {
                    // Update existing
                    $vId = $v['id'];
                    $newVariantIds[] = $vId;
                    unset($v['id']);

                    $fields = [];
                    $values = [];
                    foreach ($v as $key => $val) {
                        $fields[] = "$key = ?";
                        $values[] = $val;
                    }
                    $values[] = $vId;
                    $sql = "UPDATE product_variants SET " . implode(', ', $fields) . " WHERE id = ?";
                    $this->db->prepare($sql)->execute($values);
                }
                else {
                    // Insert new
                    $v['product_id'] = $id;
                    $this->addVariant($v);
                }
            }

            // Xóa biến thể không còn trong danh sách gửi lên
            $toDelete = array_diff($currentVariantIds, $newVariantIds);
            if (!empty($toDelete)) {
                $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
                $sql = "DELETE FROM product_variants WHERE id IN ($placeholders) AND id NOT IN (SELECT product_variant_id FROM order_details)";
                $this->db->prepare($sql)->execute(array_values($toDelete));
            }

            // 3. Tự động kiểm tra tổng kho để cập nhật trạng thái sản phẩm gốc
            $stmtStock = $this->db->prepare("SELECT SUM(stock_quantity) FROM product_variants WHERE product_id = ?");
            $stmtStock->execute([$id]);
            $totalStock = $stmtStock->fetchColumn() ?: 0;

            if ($totalStock <= 0) {
                $this->update($id, ['status' => 'Out of Stock']);
            }
            else {
                $this->update($id, ['status' => 'In Stock']);
            }

            $this->db->commit();
            return true;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    private function createSlug($string)
    {
        $string = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $string));
        return rtrim($string, '-');
    }
}
