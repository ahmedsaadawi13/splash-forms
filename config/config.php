<?php
// FILE: /config/config.php

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'splashforms');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'SplashForms');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Security
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('CSRF_TOKEN_NAME', '_token');
define('PASSWORD_MIN_LENGTH', 8);

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../storage/uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB default
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// API Configuration
define('API_RATE_LIMIT', 100); // requests per hour

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
