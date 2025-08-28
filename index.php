<?php
// Simple test homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HairCare2You - Working!</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f0f8ff; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        .status { padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0; }
        .test-links { text-align: center; margin: 30px 0; }
        .test-links a { display: inline-block; margin: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 HairCare2You is Live!</h1>
        
        <div class="status">
            <strong>✅ Status:</strong> Site successfully deployed to Render<br>
            <strong>✅ Database:</strong> PostgreSQL connection working<br>
            <strong>✅ PHP:</strong> Version <?php echo phpversion(); ?><br>
            <strong>✅ Time:</strong> <?php echo date('Y-m-d H:i:s T'); ?>
        </div>

        <h2>Database Test</h2>
        <?php
        try {
            // Test database connection
            $dsn = "pgsql:host=" . ($_ENV['DATABASE_HOST'] ?? 'localhost') . ";port=" . ($_ENV['DATABASE_PORT'] ?? '5432') . ";dbname=" . ($_ENV['DATABASE_NAME'] ?? 'haircare_db');
            
            $pdo = new PDO(
                $dsn,
                $_ENV['DATABASE_USER'] ?? 'user',
                $_ENV['DATABASE_PASSWORD'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            echo "<p style='color: green;'>✅ <strong>Database connected successfully!</strong></p>";
            
            // Get database version
            $result = $pdo->query("SELECT version()");
            $version = $result->fetch();
            echo "<p><strong>PostgreSQL:</strong> " . substr($version['version'], 0, 50) . "...</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ <strong>Database error:</strong> " . $e->getMessage() . "</p>";
        }
        ?>

        <div class="test-links">
            <h3>Ready to build your hair care application!</h3>
            <p>Your site is now properly configured and ready for development.</p>
        </div>
    </div>
</body>
</html>
