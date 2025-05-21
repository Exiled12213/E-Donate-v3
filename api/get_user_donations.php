<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

// Get user ID from query parameter
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

try {
    // Get user's donations with category names
    $sql = "SELECT d.*, c.name as category_name 
            FROM donations d 
            JOIN categories c ON d.category_id = c.id 
            WHERE d.user_id = ? 
            ORDER BY d.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $donations = [];
    while ($row = $result->fetch_assoc()) {
        // Get images for this donation
        $img_sql = "SELECT file_path FROM images WHERE donation_id = ? LIMIT 1";
        $img_stmt = $conn->prepare($img_sql);
        $img_stmt->bind_param("i", $row['id']);
        $img_stmt->execute();
        $img_result = $img_stmt->get_result();
        $image = $img_result->fetch_assoc();
        
        // Get videos for this donation
        $video_sql = "SELECT file_path FROM videos WHERE donation_id = ? LIMIT 1";
        $video_stmt = $conn->prepare($video_sql);
        $video_stmt->bind_param("i", $row['id']);
        $video_stmt->execute();
        $video_result = $video_stmt->get_result();
        $video = $video_result->fetch_assoc();
        
        $donations[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category_name'],
            'condition' => $row['condition'],
            'status' => $row['status'],
            'admin_notes' => $row['admin_notes'],
            'created_at' => $row['created_at'],
            'reviewed_at' => $row['reviewed_at'],
            'image_path' => $image ? $image['file_path'] : null,
            'video_path' => $video ? $video['file_path'] : null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'donations' => $donations
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching donations: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 