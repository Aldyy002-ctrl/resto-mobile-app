-- Sample Menu Data for Testing Categories
-- Run this to add menu items with proper categories

INSERT INTO menu (nama_menu, kategori, harga, deskripsi, stok, foto_makanan) VALUES
-- Makanan
('Nasi Goreng Spesial', 'makanan', 25000, 'Nasi goreng dengan telur dan ayam', 50, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400'),
('Mie Goreng', 'makanan', 20000, 'Mie goreng dengan sayuran segar', 45, 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=400'),
('Ayam Bakar', 'makanan', 30000, 'Ayam bakar dengan sambal khas', 30, 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400'),
('Sate Ayam', 'makanan', 28000, 'Sate ayam 10 tusuk dengan bumbu kacang', 35, 'https://images.unsplash.com/photo-1529563021893-cc83c992d75d?w=400'),

-- Minuman  
('Es Teh Manis', 'minuman', 5000, 'Teh manis dingin segar', 100, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400'),
('Es Jeruk', 'minuman', 8000, 'Jus jeruk segar dengan es', 80, 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400'),
('Kopi Hitam', 'minuman', 10000, 'Kopi hitam premium', 60, 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400'),
('Cappuccino', 'minuman', 15000, 'Cappuccino dengan latte art', 50, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=400'),
('Jus Alpukat', 'minuman', 12000, 'Jus alpukat segar', 40, 'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?w=400'),

-- Snack
('French Fries', 'snack', 15000, 'Kentang goreng crispy', 70, 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400'),
('Chicken Wings', 'snack', 25000, 'Sayap ayam goreng pedas', 40, 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?w=400'),
('Onion Rings', 'snack', 18000, 'Bawang bombay goreng crispy', 55, 'https://images.unsplash.com/photo-1639024471283-03518883512d?w=400'),
('Spring Rolls', 'snack', 20000, 'Lumpia goreng isi sayur', 45, 'https://images.unsplash.com/photo-1625398407796-82650a8c135b?w=400'),

-- Dessert
('Es Krim Vanilla', 'dessert', 12000, 'Es krim vanilla premium', 60, 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400'),
('Pancake', 'dessert', 20000, 'Pancake dengan sirup maple dan buah', 35, 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400'),
('Brownies Coklat', 'dessert', 15000, 'Brownies coklat lembut', 50, 'https://images.unsplash.com/photo-1564355808539-22fda35bed7e?w=400'),
('Pudding Mangga', 'dessert', 10000, 'Pudding mangga seger', 70, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400');
