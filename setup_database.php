<?php
// Complete Database setup script - PostgreSQL version for Render
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

    // Create parsed_resumes table
    $sql = "
    CREATE TABLE IF NOT EXISTS parsed_resumes (
        id SERIAL PRIMARY KEY,
        original_filename VARCHAR(255) NOT NULL,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(50),
        address VARCHAR(500),
        linkedin VARCHAR(500),
        github VARCHAR(500),
        work_experience TEXT,
        education TEXT,
        languages TEXT,
        skills TEXT,
        certificates TEXT,
        raw_data TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "✅ Parsed resumes table created<br>";

    // Create support_tickets table
    $sql = "
    CREATE TABLE IF NOT EXISTS support_tickets (
        id SERIAL PRIMARY KEY,
        employee_id INTEGER REFERENCES users(id),
        subject VARCHAR(255) NOT NULL,
        category VARCHAR(100) DEFAULT 'general',
        priority VARCHAR(20) DEFAULT 'medium',
        status VARCHAR(20) DEFAULT 'open',
        description TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL
    );";
    $pdo->exec($sql);
    echo "✅ Support tickets table created<br>";

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

    // Create PostgreSQL function equivalent to MySQL stored procedure
    $sql = "
    CREATE OR REPLACE FUNCTION hire_candidate(p_application_id INTEGER)
    RETURNS BOOLEAN AS $$
    DECLARE
        v_candidate_id INTEGER;
        v_job_position_id INTEGER;
        v_department VARCHAR(50);
        v_job_title VARCHAR(100);
    BEGIN
        -- Get application details
        SELECT candidate_id, job_position_id INTO v_candidate_id, v_job_position_id
        FROM applications 
        WHERE id = p_application_id;
        
        IF NOT FOUND THEN
            RETURN FALSE;
        END IF;
        
        -- Get job details
        SELECT department, title INTO v_department, v_job_title
        FROM job_positions 
        WHERE id = v_job_position_id;
        
        -- Update application status
        UPDATE applications 
        SET status = 'hired', updated_at = CURRENT_TIMESTAMP 
        WHERE id = p_application_id;
        
        -- Update user role and department
        UPDATE users 
        SET 
            role = 'employee',
            department = v_department,
            job_position_id = v_job_position_id,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = v_candidate_id;
        
        -- Create onboarding tasks
        INSERT INTO employee_onboarding (employee_id, task_id, status)
        SELECT v_candidate_id, ot.id, 'pending'
        FROM onboarding_tasks ot
        WHERE ot.department = v_department OR ot.department = 'ALL'
        ON CONFLICT (employee_id, task_id) DO NOTHING;
        
        -- Create training assignments
        INSERT INTO employee_training (employee_id, module_id, status, progress_percentage)
        SELECT v_candidate_id, tm.id, 'not_started', 0
        FROM training_modules tm
        WHERE tm.department = v_department OR tm.department = 'ALL'
        ON CONFLICT (employee_id, module_id) DO NOTHING;
        
        -- Create employee documents
        INSERT INTO employee_documents (employee_id, document_name, document_type, status, is_required, description)
        VALUES 
        (v_candidate_id, 'Employment Contract', 'contract', 'pending', TRUE, 'Your employment contract and terms of service'),
        (v_candidate_id, 'Personal Information Form', 'personal_form', 'pending', TRUE, 'Complete personal details and emergency contacts'),
        (v_candidate_id, 'Bank Details Form', 'bank_form', 'pending', TRUE, 'Banking information for salary processing'),
        (v_candidate_id, 'ID Copy', 'identification', 'pending', TRUE, 'Copy of your identification document (IC/Passport)'),
        (v_candidate_id, 'Educational Certificates', 'education', 'pending', TRUE, 'Copies of your educational qualifications')
        ON CONFLICT (employee_id, document_type) DO NOTHING;
        
        RETURN TRUE;
    END;
    $$ LANGUAGE plpgsql;";
    $pdo->exec($sql);
    echo "✅ Hire candidate function created<br>";

    echo "<h3>Force inserting job positions...</h3>";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM job_positions");
$jobCount = $stmt->fetch()['count'];

if ($jobCount == 0) {
    $jobs = [
        ['Software Developer', 'IT', 'Full-stack developer position', 'PHP,JavaScript,MySQL,HTML,CSS', 'mid'],
        ['Marketing Specialist', 'Sales & Marketing', 'Digital marketing role', 'SEO,Social Media,Analytics,Content Writing', 'entry'],
        ['Project Manager', 'IT', 'Technical project management', 'Agile,Scrum,Leadership,Communication', 'senior'],
        // ... (add all 16 job positions from the original script)
    ];
    
    $stmt = $pdo->prepare("INSERT INTO job_positions (title, department, description, required_skills, experience_level, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($jobs as $job) {
        $stmt->execute(array_merge($job, [1]));
    }
    echo "✅ Job positions inserted successfully!<br>";
}

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
            ['azim_m', 'azim.muhd@example.com', $hashedPassword, 'Muhd Azim Bin Ali', 'candidate', 'ALL'],
            ['bella_lee', 'bella.lee@example.com', $hashedPassword, 'Bella Lee', 'candidate', 'ALL'],
            ['carlos_s', 'carlos.smith@example.com', $hashedPassword, 'Carlos Smith', 'candidate', 'ALL'],
            ['diana_w', 'diana.wong@example.com', $hashedPassword, 'Diana Wong', 'candidate', 'ALL'],
            ['siti_r', 'siti.rahman@example.com', $hashedPassword, 'Siti Rahman', 'employee', 'Sales & Marketing'],
            ['john_d', 'john.doe@example.com', $hashedPassword, 'John Doe', 'employee', 'IT'],
            ['kevin_l', 'kevin.lim@example.com', $hashedPassword, 'Kevin Lim', 'employee', 'Customer Service'],
            ['linda_t', 'linda.tan@example.com', $hashedPassword, 'Linda Tan', 'employee', 'Operations'],
            ['michael_c', 'michael.choo@example.com', $hashedPassword, 'Michael Choo', 'employee', 'ALL']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, department) VALUES (?, ?, ?, ?, ?, ?) RETURNING id");
        $userIds = [];
        foreach ($users as $i => $user) {
            $stmt->execute($user);
            $userIds[$i] = $stmt->fetch()['id'];
        }
        echo "✅ Demo users created<br>";

        // Insert job positions
        $jobs = [
            ['Software Developer', 'IT', 'Full-stack developer position', 'PHP,JavaScript,MySQL,HTML,CSS', 'mid'],
            ['Marketing Specialist', 'Sales & Marketing', 'Digital marketing role', 'SEO,Social Media,Analytics,Content Writing', 'entry'],
            ['Project Manager', 'IT', 'Technical project management', 'Agile,Scrum,Leadership,Communication', 'senior'],
            ['Product Specialist', 'Sales & Marketing', 'Responsible for promoting and demonstrating haircare products to clients and partners.', 'Product Knowledge,Sales Techniques,Presentation Skills,Customer Engagement', 'entry'],
            ['Digital Marketer', 'Sales & Marketing', 'Manage online marketing campaigns for haircare products.', 'SEO,Social Media Ads,Content Creation,Analytics', 'mid'],
            ['Brand Ambassador', 'Sales & Marketing', 'Represent the company brand at events and campaigns.', 'Public Speaking,Influencing Skills,Networking,Product Knowledge', 'entry'],
            ['Customer Support Representative', 'Customer Service', 'Assist customers with inquiries, complaints, and product guidance.', 'Communication,Problem Solving,CRM Tools,Patience', 'entry'],
            ['Beauty Consultant', 'Customer Service', 'Provide personalized haircare consultations to customers.', 'Haircare Knowledge,Customer Service,Product Recommendation,Sales Skills', 'mid'],
            ['Logistics Coordinator', 'Operations', 'Manage supply chain, deliveries, and shipping of haircare products.', 'Logistics Management,Excel,Inventory Tracking,Vendor Coordination', 'mid'],
            ['Inventory Specialist', 'Operations', 'Monitor and maintain stock levels for haircare products.', 'Inventory Management,Attention to Detail,ERP Systems,Excel', 'entry'],
            ['Quality Control Officer', 'Operations', 'Ensure haircare products meet quality and safety standards.', 'Quality Assurance,Detail Orientation,Analytical Thinking,Reporting', 'mid'],
            ['E-commerce Developer', 'IT', 'Develop and maintain the company e-commerce website.', 'PHP,JavaScript,MySQL,Shopify/WordPress', 'mid'],
            ['Data Analyst', 'IT', 'Analyze sales and customer data to improve business decisions.', 'SQL,Excel,Data Visualization,Python/R', 'mid'],
            ['Team Leader', 'Management', 'Lead a small team to achieve sales and operational targets.', 'Leadership,Communication,Reporting,Coaching', 'mid'],
            ['Department Head', 'Management', 'Oversee departmental operations and strategy execution.', 'Strategic Planning,Leadership,Budget Management,Decision Making', 'senior'],
            ['Regional Manager', 'Management', 'Manage business operations across multiple regions.', 'Leadership,Market Knowledge,Negotiation,People Management', 'senior']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO job_positions (title, department, description, required_skills, experience_level, created_by) VALUES (?, ?, ?, ?, ?, ?) RETURNING id");
        $jobIds = [];
        foreach ($jobs as $i => $job) {
            $stmt->execute(array_merge($job, [1])); // created_by = 1 (hr_admin)
            $jobIds[$i] = $stmt->fetch()['id'];
        }
        echo "✅ Demo job positions created<br>";

        // Insert parsed resumes first
        $resumeData = [
            [
                'for_hackathon_sample_resume.pdf',
                'KEH BAN NING',
                'banningkeh@gmail.com',
                '0172230741',
                'Rembau, Negeri Sembilan.',
                'https://www.linkedin.com/in/john-doe',
                'https://github.com/johndoe',
                '[{"company":"Adaptive Netpoleon Malaysia Sdn Bhd","description":"Documented technical issues and solutions to enable tracking history and maintain accurate logs. Improved team performance by collaborating with coworkers and engineers to develop best practices for issue resolution. Acquired comprehensive knowledge of the company\'s distributed products and services. Mastered the customer communication system for addressing technical issues. Supported the validation process for support case contracts. Provided initial responses and follow-ups on support cases, collaborating closely with engineers until resolution.","end_date":"2023-08","start_date":"2023-03","title":"Technical Helpdesk Support Intern"}]',
                '[{"description":"Current CGPA: 3.50","end_date":"2026","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2023","title":"Bachelor of Computer Science (Software Engineering) With Honours"},{"description":"3.71 CGPA. Dean\'s List, Semester 1, 2021/2022. Dean\'s List, Semester 3, 2021/2022. Dean\'s List, Semester 2, 2021/2022. Dean\'s List, Semester 1, 2022/2023.","end_date":"2023","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2021","title":"Diploma in Computer Science"}]',
                '["Mandarin","English","Malay"]',
                '["HTML/CSS","JavaScript","PHP","Java","C/C++","Dart","SQL","Flutter","Firebase","Laravel","Bootstrap","MySQL","Github","Android Studio","VS Code","Figma","XAMPP","Postman","Notion","Microsoft Word","Microsoft Excel","Google Workspace","Discord","Teams"]',
                '[]',
                '{"education":[{"description":"Current CGPA: 3.50","end_date":"2026","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2023","title":"Bachelor of Computer Science (Software Engineering) With Honours"},{"description":"3.71 CGPA. Dean\'s List, Semester 1, 2021/2022. Dean\'s List, Semester 3, 2021/2022. Dean\'s List, Semester 2, 2021/2022. Dean\'s List, Semester 1, 2022/2023.","end_date":"2023","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2021","title":"Diploma in Computer Science"}],"languages":["Mandarin","English","Malay"],"personal_info":{"address":"Rembau, Negeri Sembilan.","email":"banningkeh@gmail.com","github":"https://github.com/johndoe","linkedin":"https://www.linkedin.com/in/john-doe","name":"KEH BAN NING","phone":"0172230741"},"skills":["HTML/CSS","JavaScript","PHP","Java","C/C++","Dart","SQL","Flutter","Firebase","Laravel","Bootstrap","MySQL","Github","Android Studio","VS Code","Figma","XAMPP","Postman","Notion","Microsoft Word","Microsoft Excel","Google Workspace","Discord","Teams"],"work_experience":[{"company":"Adaptive Netpoleon Malaysia Sdn Bhd","description":"Documented technical issues and solutions to enable tracking history and maintain accurate logs. Improved team performance by collaborating with coworkers and engineers to develop best practices for issue resolution. Acquired comprehensive knowledge of the company\'s distributed products and services. Mastered the customer communication system for addressing technical issues. Supported the validation process for support case contracts. Provided initial responses and follow-ups on support cases, collaborating closely with engineers until resolution.","end_date":"2023-08","start_date":"2023-03","title":"Technical Helpdesk Support Intern"}]}'
            ],
            [
                'Sample_resume.pdf',
                'KEH BAN NING',
                'banningkeh@gmail.com',
                '0172230741',
                'Rembau, Negeri Sembilan.',
                'https://www.linkedin.com/in/john-doe',
                'https://github.com/johndoe',
                '[{"company":"company abc","description":"did this that, balhblah, did this","end_date":"2023-08","start_date":"2023-03","title":"intern"}]',
                '[{"description":"cgpa 4.0, Dean\'s List, Semester 3, 2021/2022, Dean\'s List, Semester 1, 2022/2023","end_date":"2023","institute":"uni abc","location":"","start_date":"2021","title":"degree"},{"description":"cgpa 3.97","end_date":"2023","institute":"uni def","location":"","start_date":"2021","title":"Diploma in Computer Science"}]',
                '["Mandarin","English","Malay"]',
                '["tech skills 1","tech skills 2","tech skills 3","tech skills 4"]',
                '[]',
                '{"education":[{"description":"cgpa 4.0, Dean\'s List, Semester 3, 2021/2022, Dean\'s List, Semester 1, 2022/2023","end_date":"2023","institute":"uni abc","location":"","start_date":"2021","title":"degree"},{"description":"cgpa 3.97","end_date":"2023","institute":"uni def","location":"","start_date":"2021","title":"Diploma in Computer Science"}],"languages":["Mandarin","English","Malay"],"personal_info":{"address":"Rembau, Negeri Sembilan.","email":"banningkeh@gmail.com","github":"https://github.com/johndoe","linkedin":"https://www.linkedin.com/in/john-doe","name":"KEH BAN NING","phone":"0172230741"},"skills":["tech skills 1","tech skills 2","tech skills 3","tech skills 4"],"work_experience":[{"company":"company abc","description":"did this that, balhblah, did this","end_date":"2023-08","start_date":"2023-03","title":"intern"}]}'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO parsed_resumes (original_filename, name, email, phone, address, linkedin, github, work_experience, education, languages, skills, certificates, raw_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id");
        $resumeIds = [];
        foreach ($resumeData as $i => $resume) {
            $stmt->execute($resume);
            $resumeIds[$i] = $stmt->fetch()['id'];
        }
        echo "✅ Demo parsed resumes created<br>";

        // Insert sample applications
        $applications = [
            [
                $userIds[1], // alice123
                $jobIds[0],  // Software Developer
                'for_hackathon_sample_resume.pdf',
                'uploads/resumes/68adf71d5d560_1756231453.pdf',
                '{"education":[{"description":"Current CGPA: 3.50","end_date":"2026","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2023","title":"Bachelor of Computer Science (Software Engineering) With Honours"},{"description":"3.71 CGPA. Dean\'s List, Semester 1, 2021/2022. Dean\'s List, Semester 3, 2021/2022. Dean\'s List, Semester 2, 2021/2022. Dean\'s List, Semester 1, 2022/2023.","end_date":"2023","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2021","title":"Diploma in Computer Science"}],"languages":["Mandarin","English","Malay"],"personal_info":{"address":"Rembau, Negeri Sembilan.","email":"banningkeh@gmail.com","github":"https://github.com/johndoe","linkedin":"https://www.linkedin.com/in/john-doe","name":"KEH BAN NING","phone":"0172230741"},"skills":["HTML/CSS","JavaScript","PHP","Java","C/C++","Dart","SQL","Flutter","Firebase","Laravel","Bootstrap","MySQL","Github","Android Studio","VS Code","Figma","XAMPP","Postman","Notion","Microsoft Word","Microsoft Excel","Google Workspace","Discord","Teams"],"work_experience":[{"company":"Adaptive Netpoleon Malaysia Sdn Bhd","description":"Documented technical issues and solutions to enable tracking history and maintain accurate logs. Improved team performance by collaborating with coworkers and engineers to develop best practices for issue resolution. Acquired comprehensive knowledge of the company\'s distributed products and services. Mastered the customer communication system for addressing technical issues. Supported the validation process for support case contracts. Provided initial responses and follow-ups on support cases, collaborating closely with engineers until resolution.","end_date":"2023-08","start_date":"2023-03","title":"Technical Helpdesk Support Intern"}]}',
                '["HTML/CSS","JavaScript","PHP","Java","C/C++","Dart","SQL","Flutter","Firebase","Laravel","Bootstrap","MySQL","Github","Android Studio","VS Code","Figma","XAMPP","Postman","Notion","Microsoft Word","Microsoft Excel","Google Workspace","Discord","Teams"]',
                '[{"company":"Adaptive Netpoleon Malaysia Sdn Bhd","description":"Documented technical issues and solutions to enable tracking history and maintain accurate logs. Improved team performance by collaborating with coworkers and engineers to develop best practices for issue resolution. Acquired comprehensive knowledge of the company\'s distributed products and services. Mastered the customer communication system for addressing technical issues. Supported the validation process for support case contracts. Provided initial responses and follow-ups on support cases, collaborating closely with engineers until resolution.","end_date":"2023-08","start_date":"2023-03","title":"Technical Helpdesk Support Intern"}]',
                '[{"description":"Current CGPA: 3.50","end_date":"2026","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2023","title":"Bachelor of Computer Science (Software Engineering) With Honours"},{"description":"3.71 CGPA. Dean\'s List, Semester 1, 2021/2022. Dean\'s List, Semester 3, 2021/2022. Dean\'s List, Semester 2, 2021/2022. Dean\'s List, Semester 1, 2022/2023.","end_date":"2023","institute":"Universiti Malaysia Pahang Al-Sultan Abdullah","location":"","start_date":"2021","title":"Diploma in Computer Science"}]',
                '{"address":"Rembau, Negeri Sembilan.","email":"banningkeh@gmail.com","github":"https://github.com/johndoe","linkedin":"https://www.linkedin.com/in/john-doe","name":"KEH BAN NING","phone":"0172230741"}',
                100.00,
                'completed',
                null,
                'pending',
                null
            ],
            [
                $userIds[2], // azim_m
                $jobIds[11], // E-commerce Developer
                'Sample_resume.pdf',
                'uploads/resumes/68adf75418d17_1756231508.pdf',
                '{"education":[{"description":"cgpa 4.0, Dean\'s List, Semester 3, 2021/2022, Dean\'s List, Semester 1, 2022/2023","end_date":"2023","institute":"uni abc","location":"","start_date":"2021","title":"degree"},{"description":"cgpa 3.97","end_date":"2023","institute":"uni def","location":"","start_date":"2021","title":"Diploma in Computer Science"}],"languages":["Mandarin","English","Malay"],"personal_info":{"address":"Rembau, Negeri Sembilan.","email":"banningkeh@gmail.com","github":"https://github.com/johndoe","linkedin":"https://www.linkedin.com/in/john-doe","name":"KEH BAN NING","phone":"0172230741"},"skills":["tech skills 1","tech skills 2","tech skills 3","tech skills 4"],"work_experience":[{"company":"company abc","description":"did this that, balhblah, did this","end_date":"2023-08","start_date":"2023-03","title":"intern"}]}',
                '["tech skills 1","tech skills 2","tech skills 3","tech skills 4"]',
                '[{"company":"company abc","description":"did this that, balhblah, did this","end_date":"2023-08","start_date":"2023-03","title":"intern"}]',
                '[{"description":"cgpa 4.0, Dean\'s List, Semester 3, 2021/2022, Dean\'s List, Semester 1, 2022/2023","end_date":"2023","institute":"uni abc","location":"","start_date":"2021","title":"degree"},{"description":"cgpa 3.97","end_date":"2023","institute":"uni def","location":"","start_date":"2021","title":"Diploma in Computer Science"}]',
                '{"address":"Rembau, Negeri Sembilan.","email":"banningkeh@gmail.com","github":"https://github.com/johndoe","linkedin":"https://www.linkedin.com/in/john-doe","name":"KEH BAN NING","phone":"0172230741"}',
                30.00,
                'completed',
                null,
                'pending',
                null
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO applications (candidate_id, job_position_id, resume_filename, resume_path, api_response, extracted_skills, extracted_experience, extracted_education, extracted_contact, match_percentage, api_processing_status, api_error_message, status, hr_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?::jsonb, ?, ?, ?, ?, ?)");
        foreach ($applications as $app) {
            $stmt->execute($app);
        }
        echo "✅ Demo applications created<br>";

        // Insert onboarding tasks
        $tasks = [
            ['Complete Personal Information Form', 'Fill out personal details and emergency contacts', 'ALL', TRUE, 1],
            ['IT Equipment Setup', 'Receive laptop, access cards, and accounts', 'ALL', TRUE, 2],
            ['Company Orientation', 'Attend company culture and policy session', 'ALL', TRUE, 3],
            ['Department Introduction', 'Meet team members and understand role', 'ALL', TRUE, 4],
            ['Security Training', 'Complete cybersecurity awareness training', 'IT', TRUE, 5],
            ['Development Environment Setup', 'Install required software and tools', 'IT', TRUE, 6],
            ['Product Knowledge Training', 'Learn about company products and services', 'Sales & Marketing', TRUE, 7],
            ['Customer Service Training', 'Learn customer interaction protocols', 'Customer Service', TRUE, 8],
            ['Operations Training', 'Understanding operational procedures and workflows', 'Operations', TRUE, 9]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO onboarding_tasks (task_name, description, department, is_mandatory, order_sequence) VALUES (?, ?, ?, ?, ?)");
        foreach ($tasks as $task) {
            $stmt->execute($task);
        }
        echo "✅ Demo onboarding tasks created<br>";

        // Insert training modules
        $modules = [
            ['Company Policies', 'Learn about company rules and regulations', 'https://learning.haircare2u.my/policies', 'ALL', 2, TRUE],
            ['Cybersecurity Awareness', 'Security best practices and protocols', 'https://learning.haircare2u.my/security', 'ALL', 1, TRUE],
            ['PHP Development Basics', 'Introduction to PHP programming', 'https://learning.haircare2u.my/php-basics', 'IT', 8, TRUE],
            ['Database Management', 'MySQL fundamentals', 'https://learning.haircare2u.my/mysql', 'IT', 6, TRUE],
            ['Marketing Fundamentals', 'Basic marketing principles', 'https://learning.haircare2u.my/marketing', 'Sales & Marketing', 4, TRUE],
            ['Customer Service Excellence', 'Providing outstanding customer service', 'https://learning.haircare2u.my/customer-service', 'Customer Service', 3, TRUE],
            ['Product Knowledge Training', 'Understanding our haircare products', 'https://learning.haircare2u.my/products', 'Sales & Marketing', 4, TRUE],
            ['Quality Control Procedures', 'Standards and testing procedures', 'https://learning.haircare2u.my/quality', 'Operations', 3, TRUE],
            ['Communication Skills', 'Effective workplace communication', NULL, 'ALL', 2, FALSE],
            ['Time Management', 'Productivity and time management techniques', NULL, 'ALL', 2, FALSE]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO training_modules (module_name, description, content_url, department, duration_hours, is_mandatory) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($modules as $module) {
            $stmt->execute($module);
        }
        echo "✅ Demo training modules created<br>";

        // Insert comprehensive chatbot FAQ
        $faqs = [
            ['What are my working hours?', 'Standard working hours are 9 AM to 6 PM, Monday to Friday. Flexible timing available after probation period.', 'hr_policy', 'working hours, timing, schedule'],
            ['How do I reset my password?', 'Contact IT support at it@company.com or call extension 1234 to reset your password.', 'technical', 'password reset, login issue, access'],
            ['When will I get my laptop?', 'IT equipment including laptop will be provided within 2-3 business days of joining.', 'onboarding', 'laptop, equipment, IT setup'],
            ['What is the dress code?', 'Business casual is our standard dress code. Formal wear required for client meetings.', 'hr_policy', 'dress code, attire, clothing'],
            ['How do I submit my timesheet?', 'Use the company portal to submit weekly timesheets by Friday 5 PM each week.', 'hr_policy', 'timesheet, attendance, hours'],
            ['Who is my reporting manager?', 'Your reporting manager information will be provided by HR during your first week. Please contact HR at hr@haircare2u.my for this information.', 'onboarding', 'manager, supervisor, reporting'],
            ['How do I access the training portal?', 'You can access all training modules through the Employee Portal under "Training Modules" section.', 'onboarding', 'training, learning, modules'],
            ['What documents do I need to submit?', 'Required documents include Employment Contract, Personal Information Form, Bank Details, ID Copy, and Educational Certificates.', 'onboarding', 'documents, paperwork, requirements'],
            ['How long does document review take?', 'Document review typically takes 2-3 business days. You will be notified once your documents are approved or if any changes are needed.', 'onboarding', 'document review, approval time'],
            ['What is the company leave policy?', 'Full-time employees are entitled to 14 days annual leave, 14 days medical leave, and public holidays. Leave requests should be submitted through the HR portal.', 'hr_policy', 'leave, vacation, sick days'],
            ['How do I access the company VPN?', 'Contact IT support at it@company.com or extension 1234. They will provide you with VPN credentials and setup instructions.', 'technical', 'vpn, remote access, network'],
            ['My laptop is not working', 'For hardware issues, create a support ticket or contact IT at extension 1234. For urgent issues, email it@company.com directly.', 'technical', 'laptop, computer, hardware, broken'],
            ['How do I reset my email password?', 'Contact IT support at it@company.com or call extension 1234. You will need to verify your identity before the password reset.', 'technical', 'email, password, reset, login'],
            ['When do I get my employee ID card?', 'Employee ID cards are typically issued within 3-5 business days. Check with HR if you have not received yours after one week.', 'onboarding', 'id card, badge, access card'],
            ['What documents do I need for onboarding?', 'Required documents include: Employment Contract, Personal Information Form, Bank Details Form, ID Copy, and Educational Certificates. Optional documents may also be requested.', 'onboarding', 'documents, paperwork, requirements, forms'],
            ['How long does onboarding take?', 'The complete onboarding process typically takes 2-4 weeks, depending on your department and role complexity.', 'onboarding', 'duration, timeline, how long'],
            ['How do I apply for leave?', 'Submit leave requests through the HR portal at least 2 weeks in advance. For emergency leave, contact your manager and HR immediately.', 'hr_policy', 'leave, vacation, time off, apply'],
            ['What is our sick leave policy?', 'Employees are entitled to 14 days medical leave annually. Medical certificates are required for sick leave exceeding 2 consecutive days.', 'hr_policy', 'sick leave, medical, illness'],
            ['Do we have health insurance?', 'Yes, comprehensive health insurance is provided. Contact HR at hr@haircare2u.my for details about coverage and claims.', 'hr_policy', 'health insurance, medical coverage, benefits'],
            ['What is the company mission?', 'HairCare2U is dedicated to providing premium haircare solutions while fostering employee growth and customer satisfaction.', 'company', 'mission, values, purpose'],
            ['Are there team building activities?', 'Yes, we organize quarterly team building events and monthly social activities. Check with your department head for upcoming events.', 'company', 'team building, events, social'],
            ['hello', 'Hello! How can I help you today?', 'greeting', 'hi, hey, hello, good morning'],
            ['thanks', 'You\'re welcome! Is there anything else I can help you with?', 'greeting', 'thank you, thanks, appreciate'],
            ['help', 'I can assist you with HR policies, onboarding, training, documents, and general company information. What would you like to know?', 'general', 'help, assist, support']
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
            ['api_key', '', 'API key for external chatbot service'],
            ['default_response', "I apologize, but I couldn't find a specific answer to your question. Please contact HR at hr@haircare2u.my for further assistance.", 'Default response when no match found'],
            ['greeting_message', "Hello! I'm your AI-powered HR assistant. How can I help you today?", 'Initial greeting message']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO chatbot_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        foreach ($settings as $setting) {
            $stmt->execute($setting);
        }
        echo "✅ Chatbot settings created<br>";

        // Insert sample employee documents
        $employeeDocs = [
            [$userIds[7], 'Employment Contract', 'contract', NULL, NULL, 'pending', TRUE, 'Your employment contract and terms of service'] // john_d
        ];
        
        $stmt = $pdo->prepare("INSERT INTO employee_documents (employee_id, document_name, document_type, file_path, file_size, status, is_required, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($employeeDocs as $doc) {
            $stmt->execute($doc);
        }
        echo "✅ Sample employee documents created<br>";

        // Insert sample chat conversations
        $conversations = [
            [$userIds[7], '5nmd3k14muq9tl5g8964i1fom5', 'General HR inquiries', 'Hi John! How can I assist you with your HR-related inquiry today? Below are some common topics I can help you with:\n\n### **Common HR Topics**\n- **Onboarding Process:**\n  • Guidance on completing onboarding tasks  \n  • Document submission requirements  \n\n- **Company Policies:**\n  • Leave policies (annual, medical, maternity, paternity, etc.)  \n  • Work schedule, remote work, and dress code details  \n\n- **Benefits Information:**\n  • Health coverage, bonuses, and perks  \n  • Professional development opportunities  \n\n- **Leave and Time Off:**\n  • How to apply for leave  \n  • Emergency leave procedures  \n\n- **Training and Development:**\n  • Mandatory training modules  \n  • Access to professional development courses  \n\n- **Contact Information:**\n  • HR, IT support, and other departmental contacts  \n\nIf your question isn\'t listed here, feel free to ask! I\'m here to help. 😊', 'user', 5.288],
            [$userIds[7], '5nmd3k14muq9tl5g8964i1fom5', 'hello', 'Hello! How can I help you today?', 'user', 0.000],
            [$userIds[7], '5nmd3k14muq9tl5g8964i1fom5', 'im sick', 'I\'m sorry to hear that you\'re feeling unwell, John. Here\'s what you need to know about **taking medical leave** at HairCare2U:\n\n### **Medical Leave Guidelines**\n1. **Notify Your Manager:**\n   - Inform your manager as soon as possible about your situation.\n   - Provide an estimated duration of your absence.\n\n2. **Medical Certificate (MC):**\n   - If you are on medical leave for **two or more consecutive days**, you will need to provide a valid medical certificate from a registered doctor.\n   - Submit the MC to HR via email at **hr@haircare2u.my** or directly through your manager.\n\n3. **HR Notification:**\n   - Send an email to HR (hr@haircare2u.my) to formally notify them of your medical leave.\n   - Mention your full name, employee ID (if applicable), and the dates you\'ll be absent.\n\n4. **Record Keeping:**\n   - Your medical leave balance is **14 days per year**.\n\n---\n\nIf you need further assistance or if this is an urgent matter, you can:\n- Contact HR directly at **+60 12-345 6790**.\n- Reach out to your manager for immediate help.\n\nTake care of yourself, and let me know if you need help with anything else!', 'user', 7.728],
            [$userIds[7], '5nmd3k14muq9tl5g8964i1fom5', 'i hate my boss', 'I\'m sorry to hear you\'re feeling this way, John. Workplace challenges can be tough, but I\'m here to help you navigate them professionally. If you\'re comfortable, here are a few suggestions to handle the situation constructively:\n\n---\n\n### **1. Reflect on the Issues**\n- Try to identify specific behaviors or situations that are causing frustration.\n- Reflect on whether it\'s a communication issue, workload imbalance, or differing expectations.\n\n---\n\n### **2. Communicate Professionally**\n- If you feel safe doing so, consider scheduling a private, respectful discussion with your boss to address concerns.\n- Use \"I\" statements to express how you feel (e.g., \"I feel overwhelmed when deadlines are unclear.\").\n\n---\n\n### **3. Seek Support**\n- If the issue persists, you can reach out to the **HR Department** for guidance or mediation:\n  - **Email:** hr@haircare2u.my  \n  - **Phone:** +60 12-345 6790\n\n---\n\n### **4. Focus on Self-Care**\n- Workplace stress can take a toll. Ensure you\'re taking breaks, managing your workload, and seeking support from colleagues or mentors.\n\n---\n\nIf you\'d like, I can also provide information on workplace conflict resolution training or guide you on how to formally raise concerns. Let me know how I can assist further!', 'user', 6.707],
            [$userIds[7], '6sef8agqkr6dd4hk7gshdqef6b', 'im sick', 'I\'m sorry to hear that you\'re feeling unwell, John. Here\'s what you need to know about taking medical leave at HairCare2U:\n\n### **Medical Leave Policy**\n1. **Entitlement**: You are entitled to 14 days of medical leave per year.\n2. **Documentation**: If you\'re on medical leave for **2 or more consecutive days**, you\'ll need to provide a **medical certificate** from a registered doctor.\n3. **Notification**: Notify your manager or HR as soon as possible about your absence.\n\n### **Steps to Take**\n1. Inform your immediate manager via email, chat, or call about your condition and the expected duration of your absence.\n2. Visit a registered doctor to get a medical certificate if needed.\n3. Submit the medical certificate to HR at **hr@haircare2u.my** after your recovery.\n\nIf you have any difficulties or need further assistance, feel free to:\n- Contact HR: **hr@haircare2u.my** | **+60 12-345 6790**\n\nTake care, and I hope you feel better soon! Let me know if you need help with anything else.', 'user', 6.722],
            [$userIds[7], '6sef8agqkr6dd4hk7gshdqef6b', 'hi', 'Hello! How can I help you today?', 'user', 0.000]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO chat_conversations (user_id, session_id, message, response, message_type, api_response_time) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($conversations as $conv) {
            $stmt->execute($conv);
        }
        echo "✅ Sample chat conversations created<br>";

        // Update user job positions and departments for employees
        $pdo->exec("UPDATE users SET job_position_id = {$jobIds[1]}, department = 'Sales & Marketing' WHERE username = 'siti_r'");
        $pdo->exec("UPDATE users SET job_position_id = {$jobIds[0]}, department = 'IT' WHERE username = 'john_d'");
        $pdo->exec("UPDATE users SET job_position_id = {$jobIds[6]}, department = 'Customer Service' WHERE username = 'kevin_l'");
        $pdo->exec("UPDATE users SET job_position_id = {$jobIds[8]}, department = 'Operations' WHERE username = 'linda_t'");
        echo "✅ User job assignments updated<br>";

    } else {
        echo "ℹ️ Database already has data ($userCount users found)<br>";
    }

    // Test the setup
    echo "<h3>Database Setup Summary:</h3>";
    $tables = [
        'users', 'job_positions', 'applications', 'onboarding_tasks', 
        'training_modules', 'employee_onboarding', 'employee_training', 
        'employee_documents', 'chatbot_faq', 'chatbot_settings',
        'chat_conversations', 'parsed_resumes', 'support_tickets'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "• $table: $count records<br>";
        } catch (Exception $e) {
            echo "• $table: Error checking records<br>";
        }
    }
    
    echo "<br><h3>🎉 Database setup completed successfully!</h3>";
    echo "<h4>Demo Login Credentials:</h4>";
    echo "<ul>";
    echo "<li><strong>HR Admin:</strong> hr_admin / password123</li>";
    echo "<li><strong>Candidate:</strong> alice123 / password123</li>";
    echo "<li><strong>Employee (IT):</strong> john_d / password123</li>";
    echo "<li><strong>Employee (Sales):</strong> siti_r / password123</li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    echo "<p><strong>Security Note:</strong> Delete this setup_database.php file after running it.</p>";
    
    // Display PostgreSQL hire candidate function usage
    echo "<div style='background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h4>📝 Hiring Process Function Usage:</h4>";
    echo "<p>To hire a candidate, you can now call the PostgreSQL function:</p>";
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; font-size: 12px;'>";
    echo htmlspecialchars("SELECT hire_candidate(application_id);");
    echo "</pre>";
    echo "<p>Or use the PHP equivalent function for more complex operations.</p>";
    echo "</div>";
    
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

// PHP function equivalent for complex hiring operations
echo "<br><h4>📋 PHP Helper Function for Hiring:</h4>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px; overflow-x: auto;'>";
echo htmlspecialchars('
function hireCandidateComplete($pdo, $application_id) {
    try {
        $pdo->beginTransaction();
        
        // Get application details
        $stmt = $pdo->prepare("SELECT candidate_id, job_position_id FROM applications WHERE id = ?");
        $stmt->execute([$application_id]);
        $app = $stmt->fetch();
        
        if (!$app) {
            throw new Exception("Application not found");
        }
        
        // Get job details
        $stmt = $pdo->prepare("SELECT department, title FROM job_positions WHERE id = ?");
        $stmt->execute([$app["job_position_id"]]);
        $job = $stmt->fetch();
        
        // Update application status
        $stmt = $pdo->prepare("UPDATE applications SET status = \'hired\', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$application_id]);
        
        // Update user role and department
        $stmt = $pdo->prepare("UPDATE users SET role = \'employee\', department = ?, job_position_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$job["department"], $app["job_position_id"], $app["candidate_id"]]);
        
        // Create onboarding tasks
        $stmt = $pdo->prepare("
            INSERT INTO employee_onboarding (employee_id, task_id, status)
            SELECT ?, ot.id, \'pending\'
            FROM onboarding_tasks ot
            WHERE ot.department = ? OR ot.department = \'ALL\'
            ON CONFLICT (employee_id, task_id) DO NOTHING
        ");
        $stmt->execute([$app["candidate_id"], $job["department"]]);
        
        // Create training assignments
        $stmt = $pdo->prepare("
            INSERT INTO employee_training (employee_id, module_id, status, progress_percentage)
            SELECT ?, tm.id, \'not_started\', 0
            FROM training_modules tm
            WHERE tm.department = ? OR tm.department = \'ALL\'
            ON CONFLICT (employee_id, module_id) DO NOTHING
        ");
        $stmt->execute([$app["candidate_id"], $job["department"]]);
        
        // Create employee documents
        $documents = [
            ["Employment Contract", "contract", "Your employment contract and terms of service"],
            ["Personal Information Form", "personal_form", "Complete personal details and emergency contacts"],
            ["Bank Details Form", "bank_form", "Banking information for salary processing"],
            ["ID Copy", "identification", "Copy of your identification document (IC/Passport)"],
            ["Educational Certificates", "education", "Copies of your educational qualifications"]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO employee_documents (employee_id, document_name, document_type, status, is_required, description)
            VALUES (?, ?, ?, \'pending\', TRUE, ?)
            ON CONFLICT (employee_id, document_type) DO NOTHING
        ");
        
        foreach ($documents as $doc) {
            $stmt->execute([$app["candidate_id"], $doc[0], $doc[1], $doc[2]]);
        }
        
        $pdo->commit();
        return ["success" => true, "message" => "Candidate hired successfully"];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Hiring process failed: " . $e->getMessage());
        return ["success" => false, "message" => $e->getMessage()];
    }
}

// Usage example:
// $result = hireCandidateComplete($pdo, $application_id);
// if ($result["success"]) {
//     echo "Hiring successful!";
// } else {
//     echo "Error: " . $result["message"];
// }
');
echo "</pre>";
?>
