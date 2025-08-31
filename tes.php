<?php
// Corrected test to USE existing extraction template instead of creating new ones
require_once 'includes/config.php';
require_once 'includes/extracta_api.php';

echo "<h2>Using Existing Resume Extraction Template</h2>\n";

if (!defined('EXTRACTA_API_KEY') || empty(EXTRACTA_API_KEY)) {
    echo "❌ API Key not found or empty\n";
    exit;
}

// YOUR EXISTING EXTRACTION ID
$existing_extraction_id = '-OY-tgWlYrpfUxqIQqWr';

echo "✅ Using existing extraction template: $existing_extraction_id\n\n";

try {
    // Initialize API with your existing extraction ID
    $extracta = new ExtractaAPI(EXTRACTA_API_KEY, $existing_extraction_id);
    echo "✅ ExtractaAPI initialized with existing template\n";
    
    // Test 1: Get batch results (to see what data structure looks like)
    echo "\n=== Test 1: Check existing template status ===\n";
    $test_batch_result = $extracta->getBatchResults($existing_extraction_id, 'test-batch-id');
    echo "Batch check result: " . json_encode($test_batch_result, JSON_PRETTY_PRINT) . "\n";
    
    // Test 2: Upload a test file (if you have one)
    $test_file = '/path/to/test/resume.pdf'; // Update this path
    
    if (file_exists($test_file)) {
        echo "\n=== Test 2: Upload resume file ===\n";
        echo "Uploading: $test_file\n";
        
        $upload_result = $extracta->uploadResume($test_file);
        
        if (isset($upload_result['error'])) {
            echo "❌ Upload error: " . $upload_result['error'] . "\n";
        } else {
            echo "✅ Upload successful!\n";
            echo "Response: " . json_encode($upload_result, JSON_PRETTY_PRINT) . "\n";
            
            // If successful, try to get results
            if (isset($upload_result['batchId'])) {
                echo "\n=== Test 3: Get processing results ===\n";
                $batch_id = $upload_result['batchId'];
                
                // Wait a moment
                sleep(2);
                
                // Check results
                $results = $extracta->getBatchResults($existing_extraction_id, $batch_id);
                echo "Results: " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
            }
        }
    } else {
        echo "\n=== Test 2: File upload test skipped ===\n";
        echo "No test file found at: $test_file\n";
        echo "To test file upload:\n";
        echo "1. Update the \$test_file variable above with path to a real resume file\n";
        echo "2. Run this script again\n";
    }
    
    // Test 3: Demonstrate how to use the API correctly
    echo "\n=== How to Use Your Existing Template ===\n";
    echo "✅ Correct usage examples:\n\n";
    
    echo "// Initialize with your existing extraction ID\n";
    echo "\$extracta = new ExtractaAPI(EXTRACTA_API_KEY, '$existing_extraction_id');\n\n";
    
    echo "// Upload and parse a resume\n";
    echo "\$result = \$extracta->parseResume('/path/to/resume.pdf');\n\n";
    
    echo "// Or just upload without parsing\n";
    echo "\$upload = \$extracta->uploadResume('/path/to/resume.pdf');\n\n";
    
    echo "// Set extraction ID later if needed\n";
    echo "\$extracta->setExtractionId('$existing_extraction_id');\n\n";
    
    echo "❌ Don't do this (creates new templates):\n";
    echo "// \$extracta->createResumeExtraction(); // This creates NEW templates!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Summary ===\n";
echo "✅ Your existing extraction template ID: $existing_extraction_id\n";
echo "✅ Template name: Resume extraction\n";
echo "✅ Use this template for all resume processing\n";
echo "❌ Don't call createExtraction methods - you already have a template!\n";
?>
