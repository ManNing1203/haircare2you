<?php
// Database connection file - PostgreSQL version
// Enhanced environment variable handling

// Function to get environment variable with fallback
function getEnvVar($key, $default = null) {
    // Try $_ENV first
    if (isset($_ENV[$key]) && !empty($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    // Try getenv() as fallback
    $value = getenv($key);
    if ($value !== false && !empty($value)) {
        return $value;
    }
    
    // Try $_SERVER as last resort
    if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    
    return $default;
}

// Only define constants if they haven't been defined yet
if (!defined('DB_HOST')) {
    define('DB_HOST', getEnvVar('DATABASE_HOST', 'localhost'));
}
if (!defined('DB_USER')) {
    define('DB_USER', getEnvVar('DATABASE_USER', 'postgres'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getEnvVar('DATABASE_PASSWORD', ''));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getEnvVar('DATABASE_NAME', 'haircare_db'));
}
if (!defined('DB_PORT')) {
    define('DB_PORT', getEnvVar('DATABASE_PORT', '5432'));
}

// Debug information (remove in production)
error_log("DB Connection Debug - Host: " . DB_HOST . ", User: " . DB_USER . ", DB: " . DB_NAME . ", Port: " . DB_PORT);

// Create PDO database connection for PostgreSQL
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 30, // Add connection timeout
            PDO::ATTR_PERSISTENT => false // Disable persistent connections in cloud env
        ]
    );
    
    // Test the connection
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // Log the full error for debugging
    error_log("Database connection error: " . $e->getMessage());
    error_log("Connection string: pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME);
    
    die("PostgreSQL Database connection error: " . $e->getMessage());
}
?>
