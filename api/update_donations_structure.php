<?php
header('Content-Type: application/json');
require_once '../config/db_config.php';

try {
    // Check if 'condition' column exists
    $result = $conn->query("SHOW COLUMNS FROM donations LIKE 'condition'");
    $columnExists = ($result->num_rows > 0);
    
    if (!$columnExists) {
        // Add 'condition' column if it doesn't exist
        $conn->query("ALTER TABLE donations ADD COLUMN `condition` VARCHAR(50) AFTER description");
        echo json_encode([
            'success' => true,
            'message' => 'Added missing column "condition" to donations table'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Column "condition" already exists in donations table'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating database structure: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 