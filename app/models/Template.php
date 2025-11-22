<?php
// FILE: /app/models/Template.php

class Template extends Model {
    protected $table = 'form_templates';
    protected $primaryKey = 'id';
    protected $tenantColumn = null;

    public function getPublicTemplates() {
        return $this->where(['is_public' => 1], 'usage_count DESC');
    }

    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }

    public function getTemplatesByCategory($category) {
        return $this->where(['category' => $category, 'is_public' => 1], 'usage_count DESC');
    }

    public function incrementUsage($templateId) {
        $sql = "UPDATE form_templates SET usage_count = usage_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$templateId]);
    }

    public function createFromTemplate($templateId, $tenantId, $userId, $formName = null) {
        $template = $this->find($templateId);

        if (!$template) {
            return false;
        }

        $formModel = new Form();

        $slug = strtolower(str_replace(' ', '-', $formName ?? $template['name']));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = $formModel->generateUniqueSlug($slug, $tenantId);

        $formData = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $formName ?? $template['name'],
            'slug' => $slug,
            'description' => $template['description'],
            'schema' => $template['schema'],
            'settings' => json_encode([
                'redirect_url' => '',
                'success_message' => 'Thank you for your submission!',
                'notification_email' => '',
                'enable_spam_protection' => true
            ])
        ];

        $formId = $formModel->createForm($formData);

        if ($formId) {
            $this->incrementUsage($templateId);
        }

        return $formId;
    }

    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM form_templates WHERE is_public = 1 ORDER BY category";
        $results = $this->db->fetchAll($sql);

        return array_column($results, 'category');
    }
}
