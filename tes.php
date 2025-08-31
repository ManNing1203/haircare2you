<?php
// Fixed version - USES your existing template instead of creating new ones
require_once 'includes/config.php';

echo "<h2>Fixed Test - Using Existing Resume Extraction Template</h2>\n";

if (!defined('EXTRACTA_API_KEY')) {
    echo "❌ API Key not found\n";
    exit;
}

// YOUR EXISTING TEMPLATE ID
$existing_extraction_id = '-OY-tgWlYrpfUxqIQqWr';

echo "✅ Using your EXISTING extraction template: $existing_extraction_id\n";
echo "✅ Template name: Resume extraction\n\n";

// Test 1: Verify the existing template works
echo "=== Test 1: Verify Existing Template ===\n";

$test_batch_payload = [
    'extractionId' => $existing_extraction_id,
    'batchId' => 'test-batch-' . time() // Dummy batch ID for testing
];

echo "Testing getBatchResults with existing template...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.extracta.ai/api/v1/getBatchResults',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($test_batch_payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . EXTRACTA_API_KEY,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "cURL Error: " . ($error ?: 'None') . "\n";
echo "Response: $response\n\n";

if ($http_code >= 200 && $http_code < 300) {
    echo "✅ SUCCESS! Your existing template is working correctly.\n";
    echo "✅ Template ID $existing_extraction_id is valid and accessible.\n\n";
    
    // Test 2: Show correct usage for file upload
    echo "=== Test 2: File Upload Example (Correct Way) ===\n";
    echo "To upload a resume file to your EXISTING template:\n\n";
    
    echo "```php\n";
    echo "// Method 1: Using ExtractaAPI class\n";
    echo "require_once 'includes/extracta_api.php';\n";
    echo "\$extracta = new ExtractaAPI(EXTRACTA_API_KEY, '$existing_extraction_id');\n";
    echo "\$result = \$extracta->uploadResume('/path/to/resume.pdf');\n\n";
    
    echo "// Method 2: Direct cURL (for file upload)\n";
    echo "\$file = new CURLFile('/path/to/resume.pdf');\n";
    echo "\$postdata = [\n";
    echo "    'extractionId' => '$existing_extraction_id',\n";
    echo "    'files' => [\$file]\n";
    echo "];\n";
    echo "\n";
    echo "\$ch = curl_init();\n";
    echo "curl_setopt_array(\$ch, [\n";
    echo "    CURLOPT_URL => 'https://api.extracta.ai/api/v1/uploadFiles',\n";
    echo "    CURLOPT_POST => true,\n";
    echo "    CURLOPT_POSTFIELDS => \$postdata,\n";
    echo "    CURLOPT_RETURNTRANSFER => true,\n";
    echo "    CURLOPT_HTTPHEADER => [\n";
    echo "        'Authorization: Bearer ' . EXTRACTA_API_KEY,\n";
    echo "        'Accept: application/json'\n";
    echo "        // Note: Don't set Content-Type for file uploads\n";
    echo "    ]\n";
    echo "]);\n";
    echo "```\n\n";
    
} else {
    echo "❌ Template verification failed.\n";
    echo "This could mean:\n";
    echo "1. The extraction ID '$existing_extraction_id' is incorrect\n";
    echo "2. Your API key doesn't have access to this template\n";
    echo "3. There's a network/API issue\n\n";
}

echo "=== Key Differences From Your Original Test ===\n";
echo "❌ Your original test was CREATING new templates:\n";
echo "   - Called 'createExtraction' endpoint\n";
echo "   - Generated new extraction IDs each time\n";
echo "   - That's why you got '-OZ0dNyhOsGpbs8Z1YPy' instead of using '-OY-tgWlYrpfUxqIQqWr'\n\n";

echo "✅ Corrected approach:\n";
echo "   - Use your existing extraction ID: $existing_extraction_id\n";
echo "   - Call 'uploadFiles' with this ID to process resumes\n";
echo "   - Don't call 'createExtraction' again\n\n";

echo "=== Next Steps ===\n";
echo "1. ✅ Use the updated extracta_api.php class (provided above)\n";
echo "2. ✅ Initialize with your existing template ID:\n";
echo "   \$extracta = new ExtractaAPI(EXTRACTA_API_KEY, '$existing_extraction_id');\n";
echo "3. ✅ Upload resume files:\n";
echo "   \$result = \$extracta->uploadResume('/path/to/file.pdf');\n";
echo "4. ✅ Parse complete resumes:\n";
echo "   \$result = \$extracta->parseResume('/path/to/file.pdf');\n\n";

echo "🎯 REMEMBER: You already have a working extraction template!\n";
echo "   Don't create new ones - use the existing one: $existing_extraction_id\n";
?>
