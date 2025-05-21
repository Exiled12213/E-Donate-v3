<?php
require_once 'config/db_config.php';

try {
    // Create announcements table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($createTableSQL)) {
        echo "Announcements table created or already exists.\n";
    } else {
        echo "Error creating announcements table: " . $conn->error . "\n";
    }

    // Insert sample announcements
    $announcements = [
        [
            'title' => 'Welcome to E-Donate!',
            'content' => 'Our platform is now fully functional. Start donating your electronic items to help fellow UA Computer Engineering students!',
            'is_active' => true
        ],
        [
            'title' => 'New Microcontroller Donations Needed',
            'content' => 'We are currently looking for Arduino, Raspberry Pi and ESP32 donations for upcoming robotics projects. Please consider donating if you have spare items.',
            'is_active' => true
        ],
        [
            'title' => 'Donation Drive: Electronic Components',
            'content' => 'This month we are focusing on collecting basic electronic components: resistors, capacitors, LEDs, etc. Even small donations can make a big difference!',
            'is_active' => true
        ]
    ];

    // Prepare and execute statement for each announcement
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, is_active) VALUES (?, ?, ?)");
    
    foreach ($announcements as $announcement) {
        $stmt->bind_param("ssi", $announcement['title'], $announcement['content'], $announcement['is_active']);
        $stmt->execute();
        echo "Added announcement: {$announcement['title']}\n";
    }
    
    echo "Sample announcements added successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 