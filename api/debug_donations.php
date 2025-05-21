<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../config/db_config.php';

try {
    // Get a single donation with its images
    $query = "SELECT d.*, u.username as donor_name, c.name as category
              FROM donations d
              JOIN users u ON d.user_id = u.id
              JOIN categories c ON d.category_id = c.id
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $donation = $result->fetch_assoc();
        
        // Convert status codes to human-readable form
        if ($donation['status'] == 'A') {
            $donation['status'] = 'Accepted';
        } elseif ($donation['status'] == 'D') {
            $donation['status'] = 'Declined';
        } else {
            $donation['status'] = 'Pending';
        }
        
        // Fetch images for this donation
        $imagesSql = "SELECT * FROM images WHERE donation_id = ?";
        $imagesStmt = $conn->prepare($imagesSql);
        $imagesStmt->bind_param("i", $donation['id']);
        $imagesStmt->execute();
        $imagesResult = $imagesStmt->get_result();
        
        $images = [];
        while ($image = $imagesResult->fetch_assoc()) {
            $images[] = $image;
        }
        
        // Add images to donation data
        $donation['images'] = $images;
        
        // Add table structure information
        $tableStructure = [];
        $structureResult = $conn->query("DESCRIBE images");
        while ($row = $structureResult->fetch_assoc()) {
            $tableStructure[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'donation' => $donation,
            'images_table_structure' => $tableStructure
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No donations found in the database'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in debug_donations.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

$conn->close(); 