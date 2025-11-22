<?php
// FILE: /app/core/TenantContext.php

class TenantContext {
    private static $tenant = null;
    private static $subscription = null;

    public static function getTenantId() {
        return Auth::tenantId();
    }

    public static function getTenant() {
        if (self::$tenant === null && self::getTenantId()) {
            $db = Database::getInstance();
            self::$tenant = $db->fetchOne(
                "SELECT * FROM tenants WHERE id = ? LIMIT 1",
                [self::getTenantId()]
            );
        }
        return self::$tenant;
    }

    public static function getSubscription() {
        if (self::$subscription === null && self::getTenantId()) {
            $db = Database::getInstance();
            self::$subscription = $db->fetchOne(
                "SELECT ts.*, p.*
                FROM tenant_subscriptions ts
                JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = ? AND ts.status = 'active'
                ORDER BY ts.expires_at DESC LIMIT 1",
                [self::getTenantId()]
            );
        }
        return self::$subscription;
    }

    public static function canCreateForm() {
        $subscription = self::getSubscription();
        if (!$subscription) {
            return false;
        }

        $db = Database::getInstance();
        $formCount = $db->fetchColumn(
            "SELECT COUNT(*) FROM forms WHERE tenant_id = ?",
            [self::getTenantId()]
        );

        return $formCount < $subscription['max_forms'];
    }

    public static function canAcceptSubmission() {
        $subscription = self::getSubscription();
        if (!$subscription) {
            return false;
        }

        $db = Database::getInstance();
        $period = date('Y-m');

        $submissionCount = $db->fetchColumn(
            "SELECT value FROM usage_tracking
            WHERE tenant_id = ? AND metric = 'submissions' AND period = ?",
            [self::getTenantId(), $period]
        );

        $submissionCount = $submissionCount ?: 0;

        return $submissionCount < $subscription['max_submissions_per_month'];
    }

    public static function trackUsage($metric, $increment = 1) {
        $db = Database::getInstance();
        $tenantId = self::getTenantId();
        $period = date('Y-m');

        $existing = $db->fetchOne(
            "SELECT * FROM usage_tracking WHERE tenant_id = ? AND metric = ? AND period = ?",
            [$tenantId, $metric, $period]
        );

        if ($existing) {
            $db->update(
                'usage_tracking',
                ['value' => $existing['value'] + $increment],
                'id = ?',
                [$existing['id']]
            );
        } else {
            $db->insert('usage_tracking', [
                'tenant_id' => $tenantId,
                'metric' => $metric,
                'value' => $increment,
                'period' => $period
            ]);
        }
    }

    public static function isSubscriptionActive() {
        $subscription = self::getSubscription();
        if (!$subscription) {
            return false;
        }

        if ($subscription['status'] !== 'active') {
            return false;
        }

        if ($subscription['expires_at'] && strtotime($subscription['expires_at']) < time()) {
            return false;
        }

        return true;
    }

    public static function hasFeature($feature) {
        $subscription = self::getSubscription();
        if (!$subscription || !$subscription['features']) {
            return false;
        }

        $features = json_decode($subscription['features'], true);
        return in_array($feature, $features);
    }

    public static function enforceQuota($type) {
        switch ($type) {
            case 'form':
                if (!self::canCreateForm()) {
                    return [
                        'allowed' => false,
                        'message' => 'Form creation limit reached. Please upgrade your plan.'
                    ];
                }
                break;

            case 'submission':
                if (!self::canAcceptSubmission()) {
                    return [
                        'allowed' => false,
                        'message' => 'Monthly submission limit reached. Please upgrade your plan.'
                    ];
                }
                break;

            default:
                return ['allowed' => true];
        }

        return ['allowed' => true];
    }
}
