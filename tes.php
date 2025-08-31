<?php
// Simple test to verify exact API format
require_once 'includes/config.php';

echo "<h2>Simple Extracta API Test</h2>\n";

if (!defined('EXTRACTA_API_KEY')) {
    echo "❌ API Key not found\n";
    exit;
}

echo "✅ Testing with Authorization: Bearer header\n\n";

$test_data = [
    'extractionDetails' => [
        'name' => 'Simple Resume Parser',
        'description' => 'Test extraction template',
        'language' => 'English',
        'fields' => [
            [
                'key' => 'name',
                'description' => 'Full name of candidate',
                'example' => 'John Doe'
            ],
            [
                'key' => 'email',
                'description' => 'Email address',
                'example' => 'john@example.com'
            ],
            [
                'key' => 'phone',
                'description' => 'Phone number', 
                'example' => '+1-555-123-4567'
            ]
        ]
    ]
];

echo "Request payload:\n";
echo json_encode($test_data, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.extracta.ai/api/v1/createExtraction',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($test_data),
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
echo "Response:\n";
echo "```\n$response\n```\n";

if ($http_code >= 200 && $http_code < 300) {
    $decoded = json_decode($response, true);
    if (isset($decoded['extractionId'])) {
        echo "\n🎉 SUCCESS! Extraction ID: " . $decoded['extractionId'] . "\n";
        echo "Your API integration is working correctly!\n";
    }
} else {
    echo "\n❌ Still getting errors. Response details above.\n";
}
?>
