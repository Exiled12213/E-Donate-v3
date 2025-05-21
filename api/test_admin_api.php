<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// This is a test endpoint to check if the API is accessible
echo json_encode([
    'success' => true,
    'message' => 'API is working correctly',
    'timestamp' => date('Y-m-d H:i:s')
]); 