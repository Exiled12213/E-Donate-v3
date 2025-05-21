<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once '../config/db_config.php';

try {
    // Simple query with no filters
    $query = "SELECT d.id, d.title, d.description, d.condition, d.status, 
                     d.created_at, u.username as donor_name, c.name as category 
              FROM donations d
              JOIN users u ON d.user_id = u.id
              JOIN categories c ON d.category_id = c.id
              ORDER BY d.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $donations = [];
    while ($row = $result->fetch_assoc()) {
        // Convert status codes
        if ($row['status'] == 'A') {
            $row['status_text'] = 'Accepted';
        } elseif ($row['status'] == 'D') {
            $row['status_text'] = 'Declined';
        } else {
            $row['status_text'] = 'Pending';
        }
        
        $donations[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($donations),
        'donations' => $donations
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close(); 