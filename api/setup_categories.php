<?php
require_once '../config/db_config.php';

echo "Starting categories setup...\n";

// Make sure categories table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'categories'");
if ($checkTable->num_rows == 0) {
    echo "Categories table does not exist, creating...\n";
    
    $createTable = "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($createTable);
    echo "Categories table created.\n";
}

// Check if categories already exist
$checkCategories = $conn->query("SELECT COUNT(*) as count FROM categories");
$result = $checkCategories->fetch_assoc();

if ($result['count'] == 0) {
    echo "No categories found, adding default categories...\n";
    
    // Add default categories
    $categories = [
        ['name' => 'Microcontrollers and Boards', 'description' => 'Arduino, Raspberry Pi, ESP32, and other microcontroller boards'],
        ['name' => 'Sensors and Modules', 'description' => 'Various sensors, modules, and electronic components'],
        ['name' => 'Wires and Connectors', 'description' => 'Jumper wires, connectors, and cables'],
        ['name' => 'Power Supply', 'description' => 'Power supplies, batteries, and voltage regulators'],
        ['name' => 'Display Screens', 'description' => 'LCDs, OLEDs, and other display devices'],
        ['name' => 'Prototyping Materials', 'description' => 'Breadboards, perfboards, and other prototyping materials'],
        ['name' => 'Electronic Components', 'description' => 'Resistors, capacitors, diodes, and other basic components'],
        ['name' => 'Cables and Adapters', 'description' => 'USB cables, HDMI cables, adapters, and converters'],
        ['name' => 'Past Projects', 'description' => 'Completed projects that can be reused or studied'],
        ['name' => 'Tools and Equipment', 'description' => 'Soldering irons, multimeters, and other tools']
    ];
    
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    
    foreach ($categories as $category) {
        $stmt->bind_param("ss", $category['name'], $category['description']);
        $stmt->execute();
        echo "Added category: {$category['name']}\n";
    }
    
    $stmt->close();
    echo "Default categories added successfully.\n";
} else {
    echo "Categories already exist in the database.\n";
    
    // List existing categories
    $listCategories = $conn->query("SELECT id, name FROM categories");
    echo "Existing categories:\n";
    
    while ($category = $listCategories->fetch_assoc()) {
        echo "- ID: {$category['id']}, Name: {$category['name']}\n";
    }
}

echo "Categories setup complete.\n";
$conn->close();
?> 