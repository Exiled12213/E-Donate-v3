<?php
header('Content-Type: application/json');

require_once '../config/db_config.php';

// Get current database error reporting settings
$error_reporting = mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Test simple database connection
    $testQuery = "SELECT 1";
    $conn->query($testQuery);
    
    // Try to describe the donations table
    $tableResult = $conn->query("DESCRIBE donations");
    $tableColumns = [];
    while ($row = $tableResult->fetch_assoc()) {
        $tableColumns[] = $row;
    }

    // For demonstration, try a simple donation insert
    $testUserId = 1; // Replace with an actual user ID from your database
    $testCategoryId = 1; // Replace with an actual category ID from your database
    $testTitle = "Test Donation";
    $testDesc = "This is a test donation";
    $testCondition = "good";
    
    // Prepare statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO donations (user_id, category_id, title, description, `condition`, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("iisss", $testUserId, $testCategoryId, $testTitle, $testDesc, $testCondition);
    
    $insertSuccess = $stmt->execute();
    $newDonationId = $conn->insert_id;
    
    $stmt->close();
    
    // Get last inserted donation
    $lastDonation = null;
    if ($newDonationId) {
        $result = $conn->query("SELECT * FROM donations WHERE id = $newDonationId");
        $lastDonation = $result->fetch_assoc();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Test completed successfully',
        'donation_insert_success' => $insertSuccess,
        'new_donation_id' => $newDonationId,
        'table_structure' => $tableColumns,
        'last_donation' => $lastDonation,
        'db_info' => [
            'server' => DB_SERVER,
            'database' => DB_NAME,
            'user' => DB_USERNAME
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'db_info' => [
            'server' => DB_SERVER,
            'database' => DB_NAME,
            'user' => DB_USERNAME
        ]
    ]);
}

$conn->close();
?> 