<?php
// FILE: /routes.php

$router = $app->getRouter();

$router->get('/', [HomeController::class, 'index']);
$router->get('/pricing', [HomeController::class, 'pricing']);
$router->get('/docs', [HomeController::class, 'docs']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/analytics', [DashboardController::class, 'analytics']);

$router->get('/forms', [FormController::class, 'index']);
$router->get('/forms/create', [FormController::class, 'create']);
$router->post('/forms', [FormController::class, 'store']);
$router->get('/forms/{id}', [FormController::class, 'edit']);
$router->post('/forms/{id}', [FormController::class, 'update']);
$router->get('/forms/{id}/builder', [FormController::class, 'builder']);
$router->post('/forms/{id}/schema', [FormController::class, 'saveSchema']);
$router->post('/forms/{id}/toggle', [FormController::class, 'toggle']);
$router->post('/forms/{id}/duplicate', [FormController::class, 'duplicate']);
$router->post('/forms/{id}/delete', [FormController::class, 'delete']);

$router->get('/submissions', [SubmissionController::class, 'index']);
$router->get('/forms/{form_id}/submissions', [SubmissionController::class, 'formSubmissions']);
$router->get('/submissions/{id}', [SubmissionController::class, 'view']);
$router->post('/submissions/{id}/status', [SubmissionController::class, 'updateStatus']);
$router->post('/submissions/{id}/delete', [SubmissionController::class, 'delete']);
$router->get('/forms/{form_id}/submissions/export', [SubmissionController::class, 'export']);

$router->get('/forms/{form_id}/webhooks', [WebhookController::class, 'index']);
$router->post('/forms/{form_id}/webhooks', [WebhookController::class, 'store']);
$router->post('/webhooks/{id}/delete', [WebhookController::class, 'delete']);
$router->post('/webhooks/{id}/toggle', [WebhookController::class, 'toggle']);
$router->get('/webhooks/{id}/logs', [WebhookController::class, 'logs']);
$router->post('/webhooks/{id}/test', [WebhookController::class, 'test']);

$router->get('/settings', [SettingsController::class, 'index']);
$router->post('/settings/tenant', [SettingsController::class, 'updateTenant']);
$router->post('/settings/api-keys', [SettingsController::class, 'createApiKey']);
$router->post('/settings/api-keys/{id}/revoke', [SettingsController::class, 'revokeApiKey']);
$router->post('/settings/users', [SettingsController::class, 'createUser']);
$router->post('/settings/users/{id}/delete', [SettingsController::class, 'deleteUser']);

$router->get('/f/{slug}', [FormController::class, 'publicView']);
$router->post('/f/{slug}/submit', [SubmissionController::class, 'submit']);
$router->post('/forms/{form_id}/submit', [SubmissionController::class, 'submit']);

$router->get('/api/forms', [ApiController::class, 'getForms']);
$router->get('/api/forms/{id}', [ApiController::class, 'getForm']);
$router->post('/api/forms', [ApiController::class, 'createForm']);
$router->post('/api/forms/{id}/submit', [ApiController::class, 'submitForm']);
$router->get('/api/forms/{id}/submissions', [ApiController::class, 'getSubmissions']);
$router->get('/api/submissions/{id}', [ApiController::class, 'getSubmission']);

$router->notFound(function($request, $response) {
    $response->notFound('404 - Page Not Found');
});
