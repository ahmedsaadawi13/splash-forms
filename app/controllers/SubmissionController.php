<?php
// FILE: /app/controllers/SubmissionController.php

class SubmissionController extends Controller {
    private $submissionModel;
    private $formModel;

    public function __construct() {
        parent::__construct();
        $this->submissionModel = new Submission();
        $this->formModel = new Form();
    }

    public function index($request, $response) {
        Auth::requireAuth();

        $tenantId = Auth::tenantId();
        $page = $request->get('page', 1);

        $result = $this->submissionModel->getTenantSubmissions($tenantId, $page);

        $this->view('submissions/index', [
            'submissions' => $result['items'],
            'pagination' => $result,
            'user' => Auth::user()
        ]);
    }

    public function formSubmissions($request, $response) {
        Auth::requireAuth();

        $formId = $request->param('form_id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Form not found');
            return $this->redirect('/forms');
        }

        $page = $request->get('page', 1);
        $status = $request->get('status');

        $result = $this->submissionModel->getFormSubmissions($formId, $status, $page);

        $form['schema'] = json_decode($form['schema'], true);

        $this->view('submissions/list', [
            'form' => $form,
            'submissions' => $result['items'],
            'pagination' => $result,
            'currentStatus' => $status,
            'user' => Auth::user()
        ]);
    }

    public function view($request, $response) {
        Auth::requireAuth();

        $submissionId = $request->param('id');
        $submission = $this->submissionModel->find($submissionId);

        if (!$submission || $submission['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Submission not found');
            return $this->back();
        }

        $form = $this->formModel->find($submission['form_id']);
        $form['schema'] = json_decode($form['schema'], true);

        $submission['data'] = json_decode($submission['data'], true);

        if ($submission['status'] === 'new') {
            $this->submissionModel->markAsRead($submissionId);
        }

        $this->view('submissions/view', [
            'submission' => $submission,
            'form' => $form,
            'user' => Auth::user()
        ]);
    }

    public function submit($request, $response) {
        if ($request->isPost()) {
            $this->validateCsrf();
        }

        $formId = $request->param('form_id');
        $form = $this->formModel->find($formId);

        if (!$form || !$form['is_active']) {
            return $this->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        $quota = TenantContext::enforceQuota('submission');
        if (!$quota['allowed']) {
            return $this->json(['success' => false, 'message' => $quota['message']], 403);
        }

        $formData = $request->isPost() ? $request->all() : $request->getJson();

        unset($formData[CSRF_TOKEN_NAME]);

        $schema = json_decode($form['schema'], true);
        $errors = [];

        foreach ($schema as $field) {
            if (!empty($field['required']) && empty($formData[$field['name']])) {
                $errors[$field['name']] = $field['label'] . ' is required';
            }

            if ($field['type'] === 'email' && !empty($formData[$field['name']])) {
                if (!filter_var($formData[$field['name']], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field['name']] = 'Please enter a valid email address';
                }
            }

            if ($field['type'] === 'file' && $request->hasFile($field['name'])) {
                $fileResult = $this->uploadFile($request->file($field['name']));
                if ($fileResult['success']) {
                    $formData[$field['name']] = $fileResult['url'];
                } else {
                    $errors[$field['name']] = $fileResult['error'];
                }
            }
        }

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        $settings = json_decode($form['settings'], true);

        if (!empty($settings['enable_spam_protection'])) {
            $honeypot = $request->post('_honeypot');
            if (!empty($honeypot)) {
                return $this->json(['success' => false, 'message' => 'Spam detected'], 400);
            }
        }

        $submissionId = $this->submissionModel->createSubmission([
            'tenant_id' => $form['tenant_id'],
            'form_id' => $form['id'],
            'data' => $formData,
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
                $webhookModel->trigger($webhook['id'], $submissionId, $formData);
            }
        }

        if (!empty($settings['notification_email'])) {
            $this->sendNotificationEmail($settings['notification_email'], $form, $formData);
        }

        $redirectUrl = $settings['redirect_url'] ?? '';
        $successMessage = $settings['success_message'] ?? 'Thank you for your submission!';

        return $this->json([
            'success' => true,
            'message' => $successMessage,
            'redirect_url' => $redirectUrl
        ]);
    }

    private function sendNotificationEmail($email, $form, $data) {
        $subject = 'New form submission: ' . $form['name'];
        $message = "You have received a new submission for form: {$form['name']}\n\n";

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $message .= ucfirst(str_replace('_', ' ', $key)) . ": $value\n";
        }

        $headers = 'From: noreply@' . $_SERVER['HTTP_HOST'];

        mail($email, $subject, $message, $headers);
    }

    public function export($request, $response) {
        Auth::requireAuth();

        $formId = $request->param('form_id');
        $form = $this->formModel->find($formId);

        if (!$form || $form['tenant_id'] != Auth::tenantId()) {
            Session::setFlash('error', 'Form not found');
            return $this->redirect('/forms');
        }

        $submissions = $this->submissionModel->getSubmissionsForExport($formId);

        $schema = json_decode($form['schema'], true);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $form['slug'] . '-submissions-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        $headers = ['ID', 'Submitted At', 'IP Address', 'Status'];
        foreach ($schema as $field) {
            $headers[] = $field['label'];
        }
        fputcsv($output, $headers);

        foreach ($submissions as $submission) {
            $data = json_decode($submission['data'], true);

            $row = [
                $submission['id'],
                $submission['created_at'],
                $submission['ip_address'],
                $submission['status']
            ];

            foreach ($schema as $field) {
                $value = $data[$field['name']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $row[] = $value;
            }

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    public function updateStatus($request, $response) {
        Auth::requireAuth();

        $submissionId = $request->param('id');
        $submission = $this->submissionModel->find($submissionId);

        if (!$submission || $submission['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Submission not found'], 404);
        }

        $status = $request->post('status');

        if (!in_array($status, ['new', 'read', 'spam', 'archived'])) {
            return $this->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $this->submissionModel->update($submissionId, ['status' => $status]);

        return $this->json(['success' => true]);
    }

    public function delete($request, $response) {
        Auth::requireAuth();

        $submissionId = $request->param('id');
        $submission = $this->submissionModel->find($submissionId);

        if (!$submission || $submission['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false, 'message' => 'Submission not found'], 404);
        }

        $this->submissionModel->delete($submissionId);

        Session::setFlash('success', 'Submission deleted');
        return $this->back();
    }
}
