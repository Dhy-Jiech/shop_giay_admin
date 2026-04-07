<?php
// app/Models/Promotion.php

class Promotion extends Model
{
    protected $table = 'promotions';

    public function getActivePromotions()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE start_date <= CURDATE() AND end_date >= CURDATE() AND status = 1");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByCode($code)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function checkPromotion($code, $amount)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE code = :code AND status = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()");
        $stmt->execute([':code' => $code]);
        $promo = $stmt->fetch();

        if ($promo && $amount >= $promo['min_order_value']) {
            if ($promo['usage_limit'] && $promo['used_count'] >= $promo['usage_limit'])
                return false;
            return $promo;
        }
        return false;
    }

    public function incrementUsedCount($id)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (code, name, discount_type, discount_value, min_order_value, max_discount_amount, start_date, end_date, usage_limit, status) 
                VALUES (:code, :name, :discount_type, :discount_value, :min_order_value, :max_discount_amount, :start_date, :end_date, :usage_limit, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updatePromo($id, $data)
    {
        $fields = "";
        foreach ($data as $key => $val) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ", ");
        $sql = "UPDATE {$this->table} SET $fields WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleStatus($id)
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = NOT status WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
