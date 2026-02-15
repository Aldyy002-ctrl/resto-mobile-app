-- Verification query
-- Run this in phpMyAdmin to check if kategori is actually lowercase now

SELECT 
    id_kategori,
    nama_kategori,
    LENGTH(nama_kategori) as length,
    LOWER(nama_kategori) as lowercase_version,
    CASE 
        WHEN nama_kategori = LOWER(nama_kategori) THEN '✓ Lowercase'
        ELSE '✗ Not lowercase'
    END as status
FROM kategori
ORDER BY id_kategori;

-- Also check what categories are in menu items
SELECT DISTINCT 
    k.nama_kategori as kategori_table,
    COUNT(*) as menu_count
FROM menu m
LEFT JOIN kategori k ON m.id_kategori = k.id_kategori
GROUP BY k.nama_kategori;
