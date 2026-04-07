<?php
// app/Models/InventoryReceipt.php
class InventoryReceipt extends Model
{
    protected $table = 'inventory_receipts';
    public function getAll(){

$stmt=$this->db->query("
SELECT r.*, s.name as supplier_name
FROM inventory_receipts r
LEFT JOIN suppliers s
ON r.supplier_id=s.id
ORDER BY r.id DESC
");

return $stmt->fetchAll(PDO::FETCH_ASSOC);

}
    public function generateCode()
    {
        $prefix = "PN" . date("Ymd");
        $stmt = $this->db->prepare("
SELECT COUNT(*) total
FROM inventory_receipts
WHERE receipt_code LIKE ?
");
        $stmt->execute([$prefix . "%"]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] + 1;
        $number = str_pad($count, 3, "0", STR_PAD_LEFT);
        return $prefix . $number;
    }
    public function getDb(){
    return $this->db;
}
}
