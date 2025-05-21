<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

// Debug info
$debug = [];
$debug['request_method'] = $_SERVER['REQUEST_METHOD'];
$debug['post_data'] = $_POST;
$debug['files_data'] = isset($_FILES) ? 'Files set' : 'No files in request';

// Check if the upload directory exists, create it if not
$uploadDir = '../uploads/';
if (!file_exists($uploadDir)) {
    $debug['mkdir_result'] = mkdir($uploadDir, 0777, true) ? 'Created upload directory' : 'Failed to create directory';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method',
        'debug' => $debug
    ]);
    exit;
}

// Check if files were uploaded
if (!isset($_FILES['files'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'No files uploaded',
        'debug' => $debug
    ]);
    exit;
}

$donation_id = $_POST['donation_id'] ?? null;
if (!$donation_id) {
    echo json_encode([
        'success' => false, 
        'message' => 'Donation ID is required',
        'debug' => $debug
    ]);
    exit;
}

// Check if the images table exists
try {
    $result = $conn->query("SHOW TABLES LIKE 'images'");
    if ($result->num_rows == 0) {
        $debug['table_creation'] = 'Images table does not exist, creating it';
        // Create images table
        $createImagesTable = "CREATE TABLE images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            donation_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createImagesTable);
        $debug['table_created'] = 'Images table created successfully';
    } else {
        $debug['table_exists'] = 'Images table already exists';
    }
} catch (Exception $e) {
    $debug['table_error'] = $e->getMessage();
}

$uploadedFiles = [];
$errors = [];

// Debug info about uploaded files
$debug['files_count'] = count($_FILES['files']['tmp_name']);
$debug['file_names'] = $_FILES['files']['name'];

// Process each uploaded file
foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
    if (empty($tmp_name)) {
        $errors[] = "Empty file at index $key";
        continue;
    }
    
    $file_name = $_FILES['files']['name'][$key];
    $file_size = $_FILES['files']['size'][$key];
    $file_type = $_FILES['files']['type'][$key];
    
    $debug["processing_file_$key"] = [
        'name' => $file_name,
        'size' => $file_size,
        'type' => $file_type
    ];
    
    // Validate file type
    if (!in_array($file_type, ['image/jpeg', 'image/png', 'image/jpg'])) {
        $errors[] = "$file_name: Invalid file type. Only JPG and PNG allowed.";
        continue;
    }
    
    // Validate file size (5MB max)
    if ($file_size > 5 * 1024 * 1024) {
        $errors[] = "$file_name: File too large. Maximum size is 5MB.";
        continue;
    }
    
    // Generate unique filename
    $unique_name = uniqid() . '_' . $file_name;
    $upload_path = $uploadDir . $unique_name;
    
    $debug["upload_attempt_$key"] = [
        'path' => $upload_path,
        'tmp_name' => $tmp_name
    ];
    
    if (move_uploaded_file($tmp_name, $upload_path)) {
        // Save file info to database
        $sql = "INSERT INTO images (donation_id, file_path, file_name, file_size) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $relative_path = 'uploads/' . $unique_name;
        $stmt->bind_param("issi", $donation_id, $relative_path, $file_name, $file_size);
        
        $debug["db_insert_$key"] = [
            'donation_id' => $donation_id,
            'path' => $relative_path,
            'name' => $file_name
        ];
        
        if ($stmt->execute()) {
            $uploadedFiles[] = [
                'name' => $file_name,
                'path' => $relative_path,
                'size' => $file_size
            ];
            $debug["db_success_$key"] = true;
        } else {
            $errors[] = "$file_name: Failed to save to database: " . $stmt->error;
            $debug["db_error_$key"] = $stmt->error;
            // Don't remove the file if we couldn't add it to the database
            // This gives us a chance to diagnose issues
        }
    } else {
        $errors[] = "$file_name: Failed to upload file";
        $debug["move_error_$key"] = "Failed to move uploaded file";
    }
}

$response = [
    'success' => count($uploadedFiles) > 0,
    'uploaded_files' => $uploadedFiles,
    'debug' => $debug
];

if (count($errors) > 0) {
    $response['errors'] = $errors;
}

echo json_encode($response);
?> 