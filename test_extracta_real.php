<?php
// Updated test_extracta_real.php with proper POST requests
require_once 'ExtractaAPI.php';

echo "<h2>Extracta.ai Production Test - Fixed Version</h2>\n";

// Check if API key exists
if (!defined('EXTRACTA_API_KEY') || empty(EXTRACTA_API_KEY)) {
    echo "❌ API Key not found\n";
    exit;
}

echo "✅ API Key found\n\n";

echo "=== Testing Domain Connectivity ===\n";
$domain_test = curl_init();
curl_setopt($domain_test, CURLOPT_URL, 'https://api.extracta.ai');
curl_setopt($domain_test, CURLOPT_RETURNTRANSFER, true);
curl_setopt($domain_test, CURLOPT_TIMEOUT, 10);
curl_setopt($domain_test, CURLOPT_SSL_VERIFYPEER, true);

$domain_response = curl_exec($domain_test);
$domain_http_code = curl_getinfo($domain_test, CURLINFO_HTTP_CODE);
$domain_error = curl_error($domain_test);
curl_close($domain_test);

echo "Domain HTTP Code: $domain_http_code\n";
echo "Domain cURL Error: " . ($domain_error ?: 'None') . "\n\n";

echo "=== Testing API Endpoints with Proper Methods ===\n";

// Test createExtraction with POST and minimal data
echo "Testing: https://api.extracta.ai/api/v1/createExtraction (POST)\n";
$create_test = curl_init();
curl_setopt_array($create_test, [
    CURLOPT_URL => 'https://api.extracta.ai/api/v1/createExtraction',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'extractionType' => 'resume',
        'name' => 'Test Extraction'
    ]),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-api-key: ' . EXTRACTA_API_KEY
    ],
    CURLOPT_TIMEOUT => 30
]);

$create_response = curl_exec($create_test);
$create_http_code = curl_getinfo($create_test, CURLINFO_HTTP_CODE);
$create_error = curl_error($create_test);
curl_close($create_test);

echo "HTTP Code: $create_http_code\n";
echo "cURL Error: " . ($create_error ?: 'None') . "\n";
echo "Response: \n```\n$create_response\n```\n\n";

// Parse the response to get extraction ID
$create_data = json_decode($create_response, true);
$test_extraction_id = $create_data['extractionId'] ?? null;

if ($test_extraction_id) {
    echo "✅ Successfully created test extraction: $test_extraction_id\n\n";
    
    // Test uploadFiles with the extraction ID
    echo "Testing: https://api.extracta.ai/api/v1/uploadFiles (POST)\n";
    $upload_test = curl_init();
    curl_setopt_array($upload_test, [
        CURLOPT_URL => 'https://api.extracta.ai/api/v1/uploadFiles',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'extractionId' => $test_extraction_id,
            'files' => [] // Empty files array for testing
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . EXTRACTA_API_KEY
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $upload_response = curl_exec($upload_test);
    $upload_http_code = curl_getinfo($upload_test, CURLINFO_HTTP_CODE);
    $upload_error = curl_error($upload_test);
    curl_close($upload_test);

    echo "HTTP Code: $upload_http_code\n";
    echo "cURL Error: " . ($upload_error ?: 'None') . "\n";
    echo "Response: \n```\n$upload_response\n```\n\n";

    // Test getBatchResults
    echo "Testing: https://api.extracta.ai/api/v1/getBatchResults (POST)\n";
    $results_test = curl_init();
    curl_setopt_array($results_test, [
        CURLOPT_URL => 'https://api.extracta.ai/api/v1/getBatchResults',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'extractionId' => $test_extraction_id
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . EXTRACTA_API_KEY
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $results_response = curl_exec($results_test);
    $results_http_code = curl_getinfo($results_test, CURLINFO_HTTP_CODE);
    $results_error = curl_error($results_test);
    curl_close($results_test);

    echo "HTTP Code: $results_http_code\n";
    echo "cURL Error: " . ($results_error ?: 'None') . "\n";
    echo "Response: \n```\n$results_response\n```\n\n";
}

echo "=== ExtractaAPI Class Test ===\n";
try {
    $extracta = new ExtractaAPI();
    echo "✅ ExtractaAPI class initialized successfully\n";
    
    // Test template creation
    $template_result = $extracta->createExtractionTemplate();
    echo "✅ Template creation result: " . json_encode($template_result, JSON_PRETTY_PRINT) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ExtractaAPI class error: " . $e->getMessage() . "\n\n";
}

echo "=== Server Information ===\n";
$curl_info = curl_version();
echo "cURL version: " . $curl_info['version'] . "\n";
echo "SSL version: " . $curl_info['ssl_version'] . "\n";
echo "Protocols: " . implode(', ', $curl_info['protocols']) . "\n\n";

echo "=== PHP Configuration ===\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Yes' : 'No') . "\n";
echo "openssl extension: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "\n";
echo "curl extension: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n\n";

echo "✅ All tests completed!\n";
echo "\nIf you see successful responses above, your Extracta.ai integration is working correctly.\n";
echo "The 404 errors in your original test were because GET requests were used instead of POST.\n";
?>
