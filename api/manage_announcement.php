<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Required for preflight requests (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/db_config.php';

// Get the request method (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Debug logging
error_log("Request method: " . $method);
error_log("Request data: " . print_r($data, true));

try {
    switch ($method) {
        case 'POST': // Create new announcement
            if (!isset($data['title']) || !isset($data['content'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                exit;
            }
            
            $sql = "INSERT INTO announcements (title, content, is_active) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $isActive = isset($data['is_active']) ? $data['is_active'] : true;
            $stmt->bind_param("ssi", $data['title'], $data['content'], $isActive);
            
            if ($stmt->execute()) {
                $id = $conn->insert_id;
                echo json_encode([
                    'success' => true, 
                    'message' => 'Announcement created successfully',
                    'announcement_id' => $id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create announcement']);
            }
            break;
            
        case 'PUT': // Update existing announcement
            error_log("Processing PUT request"); // Debug log
            
            if (!isset($data['id'])) {
                error_log("Missing announcement ID in PUT request"); // Debug log
                echo json_encode(['success' => false, 'message' => 'Announcement ID is required']);
                exit;
            }
            
            $updateFields = [];
            $types = "";
            $params = [];
            
            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $types .= "s";
                $params[] = $data['title'];
            }
            
            if (isset($data['content'])) {
                $updateFields[] = "content = ?";
                $types .= "s";
                $params[] = $data['content'];
            }
            
            if (isset($data['is_active'])) {
                $updateFields[] = "is_active = ?";
                $types .= "i";
                $params[] = $data['is_active'] ? 1 : 0;
            }
            
            if (empty($updateFields)) {
                error_log("No fields to update in PUT request"); // Debug log
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }
            
            $sql = "UPDATE announcements SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $types .= "i";
            $params[] = $data['id'];
            
            error_log("SQL query: " . $sql); // Debug log
            error_log("Parameters: " . print_r($params, true)); // Debug log
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                error_log("Announcement updated successfully"); // Debug log
                echo json_encode([
                    'success' => true, 
                    'message' => 'Announcement updated successfully'
                ]);
            } else {
                error_log("Failed to update announcement: " . $stmt->error); // Debug log
                echo json_encode(['success' => false, 'message' => 'Failed to update announcement']);
            }
            break;
            
        case 'DELETE': // Delete announcement
            if (!isset($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Announcement ID is required']);
                exit;
            }
            
            $sql = "DELETE FROM announcements WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $data['id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Announcement deleted successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close(); 