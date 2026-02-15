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
            $stmt = $conn->prepare("SELECT m.*, k.nama_kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori WHERE m.id_menu = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                sendResponse(true, "Data menu ditemukan", $result->fetch_assoc());
            } else {
                sendResponse(false, "Menu tidak ditemukan", null, 404);
            }
        } else {
            // Get all menu with kategori
            $kategori = $_GET['kategori'] ?? null;
            $sql = "SELECT m.*, k.nama_kategori FROM menu m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori WHERE m.status = 'tersedia'";
            
            if ($kategori) {
                $stmt = $conn->prepare($sql . " AND k.nama_kategori = ? ORDER BY m.nama_menu ASC");
                $stmt->bind_param("s", $kategori);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql . " ORDER BY m.id_kategori, m.nama_menu ASC");
            }
            
            $menu = [];
            while ($row = $result->fetch_assoc()) {
                $menu[] = $row;
            }
            sendResponse(true, "Data menu berhasil diambil", $menu);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['nama_menu']) || !isset($input['harga']) || !isset($input['stok'])) {
            sendResponse(false, "Nama menu, harga, dan stok wajib diisi", null, 400);
        }
        
        $id_kategori = $input['id_kategori'] ?? 1;
        $foto_makanan = $input['foto_makanan'] ?? null;
        
        $stmt = $conn->prepare("INSERT INTO menu (nama_menu, harga, stok, foto_makanan, id_kategori, status) VALUES (?, ?, ?, ?, ?, 'tersedia')");
        $stmt->bind_param("siisi", $input['nama_menu'], $input['harga'], $input['stok'], $foto_makanan, $id_kategori);
        
        if ($stmt->execute()) {
            sendResponse(true, "Menu berhasil ditambahkan", ['id_menu' => $conn->insert_id]);
        } else {
            sendResponse(false, "Gagal menambahkan menu", null, 500);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            sendResponse(false, "ID menu tidak valid", null, 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $updates = [];
        $types = "";
        $params = [];
        
        if (isset($input['nama_menu'])) {
            $updates[] = "nama_menu = ?";
            $types .= "s";
            $params[] = $input['nama_menu'];
        }
        if (isset($input['harga'])) {
            $updates[] = "harga = ?";
            $types .= "i";
            $params[] = $input['harga'];
        }
        if (isset($input['stok'])) {
            $updates[] = "stok = ?";
            $types .= "i";
            $params[] = $input['stok'];
        }
        if (isset($input['foto_makanan'])) {
            $updates[] = "foto_makanan = ?";
            $types .= "s";
            $params[] = $input['foto_makanan'];
        }
        if (isset($input['id_kategori'])) {
            $updates[] = "id_kategori = ?";
            $types .= "i";
            $params[] = $input['id_kategori'];
        }
        if (isset($input['status'])) {
            $updates[] = "status = ?";
            $types .= "s";
            $params[] = $input['status'];
        }
        
        if (empty($updates)) {
            sendResponse(false, "Tidak ada data yang diupdate", null, 400);
        }
        
        $params[] = $id;
        $types .= "i";
        
        $sql = "UPDATE menu SET " . implode(", ", $updates) . " WHERE id_menu = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            sendResponse(true, "Menu berhasil diupdate");
        } else {
            sendResponse(false, "Gagal mengupdate menu", null, 500);
        }
        break;
        
    case 'DELETE':
        if (!$id) {
            sendResponse(false, "ID menu tidak valid", null, 400);
        }
        
        $stmt = $conn->prepare("DELETE FROM menu WHERE id_menu = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            sendResponse(true, "Menu berhasil dihapus");
        } else {
            sendResponse(false, "Gagal menghapus menu", null, 500);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung", null, 405);
}
