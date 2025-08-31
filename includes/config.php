<?php
// Database configuration - PostgreSQL
define('DB_HOST', $_ENV['DATABASE_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DATABASE_USER'] ?? 'root');
define('DB_PASS', $_ENV['DATABASE_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DATABASE_NAME'] ?? 'haircare_db');
define('DB_PORT', $_ENV['DATABASE_PORT'] ?? '5432');
define('API_TIMEOUT', 30);
define('API_CONNECT_TIMEOUT', 10);

// Debug: Show connection info (remove in production)
if (isset($_ENV['DATABASE_HOST'])) {
    error_log("Connecting to PostgreSQL: " . $_ENV['DATABASE_HOST'] . ":" . $_ENV['DATABASE_PORT']);
}

// Extracta.ai API Configuration - FIXED VERSION
$extracta_api_key = $_ENV['EXTRACTA_API_KEY'] ?? getenv('EXTRACTA_API_KEY') ?? null;
$extracta_extraction_id = $_ENV['EXTRACTA_EXTRACTION_ID'] ?? getenv('EXTRACTA_EXTRACTION_ID') ?? '-OY-tgWlYrpfUxqIQqWr';

// ALWAYS define constants (this was missing before)
if (!empty($extracta_api_key)) {
    define('EXTRACTA_API_KEY', trim($extracta_api_key));
} else {
    error_log("ERROR: EXTRACTA_API_KEY not found in environment variables");
}

// FIXED: Always define the extraction ID constant
define('EXTRACTA_EXTRACTION_ID', trim($extracta_extraction_id));

// File upload settings for Render
define('UPLOAD_PATH', '/tmp/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Ensure upload directory exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Debug logging
error_log("Config loaded - API Key: " . (defined('EXTRACTA_API_KEY') ? 'Found' : 'Missing'));
error_log("Config loaded - Extraction ID: " . EXTRACTA_EXTRACTION_ID);
