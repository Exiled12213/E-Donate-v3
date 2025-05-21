<?php
header('Content-Type: text/plain');
require_once '../config/db_config.php';

try {
    // Get the structure of the donations table
    $result = $conn->query("DESCRIBE donations");
    
    echo "DONATIONS TABLE STRUCTURE:\n";
    echo "==========================\n";
    
    while($row = $result->fetch_assoc()) {
        echo "Column: " . $row['Field'] . "\n";
        echo "Type: " . $row['Type'] . "\n";
        echo "Null: " . $row['Null'] . "\n";
        echo "Default: " . ($row['Default'] ?? 'NULL') . "\n";
        echo "--------------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close(); 