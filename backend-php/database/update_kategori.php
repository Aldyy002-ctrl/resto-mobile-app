<?php
/**
 * Database Update Script
 * Updates kategori table to use lowercase category names
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "Updating kategori table to lowercase...\n";
echo "========================================\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Step 1: Show current data
    echo "BEFORE UPDATE:\n";
    $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['id_kategori']} | {$row['nama_kategori']}\n";
    }
    echo "\n";
    
    // Step 2: Update to lowercase
    echo "Running UPDATE query...\n";
    $sql = "UPDATE kategori SET nama_kategori = LOWER(nama_kategori)";
    if ($conn->query($sql)) {
        echo "✓ Update successful!\n\n";
    } else {
        throw new Exception("Update failed: " . $conn->error);
    }
    
    // Step 3: Show updated data
    echo "AFTER UPDATE:\n";
    $result = $conn->query("SELECT * FROM kategori ORDER BY id_kategori");
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['id_kategori']} | {$row['nama_kategori']}\n";
    }
    echo "\n";
    
    echo "========================================\n";
    echo "✓ Database update complete!\n";
    echo "  Now refresh your browser to test.\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
