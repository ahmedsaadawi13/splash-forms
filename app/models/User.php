<?php
// FILE: /app/models/User.php

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'id';

    public function findByEmail($email, $tenantId = null) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $params = [$email];

        if ($tenantId !== null) {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }

        $sql .= " LIMIT 1";

        return $this->db->fetchOne($sql, $params);
    }

    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->create($data);
    }

    public function updatePassword($userId, $newPassword) {
        return $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    public function getTenantUsers($tenantId, $role = null) {
        $sql = "SELECT * FROM users WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function deactivate($userId) {
        return $this->update($userId, ['is_active' => 0]);
    }

    public function activate($userId) {
        return $this->update($userId, ['is_active' => 1]);
    }
}
