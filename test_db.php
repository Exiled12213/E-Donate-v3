<?php
require_once 'config/db_config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    if ($conn->ping()) {
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
    }

    // Test if database exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'e_donate'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Database 'e_donate' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Database 'e_donate' does not exist</p>";
    }

    // Test if tables exist
    $tables = ['users', 'categories', 'donations', 'images', 'videos', 'messages'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
        }
    }

    // Test if categories are seeded
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "<p style='color: green;'>✓ Categories are seeded (Found {$row['count']} categories)</p>";
    } else {
        echo "<p style='color: red;'>✗ Categories are not seeded</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 