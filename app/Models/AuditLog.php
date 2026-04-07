<?php
// app/Models/AuditLog.php

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public function logAction($user_id, $action, $table_name, $record_id, $old_data = null, $new_data = null)
    {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $data = [
            'user_id' => $user_id,
            'action' => $action,
            'table_name' => $table_name,
            'record_id' => $record_id,
            'old_data' => $old_data ? json_encode($old_data, JSON_UNESCAPED_UNICODE) : null,
            'new_data' => $new_data ? json_encode($new_data, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ip_address
        ];

        return $this->insert($data);
    }

    public function getRecentLogs($limit = 20)
    {
        $sql = "SELECT a.*, u.username as by_user 
                FROM {$this->table} a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
