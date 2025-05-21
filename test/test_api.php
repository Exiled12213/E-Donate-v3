<?php
header("Content-Type: text/plain");
require_once '../config/db_config.php';

echo "Testing Database Connection...\n";
if($conn) {
    echo "✓ Database connection successful\n\n";
} else {
    echo "✗ Database connection failed\n\n";
    exit;
}

echo "Testing User Registration...\n";
$test_user = [
    'username' => 'testuser',
    'email' => 'test@ua.edu.ph',
    'password' => password_hash('test123', PASSWORD_DEFAULT)
];

$sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $test_user['username'], $test_user['email'], $test_user['password']);

if(mysqli_stmt_execute($stmt)) {
    echo "✓ User registration successful\n";
    $test_user['id'] = mysqli_insert_id($conn);
} else {
    echo "✗ User registration failed: " . mysqli_error($conn) . "\n";
}

echo "\nTesting Categories...\n";
$sql = "SELECT * FROM categories LIMIT 1";
$result = mysqli_query($conn, $sql);
if($result && mysqli_num_rows($result) > 0) {
    echo "✓ Categories table accessible\n";
    $category = mysqli_fetch_assoc($result);
    echo "  Sample category: " . $category['name'] . "\n";
} else {
    echo "✗ Categories table empty or not accessible\n";
}

echo "\nTesting Donation Creation...\n";
$sql = "INSERT INTO donations (user_id, category_id, title, description, `condition`) 
        VALUES (?, 1, 'Test Donation', 'This is a test donation', 'good')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $test_user['id']);

if(mysqli_stmt_execute($stmt)) {
    echo "✓ Donation creation successful\n";
    $donation_id = mysqli_insert_id($conn);
} else {
    echo "✗ Donation creation failed: " . mysqli_error($conn) . "\n";
}

// Clean up test data
echo "\nCleaning up test data...\n";
mysqli_query($conn, "DELETE FROM donations WHERE id = " . $donation_id);
mysqli_query($conn, "DELETE FROM users WHERE id = " . $test_user['id']);
echo "✓ Test data cleaned up\n";

mysqli_close($conn);
echo "\nTest completed!\n"; 