<?php
// FILE: /app/models/Tenant.php

class Tenant extends Model {
    protected $table = 'tenants';
    protected $primaryKey = 'id';
    protected $tenantColumn = null;

    public function createTenant($data) {
        return $this->create($data);
    }

    public function findByDomain($domain) {
        return $this->findBy('domain', $domain);
    }

    public function suspend($tenantId) {
        return $this->update($tenantId, ['status' => 'suspended']);
    }

    public function activate($tenantId) {
        return $this->update($tenantId, ['status' => 'active']);
    }

    public function cancel($tenantId) {
        return $this->update($tenantId, ['status' => 'cancelled']);
    }

    public function getStats($tenantId) {
        $stats = [];

        $stats['total_forms'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM forms WHERE tenant_id = ?",
            [$tenantId]
        );

        $stats['total_submissions'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM form_submissions WHERE tenant_id = ?",
            [$tenantId]
        );

        $stats['total_users'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE tenant_id = ?",
            [$tenantId]
        );

        $stats['active_webhooks'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM webhooks WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        );

        return $stats;
    }

    public function updateSettings($tenantId, $settings) {
        return $this->update($tenantId, [
            'settings' => json_encode($settings)
        ]);
    }
}
