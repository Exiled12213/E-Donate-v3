<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing MySQL Connection...\n\n";

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass);
    echo "✓ Connected to MySQL successfully\n";
    
    // Try to create database
    $result = $conn->query("CREATE DATABASE IF NOT EXISTS e_donate");
    if($result) {
        echo "✓ Database 'e_donate' created or already exists\n";
    }
    
    // Select the database
    $conn->select_db('e_donate');
    echo "✓ Selected database 'e_donate'\n";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    if($result) {
        echo "\nExisting tables:\n";
        while($row = $result->fetch_array()) {
            echo "- " . $row[0] . "\n";
        }
    }
    
} catch(Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} 