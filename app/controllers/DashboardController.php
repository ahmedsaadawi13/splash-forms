<?php
// FILE: /app/controllers/DashboardController.php

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
    }

    public function index($request, $response) {
        $tenantId = Auth::tenantId();

        $formModel = new Form();
        $submissionModel = new Submission();
        $tenantModel = new Tenant();

        $stats = $tenantModel->getStats($tenantId);

        $recentForms = $formModel->getTenantForms($tenantId);
        $recentForms = array_slice($recentForms, 0, 5);

        $recentSubmissions = $submissionModel->getRecentSubmissions($tenantId, 10);

        $subscription = TenantContext::getSubscription();

        $submissionsThisMonth = $this->db->fetchColumn(
            "SELECT value FROM usage_tracking
            WHERE tenant_id = ? AND metric = 'submissions' AND period = ?",
            [$tenantId, date('Y-m')]
        );

        $this->view('dashboard/index', [
            'stats' => $stats,
            'recentForms' => $recentForms,
            'recentSubmissions' => $recentSubmissions,
            'subscription' => $subscription,
            'submissionsThisMonth' => $submissionsThisMonth ?? 0,
            'user' => Auth::user()
        ]);
    }

    public function analytics($request, $response) {
        $tenantId = Auth::tenantId();
        $formModel = new Form();

        $forms = $formModel->getTenantForms($tenantId);

        $analyticsData = [];

        foreach ($forms as $form) {
            $submissionModel = new Submission();
            $stats = $submissionModel->getSubmissionStats($form['id'], 30);

            $analyticsData[$form['id']] = [
                'form' => $form,
                'stats' => $stats
            ];
        }

        $this->view('dashboard/analytics', [
            'analyticsData' => $analyticsData,
            'user' => Auth::user()
        ]);
    }
}
