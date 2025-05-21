<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Validate required parameters
if (!isset($_GET['donation_id']) || !isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Get database connection
    $conn = getConnection();
    
    // Get chat history
    $stmt = $conn->prepare("
        SELECT 
            m.*,
            u.username as sender,
            CASE WHEN m.sender_id = ? THEN 1 ELSE 0 END as is_sender
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.donation_id = ?
        ORDER BY m.created_at ASC
    ");
    
    $stmt->execute([$_GET['user_id'], $_GET['donation_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
} catch (PDOException $e) {
    error_log("Error getting chat history: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get chat history']);
} 