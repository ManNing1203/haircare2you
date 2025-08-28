<?php
// Security check - prevent direct access
// if (!defined('APP_INIT')) {
//     die('Direct access not allowed');
// }

// Database configuration - reads from environment variables
// Render provides DATABASE_URL, we need to parse it
if (isset($_ENV['DATABASE_URL'])) {
    // Parse the DATABASE_URL for PostgreSQL (Render's format)
    $url = parse_url($_ENV['DATABASE_URL']);
    define('DB_HOST', $url['host']);
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
    define('DB_NAME', ltrim($url['path'], '/'));
    define('DB_PORT', $url['port'] ?? 5432);
} else {
    // Fallback to InfinityFree settings (for local development)
    define('DB_HOST', 'sql306.infinityfree.com');
    define('DB_USER', 'if0_39803548');
    define('DB_PASS', 'oW3DTBqxnRL');
    define('DB_NAME', 'if0_39803548_talent_acquisition');
    define('DB_PORT', 3306);
}

// Extracta.ai API Configuration - reads from environment
define('EXTRACTA_API_KEY', $_ENV['EXTRACTA_API_KEY'] ?? ' ');
define('EXTRACTA_EXTRACTION_ID', $_ENV['EXTRACTA_EXTRACTION_ID'] ?? '-OY-tgWlYrpfUxqIQqWr');

// API Timeout Configuration
define('API_TIMEOUT', 30);           // 30 seconds timeout for API requests
define('API_CONNECT_TIMEOUT', 10);   // 10 seconds connection timeout

// Database connection function for both MySQL and PostgreSQL
function getDatabaseConnection() {
    try {
        if (defined('DB_PORT') && DB_PORT == 5432) {
            // PostgreSQL connection (Render)
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            return new PDO($dsn, DB_USER, DB_PASS);
        } else {
            // MySQL connection (InfinityFree)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            return new PDO($dsn, DB_USER, DB_PASS);
        }
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}
?>
