<?php
require_once '../config/db_config.php';

// Read the SQL file
$sql = file_get_contents('create_missing_tables.sql');

// Execute the SQL commands
try {
    $result = $conn->multi_query($sql);
    
    if ($result) {
        echo "✓ Tables created successfully!\n";
        
        // Clear out the results
        while ($conn->more_results() && $conn->next_result()) {
            if ($res = $conn->store_result()) {
                $res->free();
            }
        }
    } else {
        echo "✗ Error creating tables: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 