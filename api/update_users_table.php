<?php
require_once '../config/db_config.php';

try {
    // Add is_blocked column to users table if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_blocked BOOLEAN DEFAULT FALSE";
    $conn->query($sql);
    
    echo "Users table updated successfully!";
} catch (Exception $e) {
    echo "Error updating users table: " . $e->getMessage();
}

$conn->close();
?> 