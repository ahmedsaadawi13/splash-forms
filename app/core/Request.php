<?php
// FILE: /app/core/Request.php

class Request {
    private $params;
    private $query;
    private $body;
    private $files;
    private $server;
    private $headers;

    public function __construct() {
        $this->params = [];
        $this->query = $_GET;
        $this->body = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->headers = $this->parseHeaders();
    }

    private function parseHeaders() {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    public function getMethod() {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isGet() {
        return $this->getMethod() === 'GET';
    }

    public function isPost() {
        return $this->getMethod() === 'POST';
    }

    public function isPut() {
        return $this->getMethod() === 'PUT';
    }

    public function isDelete() {
        return $this->getMethod() === 'DELETE';
    }

    public function isAjax() {
        return isset($this->headers['X-REQUESTED-WITH'])
            && strtolower($this->headers['X-REQUESTED-WITH']) === 'xmlhttprequest';
    }

    public function getPath() {
        $path = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path;
    }

    public function get($key, $default = null) {
        return $this->query[$key] ?? $default;
    }

    public function post($key, $default = null) {
        return $this->body[$key] ?? $default;
    }

    public function input($key, $default = null) {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all() {
        return array_merge($this->query, $this->body);
    }

    public function only($keys) {
        $result = [];
        $all = $this->all();
        foreach ($keys as $key) {
            if (isset($all[$key])) {
                $result[$key] = $all[$key];
            }
        }
        return $result;
    }

    public function file($key) {
        return $this->files[$key] ?? null;
    }

    public function hasFile($key) {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function setParams($params) {
        $this->params = $params;
    }

    public function param($key, $default = null) {
        return $this->params[$key] ?? $default;
    }

    public function getHeader($key, $default = null) {
        return $this->headers[$key] ?? $default;
    }

    public function getIp() {
        if (!empty($this->server['HTTP_CLIENT_IP'])) {
            return $this->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $this->server['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    public function getUserAgent() {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function validate($rules) {
        return Validator::validate($this->all(), $rules);
    }

    public function sanitize($key) {
        $value = $this->input($key);
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    public function getJson() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
}
