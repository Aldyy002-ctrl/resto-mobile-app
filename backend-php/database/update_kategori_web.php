<?php
/**
 * One-time Database Update Script
 * URL: http://localhost/backend-php/database/update_kategori_web.php
 * 
 * This script updates kategori table to lowercase
 */

require_once __DIR__ . '/../config/database.php';

// Prevent running multiple times by checking query param
$confirm = $_GET['confirm'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Kategori Database</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .info-box p {
            color: #555;
            line-height: 1.6;
            font-size: 14px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .data-table th {
            background: #667eea;
            color: white;
            font-weight: 600;
        }
        .data-table tr:hover {
            background: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.4);
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .success-message {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success-message h3 {
            color: #047857;
            margin-bottom: 10px;
        }
        .error-message {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error-message h3 {
            color: #dc2626;
            margin-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Update Tool</h1>
        <p class="subtitle">Update kategori table to lowercase</p>

<?php
if ($confirm === 'yes') {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Get data before update
        echo '<div class="info-box">';
        echo '<h3>📊 Before Update</h3>';
        echo '<table class="data-table">';
        echo '<tr><th>ID</th><th>Category Name</th></tr>';
        
        $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori");
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['id_kategori']}</td><td>{$row['nama_kategori']}</td></tr>";
        }
        echo '</table>';
        echo '</div>';
        
        // Perform update
        $sql = "UPDATE kategori SET nama_kategori = LOWER(nama_kategori)";
        if ($conn->query($sql)) {
            echo '<div class="success-message">';
            echo '<h3>✅ Update Successful!</h3>';
            echo '<p>Kategori table has been updated to lowercase.</p>';
            echo '</div>';
            
            // Show data after update
            echo '<div class="info-box">';
            echo '<h3>📊 After Update</h3>';
            echo '<table class="data-table">';
            echo '<tr><th>ID</th><th>Category Name</th></tr>';
            
            $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori");
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>{$row['id_kategori']}</td><td><strong>{$row['nama_kategori']}</strong></td></tr>";
            }
            echo '</table>';
            echo '</div>';
            
            echo '<div class="success-message">';
            echo '<h3>🎉 All Done!</h3>';
            echo '<p><strong>Next steps:</strong></p>';
            echo '<ol style="margin-left: 20px; margin-top: 10px;">';
            echo '<li>Close this window</li>';
            echo '<li>Refresh your app in the browser (Ctrl+F5)</li>';
            echo '<li>Test category filtering (Makanan, Minuman, Snack)</li>';
            echo '</ol>';
            echo '</div>';
            
        } else {
            throw new Exception($conn->error);
        }
        
    } catch (Exception $e) {
        echo '<div class="error-message">';
        echo '<h3>❌ Error</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    
} else {
    // Show confirmation page
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        echo '<div class="info-box">';
        echo '<h3>📋 Current Data</h3>';
        echo '<p>This script will update the following kategori to lowercase:</p>';
        echo '<table class="data-table">';
        echo '<tr><th>ID</th><th>Current Name</th><th>Will Become</th></tr>';
        
        $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori");
        while ($row = $result->fetch_assoc()) {
            $current = $row['nama_kategori'];
            $new = strtolower($current);
            $arrow = $current !== $new ? '→' : '✓';
            echo "<tr><td>{$row['id_kategori']}</td><td>{$current}</td><td><strong>{$new}</strong> {$arrow}</td></tr>";
        }
        echo '</table>';
        echo '</div>';
        
        echo '<div class="info-box">';
        echo '<h3>⚠️ Important</h3>';
        echo '<p>This will update your database. Make sure you have a backup if needed.</p>';
        echo '</div>';
        
        echo '<a href="?confirm=yes" class="btn btn-primary">✓ Confirm Update</a>';
        
    } catch (Exception $e) {
        echo '<div class="error-message">';
        echo '<h3>❌ Database Connection Error</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
}
?>

        <a href="?" class="back-link">← Reload Page</a>
    </div>
</body>
</html>
