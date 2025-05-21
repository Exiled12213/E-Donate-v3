<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

try {
    // Check if is_blocked filter parameter exists
    $is_blocked = isset($_GET['is_blocked']) ? (int)$_GET['is_blocked'] : null;
    
    // Get users with optional filtering by blocked status
    $sql = "SELECT id, username, email, created_at, is_blocked, block_reason FROM users WHERE email != 'admin@ua.edu.ph'";
    
    // Add filter for blocked status if parameter was provided
    if ($is_blocked !== null) {
        $sql .= " AND is_blocked = $is_blocked";
    }
    
    $result = $conn->query($sql);
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'created_at' => $row['created_at'],
            'is_blocked' => (bool)$row['is_blocked'],
            'block_reason' => $row['block_reason']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching users: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 