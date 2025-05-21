<?php
require_once __DIR__ . '/../config/db_config.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/create_status_history_table.sql');
    
    // Execute the SQL commands
    if ($conn->multi_query($sql)) {
        do {
            // Store the result
            if ($result = $conn->store_result()) {
                $result->free();
            }
            // Move to the next result
        } while ($conn->more_results() && $conn->next_result());
        
        echo "Status history table created successfully.\n";
    } else {
        echo "Error creating status history table: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 