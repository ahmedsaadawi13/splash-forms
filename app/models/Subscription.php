<?php
// FILE: /app/models/Subscription.php

class Subscription extends Model {
    protected $table = 'tenant_subscriptions';
    protected $primaryKey = 'id';
    protected $tenantColumn = 'tenant_id';

    public function getTenantSubscription($tenantId) {
        $sql = "SELECT ts.*, p.*
                FROM tenant_subscriptions ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                ORDER BY ts.expires_at DESC LIMIT 1";

        return $this->db->fetchOne($sql, [$tenantId]);
    }

    public function createSubscription($tenantId, $planId, $expiresAt = null) {
        if ($expiresAt === null) {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));
        }

        return $this->create([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'expires_at' => $expiresAt
        ]);
    }

    public function cancelSubscription($subscriptionId) {
        return $this->update($subscriptionId, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function renewSubscription($subscriptionId, $expiresAt = null) {
        if ($expiresAt === null) {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));
        }

        return $this->update($subscriptionId, [
            'status' => 'active',
            'expires_at' => $expiresAt,
            'cancelled_at' => null
        ]);
    }

    public function changePlan($tenantId, $newPlanId) {
        $currentSubscription = $this->getTenantSubscription($tenantId);

        if ($currentSubscription) {
            $this->cancelSubscription($currentSubscription['id']);
        }

        return $this->createSubscription($tenantId, $newPlanId);
    }

    public function checkExpired() {
        $sql = "UPDATE tenant_subscriptions
                SET status = 'expired'
                WHERE status = 'active'
                AND expires_at IS NOT NULL
                AND expires_at < NOW()";

        return $this->db->query($sql);
    }
}
