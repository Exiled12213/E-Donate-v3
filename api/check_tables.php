<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db_config.php';

echo "DATABASE TABLES CHECK\n";
echo "====================\n\n";

// Check donations table
try {
    echo "DONATIONS TABLE:\n";
    $result = $conn->query("DESCRIBE donations");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']} ";
            echo $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            echo " Default: " . ($row['Default'] ?? 'NULL') . "\n";
        }
    } else {
        echo "Error: Could not get donations table structure.\n";
    }
} catch (Exception $e) {
    echo "Error checking donations table: " . $e->getMessage() . "\n";
}

echo "\n";

// Check users table
try {
    echo "USERS TABLE:\n";
    $result = $conn->query("DESCRIBE users");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']} ";
            echo $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            echo " Default: " . ($row['Default'] ?? 'NULL') . "\n";
        }
    } else {
        echo "Error: Could not get users table structure.\n";
    }
} catch (Exception $e) {
    echo "Error checking users table: " . $e->getMessage() . "\n";
}

echo "\n";

// Check categories table
try {
    echo "CATEGORIES TABLE:\n";
    $result = $conn->query("DESCRIBE categories");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']} ";
            echo $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            echo " Default: " . ($row['Default'] ?? 'NULL') . "\n";
        }
    } else {
        echo "Error: Could not get categories table structure.\n";
    }
} catch (Exception $e) {
    echo "Error checking categories table: " . $e->getMessage() . "\n";
}

echo "\n";

// Check if tables have data
try {
    echo "TABLE ROW COUNTS:\n";
    $tables = ['users', 'donations', 'categories', 'images'];
    
    foreach ($tables as $table) {
        $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "- $table: $count rows\n";
    }
} catch (Exception $e) {
    echo "Error counting table rows: " . $e->getMessage() . "\n";
}

$conn->close(); 