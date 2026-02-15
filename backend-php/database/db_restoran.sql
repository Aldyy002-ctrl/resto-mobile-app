-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 15, 2026 at 04:39 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_restoran`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `id_transaksi` int DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  `harga` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `catatan` text,
  `subtotal` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_menu`, `harga`, `jumlah`, `catatan`, `subtotal`) VALUES
(3, 2, 3, 20000, 1, NULL, 20000),
(4, 2, 6, 15000, 1, NULL, 15000),
(5, 4, 2, NULL, 1, NULL, 30000),
(6, 5, 1, NULL, 1, NULL, 25000),
(7, 5, 4, NULL, 1, NULL, 5000),
(8, 6, 1, NULL, 1, NULL, 25000),
(9, 6, 4, NULL, 1, NULL, 5000),
(10, 6, 7, NULL, 1, NULL, 12000),
(11, 7, 3, NULL, 2, NULL, 40000),
(12, 7, 4, NULL, 1, NULL, 5000),
(13, 8, 4, NULL, 1, NULL, 5000),
(14, 9, 3, NULL, 1, NULL, 20000),
(15, 10, 4, NULL, 1, NULL, 5000),
(16, 11, 3, NULL, 1, NULL, 20000),
(17, 12, 2, 30000, 1, NULL, 30000),
(18, 12, 4, 5000, 1, NULL, 5000),
(19, 13, 4, NULL, 1, NULL, 5000),
(20, 14, 2, 30000, 1, NULL, 30000),
(21, 15, 2, 30000, 1, NULL, 30000),
(22, 16, 2, 30000, 1, NULL, 30000),
(23, 17, 4, NULL, 1, NULL, 5000),
(24, 34, 1, 25000, 2, '0', 50000),
(25, 35, 2, 30000, 1, '0', 30000),
(26, 35, 6, 15000, 1, '0', 15000),
(27, 36, 3, 20000, 1, '0', 20000),
(28, 36, 3, 20000, 1, NULL, 20000),
(29, 37, 6, 15000, 1, '0', 15000),
(30, 38, 2, 30000, 1, '0', 30000),
(31, 39, 4, 5000, 1, '0', 5000),
(32, 39, 2, 30000, 1, NULL, 30000),
(33, 40, 2, 30000, 1, NULL, 30000),
(34, 40, 6, 15000, 1, NULL, 15000);

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` int NOT NULL,
  `nama_karyawan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text,
  `jabatan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama_karyawan`, `no_hp`, `alamat`, `jabatan`) VALUES
(1, 'Andi Saputra', '081234567890', 'Samarinda', 'Kasir'),
(2, 'Budi Pratama', '082345678901', 'Samarinda', 'Manager'),
(3, 'Citra Lestari', '083456789012', 'Samarinda', 'Owner');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'makanan'),
(2, 'minuman'),
(3, 'snack');

-- --------------------------------------------------------

--
-- Table structure for table `meja`
--

CREATE TABLE `meja` (
  `id_meja` int NOT NULL,
  `nomor_meja` varchar(10) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `kapasitas` int DEFAULT '4'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meja`
--

INSERT INTO `meja` (`id_meja`, `nomor_meja`, `status`, `kapasitas`) VALUES
(1, 'Meja 1', 'terisi', 4),
(2, 'Meja 2', 'kosong', 4),
(3, 'Meja 3', 'kosong', 4),
(4, 'Meja 4', 'kosong', 4),
(5, 'Meja 5', 'kosong', 4),
(6, 'Meja 6', 'kosong', 4),
(7, 'Meja 7', 'kosong', 4),
(8, 'Meja 8', 'kosong', 4),
(9, 'Meja 9', 'kosong', 4),
(10, 'Meja 10', 'kosong', 4);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` int NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `foto_makanan` varchar(255) DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  `status` enum('tersedia','habis') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `harga`, `stok`, `foto_makanan`, `id_kategori`, `status`) VALUES
(1, 'Nasi Goreng Spesial', 25000, 96, '/api/uploads/698fc6ff9ac43_1771030271.jpg', 1, 'tersedia'),
(2, 'Ayam Bakar Madu', 30000, 40, 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=500', 1, 'tersedia'),
(3, 'Sate Ayam', 20000, 73, '/api/uploads/698fc779efa06_1771030393.jpg', 1, 'tersedia'),
(4, 'Es Teh Manis', 5000, 44, '/api/uploads/698fc7a41bc60_1771030436.jpg', 2, 'tersedia'),
(5, 'Es Jeruk Segar', 7000, 50, '/api/uploads/698fc79a5d87a_1771030426.jpg', 2, 'tersedia'),
(6, 'Kopi Susu', 15000, 95, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=500', 2, 'tersedia'),
(7, 'Kentang Goreng', 12000, 49, '/api/uploads/698fc7cee2de3_1771030478.jpg', 3, 'tersedia'),
(8, 'Roti Bakar Coklat', 15000, 40, 'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=500', 3, 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `tipe_pesanan` enum('dine-in','takeaway') DEFAULT 'dine-in',
  `id_table` int DEFAULT NULL,
  `total_harga` int NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('cash','qris') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `tipe_pesanan`, `id_table`, `total_harga`, `status`, `payment_method`, `created_at`) VALUES
(1, 8, 'dine-in', 1, 42000, 'completed', 'cash', '2026-02-13 12:55:29'),
(2, 10, 'takeaway', NULL, 25000, 'completed', 'cash', '2026-02-13 12:55:58'),
(3, 8, 'dine-in', 1, 22000, 'completed', 'qris', '2026-02-14 00:08:03'),
(4, 10, 'takeaway', NULL, 42000, 'completed', 'qris', '2026-02-14 00:08:57'),
(5, 8, 'dine-in', 1, 12000, 'completed', 'cash', '2026-02-14 02:33:12'),
(6, 10, 'takeaway', NULL, 30000, 'completed', 'cash', '2026-02-14 02:34:21');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id_order_item` int NOT NULL,
  `id_order` int NOT NULL,
  `id_menu` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_satuan` int NOT NULL,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id_order_item`, `id_order`, `id_menu`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 1, 25000, 25000),
(2, 1, 4, 1, 5000, 5000),
(3, 1, 7, 1, 12000, 12000),
(4, 2, 3, 1, 20000, 20000),
(5, 2, 4, 1, 5000, 5000),
(6, 3, 5, 1, 7000, 7000),
(7, 3, 8, 1, 15000, 15000),
(8, 4, 2, 1, 30000, 30000),
(9, 4, 7, 1, 12000, 12000),
(10, 5, 7, 1, 12000, 12000),
(11, 6, 2, 1, 30000, 30000);

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id_table` int NOT NULL,
  `nomor_meja` int NOT NULL,
  `kapasitas` int DEFAULT '4',
  `status` enum('tersedia','terisi') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id_table`, `nomor_meja`, `kapasitas`, `status`) VALUES
(1, 1, 2, 'tersedia'),
(2, 2, 4, 'tersedia'),
(3, 3, 2, 'tersedia'),
(4, 4, 4, 'tersedia'),
(5, 5, 2, 'tersedia'),
(6, 6, 4, 'tersedia'),
(7, 7, 2, 'tersedia'),
(8, 8, 4, 'tersedia'),
(9, 9, 2, 'tersedia'),
(10, 10, 4, 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `tanggal_transaksi` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_user` int DEFAULT NULL,
  `id_meja` int DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `total_harga` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_transaksi`, `id_user`, `id_meja`, `nama_pelanggan`, `status`, `metode_pembayaran`, `total_harga`) VALUES
(2, '2026-01-26 09:01:19', 1, 2, 'Budi Kasir', 'lunas', 'qris', 38500),
(4, '2026-01-26 03:41:59', 8, 4, 'aldi', 'ready', NULL, 30000),
(5, '2026-01-26 04:13:56', 8, 1, 'aldi', 'ready', NULL, 30000),
(6, '2026-01-26 23:26:47', 8, 1, 'aldi', 'ready', NULL, 42000),
(7, '2026-01-29 00:00:00', 6, NULL, 'kasir1', 'ready', NULL, 45000),
(8, '2026-02-02 23:45:18', 8, NULL, 'aldi', 'ready', NULL, 5000),
(9, '2026-02-02 23:46:02', 8, 1, 'aldi', 'ready', NULL, 20000),
(10, '2026-02-03 01:20:35', 8, NULL, 'aldi', 'ready', NULL, 5000),
(11, '2026-02-03 01:20:52', 8, 1, 'aldi', 'ready', NULL, 20000),
(12, '2026-02-03 09:30:38', 8, 1, 'aldi', 'lunas', 'Cash/Tunai', 35000),
(13, '2026-02-03 01:32:58', 8, 2, 'aldi', 'ready', NULL, 5000),
(14, '2026-02-04 21:37:43', 8, 1, 'aldi', 'lunas', 'Cash/Tunai', 30000),
(15, '2026-02-04 21:37:57', 8, 2, 'aldi', 'lunas', 'QRIS', 30000),
(16, '2026-02-05 11:00:16', 8, 1, 'aldi', 'lunas', 'Cash/Tunai', 30000),
(17, '2026-02-13 03:50:01', 8, NULL, 'aldi', 'ready', NULL, 5000),
(34, '2026-02-13 21:49:40', 1, 1, 'Success Test', 'paid', 'Cash/Tunai', 50000),
(35, '2026-02-13 21:50:42', 8, 1, 'aldi', 'paid', 'Cash/Tunai', 45000),
(36, '2026-02-13 21:57:42', 8, 1, 'aldi', 'ready', NULL, 40000),
(37, '2026-02-13 22:01:41', 8, 1, 'aldi', 'ready', NULL, 15000),
(38, '2026-02-13 22:13:40', 8, 1, 'aldi', 'ready', NULL, 30000),
(39, '2026-02-14 08:59:32', 8, 1, 'aldi', 'ready', NULL, 35000),
(40, '2026-02-14 09:14:18', 8, 1, 'aldi', 'cooking', NULL, 45000);

-- --------------------------------------------------------

--
-- Table structure for table `update_stokharian`
--

CREATE TABLE `update_stokharian` (
  `id_update` int NOT NULL,
  `id_menu` int DEFAULT NULL,
  `jumlah_porsi` int DEFAULT NULL,
  `tanggal_update` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'owner', 'owner123', 'owner'),
(2, 'admin', 'admin123', 'admin'),
(3, 'kasir', 'kasir123', 'kasir'),
(4, 'koki', 'koki123', 'koki'),
(6, 'kasir1', '123456', 'kasir'),
(7, 'manajer1', '123456', 'admin'),
(8, 'aldi', '123456', 'pelanggan'),
(9, 'kitchen', '123456', 'koki'),
(10, 'farhan', '123', 'pelanggan'),
(11, 'abi', '123', 'pelanggan'),
(12, 'zidan', '123', 'pelanggan');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_user` varchar(100) NOT NULL,
  `role` enum('admin','kasir','manajer','kitchen') NOT NULL DEFAULT 'kasir',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_user`, `role`, `status`) VALUES
(1, 'kasir1', '123456', 'Budi Kasir', 'kasir', 'aktif'),
(2, 'manajer1', '123456', 'Siti Manajer', 'manajer', 'aktif'),
(4, 'kitchen1', '123456', 'Dapur 1', 'kitchen', 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_table` (`id_table`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id_order_item`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id_table`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_meja` (`id_meja`),
  ADD KEY `fk_transaksi_user` (`id_user`);

--
-- Indexes for table `update_stokharian`
--
ALTER TABLE `update_stokharian`
  ADD PRIMARY KEY (`id_update`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id_karyawan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id_order_item` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id_table` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `update_stokharian`
--
ALTER TABLE `update_stokharian`
  MODIFY `id_update` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_table`) REFERENCES `tables` (`id_table`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_meja`) REFERENCES `meja` (`id_meja`);

--
-- Constraints for table `update_stokharian`
--
ALTER TABLE `update_stokharian`
  ADD CONSTRAINT `update_stokharian_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
