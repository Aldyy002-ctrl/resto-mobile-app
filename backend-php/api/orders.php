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
            // Get single order with details
            $stmt = $conn->prepare("
                SELECT t.*, u.username, m.nomor_meja
                FROM transaksi t
                LEFT JOIN user u ON t.id_user = u.id_user
                LEFT JOIN meja m ON t.id_meja = m.id_meja
                WHERE t.id_transaksi = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $order = $result->fetch_assoc();
                
                // Get detail items
                $stmt2 = $conn->prepare("
                    SELECT dt.*, mn.nama_menu, mn.foto_makanan
                    FROM detail_transaksi dt
                    LEFT JOIN menu mn ON dt.id_menu = mn.id_menu
                    WHERE dt.id_transaksi = ?
                ");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                
                $items = [];
                while ($row = $result2->fetch_assoc()) {
                    $items[] = $row;
                }
                
                $order['items'] = $items;
                sendResponse(true, "Data transaksi ditemukan", $order);
            } else {
                sendResponse(false, "Transaksi tidak ditemukan", null, 404);
            }
        } else {
            // Get orders with filter
            $status = $_GET['status'] ?? null;
            $sql = "
                SELECT t.*, u.username, m.nomor_meja
                FROM transaksi t
                LEFT JOIN user u ON t.id_user = u.id_user
                LEFT JOIN meja m ON t.id_meja = m.id_meja
                WHERE 1=1
            ";
            
            if ($status) {
                $stmt = $conn->prepare($sql . " AND t.status = ? ORDER BY t.tanggal_transaksi DESC");
                $stmt->bind_param("s", $status);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($sql . " ORDER BY t.tanggal_transaksi DESC");
            }
            
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            sendResponse(true, "Data transaksi berhasil diambil", $orders);
        }
        break;
        
    case 'POST':
        // Create new order
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id_meja']) || !isset($input['id_user']) || !isset($input['items']) || empty($input['items'])) {
            sendResponse(false, "Data meja, user, dan items wajib diisi", null, 400);
        }
        
        $conn->begin_transaction();
        
        try {
            // Insert transaksi
            $nama_pelanggan = $input['nama_pelanggan'] ?? '';
            $stmt = $conn->prepare("INSERT INTO transaksi (id_user, id_meja, nama_pelanggan, status, total_harga, tanggal_transaksi) VALUES (?, ?, ?, 'pending', 0, NOW())");
            $stmt->bind_param("iis", $input['id_user'], $input['id_meja'], $nama_pelanggan);
            $stmt->execute();
            $idTransaksi = $conn->insert_id;
            
            // Insert detail items
            $totalHarga = 0;
            foreach ($input['items'] as $item) {
                // Get menu price
                $stmt2 = $conn->prepare("SELECT harga, stok FROM menu WHERE id_menu = ?");
                $stmt2->bind_param("i", $item['id_menu']);
                $stmt2->execute();
                $result = $stmt2->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Menu tidak ditemukan");
                }
                
                $menu = $result->fetch_assoc();
                $harga = $menu['harga'];
                $jumlah = $item['jumlah'] ?? 1;
                $subtotal = $harga * $jumlah;
                $totalHarga += $subtotal;
                
                // Check stock
                if ($menu['stok'] < $jumlah) {
                    throw new Exception("Stok menu tidak cukup");
                }
                
                // Insert detail
                $stmt3 = $conn->prepare("INSERT INTO detail_transaksi (id_transaksi, id_menu, harga_satuan, jumlah, subtotal, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt3->bind_param("iiidis", $idTransaksi, $item['id_menu'], $harga, $jumlah, $subtotal, $item['catatan']);
                $stmt3->execute();
                
                // Update stock
                $stmt4 = $conn->prepare("UPDATE menu SET stok = stok - ? WHERE id_menu = ?");
                $stmt4->bind_param("ii", $jumlah, $item['id_menu']);
                $stmt4->execute();
            }
            
            // Update total harga transaksi
            $stmt5 = $conn->prepare("UPDATE transaksi SET total_harga = ? WHERE id_transaksi = ?");
            $stmt5->bind_param("di", $totalHarga, $idTransaksi);
            $stmt5->execute();
            
            // Update table status to terisi
            $stmt6 = $conn->prepare("UPDATE meja SET status = 'terisi' WHERE id_meja = ?");
            $stmt6->bind_param("i", $input['id_meja']);
            $stmt6->execute();
            
            $conn->commit();
            sendResponse(true, "Pesanan berhasil dibuat", [
                'id_transaksi' => $idTransaksi,
                'total_harga' => $totalHarga
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            sendResponse(false, "Gagal membuat pesanan: " . $e->getMessage(), null, 500);
        }
        break;
        
    case 'PUT':
        if (!$id) {
            sendResponse(false, "ID transaksi tidak valid", null, 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['action'])) {
            if ($input['action'] === 'update_status') {
                $newStatus = $input['status'] ?? null;
                if (!$newStatus) {
                    sendResponse(false, "Status baru wajib diisi", null, 400);
                }
                
                $stmt = $conn->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
                $stmt->bind_param("si", $newStatus, $id);
                $stmt->execute();
                sendResponse(true, "Status pesanan berhasil diupdate");
                
            } elseif ($input['action'] === 'complete') {
                $stmt = $conn->prepare("UPDATE transaksi SET status = 'ready' WHERE id_transaksi = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                sendResponse(true, "Pesanan selesai dimasak");
                
            } elseif ($input['action'] === 'payment') {
                $metodePembayaran = $input['metode_pembayaran'] ?? null;
                
                if (!$metodePembayaran) {
                    sendResponse(false, "Metode pembayaran wajib diisi", null, 400);
                }
                
                $stmt = $conn->prepare("UPDATE transaksi SET status = 'lunas', metode_pembayaran = ? WHERE id_transaksi = ?");
                $stmt->bind_param("si", $metodePembayaran, $id);
                $stmt->execute();
                
                // Clear table
                $stmt2 = $conn->prepare("UPDATE meja SET status = 'kosong' WHERE id_meja = (SELECT id_meja FROM transaksi WHERE id_transaksi = ?)");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                
                sendResponse(true, "Pembayaran berhasil diproses");
            } else {
                sendResponse(false, "Action tidak valid", null, 400);
            }
        } else {
            sendResponse(false, "Action wajib diisi", null, 400);
        }
        break;
        
    default:
        sendResponse(false, "Method tidak didukung", null, 405);
}
