<?php
require_once '../config/db_config.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents('add_is_blocked_column.sql');
    if ($conn->query($sql)) {
        echo "✓ Added is_blocked column successfully\n";
    } else {
        echo "✗ Error adding is_blocked column: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 