<?php
// FILE: /app/models/Submission.php

class Submission extends Model {
    protected $table = 'form_submissions';
    protected $primaryKey = 'id';

    public function createSubmission($data) {
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        return $this->create($data);
    }

    public function getFormSubmissions($formId, $status = null, $page = 1, $perPage = 20) {
        $sql = "SELECT * FROM form_submissions WHERE form_id = ?";
        $params = [$formId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetchColumn(
            str_replace('SELECT *', 'SELECT COUNT(*)', $sql),
            $params
        );

        $sql .= " LIMIT $perPage OFFSET $offset";
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function getTenantSubmissions($tenantId, $page = 1, $perPage = 20) {
        $sql = "SELECT s.*, f.name as form_name, f.slug as form_slug
                FROM form_submissions s
                JOIN forms f ON s.form_id = f.id
                WHERE s.tenant_id = ?
                ORDER BY s.created_at DESC";

        $params = [$tenantId];

        $offset = ($page - 1) * $perPage;
        $total = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM form_submissions WHERE tenant_id = ?",
            $params
        );

        $sql .= " LIMIT $perPage OFFSET $offset";
        $items = $this->db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    public function markAsRead($submissionId) {
        return $this->update($submissionId, ['status' => 'read']);
    }

    public function markAsSpam($submissionId) {
        return $this->update($submissionId, ['status' => 'spam']);
    }

    public function markAsNew($submissionId) {
        return $this->update($submissionId, ['status' => 'new']);
    }

    public function archive($submissionId) {
        return $this->update($submissionId, ['status' => 'archived']);
    }

    public function getSubmissionsForExport($formId) {
        $sql = "SELECT * FROM form_submissions WHERE form_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$formId]);
    }

    public function getRecentSubmissions($tenantId, $limit = 10) {
        $sql = "SELECT s.*, f.name as form_name
                FROM form_submissions s
                JOIN forms f ON s.form_id = f.id
                WHERE s.tenant_id = ?
                ORDER BY s.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$tenantId, $limit]);
    }

    public function getSubmissionStats($formId, $days = 30) {
        $sql = "SELECT
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM form_submissions
                WHERE form_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        return $this->db->fetchAll($sql, [$formId, $days]);
    }
}
