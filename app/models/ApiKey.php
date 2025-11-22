<?php
// FILE: /app/models/ApiKey.php

class ApiKey extends Model {
    protected $table = 'api_keys';
    protected $primaryKey = 'id';

    public function generateKey($tenantId, $name = null) {
        $apiKey = 'sk_' . bin2hex(random_bytes(32));

        return $this->create([
            'tenant_id' => $tenantId,
            'api_key' => $apiKey,
            'name' => $name
        ]);
    }

    public function findByKey($apiKey) {
        return $this->findBy('api_key', $apiKey);
    }

    public function verify($apiKey) {
        $key = $this->findByKey($apiKey);

        if (!$key || !$key['is_active']) {
            return false;
        }

        $this->update($key['id'], ['last_used_at' => date('Y-m-d H:i:s')]);

        return $key;
    }

    public function getTenantKeys($tenantId) {
        return $this->where(['tenant_id' => $tenantId], 'created_at DESC');
    }

    public function revoke($keyId) {
        return $this->update($keyId, ['is_active' => 0]);
    }

    public function activate($keyId) {
        return $this->update($keyId, ['is_active' => 1]);
    }
}
