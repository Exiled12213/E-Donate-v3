<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['donation_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data: donation_id is required'
    ]);
    exit;
}

$donation_id = $data['donation_id'];
$admin_id = isset($data['admin_id']) ? $data['admin_id'] : null;

try {
    // Start transaction
    $conn->begin_transaction();
    
    // First, get image paths for deletion from filesystem
    $image_stmt = $conn->prepare("SELECT file_path FROM images WHERE donation_id = ?");
    $image_stmt->bind_param("i", $donation_id);
    $image_stmt->execute();
    $image_result = $image_stmt->get_result();
    
    $image_paths = [];
    while ($row = $image_result->fetch_assoc()) {
        $image_paths[] = $row['file_path'];
    }
    
    // Get video paths for deletion from filesystem
    $video_stmt = $conn->prepare("SELECT file_path FROM videos WHERE donation_id = ?");
    $video_stmt->bind_param("i", $donation_id);
    $video_stmt->execute();
    $video_result = $video_stmt->get_result();
    
    $video_paths = [];
    while ($row = $video_result->fetch_assoc()) {
        $video_paths[] = $row['file_path'];
    }
    
    // Delete status history records
    $delete_history = $conn->prepare("DELETE FROM donation_status_history WHERE donation_id = ?");
    $delete_history->bind_param("i", $donation_id);
    $delete_history->execute();
    
    // Delete images from database
    $delete_images = $conn->prepare("DELETE FROM images WHERE donation_id = ?");
    $delete_images->bind_param("i", $donation_id);
    $delete_images->execute();
    
    // Delete videos from database
    $delete_videos = $conn->prepare("DELETE FROM videos WHERE donation_id = ?");
    $delete_videos->bind_param("i", $donation_id);
    $delete_videos->execute();
    
    // Delete donation
    $delete_donation = $conn->prepare("DELETE FROM donations WHERE id = ?");
    $delete_donation->bind_param("i", $donation_id);
    $delete_donation->execute();
    
    // Check if the donation was actually deleted
    if ($delete_donation->affected_rows === 0) {
        // Rollback if no rows were affected (donation might not exist)
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Donation not found or already deleted'
        ]);
        exit;
    }
    
    // Delete image files from filesystem
    foreach ($image_paths as $path) {
        if (file_exists('../' . $path)) {
            unlink('../' . $path);
        }
    }
    
    // Delete video files from filesystem
    foreach ($video_paths as $path) {
        if (file_exists('../' . $path)) {
            unlink('../' . $path);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Donation deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting donation: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 