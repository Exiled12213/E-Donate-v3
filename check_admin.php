<?php
require_once 'config/db_config.php';

echo "<h2>Admin User Check</h2>";

try {
    $sql = "SELECT id, username, email, password_hash FROM users WHERE email = 'admin@ua.edu.ph'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<p>Admin user found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Username: " . $user['username'] . "</li>";
        echo "<li>Email: " . $user['email'] . "</li>";
        echo "<li>Password Hash Length: " . strlen($user['password_hash']) . " characters</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>No admin user found with email admin@ua.edu.ph</p>";
        
        // Create admin user if not exists
        $username = "admin";
        $email = "admin@ua.edu.ph";
        $password = "cpe12345";
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password_hash);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Admin user created successfully!</p>";
        } else {
            echo "<p style='color: red;'>Error creating admin user: " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 