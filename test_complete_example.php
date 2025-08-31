<?php
// Complete working example using your existing extraction template
require_once 'includes/config.php';
require_once 'includes/extracta_api.php';

echo "<h2>Resume Parser - Using Existing Template</h2>\n";

// Your existing extraction ID
$extraction_id = '-OY-tgWlYrpfUxqIQqWr';

try {
    // Initialize API with your existing template
    $extracta = new ExtractaAPI(EXTRACTA_API_KEY, $extraction_id);
    
    echo "Using extraction template: $extraction_id\n";
    echo "Template name: Resume extraction\n\n";
    
    // Example 1: Parse a resume file (complete workflow)
    function parseResumeExample($extracta, $file_path) {
        echo "=== Parsing Resume: " . basename($file_path) . " ===\n";
        
        if (!file_exists($file_path)) {
            echo "File not found: $file_path\n";
            return false;
        }
        
        $result = $extracta->parseResume($file_path);
        
        if (isset($result['error'])) {
            echo "Error: " . $result['error'] . "\n";
            return false;
        }
        
        if (isset($result['success']) && $result['success']) {
            echo "✅ Resume parsed successfully!\n";
            echo "Filename: " . $result['filename'] . "\n";
            
            $data = $result['data'];
            
            // Display extracted data
            if (isset($data['personal_info'])) {
                echo "\nPersonal Info:\n";
                $personal = $data['personal_info'];
                echo "  Name: " . ($personal['name'] ?? 'N/A') . "\n";
                echo "  Email: " . ($personal['email'] ?? 'N/A') . "\n";
                echo "  Phone: " . ($personal['phone'] ?? 'N/A') . "\n";
                echo "  Address: " . ($personal['address'] ?? 'N/A') . "\n";
            }
            
            if (isset($data['work_experience']) && is_array($data['work_experience'])) {
                echo "\nWork Experience (" . count($data['work_experience']) . " entries):\n";
                foreach ($data['work_experience'] as $job) {
                    echo "  • " . ($job['title'] ?? 'N/A') . " at " . ($job['company'] ?? 'N/A') . "\n";
                }
            }
            
            if (isset($data['education']) && is_array($data['education'])) {
                echo "\nEducation (" . count($data['education']) . " entries):\n";
                foreach ($data['education'] as $edu) {
                    echo "  • " . ($edu['degree'] ?? 'N/A') . " from " . ($edu['institution'] ?? 'N/A') . "\n
