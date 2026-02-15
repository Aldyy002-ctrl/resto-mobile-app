<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';

header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];

if (strpos($request, '/register') !== false && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        sendResponse(false, "Username dan password wajib diisi", null, 400);
    }
    
    $username = $input['username'];
    $password = $input['password'];
    $role = $input['role'] ?? 'pelanggan';
    
    $stmt = $conn->prepare("SELECT id_user FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        sendResponse(false, "Username sudah digunakan", null, 400);
    }
    
    $stmt = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        $token = generateJWT($userId, $username, $role);
        
        sendResponse(true, "Registrasi berhasil", [
            'token' => $token,
            'user' => [
                'id_user' => $userId,
                'username' => $username,
                'role' => $role
            ]
        ]);
    } else {
        sendResponse(false, "Gagal registrasi", null, 500);
    }
    
} elseif (strpos($request, '/login') !== false && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        sendResponse(false, "Username dan password wajib diisi", null, 400);
    }
    
    $username = $input['username'];
    $password = $input['password'];
    
    $stmt = $conn->prepare("SELECT id_user, username, password, role FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, "Username tidak ditemukan", null, 401);
    }
    
    $user = $result->fetch_assoc();
    
    if ($password !== $user['password']) {
        sendResponse(false, "Password salah", null, 401);
    }
    
    $token = generateJWT($user['id_user'], $user['username'], $user['role']);
    
    sendResponse(true, "Login berhasil", [
        'token' => $token,
        'user' => [
            'id_user' => $user['id_user'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ]);
    
} elseif (strpos($request, '/verify') !== false && $method === 'POST') {
    $token = getBearerToken();
    
    if (!$token) {
        sendResponse(false, "Token tidak ditemukan", null, 401);
    }
    
    $payload = verifyJWT($token);
    
    if (!$payload) {
        sendResponse(false, "Token tidak valid", null, 401);
    }
    
    sendResponse(true, "Token valid", [
        'user' => [
            'id_user' => $payload->userId,
            'username' => $payload->username,
            'role' => $payload->role
        ]
    ]);
    
} else {
    sendResponse(false, "Endpoint tidak ditemukan", null, 404);
}
