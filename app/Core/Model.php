<?php
// app/Core/Model.php
require_once __DIR__ . '/Database.php';

class Model
{
    protected $db;
    protected $table;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function insert($data)
    {
        try {
            $keys = array_keys($data);
            $fields = implode(", ", $keys);
            $placeholders = ":" . implode(", :", $keys);

            $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);

            foreach ($data as $key => $val) {
                $stmt->bindValue(":$key", $val);
            }

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        }
        catch (PDOException $e) {
            error_log("Insert Error in {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data)
    {
        $fields = "";
        foreach ($data as $key => $val) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ", ");

        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($data as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
