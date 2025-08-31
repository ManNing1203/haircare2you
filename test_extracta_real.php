<?php
// Test actual Extracta.ai API
$api_key = $_ENV['EXTRACTA_API_KEY'] ?? getenv('EXTRACTA_API_KEY');

if (empty($api_key)) {
    echo "❌ API Key not found in environment variables\n";
    exit;
}

echo "✅ API Key found\n";

// Test 1: Basic connectivity to extracta.ai domain
echo "\n=== Testing Domain Connectivity ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://extracta.ai');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Domain HTTP Code: " . $http_code . "\n";
echo "Domain cURL Error: " . ($curl_error ?: 'None') . "\n";

// Test 2: Try actual Extracta.ai endpoints from your class
echo "\n=== Testing API Endpoints ===\n";
$endpoints = [
    'https://api.extracta.ai/api/v1/createExtraction',
    'https://api.extracta.ai/api/v1/uploadFiles',
    'https://api.extracta.ai/api/v1/getBatchResults'
];

foreach ($endpoints as $endpoint) {
    echo "Testing: $endpoint\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "  HTTP Code: " . $http_code . "\n";
    echo "  cURL Error: " . ($curl_error ?: 'None') . "\n";
    echo "  Response: " . substr($response, 0, 200) . "...\n";
    echo "---\n";
}

// Test 3: Check cURL capabilities
echo "\n=== Server cURL Info ===\n";
echo "cURL version: " . curl_version()['version'] . "\n";
echo "SSL version: " . curl_version()['ssl_version'] . "\n";
echo "Protocols: " . implode(', ', curl_version()['protocols']) . "\n";

// Test 4: PHP configuration
echo "\n=== PHP Configuration ===\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Yes' : 'No') . "\n";
echo "openssl extension: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "\n";
echo "curl extension: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
?>
