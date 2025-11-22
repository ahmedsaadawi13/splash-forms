<?php
// FILE: /app/models/Webhook.php

class Webhook extends Model {
    protected $table = 'webhooks';
    protected $primaryKey = 'id';

    public function getFormWebhooks($formId) {
        return $this->where(['form_id' => $formId], 'created_at DESC');
    }

    public function createWebhook($data) {
        if (!isset($data['secret'])) {
            $data['secret'] = bin2hex(random_bytes(32));
        }

        return $this->create($data);
    }

    public function toggleActive($webhookId) {
        $webhook = $this->find($webhookId);
        if ($webhook) {
            return $this->update($webhookId, ['is_active' => !$webhook['is_active']]);
        }
        return false;
    }

    public function trigger($webhookId, $submissionId, $submissionData) {
        $webhook = $this->find($webhookId);

        if (!$webhook || !$webhook['is_active']) {
            return false;
        }

        $payload = [
            'event' => 'submission.created',
            'form_id' => $webhook['form_id'],
            'submission_id' => $submissionId,
            'data' => $submissionData,
            'timestamp' => date('c')
        ];

        $payloadJson = json_encode($payload);

        $signature = hash_hmac('sha256', $payloadJson, $webhook['secret']);

        $ch = curl_init($webhook['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Webhook-Signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $success = $statusCode >= 200 && $statusCode < 300;

        curl_close($ch);

        $this->logWebhook($webhookId, $submissionId, $payloadJson, $response, $statusCode, $success);

        return $success;
    }

    private function logWebhook($webhookId, $submissionId, $payload, $response, $statusCode, $success) {
        $this->db->insert('webhook_logs', [
            'webhook_id' => $webhookId,
            'submission_id' => $submissionId,
            'payload' => $payload,
            'response' => substr($response, 0, 5000),
            'status_code' => $statusCode,
            'success' => $success ? 1 : 0
        ]);
    }

    public function getWebhookLogs($webhookId, $limit = 50) {
        $sql = "SELECT * FROM webhook_logs WHERE webhook_id = ? ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$webhookId, $limit]);
    }
}
