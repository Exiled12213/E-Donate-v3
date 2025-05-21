<?php
require_once 'admin_auth.php';
require_once '../config/db_config.php';

// Check if user is admin
requireAdmin();

try {
    $sql = "SELECT d.*, u.username as donor_name, u.email as donor_email 
            FROM donations d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.status = 'pending' 
            ORDER BY d.created_at DESC";
            
    $result = $conn->query($sql);
    $donations = [];
    
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'donations' => $donations
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 