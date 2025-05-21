<?php
header('Content-Type: application/json');

require_once '../config/db_config.php';

try {
    // Test database connection
    $testQuery = "SELECT 1";
    $result = $conn->query($testQuery);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'db_server' => DB_SERVER,
        'db_name' => DB_NAME,
        'db_user' => DB_USERNAME
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 