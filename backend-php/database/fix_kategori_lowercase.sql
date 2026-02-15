-- ========================================
-- FIX KATEGORI FILTERING - QUICK SOLUTION
-- ========================================
-- Run this in phpMyAdmin SQL tab for db_restoran

-- STEP 1: Update kategori table to lowercase
UPDATE kategori SET nama_kategori = LOWER(nama_kategori);

-- STEP 2: Verify kategori table
SELECT * FROM kategori;
-- Expected result:
-- 1 | makanan
-- 2 | minuman  
-- 3 | snack

-- ========================================
-- DONE! Now refresh browser and test category filtering
-- ========================================
