<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db_config.php';

try {
    $response = [
        'success' => true,
        'table_structure' => [],
        'sample_data' => [],
        'has_file_path' => false
    ];
    
    // Get table structure
    $result = $conn->query("DESCRIBE images");
    
    while ($row = $result->fetch_assoc()) {
        $response['table_structure'][] = $row;
    }
    
    // Check if file_path exists
    foreach ($response['table_structure'] as $column) {
        if ($column['Field'] === 'file_path') {
            $response['has_file_path'] = true;
            break;
        }
    }
    
    // Get sample data
    $result = $conn->query("SELECT * FROM images LIMIT 5");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response['sample_data'][] = $row;
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 