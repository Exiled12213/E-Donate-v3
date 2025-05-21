<?php
header('Content-Type: text/html');
require_once 'config/db_config.php';

echo "<h2>Images Table Structure</h2>";

try {
    // Get table structure
    $result = $conn->query("DESCRIBE images");
    
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Get sample data
    $result = $conn->query("SELECT * FROM images LIMIT 5");
    
    echo "<h3>Sample Data (up to 5 rows):</h3>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        
        // Get field names
        $fields = $result->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>{$field->name}</th>";
        }
        echo "</tr>";
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Get data rows
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No data found in the images table.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Check if file_path exists in the images table
try {
    $result = $conn->query("SHOW COLUMNS FROM images LIKE 'file_path'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Column 'file_path' exists in the images table</p>";
    } else {
        echo "<p style='color: red;'>✗ Column 'file_path' does not exist in the images table</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking for file_path column: " . $e->getMessage() . "</p>";
}
?> 