<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db_config.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['user_id']) || !isset($data['block'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$user_id = $data['user_id'];
$block = $data['block'] ? 1 : 0;
$block_reason = isset($data['block_reason']) ? $data['block_reason'] : null;

try {
    // Check if block_reason column exists, add it if it doesn't
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'block_reason'");
    if ($result->num_rows == 0) {
        // Add the block_reason column
        $conn->query("ALTER TABLE users ADD COLUMN block_reason TEXT NULL DEFAULT NULL");
    }

    // Check if user exists and is not admin
    $check_sql = "SELECT email FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $user = $result->fetch_assoc();
    if ($user['email'] === 'admin@ua.edu.ph') {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot block admin user'
        ]);
        exit;
    }
    
    // Update user block status and reason
    if ($block) {
        // When blocking, update both block status and reason
        $sql = "UPDATE users SET is_blocked = ?, block_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $block, $block_reason, $user_id);
    } else {
        // When unblocking, clear the block reason
        $sql = "UPDATE users SET is_blocked = 0, block_reason = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => $block ? 'User blocked successfully' : 'User unblocked successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update user status'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating user: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 