<?php
// FILE: /app/controllers/HomeController.php

class HomeController extends Controller {
    public function index($request, $response) {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }

        $planModel = new Plan();
        $plans = $planModel->getActivePlans();

        $this->view('home/index', [
            'plans' => $plans
        ]);
    }

    public function pricing($request, $response) {
        $planModel = new Plan();
        $plans = $planModel->getActivePlans();

        $this->view('home/pricing', [
            'plans' => $plans
        ]);
    }

    public function docs($request, $response) {
        $this->view('home/docs', []);
    }
}
