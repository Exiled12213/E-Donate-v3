<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

require_once '../config/db_config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start debugging
$debug_log = fopen("debug.log", "a");
fwrite($debug_log, "\n[" . date('Y-m-d H:i:s') . "] === Login Debug Start ===\n");

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    fwrite($debug_log, "No data received or invalid JSON\n");
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

fwrite($debug_log, "Attempting login for email: " . $email . "\n");

// Get user from database
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    fwrite($debug_log, "User not found in database\n");
    echo json_encode(['success' => false, 'message' => 'User not found']);
    fclose($debug_log);
    exit;
}

$user = $result->fetch_assoc();

// Check if user is blocked
if ($user['is_blocked'] && $user['email'] !== 'admin@ua.edu.ph') {
    fwrite($debug_log, "User is blocked\n");
    echo json_encode(['success' => false, 'message' => 'Your account has been blocked. Please contact the administrator.']);
    fclose($debug_log);
    exit;
}

fwrite($debug_log, "User found in database:\n");
fwrite($debug_log, "User ID: " . $user['id'] . "\n");
fwrite($debug_log, "Username: " . $user['username'] . "\n");
fwrite($debug_log, "Email: " . $user['email'] . "\n");

// For admin user, handle special case
if ($email === 'admin@ua.edu.ph') {
    if ($password === 'cpe12345') {
        fwrite($debug_log, "Admin login successful\n");
        
        // Create session data for admin
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'is_admin' => true
        ];
        
        fwrite($debug_log, "Session created for admin: " . print_r($_SESSION, true) . "\n");
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => true
            ]
        ]);
        fwrite($debug_log, "Admin response sent\n");
    } else {
        fwrite($debug_log, "Admin password match failed\n");
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
    }
} else {
    // For regular users
    if (password_verify($password, $user['password_hash'])) {
        fwrite($debug_log, "Password verification successful for regular user\n");
        
        // Create session data for regular user
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'is_admin' => false
        ];
        
        fwrite($debug_log, "Session created for regular user: " . print_r($_SESSION, true) . "\n");
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => false
            ]
        ]);
    } else {
        fwrite($debug_log, "Password verification failed\n");
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
    }
}

fwrite($debug_log, "=== Login Debug End ===\n");
fclose($debug_log);
$stmt->close();
$conn->close();
?> 