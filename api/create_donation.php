<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

// Check and update database structure
try {
    // Check if 'condition' column exists
    $result = $conn->query("SHOW COLUMNS FROM donations LIKE 'condition'");
    if ($result->num_rows == 0) {
        // Add 'condition' column if it doesn't exist
        $conn->query("ALTER TABLE donations ADD COLUMN `condition` VARCHAR(50) AFTER description");
    }
    
    // Check if 'images' table exists
    $result = $conn->query("SHOW TABLES LIKE 'images'");
    if ($result->num_rows == 0) {
        // Create images table if it doesn't exist
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
    }
    
} catch (Exception $e) {
    // Just log the error, but continue with the request
    error_log("Database structure update error: " . $e->getMessage());
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['user_id']) || !isset($data['category_id']) || !isset($data['title'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: user_id, category_id, and title are required'
    ]);
    exit;
}

// Sanitize input data
$user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
$category_id = filter_var($data['category_id'], FILTER_VALIDATE_INT);
$title = htmlspecialchars(trim($data['title']));
$description = isset($data['description']) ? htmlspecialchars(trim($data['description'])) : '';
$condition = isset($data['condition']) ? htmlspecialchars(trim($data['condition'])) : '';

// Additional validation
if (!$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID'
    ]);
    exit;
}

if (!$category_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category ID'
    ]);
    exit;
}

try {
    // First check if user exists
    $user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $user_check->bind_param("i", $user_id);
    $user_check->execute();
    $user_result = $user_check->get_result();
    
    if ($user_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Then check if category exists
    $cat_check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $cat_check->bind_param("i", $category_id);
    $cat_check->execute();
    $cat_result = $cat_check->get_result();
    
    if ($cat_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Category not found'
        ]);
        exit;
    }
    
    // Insert donation with pending status
    $stmt = $conn->prepare("INSERT INTO donations (user_id, category_id, title, description, `condition`, status) 
                           VALUES (?, ?, ?, ?, ?, 'pending')");
    
    $stmt->bind_param("iisss", $user_id, $category_id, $title, $description, $condition);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'donation_id' => $conn->insert_id,
            'message' => 'Donation created successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create donation: ' . $stmt->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 