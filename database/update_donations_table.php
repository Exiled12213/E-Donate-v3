<?php
require_once '../config/db_config.php';

try {
    // Add status column if it doesn't exist
    $sql = "ALTER TABLE donations 
            ADD COLUMN IF NOT EXISTS status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
            ADD COLUMN IF NOT EXISTS admin_notes TEXT,
            ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL";
    
    if ($conn->query($sql)) {
        echo "✓ Added new columns to donations table successfully\n";
    } else {
        echo "✗ Error adding columns: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 