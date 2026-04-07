<?php
// app/Models/Category.php

class Category extends Model
{
    protected $table = 'categories';

    public function createSlug($string)
    {
        $string = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $string));
        return rtrim($string, '-');
    }public function hasChildren($id)
{
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

public function hasProducts($id)
{
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}
}
