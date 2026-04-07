<?php
// app/Models/OrderStatusHistory.php
class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';

    public function getByOrder($orderId)
    {
        $stmt = $this->db->prepare("SELECT h.*, u.full_name as user_name 
                                   FROM {$this->table} h 
                                   LEFT JOIN users u ON h.changed_by = u.id 
                                   WHERE order_id = ? ORDER BY h.created_at DESC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}
