<?php
// app/Models/Customer.php

class Customer extends Model
{
    protected $table = 'customers';

    public function findAllWithStats($params = [])
    {
        $sql = "SELECT c.*, ct.name as tier_name 
                FROM {$this->table} c 
                LEFT JOIN customer_tiers ct ON c.tier_id = ct.id 
                WHERE 1=1";
        $bindParams = [];

        if (!empty($params['search'])) {
            $sql .= " AND (c.full_name LIKE :search OR c.phone LIKE :search OR c.email LIKE :search)";
            $bindParams[':search'] = '%' . $params['search'] . '%';
        }

        if (!empty($params['tier_id'])) {
            $sql .= " AND c.tier_id = :tier_id";
            $bindParams[':tier_id'] = $params['tier_id'];
        }

        $sql .= " ORDER BY c.total_spent DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindParams);
        return $stmt->fetchAll();
    }

    // Tính toán lại hạng thành viên dựa trên tổng chi tiêu
    public function updateTier($customer_id)
    {
        $customer = $this->findById($customer_id);
        if (!$customer)
            return false;

        $total_spent = $customer['total_spent'];

        // Lấy tất cả tiers sắp xếp giảm dần theo min_spent
        $stmt = $this->db->prepare("SELECT * FROM customer_tiers ORDER BY min_spent DESC");
        $stmt->execute();
        $tiers = $stmt->fetchAll();

        $new_tier_id = null;
        foreach ($tiers as $tier) {
            if ($total_spent >= $tier['min_spent']) {
                $new_tier_id = $tier['id'];
                break;
            }
        }

        if ($new_tier_id && $new_tier_id != $customer['tier_id']) {
            $this->update($customer_id, ['tier_id' => $new_tier_id]);
            return true;
        }

        return false;
    }
}
