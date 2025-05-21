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
$uploadDir = '../uploads/videos/';
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

// Check if video was uploaded
if (!isset($_FILES['video'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'No video uploaded',
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

// Check if the videos table exists
try {
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($result->num_rows == 0) {
        $debug['table_creation'] = 'Videos table does not exist, creating it';
        // Create videos table
        $createVideosTable = "CREATE TABLE videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            donation_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createVideosTable);
        $debug['table_created'] = 'Videos table created successfully';
    } else {
        $debug['table_exists'] = 'Videos table already exists';
    }
} catch (Exception $e) {
    $debug['table_error'] = $e->getMessage();
}

// Process the uploaded video
$file = $_FILES['video'];
$file_name = $file['name'];
$file_size = $file['size'];
$file_type = $file['type'];
$tmp_name = $file['tmp_name'];

$debug["processing_video"] = [
    'name' => $file_name,
    'size' => $file_size,
    'type' => $file_type
];

// Validate file type
$allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
if (!in_array($file_type, $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Only MP4, WEBM, and OGG videos are allowed.',
        'debug' => $debug
    ]);
    exit;
}

// Validate file size (30MB max)
if ($file_size > 30 * 1024 * 1024) {
    echo json_encode([
        'success' => false,
        'message' => 'File too large. Maximum size is 30MB.',
        'debug' => $debug
    ]);
    exit;
}

// Generate unique filename
$unique_name = uniqid() . '_' . $file_name;
$upload_path = $uploadDir . $unique_name;

$debug["upload_attempt"] = [
    'path' => $upload_path,
    'tmp_name' => $tmp_name
];

if (move_uploaded_file($tmp_name, $upload_path)) {
    // Save file info to database
    $sql = "INSERT INTO videos (donation_id, file_path, file_name, file_size) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $relative_path = 'uploads/videos/' . $unique_name;
    $stmt->bind_param("issi", $donation_id, $relative_path, $file_name, $file_size);
    
    $debug["db_insert"] = [
        'donation_id' => $donation_id,
        'path' => $relative_path,
        'name' => $file_name
    ];
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Video uploaded successfully',
            'video' => [
                'name' => $file_name,
                'path' => $relative_path,
                'size' => $file_size
            ],
            'debug' => $debug
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save video info to database: ' . $stmt->error,
            'debug' => $debug
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to upload video',
        'debug' => $debug
    ]);
}
?> 