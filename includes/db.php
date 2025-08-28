<?php
// Database connection file - PostgreSQL version

// Only define constants if they haven't been defined yet
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DATABASE_HOST'] ?? 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['DATABASE_USER'] ?? 'postgres');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $_ENV['DATABASE_PASSWORD'] ?? '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DATABASE_NAME'] ?? 'haircare_db');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', $_ENV['DATABASE_PORT'] ?? '5432');
}

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
        ]
    );
    
    echo "Database connected successfully!<br>";
    
} catch (PDOException $e) {
    die("PostgreSQL Database connection error: " . $e->getMessage());
}
?>
