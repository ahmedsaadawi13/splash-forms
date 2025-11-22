<?php
// FILE: /app/controllers/ApiController.php

class ApiController extends Controller {
    private $apiKeyModel;
    private $formModel;
    private $submissionModel;

    public function __construct() {
        parent::__construct();
        $this->apiKeyModel = new ApiKey();
        $this->formModel = new Form();
        $this->submissionModel = new Submission();
    }

    private function authenticate($request) {
        $apiKey = $request->getHeader('X-API-KEY');

        if (!$apiKey) {
            return ['success' => false, 'message' => 'API key required', 'code' => 401];
        }

        $key = $this->apiKeyModel->verify($apiKey);

        if (!$key) {
            return ['success' => false, 'message' => 'Invalid API key', 'code' => 401];
        }

        return ['success' => true, 'tenant_id' => $key['tenant_id']];
    }

    public function getForms($request, $response) {
        $auth = $this->authenticate($request);

        if (!$auth['success']) {
            return $this->json(['error' => $auth['message']], $auth['code']);
        }

        $forms = $this->formModel->getTenantForms($auth['tenant_id'], 1);

        $formsData = array_map(function($form) {
            return [
                'id' => $form['id'],
                'name' => $form['name'],
                'slug' => $form['slug'],
                'description' => $form['description'],
                'is_active' => (bool)$form['is_active'],
                'submissions_count' => $form['submissions_count'],
                'created_at' => $form['created_at']
            ];
        }, $forms);

        return $this->json([
            'success' => true,
            'data' => $formsData
        ]);
    }

    public function getForm($request, $response) {
        $auth = $this->authenticate($request);

        if (!$auth['success']) {
            return $this->json(['error' => $auth['message']], $auth['code']);
        }

        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != $auth['tenant_id']) {
            return $this->json(['error' => 'Form not found'], 404);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $form['id'],
                'name' => $form['name'],
                'slug' => $form['slug'],
                'description' => $form['description'],
                'schema' => json_decode($form['schema'], true),
                'is_active' => (bool)$form['is_active'],
                'submissions_count' => $form['submissions_count'],
                'created_at' => $form['created_at']
            ]
        ]);
    }

    public function createForm($request, $response) {
        $auth = $this->authenticate($request);

        if (!$auth['success']) {
            return $this->json(['error' => $auth['message']], $auth['code']);
        }

        $data = $request->getJson();

        $validation = Validator::validate($data, [
            'name' => 'required|min:2|max:255',
            'slug' => 'required'
        ]);

        if (!$validation['valid']) {
            return $this->json(['error' => 'Validation failed', 'errors' => $validation['errors']], 400);
        }

        $quota = TenantContext::enforceQuota('form');
        if (!$quota['allowed']) {
            return $this->json(['error' => $quota['message']], 403);
        }

        $slug = $this->formModel->generateUniqueSlug($data['slug'], $auth['tenant_id']);

        $formData = [
            'tenant_id' => $auth['tenant_id'],
            'user_id' => $this->db->fetchColumn("SELECT id FROM users WHERE tenant_id = ? AND role = 'tenant_admin' LIMIT 1", [$auth['tenant_id']]),
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'schema' => $data['schema'] ?? [],
            'settings' => $data['settings'] ?? [
                'redirect_url' => '',
                'success_message' => 'Thank you for your submission!',
                'notification_email' => '',
                'enable_spam_protection' => true
            ]
        ];

        $formId = $this->formModel->createForm($formData);

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $formId,
                'message' => 'Form created successfully'
            ]
        ], 201);
    }

    public function submitForm($request, $response) {
        $auth = $this->authenticate($request);

        if (!$auth['success']) {
            return $this->json(['error' => $auth['message']], $auth['code']);
        }

        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != $auth['tenant_id']) {
            return $this->json(['error' => 'Form not found'], 404);
        }

        if (!$form['is_active']) {
            return $this->json(['error' => 'Form is not active'], 400);
        }

        $quota = TenantContext::enforceQuota('submission');
        if (!$quota['allowed']) {
            return $this->json(['error' => $quota['message']], 403);
        }

        $data = $request->getJson();

        $submissionId = $this->submissionModel->createSubmission([
            'tenant_id' => $form['tenant_id'],
            'form_id' => $form['id'],
            'data' => $data,
            'ip_address' => $request->getIp(),
            'user_agent' => $request->getUserAgent(),
            'status' => 'new'
        ]);

        $this->formModel->incrementSubmissionCount($form['id']);

        TenantContext::trackUsage('submissions', 1);

        $webhookModel = new Webhook();
        $webhooks = $webhookModel->getFormWebhooks($form['id']);

        foreach ($webhooks as $webhook) {
            if ($webhook['is_active']) {
                $webhookModel->trigger($webhook['id'], $submissionId, $data);
            }
        }

        return $this->json([
            'success' => true,
            'data' => [
                'submission_id' => $submissionId,
                'message' => 'Submission received'
            ]
        ], 201);
    }

    public function getSubmissions($request, $response) {
        $auth = $this->authenticate($request);

        if (!$auth['success']) {
            return $this->json(['error' => $auth['message']], $auth['code']);
        }

        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != $auth['tenant_id']) {
            return $this->json(['error' => 'Form not found'], 404);
        }

        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 20), 100);

        $result = $this->submissionModel->getFormSubmissions($formId, null, $page, $perPage);

        $submissions = array_map(function($submission) {
            return [
                'id' => $submission['id'],
                'data' => json_decode($submission['data'], true),
                'ip_address' => $submission['ip_address'],
                'status' => $submission['status'],
                'created_at' => $submission['created_at']
            ];
        }, $result['items']);

        return $this->json([
            'success' => true,
            'data' => $submissions,
            'meta' => [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'per_page' => $result['per_page'],
                'total' => $result['total']
            ]
        ]);
    }

    public function getSubmission($request, $response) {
        $auth = $this->authenticate($request);

        if (!$auth['success']) {
            return $this->json(['error' => $auth['message']], $auth['code']);
        }

        $submissionId = $request->param('id');
        $submission = $this->submissionModel->find($submissionId);

        if (!$submission || $submission['tenant_id'] != $auth['tenant_id']) {
            return $this->json(['error' => 'Submission not found'], 404);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $submission['id'],
                'form_id' => $submission['form_id'],
                'data' => json_decode($submission['data'], true),
                'ip_address' => $submission['ip_address'],
                'status' => $submission['status'],
                'created_at' => $submission['created_at']
            ]
        ]);
    }
}
