<?php
// Database setup script - run this once to create tables
require_once 'includes/db.php';

try {
    echo "<h2>Setting up Vector Database...</h2>";
    
    // Create users table
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'candidate',
        reset_token VARCHAR(64) NULL,
        reset_expires TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    
    $pdo->exec($sql);
    echo "✅ Users table created successfully<br>";
    
    // Create indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);",
        "CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);",
        "CREATE INDEX IF NOT EXISTS idx_users_reset_token ON users(reset_token);"
    ];
    
    foreach ($indexes as $index) {
        $pdo->exec($index);
    }
    echo "✅ Indexes created successfully<br>";
    
    // Check if demo users already exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    if ($userCount == 0) {
        // Insert demo users (password is 'password123' for all)
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        $demoUsers = [
            ['hr_admin', 'hr@vector.com', $hashedPassword, 'HR Administrator', 'hr'],
            ['alice123', 'alice@example.com', $hashedPassword, 'Alice Johnson', 'candidate'],
            ['john_d', 'john@example.com', $hashedPassword, 'John Doe', 'employee']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($demoUsers as $user) {
            $stmt->execute($user);
        }
        
        echo "✅ Demo users created successfully<br>";
        echo "<ul>";
        echo "<li>HR Admin: hr_admin / password123</li>";
        echo "<li>Candidate: alice123 / password123</li>";
        echo "<li>Employee: john_d / password123</li>";
        echo "</ul>";
    } else {
        echo "ℹ️ Users already exist in database ($userCount users)<br>";
    }
    
    // Test the setup
    echo "<h3>Testing Database Setup:</h3>";
    $stmt = $pdo->query("SELECT username, email, role FROM users ORDER BY role, username");
    $users = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Username</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><h3>🎉 Database setup completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to Login Page</a></p>";
    echo "<p><strong>Note:</strong> Delete this setup_database.php file after running it for security.</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Database setup failed:</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>
