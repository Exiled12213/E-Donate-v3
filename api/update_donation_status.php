<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['donation_id']) || !isset($data['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$donation_id = $data['donation_id'];
$status = $data['status'];
$admin_notes = isset($data['admin_notes']) ? $data['admin_notes'] : '';
$admin_id = isset($data['admin_id']) ? $data['admin_id'] : null;

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Add status history entry first (so we have a record even if the donation is deleted)
    // Check if the status history table exists
    $result = $conn->query("SHOW TABLES LIKE 'donation_status_history'");
    if ($result->num_rows == 0) {
        // Include the script to create the table
        require_once __DIR__ . '/../database/create_status_history_table.php';
    }
    
    $history_stmt = $conn->prepare("INSERT INTO donation_status_history (donation_id, status, comment, created_by) 
                                   VALUES (?, ?, ?, ?)");
    $history_stmt->bind_param("issi", $donation_id, $status, $admin_notes, $admin_id);
    $history_stmt->execute();
    
    if ($status === 'declined') {
        // If status is declined, delete the donation and related records
        
        // First, get image paths for deletion from filesystem
        $image_stmt = $conn->prepare("SELECT file_path FROM images WHERE donation_id = ?");
        $image_stmt->bind_param("i", $donation_id);
        $image_stmt->execute();
        $image_result = $image_stmt->get_result();
        
        $image_paths = [];
        while ($row = $image_result->fetch_assoc()) {
            $image_paths[] = $row['file_path'];
        }
        
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
        
        // Delete image files from filesystem
        foreach ($image_paths as $path) {
            if (file_exists('../' . $path)) {
                unlink('../' . $path);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Donation declined and deleted successfully'
        ]);
    } else {
        // For other statuses, just update the status
        // Check if admin_notes column exists
        $checkAdminNotesCol = "SHOW COLUMNS FROM donations LIKE 'admin_notes'";
        $hasAdminNotesColumn = ($conn->query($checkAdminNotesCol)->num_rows > 0);
        
        // Build SQL query based on available columns
        $sql = "UPDATE donations SET status = ?, reviewed_at = CURRENT_TIMESTAMP";
        $types = "s";
        $params = [$status];
        
        if ($hasAdminNotesColumn) {
            $sql .= ", admin_notes = ?";
            $types .= "s";
            $params[] = $admin_notes;
        }
        
        $sql .= " WHERE id = ?";
        $types .= "i";
        $params[] = $donation_id;
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        
        // Dynamically bind parameters
        $stmt->bind_param($types, ...$params);
        
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Donation status updated successfully'
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error updating donation: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 