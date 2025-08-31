<?php
// test_extracta.php
$api_key = $_ENV['EXTRACTA_API_KEY'] ?? getenv('EXTRACTA_API_KEY');

if (empty($api_key)) {
    echo "❌ API Key not found in environment variables";
    exit;
}

// Test basic connectivity
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.extracta.ai/v1/ping'); // or whatever test endpoint
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "\n";
echo "cURL Error: " . $curl_error . "\n";
echo "Response: " . $response . "\n";
?>
