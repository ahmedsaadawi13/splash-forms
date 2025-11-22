<?php
// FILE: /app/controllers/SettingsController.php

class SettingsController extends Controller {
    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
    }

    public function index($request, $response) {
        $tenantId = Auth::tenantId();

        $tenantModel = new Tenant();
        $tenant = $tenantModel->find($tenantId);

        $subscriptionModel = new Subscription();
        $subscription = $subscriptionModel->getTenantSubscription($tenantId);

        $apiKeyModel = new ApiKey();
        $apiKeys = $apiKeyModel->getTenantKeys($tenantId);

        $userModel = new User();
        $users = $userModel->getTenantUsers($tenantId);

        $this->view('settings/index', [
            'tenant' => $tenant,
            'subscription' => $subscription,
            'apiKeys' => $apiKeys,
            'users' => $users,
            'csrf_token' => $this->generateCsrf(),
            'user' => Auth::user()
        ]);
    }

    public function updateTenant($request, $response) {
        $this->validateCsrf();
        Auth::authorize('manage_settings');

        $tenantId = Auth::tenantId();
        $data = $request->only(['name', 'domain']);

        $validation = Validator::validate($data, [
            'name' => 'required|min:2|max:255'
        ]);

        if (!$validation['valid']) {
            Session::setFlash('error', 'Please check your input');
            return $this->back();
        }

        $tenantModel = new Tenant();
        $tenantModel->update($tenantId, $data);

        Session::setFlash('success', 'Settings updated successfully');
        return $this->redirect('/settings');
    }

    public function createApiKey($request, $response) {
        $this->validateCsrf();

        $name = $request->post('name');

        $apiKeyModel = new ApiKey();
        $keyId = $apiKeyModel->generateKey(Auth::tenantId(), $name);

        $key = $apiKeyModel->find($keyId);

        Session::setFlash('success', 'API key created successfully');
        Session::setFlash('new_api_key', $key['api_key']);

        return $this->redirect('/settings');
    }

    public function revokeApiKey($request, $response) {
        $keyId = $request->param('id');

        $apiKeyModel = new ApiKey();
        $key = $apiKeyModel->find($keyId);

        if (!$key || $key['tenant_id'] != Auth::tenantId()) {
            return $this->json(['success' => false], 404);
        }

        $apiKeyModel->revoke($keyId);

        Session::setFlash('success', 'API key revoked');
        return $this->redirect('/settings');
    }

    public function createUser($request, $response) {
        $this->validateCsrf();
        Auth::authorize('manage_users');

        $data = $request->only(['name', 'email', 'password', 'role']);

        $validation = Validator::validate($data, [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:' . PASSWORD_MIN_LENGTH,
            'role' => 'required|in:staff,user'
        ]);

        if (!$validation['valid']) {
            Session::setFlash('error', 'Please check your input');
            Session::setFlash('errors', $validation['errors']);
            return $this->back();
        }

        $userModel = new User();
        $userModel->createUser([
            'tenant_id' => Auth::tenantId(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role']
        ]);

        Session::setFlash('success', 'User created successfully');
        return $this->redirect('/settings');
    }

    public function deleteUser($request, $response) {
        Auth::authorize('manage_users');

        $userId = $request->param('id');

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user || $user['tenant_id'] != Auth::tenantId() || $user['id'] == Auth::id()) {
            return $this->json(['success' => false], 404);
        }

        $userModel->delete($userId);

        Session::setFlash('success', 'User deleted');
        return $this->redirect('/settings');
    }
}
