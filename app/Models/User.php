<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    protected $table = 'users';

    public function __construct()
    {
        parent::__construct();
    }

    // Tìm user theo username
    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyLogin($username, $password)
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        if ((int)$user['is_active'] !== 1) {
            return false;
        }

        return $user;
    }
}