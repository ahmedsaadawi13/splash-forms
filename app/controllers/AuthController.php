<?php
// FILE: /app/controllers/AuthController.php

class AuthController extends Controller {
    private $userModel;
    private $tenantModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->tenantModel = new Tenant();
    }

    public function showLogin($request, $response) {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'csrf_token' => $this->generateCsrf()
        ]);
    }

    public function login($request, $response) {
        $this->validateCsrf();

        $email = $request->post('email');
        $password = $request->post('password');

        $validation = Validator::validate([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!$validation['valid']) {
            Session::setFlash('error', 'Invalid email or password');
            return $this->redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            Session::setFlash('success', 'Welcome back!');
            return $this->redirect('/dashboard');
        }

        Session::setFlash('error', 'Invalid credentials');
        return $this->redirect('/login');
    }

    public function showRegister($request, $response) {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }

        $planModel = new Plan();
        $plans = $planModel->getActivePlans();

        $this->view('auth/register', [
            'csrf_token' => $this->generateCsrf(),
            'plans' => $plans
        ]);
    }

    public function register($request, $response) {
        $this->validateCsrf();

        $data = $request->only(['name', 'email', 'password', 'password_confirmation', 'tenant_name', 'plan_id']);

        $validation = Validator::validate($data, [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:' . PASSWORD_MIN_LENGTH . '|confirmed',
            'tenant_name' => 'required|min:2',
            'plan_id' => 'required|integer'
        ]);

        if (!$validation['valid']) {
            Session::setFlash('error', 'Please check your input');
            Session::setFlash('errors', $validation['errors']);
            return $this->back();
        }

        try {
            $this->db->getConnection()->beginTransaction();

            $tenantId = $this->tenantModel->createTenant([
                'name' => $data['tenant_name'],
                'status' => 'active'
            ]);

            $userId = $this->userModel->createUser([
                'tenant_id' => $tenantId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'tenant_admin',
                'is_active' => 1
            ]);

            $subscriptionModel = new Subscription();
            $subscriptionModel->createSubscription($tenantId, $data['plan_id']);

            $apiKeyModel = new ApiKey();
            $apiKeyModel->generateKey($tenantId, 'Default API Key');

            $this->db->getConnection()->commit();

            $user = $this->userModel->find($userId);
            Auth::login($user);

            Session::setFlash('success', 'Registration successful! Welcome to SplashForms!');
            return $this->redirect('/dashboard');

        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            Session::setFlash('error', 'Registration failed. Please try again.');
            return $this->back();
        }
    }

    public function logout($request, $response) {
        Auth::logout();
        Session::setFlash('success', 'You have been logged out');
        return $this->redirect('/login');
    }
}
