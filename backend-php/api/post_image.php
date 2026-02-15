<?php
// CORS headers MUST be first - before any includes or code
header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response['success'] = true;
    $response['message'] = 'Upload endpoint is reachable';
    http_response_code(200);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error');
        }

        $file = $_FILES['image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
        }

        // Validate file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum 5MB allowed.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Upload directory
        $uploadDir = __DIR__ . '/../uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadPath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Return the file URL (proxied path)
        $fileUrl = '/api/../uploads/' . $filename; // This is a bit hacky, but let's try to make it work with proxy
        // Better yet, just return the filename and let frontend handle it? 
        // No, let's return a path that works with the existing proxy or a new one.
        $fileUrl = '/api/uploads/' . $filename; 

        $response['success'] = true;
        $response['message'] = 'File uploaded successfully';
        $response['data'] = [
            'filename' => $filename,
            'url' => $fileUrl,
            'path' => $uploadPath
        ];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        http_response_code(400);
    }
} else {
    $response['message'] = 'Method not allowed';
    http_response_code(405);
}

header('Content-Type: application/json');
echo json_encode($response);
