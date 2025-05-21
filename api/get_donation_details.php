<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db_config.php';

// Get donation ID from request
$donation_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$donation_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Donation ID is required'
    ]);
    exit;
}

try {
    // Get basic donation details
    $sql = "SELECT d.*, c.name as category, u.username as donor_name, u.email as donor_email 
            FROM donations d 
            LEFT JOIN categories c ON d.category_id = c.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Donation not found'
        ]);
        exit;
    }
    
    $donation = $result->fetch_assoc();
    
    // Get associated images
    $imagesSql = "SELECT * FROM images WHERE donation_id = ?";
    $imagesStmt = $conn->prepare($imagesSql);
    $imagesStmt->bind_param("i", $donation_id);
    $imagesStmt->execute();
    $imagesResult = $imagesStmt->get_result();
    
    $images = [];
    while ($image = $imagesResult->fetch_assoc()) {
        $images[] = $image;
    }
    
    // Get associated video
    $videosSql = "SELECT * FROM videos WHERE donation_id = ?";
    $videosStmt = $conn->prepare($videosSql);
    $videosStmt->bind_param("i", $donation_id);
    $videosStmt->execute();
    $videosResult = $videosStmt->get_result();
    
    $videos = [];
    while ($video = $videosResult->fetch_assoc()) {
        $videos[] = $video;
    }
    
    // Add images and videos to donation data
    $donation['images'] = $images;
    $donation['videos'] = $videos;
    
    echo json_encode([
        'success' => true,
        'donation' => $donation
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 