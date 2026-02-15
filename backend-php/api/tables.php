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
            $stmt = $conn->prepare("SELECT * FROM meja WHERE id_meja = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                sendResponse(true, "Data meja ditemukan", $result->fetch_assoc());
            } else {
                sendResponse(false, "Meja tidak ditemukan", null, 404);
            }
        } else {
            $result = $conn->query("SELECT * FROM meja ORDER BY nomor_meja ASC");
            $tables = [];
            while ($row = $result->fetch_assoc()) {
                $tables[] = $row;
            }
            sendResponse(true, "Data meja berhasil diambil", $tables);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            sendResponse(false, "ID meja tidak valid", null, 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action']) && $input['action'] === 'select') {
            // Mark table as occupied (terisi)
            $stmt = $conn->prepare("UPDATE meja SET status = 'terisi' WHERE id_meja = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, "Meja berhasil dipilih");
            } else {
                sendResponse(false, "Gagal memilih meja", null, 500);
            }
        } elseif (isset($input['action']) && $input['action'] === 'clear') {
            // Clear table (kosong)
            $stmt = $conn->prepare("UPDATE meja SET status = 'kosong' WHERE id_meja = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, "Meja berhasil dikosongkan");
            } else {
                sendResponse(false, "Gagal mengosongkan meja", null, 500);
            }
        } else {
            sendResponse(false, "Action tidak valid", null, 400);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung", null, 405);
}
