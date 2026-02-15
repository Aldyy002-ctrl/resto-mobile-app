<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/utils.php';

header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, "Method tidak didukung", null, 405);
}

$reportType = $_GET['type'] ?? 'summary';

switch ($reportType) {
    case 'revenue':
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $period = $_GET['period'] ?? 'daily';
        
        if ($period === 'daily') {
            $groupBy = "DATE(created_at)";
            $selectDate = "DATE(created_at) as tanggal";
        } elseif ($period === 'monthly') {
            $groupBy = "DATE_FORMAT(created_at, '%Y-%m')";
            $selectDate = "DATE_FORMAT(created_at, '%Y-%m') as bulan";
        } else {
            $groupBy = "YEAR(created_at)";
            $selectDate = "YEAR(created_at) as tahun";
        }
        
        $sql = "
            SELECT 
                $selectDate,
                COUNT(*) as total_transaksi,
                SUM(total_harga) as total_pendapatan
            FROM transaksi
            WHERE status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY $groupBy
            ORDER BY $selectDate DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        // Get summary
        $stmt2 = $conn->prepare("
            SELECT 
                COUNT(*) as total_transaksi,
                SUM(total_harga) as total_pendapatan,
                AVG(total_harga) as rata_rata
            FROM transaksi
            WHERE status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt2->bind_param("ss", $dateFrom, $dateTo);
        $stmt2->execute();
        $summary = $stmt2->get_result()->fetch_assoc();
        
        sendResponse(true, "Data pendapatan berhasil diambil", [
            'period' => $period,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
            'data' => $data
        ]);
        break;
        
    case 'top-menu':
        $limit = $_GET['limit'] ?? 5;
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $stmt = $conn->prepare("
            SELECT 
                m.id_menu,
                m.nama_menu,
                m.kategori,
                m.gambar,
                SUM(dt.jumlah) as total_terjual,
                SUM(dt.subtotal) as total_pendapatan
            FROM detail_transaksi dt
            JOIN menu m ON dt.id_menu = m.id_menu
            JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
            WHERE t.status = 'paid'
            AND DATE(t.created_at) BETWEEN ? AND ?
            GROUP BY m.id_menu
            ORDER BY total_terjual DESC
            LIMIT ?
        ");
        $stmt->bind_param("ssi", $dateFrom, $dateTo, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        sendResponse(true, "Top menu berhasil diambil", $data);
        break;
        
    case 'summary':
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Total pendapatan
        $stmt1 = $conn->prepare("
            SELECT 
                COUNT(*) as total_transaksi,
                SUM(total_harga) as total_pendapatan
            FROM transaksi
            WHERE status = 'paid'
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt1->bind_param("ss", $dateFrom, $dateTo);
        $stmt1->execute();
        $summary = $stmt1->get_result()->fetch_assoc();
        
        // Menu paling laris
        $stmt2 = $conn->prepare("
            SELECT 
                m.nama_menu,
                SUM(dt.jumlah) as total_terjual
            FROM detail_transaksi dt
            JOIN menu m ON dt.id_menu = m.id_menu
            JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
            WHERE t.status = 'paid'
            AND DATE(t.created_at) BETWEEN ? AND ?
            GROUP BY m.id_menu
            ORDER BY total_terjual DESC
            LIMIT 1
        ");
        $stmt2->bind_param("ss", $dateFrom, $dateTo);
        $stmt2->execute();
        $topMenu = $stmt2->get_result()->fetch_assoc();
        
        // Active orders
        $activeOrders = $conn->query("SELECT COUNT(*) as count FROM transaksi WHERE status IN ('pending', 'cooking', 'ready')")->fetch_assoc();
        
        // Tables occupied
        $tablesOccupied = $conn->query("SELECT COUNT(*) as count FROM meja WHERE status = 'terisi'")->fetch_assoc();
        
        sendResponse(true, "Summary berhasil diambil", [
            'total_transaksi' => $summary['total_transaksi'] ?? 0,
            'total_pendapatan' => $summary['total_pendapatan'] ?? 0,
            'menu_terlaris' => $topMenu['nama_menu'] ?? '-',
            'menu_terlaris_qty' => $topMenu['total_terjual'] ?? 0,
            'active_orders' => $activeOrders['count'] ?? 0,
            'tables_occupied' => $tablesOccupied['count'] ?? 0
        ]);
        break;
        
    default:
        sendResponse(false, "Report type tidak valid", null, 400);
}
