<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_config.php';

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
if (!isset($data['donation_id']) || !isset($data['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: donation_id and status are required'
    ]);
    exit;
}

// Sanitize input data
$donation_id = filter_var($data['donation_id'], FILTER_VALIDATE_INT);
$status = htmlspecialchars(trim($data['status']));
$comment = isset($data['comment']) ? htmlspecialchars(trim($data['comment'])) : null;
$created_by = isset($data['created_by']) ? filter_var($data['created_by'], FILTER_VALIDATE_INT) : null;

// Additional validation
if (!$donation_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid donation ID'
    ]);
    exit;
}

try {
    // Check if the status history table exists
    $result = $conn->query("SHOW TABLES LIKE 'donation_status_history'");
    if ($result->num_rows == 0) {
        // Include the script to create the table
        require_once __DIR__ . '/../database/create_status_history_table.php';
    }
    
    // First check if donation exists
    $donation_check = $conn->prepare("SELECT id FROM donations WHERE id = ?");
    $donation_check->bind_param("i", $donation_id);
    $donation_check->execute();
    $donation_result = $donation_check->get_result();
    
    if ($donation_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Donation not found'
        ]);
        exit;
    }
    
    // Insert status history entry
    $stmt = $conn->prepare("INSERT INTO donation_status_history (donation_id, status, comment, created_by) 
                           VALUES (?, ?, ?, ?)");
    
    $stmt->bind_param("issi", $donation_id, $status, $comment, $created_by);
    
    if ($stmt->execute()) {
        // Also update the status in the donations table
        $update_stmt = $conn->prepare("UPDATE donations SET status = ?, reviewed_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->bind_param("si", $status, $donation_id);
        $update_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'history_id' => $conn->insert_id,
            'message' => 'Status history added successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add status history: ' . $stmt->error
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