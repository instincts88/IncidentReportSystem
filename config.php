<?php
/**
 * Incident Report System - Configuration
 */

// Environment
define('APP_ENV', 'production'); // 'development' or 'production'
define('APP_NAME', 'Incident Report System');
define('APP_VERSION', '2.0.0');
define('BASE_URL', 'http://localhost/IncidentReportSystem');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'incident_report_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('BCRYPT_COST', 12);
define('SESSION_LIFETIME', 3600); // 1 hour
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// File Uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg','image/png','image/gif','application/pdf','text/plain']);

// Pagination
define('ITEMS_PER_PAGE', 15);

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}
