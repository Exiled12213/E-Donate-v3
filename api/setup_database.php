<?php
header('Content-Type: text/html');
require_once '../config/db_config.php';

echo "<h2>E-Donate Database Setup</h2>";

// Check and create users table if needed
try {
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        echo "<p>Creating users table...</p>";
        
        $createUsers = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            is_blocked TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createUsers);
        echo "<p>Users table created successfully!</p>";
    } else {
        echo "<p>Users table already exists.</p>";
        
        // Check for is_admin column
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER password_hash");
            echo "<p>Added is_admin column to users table.</p>";
        }
        
        // Check for is_blocked column
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_blocked'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD COLUMN is_blocked TINYINT(1) DEFAULT 0 AFTER is_admin");
            echo "<p>Added is_blocked column to users table.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error with users table: " . $e->getMessage() . "</p>";
}

// Check and create categories table if needed
try {
    $result = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($result->num_rows == 0) {
        echo "<p>Creating categories table...</p>";
        
        $createCategories = "CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createCategories);
        echo "<p>Categories table created successfully!</p>";
        
        // Add default categories
        echo "<p>Adding default categories...</p>";
        $categories = [
            ['name' => 'Microcontrollers and Boards', 'description' => 'Arduino, Raspberry Pi, ESP32, etc.'],
            ['name' => 'Sensors and Modules', 'description' => 'Various sensors and electronic modules'],
            ['name' => 'Wires and Connectors', 'description' => 'Various wires, cables and connectors'],
            ['name' => 'Power Supply', 'description' => 'Power supplies, batteries and adapters'],
            ['name' => 'Display', 'description' => 'LCD, LED and other display devices'],
            ['name' => 'Components', 'description' => 'Electronic components like resistors, capacitors, etc.'],
            ['name' => 'Projects', 'description' => 'Project boards and completed projects'],
            ['name' => 'Prototype', 'description' => 'Breadboards and prototyping materials'],
            ['name' => 'Tools', 'description' => 'Tools and testing equipment']
        ];
        
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->bind_param("ss", $category['name'], $category['description']);
            $stmt->execute();
        }
        echo "<p>Default categories added!</p>";
    } else {
        echo "<p>Categories table already exists.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error with categories table: " . $e->getMessage() . "</p>";
}

// Check and create donations table if needed
try {
    $result = $conn->query("SHOW TABLES LIKE 'donations'");
    if ($result->num_rows == 0) {
        echo "<p>Creating donations table...</p>";
        
        $createDonations = "CREATE TABLE donations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            `condition` VARCHAR(50),
            status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
            admin_notes TEXT,
            reviewed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createDonations);
        echo "<p>Donations table created successfully!</p>";
    } else {
        echo "<p>Donations table already exists.</p>";
        
        // Check for condition column
        $result = $conn->query("SHOW COLUMNS FROM donations LIKE 'condition'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE donations ADD COLUMN `condition` VARCHAR(50) AFTER description");
            echo "<p>Added condition column to donations table.</p>";
        }
        
        // Check for status column
        $result = $conn->query("SHOW COLUMNS FROM donations LIKE 'status'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE donations ADD COLUMN status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending' AFTER `condition`");
            echo "<p>Added status column to donations table.</p>";
        }
        
        // Check for admin_notes column
        $result = $conn->query("SHOW COLUMNS FROM donations LIKE 'admin_notes'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE donations ADD COLUMN admin_notes TEXT AFTER status");
            echo "<p>Added admin_notes column to donations table.</p>";
        }
        
        // Check for reviewed_at column
        $result = $conn->query("SHOW COLUMNS FROM donations LIKE 'reviewed_at'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE donations ADD COLUMN reviewed_at TIMESTAMP NULL AFTER admin_notes");
            echo "<p>Added reviewed_at column to donations table.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error with donations table: " . $e->getMessage() . "</p>";
}

// Check and create images table if needed
try {
    $result = $conn->query("SHOW TABLES LIKE 'images'");
    if ($result->num_rows == 0) {
        echo "<p>Creating images table...</p>";
        
        $createImages = "CREATE TABLE images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            donation_id INT NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createImages);
        echo "<p>Images table created successfully!</p>";
    } else {
        echo "<p>Images table already exists.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error with images table: " . $e->getMessage() . "</p>";
}

// Check uploads directory
$uploadsDir = '../uploads';
if (!file_exists($uploadsDir)) {
    if (mkdir($uploadsDir, 0777, true)) {
        echo "<p>Created uploads directory.</p>";
    } else {
        echo "<p style='color:red'>Failed to create uploads directory. Please create it manually and give it write permissions.</p>";
    }
} else {
    echo "<p>Uploads directory exists.</p>";
}

// Test database connection with admin user
try {
    // Check if admin user exists
    $adminEmail = 'admin@ua.edu.ph';
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<p>Creating admin user...</p>";
        
        // Create admin user
        $adminUsername = 'admin';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $isAdmin = 1;
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $adminUsername, $adminEmail, $adminPassword, $isAdmin);
        $stmt->execute();
        
        echo "<p>Admin user created! Use the following credentials to log in:</p>";
        echo "<ul>";
        echo "<li>Email: admin@ua.edu.ph</li>";
        echo "<li>Password: admin123</li>";
        echo "</ul>";
        echo "<p><strong>Important:</strong> Please change the password after first login!</p>";
    } else {
        $admin = $result->fetch_assoc();
        echo "<p>Admin user already exists (username: {$admin['username']}).</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error with admin user: " . $e->getMessage() . "</p>";
}

echo "<h3>Database Setup Complete!</h3>";
echo "<p>If you encountered any errors, please address them manually or contact support.</p>";
echo "<p><a href='../index.html'>Return to Homepage</a></p>";

$conn->close();
?> 