<?php
// app/Models/InventoryReceiptDetail.php
class InventoryReceiptDetail extends Model
{
    protected $table = 'inventory_receipt_details';
    public function getDb(){
return $this->db;
}
}
