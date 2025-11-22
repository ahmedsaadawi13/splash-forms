<?php
// FILE: /app/models/Form.php

class Form extends Model {
    protected $table = 'forms';
    protected $primaryKey = 'id';

    public function getTenantForms($tenantId, $isActive = null) {
        $sql = "SELECT * FROM forms WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($isActive !== null) {
            $sql .= " AND is_active = ?";
            $params[] = $isActive;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function findBySlug($slug, $tenantId) {
        $sql = "SELECT * FROM forms WHERE slug = ? AND tenant_id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$slug, $tenantId]);
    }

    public function createForm($data) {
        if (isset($data['schema']) && is_array($data['schema'])) {
            $data['schema'] = json_encode($data['schema']);
        }

        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings']);
        }

        return $this->create($data);
    }

    public function updateForm($formId, $data) {
        if (isset($data['schema']) && is_array($data['schema'])) {
            $data['schema'] = json_encode($data['schema']);
        }

        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings']);
        }

        return $this->update($formId, $data);
    }

    public function incrementSubmissionCount($formId) {
        $sql = "UPDATE forms SET submissions_count = submissions_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$formId]);
    }

    public function toggleActive($formId) {
        $form = $this->find($formId);
        if ($form) {
            return $this->update($formId, ['is_active' => !$form['is_active']]);
        }
        return false;
    }

    public function duplicate($formId, $newName = null) {
        $form = $this->find($formId);
        if (!$form) {
            return false;
        }

        unset($form['id']);
        $form['name'] = $newName ?? $form['name'] . ' (Copy)';
        $form['slug'] = $this->generateUniqueSlug($form['slug'] . '-copy', $form['tenant_id']);
        $form['submissions_count'] = 0;

        return $this->createForm($form);
    }

    public function generateUniqueSlug($slug, $tenantId) {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->findBySlug($slug, $tenantId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getFormWithStats($formId) {
        $form = $this->find($formId);
        if (!$form) {
            return null;
        }

        $form['total_submissions'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM form_submissions WHERE form_id = ?",
            [$formId]
        );

        $form['new_submissions'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM form_submissions WHERE form_id = ? AND status = 'new'",
            [$formId]
        );

        $form['webhooks_count'] = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM webhooks WHERE form_id = ? AND is_active = 1",
            [$formId]
        );

        return $form;
    }
}
