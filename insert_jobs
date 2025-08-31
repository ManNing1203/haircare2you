<?php
require_once 'includes/db.php';

try {
    echo "<h2>Inserting Job Positions...</h2>";
    
    // Check if job positions already exist
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_positions");
    $jobCount = $stmt->fetch()['count'];
    
    if ($jobCount > 0) {
        echo "⚠️ Job positions already exist ($jobCount found). Skipping insertion.<br>";
        echo "<a href='index.php'>Go to Login Page</a>";
        exit;
    }
    
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
    
    $stmt = $pdo->prepare("INSERT INTO job_positions (title, department, description, required_skills, experience_level, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $jobIds = [];
    
    foreach ($jobs as $i => $job) {
        // Use user ID 1 as created_by (assuming it's an admin user)
        $stmt->execute(array_merge($job, [1]));
        echo "✅ Created: {$job[0]} ({$job[1]})<br>";
    }
    
    // Verify insertion
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM job_positions");
    $newJobCount = $stmt->fetch()['count'];
    
    echo "<br><h3>🎉 Successfully created $newJobCount job positions!</h3>";
    echo "<p><a href='index.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    echo "<p><strong>Note:</strong> Delete this insert_jobs.php file after running it.</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Failed to insert job positions:</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
