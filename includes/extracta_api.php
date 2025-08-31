<?php
class ExtractaAPI {
    private $api_key;
    private $base_url = 'https://api.extracta.ai/api/v1/';
    private $extraction_id; // Will store the extraction template ID
    
    public function __construct($api_key = null, $extraction_id = null) {
        // Use provided API key or get from constant
        $this->api_key = $api_key ?: (defined('EXTRACTA_API_KEY') ? EXTRACTA_API_KEY : null);
        $this->extraction_id = $extraction_id;
        
        if (!$this->api_key) {
            throw new Exception('API key is required');
        }
    }
    
    /**
     * Create an extraction template for resumes - ONLY USE IF YOU DON'T HAVE ONE
     * @return array - Response with extractionId or error
     */
    public function createResumeExtraction() {
        $url = $this->base_url . 'createExtraction';
        
        $extraction_details = [
            'name' => 'Resume Parser',
            'description' => 'Extract key information from resumes and CVs',
            'language' => 'English',
            'options' => [
                'hasTable' => false,
                'handwrittenTextRecognition' => false
            ],
            'fields' => [
                [
                    'key' => 'personal_info',
                    'description' => 'Personal information including name, email, phone, address, LinkedIn, GitHub',
                    'type' => 'object',
                    'properties' => [
                        'name' => ['description' => 'Full name', 'example' => 'John Doe'],
                        'email' => ['description' => 'Email address', 'example' => 'john.doe@email.com'],
                        'phone' => ['description' => 'Phone number', 'example' => '+1 (555) 123-4567'],
                        'address' => ['description' => 'Address or location', 'example' => 'New York, NY'],
                        'linkedin' => ['description' => 'LinkedIn profile URL', 'example' => 'linkedin.com/in/johndoe'],
                        'github' => ['description' => 'GitHub profile URL', 'example' => 'github.com/johndoe']
                    ]
                ],
                [
                    'key' => 'work_experience',
                    'description' => 'Array of work experience entries',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['description' => 'Job title', 'example' => 'Software Engineer'],
                            'company' => ['description' => 'Company name', 'example' => 'ABC Corp'],
                            'location' => ['description' => 'Job location', 'example' => 'San Francisco, CA'],
                            'start_date' => ['description' => 'Start date', 'example' => '2020-01'],
                            'end_date' => ['description' => 'End date or "Present"', 'example' => '2023-12'],
                            'description' => ['description' => 'Job description', 'example' => 'Developed web applications...']
                        ]
                    ]
                ],
                [
                    'key' => 'education',
                    'description' => 'Array of education entries',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'degree' => ['description' => 'Degree name', 'example' => 'Bachelor of Computer Science'],
                            'institution' => ['description' => 'School/University name', 'example' => 'University of XYZ'],
                            'location' => ['description' => 'School location', 'example' => 'Boston, MA'],
                            'graduation_date' => ['description' => 'Graduation date', 'example' => '2020-05'],
                            'gpa' => ['description' => 'GPA if mentioned', 'example' => '3.8']
                        ]
                    ]
                ],
                [
                    'key' => 'skills',
                    'description' => 'Array of skills',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => ['description' => 'Skill category', 'example' => 'Programming Languages'],
                            'items' => ['description' => 'List of skills', 'example' => ['JavaScript', 'Python', 'Java']]
                        ]
                    ]
                ],
                [
                    'key' => 'languages',
                    'description' => 'Array of languages',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'language' => ['description' => 'Language name', 'example' => 'Spanish'],
                            'proficiency' => ['description' => 'Proficiency level', 'example' => 'Fluent']
                        ]
                    ]
                ],
                [
                    'key' => 'certificates',
                    'description' => 'Array of certifications',
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['description' => 'Certificate name', 'example' => 'AWS Solutions Architect'],
                            'issuer' => ['description' => 'Issuing organization', 'example' => 'Amazon Web Services'],
                            'date' => ['description' => 'Issue date', 'example' => '2023-06'],
                            'expiry' => ['description' => 'Expiry date if applicable', 'example' => '2026-06']
                        ]
                    ]
                ]
            ]
        ];
        
        return $this->makeRequest('POST', $url, ['extractionDetails' => $extraction_details]);
    }
    
    /**
     * Upload and parse a resume file using EXISTING extraction template
     * @param string $file_path - Full path to the resume file
     * @param string $extraction_id - Extraction template ID (optional, uses class property if not provided)
     * @return array - Upload response or error
     */
    public function uploadResume($file_path, $extraction_id = null) {
        // Use provided extraction_id or class property
        $extraction_id = $extraction_id ?: $this->extraction_id;
        
        if (!$extraction_id) {
            return ['error' => 'Extraction ID is required. Please set it with setExtractionId() or pass it as parameter.'];
        }
        
        // Check if file exists
        if (!file_exists($file_path)) {
            return ['error' => 'File not found: ' . $file_path];
        }
        
        // Check file size (limit to reasonable size)
        $file_size = filesize($file_path);
        if ($file_size > 10 * 1024 * 1024) { // 10MB limit
            return ['error' => 'File too large. Maximum size is 10MB.'];
        }
        
        $url = $this->base_url . 'uploadFiles';
        
        // Prepare the file for upload
        $cfile = new CURLFile($file_path);
        
        // Prepare POST data
        $post_data = [
            'extractionId' => $extraction_id,
            'files' => [$cfile] // Note: files should be an array
        ];
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options for file upload - using Authorization Bearer instead of x-api-key
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120, // Longer timeout for file upload
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_key, // Updated to use Bearer
                'Accept: application/json'
                // Don't set Content-Type for multipart/form-data - let cURL handle it
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        // Handle cURL errors
        if ($curl_error) {
            return ['error' => 'cURL Error: ' . $curl_error];
        }
        
        // Handle HTTP errors
        if ($http_code < 200 || $http_code >= 300) {
            return ['error' => 'HTTP Error ' . $http_code . ': ' . $response];
        }
        
        // Decode JSON response
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response: ' . json_last_error_msg() . ' - Raw response: ' . substr($response, 0, 500)];
        }
        
        return $decoded_response;
    }
    
    /**
     * Get batch results for processed files
     * @param string $extraction_id - Extraction ID
     * @param string $batch_id - Batch ID from upload response
     * @param string $file_id - Optional file ID to get specific file results
     * @return array - Batch results or error
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
        
        return $this->makeRequest('POST', $url, $payload);
    }
    
    /**
     * Parse a resume file using existing extraction template (complete workflow)
     * @param string $file_path - Full path to the resume file
     * @return array - Parsed resume data or error
     */
    public function parseResume($file_path) {
        // Step 1: Upload the file
        $upload_result = $this->uploadResume($file_path);
        
        if (isset($upload_result['error'])) {
            return $upload_result;
        }
        
        // Extract batch ID from upload response
        if (!isset($upload_result['batchId'])) {
            return ['error' => 'No batch ID returned from upload. Response: ' . json_encode($upload_result)];
        }
        
        $batch_id = $upload_result['batchId'];
        $extraction_id = $upload_result['extractionId'] ?? $this->extraction_id;
        
        // Step 2: Wait a moment for processing to start
        sleep(3);
        
        // Step 3: Poll for results (with timeout)
        $max_attempts = 60; // 60 attempts with 3-second delays = 3 minutes max wait
        $attempts = 0;
        
        while ($attempts < $max_attempts) {
            $results = $this->getBatchResults($extraction_id, $batch_id);
            
            if (isset($results['error'])) {
                return $results;
            }
            
            // Check if any files are processed
            if (isset($results['files']) && is_array($results['files'])) {
                foreach ($results['files'] as $file) {
                    if (isset($file['status'])) {
                        if ($file['status'] === 'processed') {
                            return [
                                'success' => true,
                                'data' => $file['result'] ?? [],
                                'filename' => $file['fileName'] ?? 'unknown',
                                'url' => $file['url'] ?? '',
                                'file_id' => $file['fileId'] ?? ''
                            ];
                        } elseif ($file['status'] === 'failed') {
                            return [
                                'error' => 'File processing failed: ' . ($file['error'] ?? 'Unknown error'),
                                'filename' => $file['fileName'] ?? 'unknown'
                            ];
                        }
                    }
                }
            }
            
            // Wait before next attempt
            sleep(3);
            $attempts++;
            
            // Log progress every 10 attempts
            if ($attempts % 10 === 0) {
                error_log("Resume parsing - attempt $attempts of $max_attempts");
            }
        }
        
        return ['error' => 'Processing timeout after ' . ($max_attempts * 3) . ' seconds. Please check results later using getBatchResults.'];
    }
    
    /**
     * Make HTTP request to API
     * @param string $method - HTTP method (GET, POST, etc.)
     * @param string $url - Full URL
     * @param array $data - Request data
     * @return array - Response or error
     */
    public function makeRequest($method, $url, $data = []) {
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key, // Updated to use Bearer
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
            return ['error' => 'cURL Error: ' . $curl_error];
        }
        
        if ($http_code < 200 || $http_code >= 300) {
            return ['error' => 'HTTP Error ' . $http_code . ': ' . $response];
        }
        
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response: ' . json_last_error_msg() . ' - Raw response: ' . substr($response, 0, 500)];
        }
        
        return $decoded_response;
    }
    
    /**
     * Set extraction ID
     * @param string $extraction_id
     */
    public function setExtractionId($extraction_id) {
        $this->extraction_id = $extraction_id;
    }
    
    /**
     * Get current extraction ID
     * @return string|null
     */
    public function getExtractionId() {
        return $this->extraction_id;
    }
    
    /**
     * Save parsed resume data to database (Clean version - no debug output)
     * @param array $parsed_data - Data from API (from the 'data' field of parseResume response)
     * @param string $original_filename - Original resume filename
     * @param object $db_connection - Database connection
     * @return bool - Success status
     */
    public function saveParsedData($parsed_data, $original_filename, $db_connection) {
        try {
            // Log debug information to error log instead of displaying on screen
            error_log("SaveParsedData - Filename: " . $original_filename);
            error_log("SaveParsedData - Data keys: " . implode(', ', array_keys($parsed_data)));
            
            // Check database connection
            if (!$db_connection) {
                error_log("SaveParsedData - Database connection is null");
                return false;
            }
            
            // Test database connection
            if (!$db_connection->ping()) {
                error_log("SaveParsedData - Database ping failed");
                return false;
            }
            
            // Check if table exists
            $table_check = $db_connection->query("SHOW TABLES LIKE 'parsed_resumes'");
            if (!$table_check || $table_check->num_rows == 0) {
                error_log("SaveParsedData - parsed_resumes table not found");
                return false;
            }
            
            // Initialize default values
            $name = '';
            $email = '';
            $phone = '';
            $address = '';
            $linkedin = '';
            $github = '';
            
            // Extract personal_info (this is an object according to your Extracta.ai structure)
            if (isset($parsed_data['personal_info']) && is_array($parsed_data['personal_info'])) {
                $personal_info = $parsed_data['personal_info'];
                $name = $personal_info['name'] ?? '';
                $email = $personal_info['email'] ?? '';
                $phone = $personal_info['phone'] ?? '';
                $address = $personal_info['address'] ?? '';
                $linkedin = $personal_info['linkedin'] ?? '';
                $github = $personal_info['github'] ?? '';
                
                error_log("SaveParsedData - Personal info extracted - Name: " . $name . ", Email: " . $email);
            }
            
            // Extract work_experience (list<object>)
            $work_experience = '[]';
            if (isset($parsed_data['work_experience']) && is_array($parsed_data['work_experience'])) {
                $work_experience = json_encode($parsed_data['work_experience']);
                error_log("SaveParsedData - Work Experience: " . count($parsed_data['work_experience']) . " entries");
            }
            
            // Extract education (list<object>)
            $education = '[]';
            if (isset($parsed_data['education']) && is_array($parsed_data['education'])) {
                $education = json_encode($parsed_data['education']);
                error_log("SaveParsedData - Education: " . count($parsed_data['education']) . " entries");
            }
            
            // Extract languages (list<object>)
            $languages = '[]';
            if (isset($parsed_data['languages']) && is_array($parsed_data['languages'])) {
                $languages = json_encode($parsed_data['languages']);
                error_log("SaveParsedData - Languages: " . count($parsed_data['languages']) . " entries");
            }
            
            // Extract skills (list<object>)
            $skills = '[]';
            if (isset($parsed_data['skills']) && is_array($parsed_data['skills'])) {
                $skills = json_encode($parsed_data['skills']);
                error_log("SaveParsedData - Skills: " . count($parsed_data['skills']) . " entries");
            }
            
            // Extract certificates (list<object>)
            $certificates = '[]';
            if (isset($parsed_data['certificates']) && is_array($parsed_data['certificates'])) {
                $certificates = json_encode($parsed_data['certificates']);
                error_log("SaveParsedData - Certificates: " . count($parsed_data['certificates']) . " entries");
            }
            
            // Create raw_data JSON
            $raw_data = json_encode($parsed_data);
            
            // Check for JSON encoding errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("SaveParsedData - JSON encoding error: " . json_last_error_msg());
                return false;
            }
            
            // Check if raw_data is too large for database
            if (strlen($raw_data) > 16777215) { // MEDIUMTEXT limit
                error_log("SaveParsedData - Warning: Raw data is large (" . strlen($raw_data) . " chars)");
            }
            
            // Prepare SQL statement (removed created_at as it has DEFAULT CURRENT_TIMESTAMP)
            $sql = "INSERT INTO parsed_resumes 
                    (original_filename, name, email, phone, address, linkedin, github, 
                     work_experience, education, languages, skills, certificates, raw_data) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db_connection->prepare($sql);
            
            if (!$stmt) {
                error_log("SaveParsedData - Prepare failed: " . $db_connection->error);
                return false;
            }
            
            // Bind parameters
            $bind_result = $stmt->bind_param("sssssssssssss", 
                $original_filename, 
                $name, 
                $email, 
                $phone, 
                $address,
                $linkedin,
                $github,
                $work_experience, 
                $education, 
                $languages,
                $skills,
                $certificates,
                $raw_data
            );
            
            if (!$bind_result) {
                error_log("SaveParsedData - Bind failed: " . $stmt->error);
                return false;
            }
            
            // Execute the statement
            $execute_result = $stmt->execute();
            
            if (!$execute_result) {
                error_log("SaveParsedData - Execute failed: " . $stmt->error . " (Error Code: " . $stmt->errno . ")");
                return false;
            }
            
            $insert_id = $db_connection->insert_id;
            error_log("SaveParsedData - Successfully inserted with ID: " . $insert_id);
            
            $stmt->close();
            
            return true;
            
        } catch (Exception $e) {
            error_log("SaveParsedData - Exception: " . $e->getMessage());
            error_log("SaveParsedData - Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
}
?>
