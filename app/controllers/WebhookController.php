<?php
// FILE: /app/controllers/WebhookController.php

class WebhookController extends Controller {
    private $webhookModel;
    private $formModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->webhookModel = new Webhook();
        $this->formModel = new Form();
    }

    public function index($request, $response) {
        $formId = $request->param('form_id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Form not found');
            return $this->redirect('/forms');
        }

        $webhooks = $this->webhookModel->getFormWebhooks($formId);

        $this->view('webhooks/index', [
            'form' => $form,
            'webhooks' => $webhooks,
            'csrf_token' => $this->generateCsrf(),
            'user' => Auth::user()
        ]);
    }

    public function store($request, $response) {
        $this->validateCsrf();

        $formId = $request->param('form_id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        if (!TenantContext::hasFeature('webhooks')) {
            return $this->json(['success' => false, 'message' => 'Webhooks not available in your plan'], 403);
        }

        $data = $request->only(['url', 'events']);

        $validation = Validator::validate($data, [
            'url' => 'required|url'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'errors' => $validation['errors']], 400);
        }

        $webhookId = $this->webhookModel->createWebhook([
            'tenant_id' => $form['tenant_id'],
            'form_id' => $formId,
            'url' => $data['url'],
            'events' => $data['events'] ?? 'submission.created'
        ]);

        Session::setFlash('success', 'Webhook created successfully');
        return $this->redirect('/forms/' . $formId . '/webhooks');
    }

    public function delete($request, $response) {
        $webhookId = $request->param('id');
        $webhook = $this->webhookModel->find($webhookId);

        if (!$webhook || $webhook['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Webhook not found'], 404);
        }

        $this->webhookModel->delete($webhookId);

        Session::setFlash('success', 'Webhook deleted');
        return $this->back();
    }

    public function toggle($request, $response) {
        $webhookId = $request->param('id');
        $webhook = $this->webhookModel->find($webhookId);

        if (!$webhook || $webhook['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Webhook not found'], 404);
        }

        $this->webhookModel->toggleActive($webhookId);

        return $this->json(['success' => true]);
    }

    public function logs($request, $response) {
        $webhookId = $request->param('id');
        $webhook = $this->webhookModel->find($webhookId);

        if (!$webhook || $webhook['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Webhook not found');
            return $this->redirect('/forms');
        }

        $logs = $this->webhookModel->getWebhookLogs($webhookId);

        $this->view('webhooks/logs', [
            'webhook' => $webhook,
            'logs' => $logs,
            'user' => Auth::user()
        ]);
    }

    public function test($request, $response) {
        $webhookId = $request->param('id');
        $webhook = $this->webhookModel->find($webhookId);

        if (!$webhook || $webhook['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Webhook not found'], 404);
        }

        $testData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'This is a test webhook'
        ];

        $success = $this->webhookModel->trigger($webhook['id'], 0, $testData);

        return $this->json([
            'success' => $success,
            'message' => $success ? 'Webhook test successful' : 'Webhook test failed'
        ]);
    }
}
