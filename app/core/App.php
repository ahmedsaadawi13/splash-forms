<?php
// FILE: /app/core/App.php

class App {
    private $router;
    private $request;
    private $response;

    public function __construct() {
        $this->router = new Router();
        $this->request = new Request();
        $this->response = new Response();

        Session::start();
        $this->loadRoutes();
    }

    private function loadRoutes() {
        require_once __DIR__ . '/../../routes.php';
    }

    public function getRouter() {
        return $this->router;
    }

    public function run() {
        $this->router->resolve($this->request, $this->response);
    }
}
