<?php
// Database permissions test
require_once 'includes/db.php';

try {
    echo "<h3>Database Permissions Test</h3>";
    
    // Test basic connection
    echo "✅ Database connection successful<br>";
    
    // Test if we can see tables
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll();
    
    echo "📋 Available tables:<br>";
    foreach ($tables as $table) {
        echo "- " . $table['table_name'] . "<br>";
    }
    
    // Test a simple SELECT (adjust table name as needed)
    // Replace 'users' with your actual users table name
    $testQuery = "SELECT COUNT(*) as count FROM users LIMIT 1";
    try {
        $stmt = $pdo->query($testQuery);
        $result = $stmt->fetch();
        echo "✅ Can read from users table: " . $result['count'] . " records<br>";
    } catch (PDOException $e) {
        echo "❌ Cannot read from users table: " . $e->getMessage() . "<br>";
        
        // Try to list all tables to help debug
        echo "Available tables in database:<br>";
        $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        while ($row = $stmt->fetch()) {
            echo "- " . $row['tablename'] . "<br>";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>
