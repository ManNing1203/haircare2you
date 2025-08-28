<?php
echo "<h1>PHP Debug Test</h1>";

echo "<h2>1. PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br><br>";

echo "<h2>2. Environment Variables</h2>";
echo "DATABASE_HOST: " . ($_ENV['DATABASE_HOST'] ?? 'NOT SET') . "<br>";
echo "DATABASE_USER: " . ($_ENV['DATABASE_USER'] ?? 'NOT SET') . "<br>";
echo "DATABASE_NAME: " . ($_ENV['DATABASE_NAME'] ?? 'NOT SET') . "<br>";
echo "DATABASE_PORT: " . ($_ENV['DATABASE_PORT'] ?? 'NOT SET') . "<br>";
echo "PASSWORD: " . (isset($_ENV['DATABASE_PASSWORD']) ? "SET" : "NOT SET") . "<br><br>";

echo "<h2>3. PDO Drivers Available</h2>";
if (extension_loaded('pdo')) {
    echo "PDO is loaded<br>";
    $drivers = PDO::getAvailableDrivers();
    echo "Available drivers: " . implode(', ', $drivers) . "<br><br>";
} else {
    echo "PDO is NOT loaded<br><br>";
}

echo "<h2>4. Database Connection Test</h2>";
try {
    if (isset($_ENV['DATABASE_HOST'])) {
        $dsn = "pgsql:host=" . $_ENV['DATABASE_HOST'] . ";port=" . $_ENV['DATABASE_PORT'] . ";dbname=" . $_ENV['DATABASE_NAME'];
        
        $pdo = new PDO(
            $dsn,
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "✅ Database connection successful!<br>";
        
        // Test a simple query
        $result = $pdo->query("SELECT version()");
        $version = $result->fetch();
        echo "PostgreSQL version: " . $version['version'] . "<br>";
        
    } else {
        echo "❌ Environment variables not set<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

echo "<h2>5. File System Check</h2>";
echo "Current directory: " . getcwd() . "<br>";
echo "Files in current directory: " . implode(', ', scandir('.')) . "<br>";
?>
