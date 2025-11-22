<?php
// FILE: /app/core/Controller.php

class Controller {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function validateCsrf() {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$token || !hash_equals(Session::get('csrf_token'), $token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }

    protected function generateCsrf() {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    protected function uploadFile($file, $allowedTypes = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed'];
        }

        $allowedTypes = $allowedTypes ?? ALLOWED_EXTENSIONS;
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedTypes)) {
            return ['success' => false, 'error' => 'File type not allowed'];
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return ['success' => false, 'error' => 'File size exceeds limit'];
        }

        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = UPLOAD_DIR . $fileName;

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'filename' => $fileName,
                'path' => $uploadPath,
                'url' => '/uploads/' . $fileName
            ];
        }

        return ['success' => false, 'error' => 'Failed to save file'];
    }

    protected function paginate($query, $params, $page = 1, $perPage = null) {
        $perPage = $perPage ?? ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $countQuery = preg_replace('/SELECT .+ FROM/i', 'SELECT COUNT(*) FROM', $query);
        $total = $this->db->fetchColumn($countQuery, $params);

        $query .= " LIMIT $perPage OFFSET $offset";
        $items = $this->db->fetchAll($query, $params);

        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
}
