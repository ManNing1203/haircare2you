<?php
// Database configuration - PostgreSQL
define('DB_HOST', $_ENV['DATABASE_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DATABASE_USER'] ?? 'root');
define('DB_PASS', $_ENV['DATABASE_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DATABASE_NAME'] ?? 'haircare_db');
define('DB_PORT', $_ENV['DATABASE_PORT'] ?? '5432');
define('API_TIMEOUT', 30);           // 30 seconds timeout for API requests
define('API_CONNECT_TIMEOUT', 10);

// Debug: Show connection info (remove in production)
if (isset($_ENV['DATABASE_HOST'])) {
    error_log("Connecting to PostgreSQL: " . $_ENV['DATABASE_HOST'] . ":" . $_ENV['DATABASE_PORT']);
}

// Extracta.ai API Configuration - reads from environment with proper fallbacks
$extracta_api_key = $_ENV['EXTRACTA_API_KEY'] ?? getenv('EXTRACTA_API_KEY') ?? null;
$extracta_extraction_id = $_ENV['EXTRACTA_EXTRACTION_ID'] ?? getenv('EXTRACTA_EXTRACTION_ID') ?? '-OY-tgWlYrpfUxqIQqWr';

// Define constants only if we have valid values
if (!empty($extracta_api_key)) {
    define('EXTRACTA_API_KEY', $extracta_api_key);
}

if (!empty($extracta_extraction_id)) {
    define('EXTRACTA_EXTRACTION_ID', $extracta_extraction_id);
}

// NO CLOSING TAG - this prevents header issues
