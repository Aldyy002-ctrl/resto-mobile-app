<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';

header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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

preg_match('/\/(\d+)$/', $_SERVER['REQUEST_URI'], $matches);
$id = $matches[1] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("SELECT id_user, username, role FROM user WHERE id_user = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                sendResponse(true, "Data user ditemukan", $result->fetch_assoc());
            } else {
                sendResponse(false, "User tidak ditemukan", null, 404);
            }
        } else {
            $role = $_GET['role'] ?? null;
            $sql = "SELECT id_user, username, role FROM user WHERE 1=1";
            
            if ($role) {
                $stmt = $conn->prepare($sql . " AND role = ? ORDER BY username ASC");
                $stmt->bind_param("s", $role);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql . " ORDER BY role, username ASC");
            }
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            sendResponse(true, "Data user berhasil diambil", $users);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['username']) || !isset($input['password']) || !isset($input['role'])) {
            sendResponse(false, "Username, password, dan role wajib diisi", null, 400);
        }
        
        // Check if username exists
        $stmt = $conn->prepare("SELECT id_user FROM user WHERE username = ?");
        $stmt->bind_param("s", $input['username']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            sendResponse(false, "Username sudah digunakan", null, 400);
        }
        
        $stmt = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $input['username'], $input['password'], $input['role']);
        
        if ($stmt->execute()) {
            sendResponse(true, "User berhasil ditambahkan", ['id_user' => $conn->insert_id]);
        } else {
            sendResponse(false, "Gagal menambahkan user", null, 500);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            sendResponse(false, "ID user tidak valid", null, 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $updates = [];
        $types = "";
        $params = [];
        
        if (isset($input['username'])) {
            $stmt = $conn->prepare("SELECT id_user FROM user WHERE username = ? AND id_user != ?");
            $stmt->bind_param("si", $input['username'], $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                sendResponse(false, "Username sudah digunakan", null, 400);
            }
            
            $updates[] = "username = ?";
            $types .= "s";
            $params[] = $input['username'];
        }
        
        if (isset($input['password'])) {
            $updates[] = "password = ?";
            $types .= "s";
            $params[] = $input['password'];
        }
        
        if (isset($input['role'])) {
            $updates[] = "role = ?";
            $types .= "s";
            $params[] = $input['role'];
        }
        
        if (empty($updates)) {
            sendResponse(false, "Tidak ada data yang diupdate", null, 400);
        }
        
        $params[] = $id;
        $types .= "i";
        
        $sql = "UPDATE user SET " . implode(", ", $updates) . " WHERE id_user = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            sendResponse(true, "User berhasil diupdate");
        } else {
            sendResponse(false, "Gagal mengupdate user", null, 500);
        }
        break;
        
    case 'DELETE':
        if (!$id) {
            sendResponse(false, "ID user tidak valid", null, 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(true, "User berhasil dihapus");
        } else {
            sendResponse(false, "Gagal menghapus user", null, 500);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung", null, 405);
}
