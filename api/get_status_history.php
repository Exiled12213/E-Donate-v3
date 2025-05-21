<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db_config.php';

// Get donation ID from query parameter
$donation_id = isset($_GET['donation_id']) ? $_GET['donation_id'] : null;

if (!$donation_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Donation ID is required'
    ]);
    exit;
}

try {
    // Check if the status history table exists
    $result = $conn->query("SHOW TABLES LIKE 'donation_status_history'");
    if ($result->num_rows == 0) {
        // Table doesn't exist, return empty history
        echo json_encode([
            'success' => true,
            'history' => []
        ]);
        exit;
    }
    
    // Get status history with user information
    $sql = "SELECT h.*, u.username as created_by_name 
            FROM donation_status_history h 
            LEFT JOIN users u ON h.created_by = u.id 
            WHERE h.donation_id = ? 
            ORDER BY h.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            'id' => $row['id'],
            'donation_id' => $row['donation_id'],
            'status' => $row['status'],
            'comment' => $row['comment'],
            'created_by' => $row['created_by'],
            'created_by_name' => $row['created_by_name'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching status history: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 