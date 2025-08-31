<?php
// Database setup script - PostgreSQL version for Render
require_once 'includes/db.php';

try {
    echo "<h2>Setting up HairCare2U Database (PostgreSQL)...</h2>";
    
    // Create users table
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'candidate',
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        department VARCHAR(100) DEFAULT 'ALL',
        job_position_id INTEGER NULL,
        reset_token VARCHAR(64) NULL,
        reset_expires TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Users table created<br>";

    // Create job_positions table
    $sql = "
    CREATE TABLE IF NOT EXISTS job_positions (
        id SERIAL PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        description TEXT,
        required_skills TEXT NOT NULL,
        experience_level VARCHAR(20) DEFAULT 'mid',
        status VARCHAR(20) DEFAULT 'active',
        created_by INTEGER,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Job positions table created<br>";

    // Create applications table
    $sql = "
    CREATE TABLE IF NOT EXISTS applications (
        id SERIAL PRIMARY KEY,
        candidate_id INTEGER REFERENCES users(id),
        job_position_id INTEGER REFERENCES job_positions(id),
        resume_filename VARCHAR(255) NOT NULL,
        resume_path VARCHAR(500) NOT NULL,
        api_response TEXT,
        extracted_skills TEXT,
        extracted_experience TEXT,
        extracted_education TEXT,
        extracted_contact JSONB,
        match_percentage DECIMAL(5,2) DEFAULT 0.00,
        api_processing_status VARCHAR(20) DEFAULT 'pending',
        api_error_message TEXT,
        status VARCHAR(50) DEFAULT 'pending',
        hr_notes TEXT,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Applications table created<br>";

    // Create onboarding_tasks table
    $sql = "
    CREATE TABLE IF NOT EXISTS onboarding_tasks (
        id SERIAL PRIMARY KEY,
        task_name VARCHAR(150) NOT NULL,
        description TEXT,
        department VARCHAR(50) DEFAULT 'ALL',
        is_mandatory BOOLEAN DEFAULT TRUE,
        order_sequence INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Onboarding tasks table created<br>";

    // Create employee_onboarding table
    $sql = "
    CREATE TABLE IF NOT EXISTS employee_onboarding (
        id SERIAL PRIMARY KEY,
        employee_id INTEGER REFERENCES users(id),
        task_id INTEGER REFERENCES onboarding_tasks(id),
        status VARCHAR(20) DEFAULT 'pending',
        completed_at TIMESTAMP NULL,
        notes TEXT,
        UNIQUE(employee_id, task_id)
    );";
    $pdo->exec($sql);
    echo "✅ Employee onboarding table created<br>";

    // Create training_modules table
    $sql = "
    CREATE TABLE IF NOT EXISTS training_modules (
        id SERIAL PRIMARY KEY,
        module_name VARCHAR(100) NOT NULL,
        description TEXT,
        content_url VARCHAR(500),
        department VARCHAR(50) DEFAULT 'ALL',
        duration_hours INTEGER DEFAULT 1,
        is_mandatory BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Training modules table created<br>";

    // Create employee_training table
    $sql = "
    CREATE TABLE IF NOT EXISTS employee_training (
        id SERIAL PRIMARY KEY,
        employee_id INTEGER REFERENCES users(id),
        module_id INTEGER REFERENCES training_modules(id),
        status VARCHAR(20) DEFAULT 'not_started',
        progress_percentage INTEGER DEFAULT 0,
        started_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        UNIQUE(employee_id, module_id)
    );";
    $pdo->exec($sql);
    echo "✅ Employee training table created<br>";

    // Create employee_documents table
    $sql = "
    CREATE TABLE IF NOT EXISTS employee_documents (
        id SERIAL PRIMARY KEY,
        employee_id INTEGER REFERENCES users(id),
        document_name VARCHAR(255) NOT NULL,
        document_type VARCHAR(100),
        file_path VARCHAR(500),
        file_size INTEGER,
        status VARCHAR(20) DEFAULT 'pending',
        is_required BOOLEAN DEFAULT FALSE,
        description TEXT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP NULL,
        reviewer_notes TEXT,
        UNIQUE(employee_id, document_type)
    );";
    $pdo->exec($sql);
    echo "✅ Employee documents table created<br>";

    // Create chatbot tables
    $sql = "
    CREATE TABLE IF NOT EXISTS chatbot_faq (
        id SERIAL PRIMARY KEY,
        question VARCHAR(255) NOT NULL,
        answer TEXT NOT NULL,
        category VARCHAR(50),
        keywords TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Chatbot FAQ table created<br>";

    $sql = "
    CREATE TABLE IF NOT EXISTS chatbot_settings (
        id SERIAL PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        description TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Chatbot settings table created<br>";

    $sql = "
    CREATE TABLE IF NOT EXISTS chat_conversations (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id),
        session_id VARCHAR(100),
        message TEXT NOT NULL,
        response TEXT NOT NULL,
        message_type VARCHAR(10) NOT NULL,
        api_response_time DECIMAL(8,3),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Chat conversations table created<br>";

    // Insert demo data only if tables are empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    if ($userCount == 0) {
        echo "<h3>Inserting demo data...</h3>";
        
        // Insert demo users
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $users = [
            ['hr_admin', 'hr@haircare2u.my', $hashedPassword, 'HR Administrator', 'hr', 'ALL'],
            ['alice123', 'alice@example.com', $hashedPassword, 'Alice Tan', 'candidate', 'ALL'],
            ['john_d', 'john.doe@example.com', $hashedPassword, 'John Doe', 'employee', 'IT'],
            ['siti_r', 'siti.rahman@example.com', $hashedPassword, 'Siti Rahman', 'employee', 'Sales & Marketing']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, department) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($users as $user) {
            $stmt->execute($user);
        }
        echo "✅ Demo users created<br>";

        // Insert job positions
        $jobs = [
            ['Software Developer', 'IT', 'Full-stack developer position', 'PHP,JavaScript,MySQL,HTML,CSS', 'mid'],
            ['Marketing Specialist', 'Sales & Marketing', 'Digital marketing role', 'SEO,Social Media,Analytics,Content Writing', 'entry'],
            ['Product Specialist', 'Sales & Marketing', 'Product promotion and demonstrations', 'Product Knowledge,Sales Techniques,Presentation Skills', 'entry'],
            ['Customer Support Representative', 'Customer Service', 'Customer assistance and support', 'Communication,Problem Solving,CRM Tools,Patience', 'entry']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO job_positions (title, department, description, required_skills, experience_level, created_by) VALUES (?, ?, ?, ?, ?, 1)");
        foreach ($jobs as $job) {
            $stmt->execute($job);
        }
        echo "✅ Demo job positions created<br>";

        // Insert onboarding tasks
        $tasks = [
            ['Complete Personal Information Form', 'Fill out personal details and emergency contacts', 'ALL', TRUE, 1],
            ['IT Equipment Setup', 'Receive laptop, access cards, and accounts', 'ALL', TRUE, 2],
            ['Company Orientation', 'Attend company culture and policy session', 'ALL', TRUE, 3],
            ['Department Introduction', 'Meet team members and understand role', 'ALL', TRUE, 4],
            ['Security Training', 'Complete cybersecurity awareness training', 'IT', TRUE, 5],
            ['Product Knowledge Training', 'Learn about company products and services', 'Sales & Marketing', TRUE, 6]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO onboarding_tasks (task_name, description, department, is_mandatory, order_sequence) VALUES (?, ?, ?, ?, ?)");
        foreach ($tasks as $task) {
            $stmt->execute($task);
        }
        echo "✅ Demo onboarding tasks created<br>";

        // Insert training modules
        $modules = [
            ['Company Policies', 'Learn about company rules and regulations', NULL, 'ALL', 2, TRUE],
            ['Cybersecurity Awareness', 'Security best practices and protocols', NULL, 'ALL', 1, TRUE],
            ['PHP Development Basics', 'Introduction to PHP programming', NULL, 'IT', 8, TRUE],
            ['Marketing Fundamentals', 'Basic marketing principles', NULL, 'Sales & Marketing', 4, TRUE],
            ['Customer Service Excellence', 'Providing outstanding customer service', NULL, 'Customer Service', 3, TRUE]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO training_modules (module_name, description, content_url, department, duration_hours, is_mandatory) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($modules as $module) {
            $stmt->execute($module);
        }
        echo "✅ Demo training modules created<br>";

        // Insert basic chatbot data
        $faqs = [
            ['What are my working hours?', 'Standard working hours are 9 AM to 6 PM, Monday to Friday. Flexible timing available after probation period.', 'hr_policy', 'working hours, timing, schedule'],
            ['How do I reset my password?', 'Contact IT support at it@company.com or call extension 1234 to reset your password.', 'technical', 'password reset, login issue, access'],
            ['When will I get my laptop?', 'IT equipment including laptop will be provided within 2-3 business days of joining.', 'onboarding', 'laptop, equipment, IT setup'],
            ['Hello', 'Hello! How can I help you today?', 'greeting', 'hi, hey, hello, good morning']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO chatbot_faq (question, answer, category, keywords) VALUES (?, ?, ?, ?)");
        foreach ($faqs as $faq) {
            $stmt->execute($faq);
        }
        echo "✅ Demo chatbot FAQ created<br>";

        // Insert chatbot settings
        $settings = [
            ['chatbot_enabled', '1', 'Enable/disable chatbot functionality'],
            ['api_provider', 'openai', 'Chatbot provider: static, openai, dialogflow'],
            ['default_response', "I apologize, but I couldn't find a specific answer to your question. Please contact HR at hr@haircare2u.my for further assistance.", 'Default response when no match found'],
            ['greeting_message', "Hello! I'm your AI-powered HR assistant. How can I help you today?", 'Initial greeting message']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO chatbot_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        echo "✅ Chatbot settings created<br>";
    } else {
        echo "ℹ️ Database already has data ($userCount users found)<br>";
    }

    // Test the setup
    echo "<h3>Database Setup Summary:</h3>";
    $tables = ['users', 'job_positions', 'applications', 'onboarding_tasks', 'training_modules'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "• $table: $count records<br>";
    }
    
    echo "<br><h3>🎉 Database setup completed successfully!</h3>";
    echo "<h4>Demo Login Credentials:</h4>";
    echo "<ul>";
    echo "<li><strong>HR Admin:</strong> hr_admin / password123</li>";
    echo "<li><strong>Candidate:</strong> alice123 / password123</li>";
    echo "<li><strong>Employee:</strong> john_d / password123</li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    echo "<p><strong>Security Note:</strong> Delete this setup_database.php file after running it.</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Database setup failed:</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
    
    // Show connection details for debugging (remove in production)
    echo "<h4>Debug Info:</h4>";
    echo "<p>Check that your environment variables are set:</p>";
    echo "<ul>";
    echo "<li>DATABASE_HOST</li>";
    echo "<li>DATABASE_USER</li>";
    echo "<li>DATABASE_NAME</li>";
    echo "<li>DATABASE_PASSWORD</li>";
    echo "</ul>";
}
?>
