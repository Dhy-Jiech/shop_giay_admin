<?php
// app/Models/ProductVariant.php
class ProductVariant extends Model
{
    protected $table = 'product_variants';

    public function getByProduct($productId)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    public function findVariant($product_id, $size, $color)
    {

        $stmt = $this->db->prepare("
            SELECT *
            FROM product_variants
            WHERE product_id=? AND size=? AND color=?
            LIMIT 1
            ");

        $stmt->execute([$product_id, $size, $color]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
