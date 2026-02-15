-- Script untuk MENAMBAH tabel baru ke db_restoran yang sudah ada
-- Jangan DROP database atau tabel yang sudah ada!

-- Cek dan buat tabel meja jika belum ada
CREATE TABLE IF NOT EXISTS meja (
  id_meja INT PRIMARY KEY AUTO_INCREMENT,
  nomor_meja VARCHAR(10) NOT NULL UNIQUE,
  kapasitas INT NOT NULL DEFAULT 4,
  status ENUM('kosong', 'terisi') DEFAULT 'kosong',
  id_pelanggan INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cek dan buat tabel menu jika belum ada
CREATE TABLE IF NOT EXISTS menu (
  id_menu INT PRIMARY KEY AUTO_INCREMENT,
  nama_menu VARCHAR(100) NOT NULL,
  kategori VARCHAR(50) NOT NULL,
  harga DECIMAL(10,2) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  gambar VARCHAR(255) DEFAULT 'default-menu.jpg',
  deskripsi TEXT,
  tersedia BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cek dan buat tabel transaksi jika belum ada
CREATE TABLE IF NOT EXISTS transaksi (
  id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
  id_meja INT NOT NULL,
  id_pelanggan INT NOT NULL,
  id_kasir INT NULL,
  total_harga DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending', 'cooking', 'ready', 'paid', 'cancelled') DEFAULT 'pending',
  metode_pembayaran ENUM('cash', 'qris') NULL,
  catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  FOREIGN KEY (id_meja) REFERENCES meja(id_meja),
  FOREIGN KEY (id_pelanggan) REFERENCES user(id_user),
  FOREIGN KEY (id_kasir) REFERENCES user(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cek dan buat tabel detail_transaksi jika belum ada
CREATE TABLE IF NOT EXISTS detail_transaksi (
  id_detail INT PRIMARY KEY AUTO_INCREMENT,
  id_transaksi INT NOT NULL,
  id_menu INT NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  catatan TEXT,
  harga_satuan DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
  FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data untuk Meja (hanya insert jika belum ada)
INSERT IGNORE INTO meja (nomor_meja, kapasitas, status) VALUES
('A1', 2, 'kosong'),
('A2', 4, 'kosong'),
('A3', 4, 'kosong'),
('B1', 6, 'kosong'),
('B2', 6, 'kosong'),
('C1', 8, 'kosong'),
('C2', 2, 'kosong'),
('C3', 4, 'kosong');

-- Sample Data untuk Menu (hanya insert jika belum ada)
INSERT IGNORE INTO menu (id_menu, nama_menu, kategori, harga, stok, gambar) VALUES
(1, 'Nasi Goreng Spesial', 'Makanan', 25000, 50, 'nasi-goreng.jpg'),
(2, 'Mie Goreng', 'Makanan', 20000, 50, 'mie-goreng.jpg'),
(3, 'Ayam Bakar', 'Makanan', 35000, 30, 'ayam-bakar.jpg'),
(4, 'Sate Ayam', 'Makanan', 30000, 40, 'sate-ayam.jpg'),
(5, 'Gado-Gado', 'Makanan', 18000, 40, 'gado-gado.jpg'),
(6, 'Es Teh Manis', 'Minuman', 5000, 100, 'es-teh.jpg'),
(7, 'Es Jeruk', 'Minuman', 7000, 100, 'es-jeruk.jpg'),
(8, 'Jus Alpukat', 'Minuman', 12000, 50, 'jus-alpukat.jpg'),
(9, 'Kopi Hitam', 'Minuman', 8000, 80, 'kopi.jpg'),
(10, 'Teh Tarik', 'Minuman', 10000, 80, 'teh-tarik.jpg');
