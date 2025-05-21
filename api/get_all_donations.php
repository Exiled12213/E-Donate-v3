<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../config/db_config.php';

try {
    // Build query based on any filter parameters
    $query = "SELECT d.*, u.username as donor_name, c.name as category
              FROM donations d
              JOIN users u ON d.user_id = u.id
              JOIN categories c ON d.category_id = c.id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Filter by status if provided
    if (isset($_GET['status']) && $_GET['status'] != '') {
        // Handle different status representations
        if ($_GET['status'] == 'accepted') {
            $query .= " AND d.status = 'accepted'";
        } elseif ($_GET['status'] == 'declined') {
            $query .= " AND d.status = 'declined'";
        } elseif ($_GET['status'] == 'pending') {
            $query .= " AND (d.status = 'pending' OR d.status IS NULL)";
        } else {
            $query .= " AND d.status = ?";
            $params[] = $_GET['status'];
            $types .= "s";
        }
    }
    
    // Filter by category if provided
    if (isset($_GET['category_id']) && $_GET['category_id'] != '') {
        $query .= " AND d.category_id = ?";
        $params[] = $_GET['category_id'];
        $types .= "i";
    }
    
    // Sort order
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
    
    switch ($sort) {
        case 'oldest':
            $query .= " ORDER BY d.created_at ASC";
            break;
        case 'title':
            $query .= " ORDER BY d.title ASC";
            break;
        case 'status':
            $query .= " ORDER BY d.status ASC";
            break;
        case 'category':
            $query .= " ORDER BY c.name ASC";
            break;
        default: // newest
            $query .= " ORDER BY d.created_at DESC";
    }
    
    // Debug: Log the final query
    error_log("Query: $query");
    error_log("Params: " . json_encode($params));
    
    // Prepare statement
    $stmt = $conn->prepare($query);
    
    // Bind parameters if any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    
    $donations = [];
    
    // Get all the donations
    while ($row = $result->fetch_assoc()) {
        // Add status_text field for display
        $row['status_text'] = ucfirst($row['status']);
        
        // Fetch images for this donation
        $imagesSql = "SELECT * FROM images WHERE donation_id = ?";
        $imagesStmt = $conn->prepare($imagesSql);
        $imagesStmt->bind_param("i", $row['id']);
        $imagesStmt->execute();
        $imagesResult = $imagesStmt->get_result();
        
        $images = [];
        while ($image = $imagesResult->fetch_assoc()) {
            $images[] = $image;
        }
        
        // Add images to donation data
        $row['images'] = $images;
        
        // Fetch videos for this donation
        $videosSql = "SELECT * FROM videos WHERE donation_id = ?";
        $videosStmt = $conn->prepare($videosSql);
        $videosStmt->bind_param("i", $row['id']);
        $videosStmt->execute();
        $videosResult = $videosStmt->get_result();
        
        $videos = [];
        while ($video = $videosResult->fetch_assoc()) {
            $videos[] = $video;
        }
        
        // Add videos to donation data
        $row['videos'] = $videos;
        
        $donations[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($donations),
        'donations' => $donations
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in get_all_donations.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading donations: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}

$conn->close(); 