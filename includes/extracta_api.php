<?php
class ExtractaAPI {
    private $api_key;
    private $base_url = 'https://api.extracta.ai/api/v1/';
    private $extraction_id;
    
    public function __construct($api_key = null, $extraction_id = null) {
        // FIXED: Better environment variable handling
        $this->api_key = $api_key ?: 
                        (defined('EXTRACTA_API_KEY') ? EXTRACTA_API_KEY : 
                        ($_ENV['EXTRACTA_API_KEY'] ?? getenv('EXTRACTA_API_KEY') ?? null));
        
        $this->extraction_id = $extraction_id ?: 
                              (defined('EXTRACTA_EXTRACTION_ID') ? EXTRACTA_EXTRACTION_ID : 
                              ($_ENV['EXTRACTA_EXTRACTION_ID'] ?? getenv('EXTRACTA_EXTRACTION_ID') ?? '-OY-tgWlYrpfUxqIQqWr'));
        
        if (!$this->api_key) {
            throw new Exception('API key is required. Set EXTRACTA_API_KEY environment variable.');
        }
        
        // Debug logging
        error_log("ExtractaAPI initialized with extraction ID: " . $this->extraction_id);
    }
    
    /**
     * Upload and parse a resume file using EXISTING extraction template
     */
    public function uploadResume($file_path, $extraction_id = null) {
        // FIXED: Use extraction_id parameter or class property
        $extraction_id = $extraction_id ?: $this->extraction_id;
        
        error_log("uploadResume called with extraction ID: " . $extraction_id);
        
        if (!$extraction_id) {
            return ['error' => 'Extraction ID is required. Current ID: ' . ($this->extraction_id ?? 'null')];
        }
        
        if (!file_exists($file_path)) {
            return ['error' => 'File not found: ' . $file_path];
        }
        
        $file_size = filesize($file_path);
        if ($file_size > 10 * 1024 * 1024) {
            return ['error' => 'File too large. Maximum size is 10MB.'];
        }
        
        $url = $this->base_url . 'uploadFiles';
        
        try {
            $cfile = new CURLFile($file_path);
            $post_data = [
                'extractionId' => $extraction_id,
                'files' => [$cfile]
            ];
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->api_key,
                    'Accept: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            error_log("Upload response - HTTP: $http_code, Response: $response");
            
            if ($curl_error) {
                return ['error' => 'Network error: ' . $curl_error];
            }
            
            if ($http_code < 200 || $http_code >= 300) {
                return ['error' => "HTTP $http_code: $response"];
            }
            
            $decoded_response = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Invalid response format: ' . json_last_error_msg()];
            }
            
            return $decoded_response;
            
        } catch (Exception $e) {
            error_log("Upload exception: " . $e->getMessage());
            return ['error' => 'Upload failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get batch results for processed files
     */
    public function getBatchResults($extraction_id, $batch_id, $file_id = null) {
        $url = $this->base_url . 'getBatchResults';
        
        $payload = [
            'extractionId' => $extraction_id,
            'batchId' => $batch_id
        ];
        
        if ($file_id) {
            $payload['fileId'] = $file_id;
        }
        
        error_log("getBatchResults called - Extraction ID: $extraction_id, Batch ID: $batch_id");
        
        return $this->makeRequest('POST', $url, $payload);
    }
    
    /**
     * Parse a resume file using existing extraction template
     */
    public function parseResume($file_path) {
        error_log("parseResume started for file: $file_path");
        
        // Step 1: Upload the file
        $upload_result = $this->uploadResume($file_path);
        
        if (isset($upload_result['error'])) {
            error_log("Upload failed: " . $upload_result['error']);
            return $upload_result;
        }
        
        if (!isset($upload_result['batchId'])) {
            error_log("No batch ID in upload response: " . json_encode($upload_result));
            return ['error' => 'No batch ID returned from upload. Response: ' . json_encode($upload_result)];
        }
        
        $batch_id = $upload_result['batchId'];
        $extraction_id = $upload_result['extractionId'] ?? $this->extraction_id;
        
        error_log("Upload successful - Batch ID: $batch_id, Extraction ID: $extraction_id");
        
        // Step 2: Wait for processing
        sleep(5); // Increased wait time for Render
        
        // Step 3: Poll for results
        $max_attempts = 40; // Reduced from 60 for Render timeout limits
        $attempts = 0;
        
        while ($attempts < $max_attempts) {
            $results = $this->getBatchResults($extraction_id, $batch_id);
            
            if (isset($results['error'])) {
                error_log("getBatchResults error: " . $results['error']);
                return $results;
            }
            
            if (isset($results['files']) && is_array($results['files'])) {
                foreach ($results['files'] as $file) {
                    if (isset($file['status'])) {
                        error_log("File status: " . $file['status']);
                        
                        if ($file['status'] === 'processed') {
                            error_log("File processed successfully");
                            return [
                                'success' => true,
                                'data' => $file['result'] ?? [],
                                'filename' => $file['fileName'] ?? 'unknown',
                                'url' => $file['url'] ?? '',
                                'file_id' => $file['fileId'] ?? ''
                            ];
                        } elseif ($file['status'] === 'failed') {
                            $error_msg = 'File processing failed: ' . ($file['error'] ?? 'Unknown error');
                            error_log($error_msg);
                            return [
                                'error' => $error_msg,
                                'filename' => $file['fileName'] ?? 'unknown'
                            ];
                        }
                    }
                }
            }
            
            sleep(3);
            $attempts++;
            
            if ($attempts % 5 === 0) {
                error_log("Resume parsing - attempt $attempts of $max_attempts");
            }
        }
        
        return ['error' => 'Processing timeout after ' . ($max_attempts * 3) . ' seconds.'];
    }
    
    /**
     * Make HTTP request to API
     */
    public function makeRequest($method, $url, $data = []) {
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key,
            'Accept: application/json'
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            error_log("makeRequest cURL error: $curl_error");
            return ['error' => 'cURL Error: ' . $curl_error];
        }
        
        if ($http_code < 200 || $http_code >= 300) {
            error_log("makeRequest HTTP error $http_code: $response");
            return ['error' => 'HTTP Error ' . $http_code . ': ' . $response];
        }
        
        $decoded_response = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("makeRequest JSON error: " . json_last_error_msg());
            return ['error' => 'Invalid JSON response: ' . json_last_error_msg()];
        }
        
        return $decoded_response;
    }
    
    public function setExtractionId($extraction_id) {
        $this->extraction_id = $extraction_id;
        error_log("Extraction ID set to: $extraction_id");
    }
    
    public function getExtractionId() {
        return $this->extraction_id;
    }
}
