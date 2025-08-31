<?php
require_once 'includes/extracta_api.php';

echo "<h2>Extracta.ai Debugging for Production</h2>";

// Test 1: Environment Variables
echo "<h3>1. Environment Check</h3>";
$api_key = $_ENV['EXTRACTA_API_KEY'] ?? getenv('EXTRACTA_API_KEY');

if (empty($api_key)) {
    echo "❌ EXTRACTA_API_KEY not found in environment variables<br>";
    echo "Available ENV vars starting with EXTRACTA: ";
    foreach ($_ENV as $key => $value) {
        if (strpos($key, 'EXTRACTA') !== false) {
            echo "$key<br>";
        }
    }
    exit;
} else {
    echo "✅ API Key found (length: " . strlen($api_key) . ")<br>";
}

// Test 2: Initialize ExtractaAPI class
echo "<h3>2. Class Initialization</h3>";
try {
    $extracta = new ExtractaAPI($api_key);
    echo "✅ ExtractaAPI class initialized successfully<br>";
} catch (Exception $e) {
    echo "❌ Failed to initialize ExtractaAPI: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Test API connectivity with actual endpoints
echo "<h3>3. API Connectivity Test</h3>";

// Test each endpoint from your class
$test_endpoints = [
    'createExtraction' => 'https://api.extracta.ai/api/v1/createExtraction',
    'uploadFiles' => 'https://api.extracta.ai/api/v1/uploadFiles',
    'getBatchResults' => 'https://api.extracta.ai/api/v1/getBatchResults'
];

foreach ($test_endpoints as $name => $url) {
    echo "<strong>Testing $name endpoint:</strong><br>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => '{}' // Empty JSON
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    echo "  HTTP Code: $http_code<br>";
    echo "  cURL Error: " . ($curl_error ?: 'None') . "<br>";
    echo "  Response: " . substr($response, 0, 200) . "...<br>";
    echo "  ---<br>";
}

// Test 4: Create extraction template
echo "<h3>4. Create Extraction Template Test</h3>";
try {
    $creation_result = $extracta->createResumeExtraction();
    
    if (isset($creation_result['error'])) {
        echo "❌ Failed to create extraction: " . $creation_result['error'] . "<br>";
    } else {
        echo "✅ Extraction template creation response:<br>";
        echo "<pre>" . htmlspecialchars(json_encode($creation_result, JSON_PRETTY_PRINT)) . "</pre>";
        
        // Try to extract extraction ID
        if (isset($creation_result['extractionId'])) {
            $extraction_id = $creation_result['extractionId'];
            $extracta->setExtractionId($extraction_id);
            echo "✅ Extraction ID set: $extraction_id<br>";
        } elseif (isset($creation_result['id'])) {
            $extraction_id = $creation_result['id'];
            $extracta->setExtractionId($extraction_id);
            echo "✅ Extraction ID set: $extraction_id<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Exception creating extraction: " . $e->getMessage() . "<br>";
}

// Test 5: Server capabilities
echo "<h3>5. Server Capabilities</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "cURL Version: " . curl_version()['version'] . "<br>";
echo "SSL Version: " . curl_version()['ssl_version'] . "<br>";
echo "OpenSSL Extension: " . (extension_loaded('openssl') ? 'Yes' : 'No') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";

// Test 6: File system check
echo "<h3>6. File System Check</h3>";
$upload_dir = __DIR__ . '/uploads/resumes/';
echo "Upload directory: $upload_dir<br>";
echo "Directory exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . "<br>";
echo "Directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "<br>";

if (!is_dir($upload_dir)) {
    echo "Creating upload directory...<br>";
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Upload directory created<br>";
    } else {
        echo "❌ Failed to create upload directory<br>";
    }
}

// Test 7: Database connection (for saveParsedData method)
echo "<h3>7. Database Connection Test</h3>";
try {
    require_once 'includes/db.php';
    if (isset($pdo)) {
        echo "✅ PDO connection available<br>";
        
        // Check if parsed_resumes table exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'parsed_resumes'");
        if ($stmt && $stmt->fetchColumn() > 0) {
            echo "✅ parsed_resumes table exists<br>";
        } else {
            echo "❌ parsed_resumes table not found<br>";
        }
    } else {
        echo "❌ PDO connection not available<br>";
    }
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
}

echo "<h3>Debug Complete</h3>";
echo "<p>If all tests pass but resume parsing still fails, the issue might be in your file upload or form processing code.</p>";
?>
