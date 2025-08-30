<?php
// Debug file to test dashboard issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Dashboard Debug Test</h2>";

echo "<h3>1. PDO Drivers Check</h3>";
if (extension_loaded('pdo')) {
    echo "✅ PDO extension is loaded<br>";
    $drivers = PDO::getAvailableDrivers();
    echo "Available drivers: " . implode(', ', $drivers) . "<br>";
    
    if (in_array('pgsql', $drivers)) {
        echo "✅ PostgreSQL driver (pgsql) is available<br>";
    } else {
        echo "❌ PostgreSQL driver (pgsql) is NOT available<br>";
    }
} else {
    echo "❌ PDO extension is not loaded<br>";
}

echo "<h3>2. Environment Variables</h3>";
echo "DATABASE_HOST: " . ($_ENV['DATABASE_HOST'] ?? 'NOT SET') . "<br>";
echo "DATABASE_USER: " . ($_ENV['DATABASE_USER'] ?? 'NOT SET') . "<br>";
echo "DATABASE_NAME: " . ($_ENV['DATABASE_NAME'] ?? 'NOT SET') . "<br>";
echo "DATABASE_PORT: " . ($_ENV['DATABASE_PORT'] ?? 'NOT SET') . "<br>";

echo "<h3>3. Config Constants</h3>";
try {
    require_once 'includes/config.php';
    echo "✅ Config loaded<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_USER: " . DB_USER . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
    echo "DB_PORT: " . DB_PORT . "<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
}

echo "<h3>4. DB Connection Test</h3>";
try {
    require_once 'includes/db.php';
    echo "✅ DB connection successful<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch();
    echo "✅ Query successful - User count: " . $result['user_count'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ DB connection failed: " . $e->getMessage() . "<br>";
    echo "❌ Error details: " . $e->getTraceAsString() . "<br>";
}

echo "<h3>5. Session Check</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ User logged in - ID: " . $_SESSION['user_id'] . "<br>";
    echo "✅ Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";
} else {
    echo "❌ No active session<br>";
}

echo "<h3>6. File Check</h3>";
$dashboard_files = ['hr-dashboard.php', 'employee-dashboard.php', 'candidate-dashboard.php'];
foreach ($dashboard_files as $file) {
    if (file_exists($file)) {
        echo "✅ " . $file . " exists<br>";
    } else {
        echo "❌ " . $file . " missing<br>";
    }
}
