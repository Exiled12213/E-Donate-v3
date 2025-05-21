<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/db_config.php';

// Handle user registration
function registerUser($data) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $data['username']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
    
    if(mysqli_stmt_execute($stmt)) {
        return ["success" => true, "message" => "Registration successful"];
    }
    return ["success" => false, "message" => "Error: " . mysqli_error($conn)];
}

// Handle user login
function loginUser($data) {
    global $conn;
    $email = mysqli_real_escape_string($conn, $data['email']);
    
    $sql = "SELECT id, username, password_hash FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        if(password_verify($data['password'], $row['password_hash'])) {
            return ["success" => true, "user" => ["id" => $row['id'], "username" => $row['username']]];
        }
    }
    return ["success" => false, "message" => "Invalid credentials"];
}

// Handle donation creation
function createDonation($data) {
    global $conn;
    $user_id = mysqli_real_escape_string($conn, $data['user_id']);
    $category_id = mysqli_real_escape_string($conn, $data['category_id']);
    $title = mysqli_real_escape_string($conn, $data['title']);
    $description = mysqli_real_escape_string($conn, $data['description']);
    $condition = mysqli_real_escape_string($conn, $data['condition']);
    
    $sql = "INSERT INTO donations (user_id, category_id, title, description, `condition`) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisss", $user_id, $category_id, $title, $description, $condition);
    
    if(mysqli_stmt_execute($stmt)) {
        return ["success" => true, "donation_id" => mysqli_insert_id($conn)];
    }
    return ["success" => false, "message" => "Error: " . mysqli_error($conn)];
}

// Handle the incoming request
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'register':
        echo json_encode(registerUser($data));
        break;
    case 'login':
        echo json_encode(loginUser($data));
        break;
    case 'create_donation':
        echo json_encode(createDonation($data));
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}

mysqli_close($conn);
?> 