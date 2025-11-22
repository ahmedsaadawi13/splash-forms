<?php
// FILE: /app/controllers/FormController.php

class FormController extends Controller {
    private $formModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->formModel = new Form();
    }

    public function index($request, $response) {
        $tenantId = Auth::tenantId();
        $forms = $this->formModel->getTenantForms($tenantId);

        $this->view('forms/index', [
            'forms' => $forms,
            'user' => Auth::user()
        ]);
    }

    public function create($request, $response) {
        $quota = TenantContext::enforceQuota('form');

        if (!$quota['allowed']) {
            Session::setFlash('error', $quota['message']);
            return $this->redirect('/forms');
        }

        $templateModel = new Template();
        $templates = $templateModel->getPublicTemplates();

        $this->view('forms/create', [
            'templates' => $templates,
            'csrf_token' => $this->generateCsrf(),
            'user' => Auth::user()
        ]);
    }

    public function store($request, $response) {
        $this->validateCsrf();

        $quota = TenantContext::enforceQuota('form');
        if (!$quota['allowed']) {
            return $this->json(['success' => false, 'message' => $quota['message']], 403);
        }

        $data = $request->only(['name', 'description', 'template_id']);

        $validation = Validator::validate($data, [
            'name' => 'required|min:2|max:255'
        ]);

        if (!$validation['valid']) {
            Session::setFlash('error', 'Please provide a valid form name');
            return $this->back();
        }

        $tenantId = Auth::tenantId();
        $userId = Auth::id();

        if (!empty($data['template_id'])) {
            $templateModel = new Template();
            $formId = $templateModel->createFromTemplate($data['template_id'], $tenantId, $userId, $data['name']);

            if ($formId) {
                Session::setFlash('success', 'Form created from template successfully!');
                return $this->redirect('/forms/' . $formId . '/builder');
            }
        }

        $slug = strtolower(str_replace(' ', '-', $data['name']));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = $this->formModel->generateUniqueSlug($slug, $tenantId);

        $formData = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? '',
            'schema' => json_encode([]),
            'settings' => json_encode([
                'redirect_url' => '',
                'success_message' => 'Thank you for your submission!',
                'notification_email' => Auth::user()['email'],
                'enable_spam_protection' => true
            ])
        ];

        $formId = $this->formModel->createForm($formData);

        Session::setFlash('success', 'Form created successfully!');
        return $this->redirect('/forms/' . $formId . '/builder');
    }

    public function edit($request, $response) {
        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Form not found');
            return $this->redirect('/forms');
        }

        $form['schema'] = json_decode($form['schema'], true);
        $form['settings'] = json_decode($form['settings'], true);

        $this->view('forms/edit', [
            'form' => $form,
            'csrf_token' => $this->generateCsrf(),
            'user' => Auth::user()
        ]);
    }

    public function update($request, $response) {
        $this->validateCsrf();

        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $data = $request->only(['name', 'description', 'settings']);

        $validation = Validator::validate($data, [
            'name' => 'required|min:2|max:255'
        ]);

        if (!$validation['valid']) {
            return $this->json(['success' => false, 'errors' => $validation['errors']], 400);
        }

        $this->formModel->updateForm($formId, $data);

        Session::setFlash('success', 'Form updated successfully!');
        return $this->redirect('/forms/' . $formId . '/edit');
    }

    public function builder($request, $response) {
        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Form not found');
            return $this->redirect('/forms');
        }

        $form['schema'] = json_decode($form['schema'], true);
        $form['settings'] = json_decode($form['settings'], true);

        $this->view('forms/builder', [
            'form' => $form,
            'csrf_token' => $this->generateCsrf(),
            'user' => Auth::user()
        ]);
    }

    public function saveSchema($request, $response) {
        $this->validateCsrf();

        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $schema = $request->getJson()['schema'] ?? $request->post('schema');

        if (!$schema) {
            return $this->json(['success' => false, 'message' => 'Schema is required'], 400);
        }

        if (is_string($schema)) {
            $schema = json_decode($schema, true);
        }

        $this->formModel->updateForm($formId, ['schema' => $schema]);

        return $this->json(['success' => true, 'message' => 'Form schema saved']);
    }

    public function delete($request, $response) {
        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $this->formModel->delete($formId);

        Session::setFlash('success', 'Form deleted successfully');
        return $this->redirect('/forms');
    }

    public function toggle($request, $response) {
        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $this->formModel->toggleActive($formId);

        return $this->json(['success' => true]);
    }

    public function duplicate($request, $response) {
        $formId = $request->param('id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $quota = TenantContext::enforceQuota('form');
        if (!$quota['allowed']) {
            return $this->json(['success' => false, 'message' => $quota['message']], 403);
        }

        $newFormId = $this->formModel->duplicate($formId);

        Session::setFlash('success', 'Form duplicated successfully');
        return $this->redirect('/forms/' . $newFormId . '/builder');
    }

    public function publicView($request, $response) {
        $slug = $request->param('slug');

        $tenantId = Auth::check() ? Auth::tenantId() : null;

        if (!$tenantId) {
            $domain = $_SERVER['HTTP_HOST'];
            $tenantModel = new Tenant();
            $tenant = $tenantModel->findByDomain($domain);

            if ($tenant) {
                $tenantId = $tenant['id'];
            } else {
                $formParts = explode('-', $slug, 2);
                if (count($formParts) == 2 && is_numeric($formParts[0])) {
                    $tenantId = $formParts[0];
                    $slug = $formParts[1];
                }
            }
        }

        $form = $this->formModel->findBySlug($slug, $tenantId);

        if (!$form || !$form['is_active']) {
            http_response_code(404);
            die('Form not found');
        }

        $form['schema'] = json_decode($form['schema'], true);
        $form['settings'] = json_decode($form['settings'], true);

        $this->view('forms/public', [
            'form' => $form,
            'csrf_token' => $this->generateCsrf()
        ]);
    }
}
