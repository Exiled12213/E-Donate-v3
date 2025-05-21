<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['donation_id']) || !isset($data['sender_id']) || !isset($data['receiver_email']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Get database connection
    $conn = getConnection();
    
    // Insert message into database
    $stmt = $conn->prepare("
        INSERT INTO messages (donation_id, sender_id, receiver_email, message, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $data['donation_id'],
        $data['sender_id'],
        $data['receiver_email'],
        $data['message']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} catch (PDOException $e) {
    error_log("Error sending message: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
} 