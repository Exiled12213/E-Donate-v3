<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db_config.php';

try {
    // If id is provided, get specific announcement, otherwise get all
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM announcements WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $announcement = $result->fetch_assoc();
            echo json_encode(['success' => true, 'announcement' => $announcement]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        }
    } else {
        // Get all announcements, filter by active status if specified
        $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
        
        $sql = "SELECT * FROM announcements";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY created_at DESC";
        
        $result = $conn->query($sql);
        
        $announcements = [];
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'count' => count($announcements), 
            'announcements' => $announcements
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close(); 