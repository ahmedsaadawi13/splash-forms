<?php
// FILE: /app/core/Response.php

class Response {
    private $statusCode = 200;
    private $headers = [];
    private $body = '';

    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setBody($body) {
        $this->body = $body;
        return $this;
    }

    public function send() {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo $this->body;
    }

    public function json($data, $statusCode = 200) {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        $this->setBody(json_encode($data));
        $this->send();
    }

    public function redirect($url, $statusCode = 302) {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->send();
        exit;
    }

    public function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    public function view($view, $data = []) {
        extract($data);
        ob_start();
        require_once __DIR__ . '/../views/' . $view . '.php';
        $this->setBody(ob_get_clean());
        $this->send();
    }

    public function notFound($message = 'Page not found') {
        $this->setStatusCode(404);
        $this->setBody($message);
        $this->send();
    }

    public function error($message = 'Internal server error', $statusCode = 500) {
        $this->setStatusCode($statusCode);
        $this->setBody($message);
        $this->send();
    }

    public function download($filePath, $fileName = null) {
        if (!file_exists($filePath)) {
            $this->notFound('File not found');
            return;
        }

        $fileName = $fileName ?? basename($filePath);

        $this->setHeader('Content-Type', 'application/octet-stream');
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->setHeader('Content-Length', filesize($filePath));
        $this->setBody(file_get_contents($filePath));
        $this->send();
    }
}
