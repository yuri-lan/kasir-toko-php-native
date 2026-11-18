-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 07:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_toko`
--
CREATE DATABASE IF NOT EXISTS `db_toko` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_toko`;

-- --------------------------------------------------------

--
-- Table structure for table `log_pelanggan`
--

DROP TABLE IF EXISTS `log_pelanggan`;
CREATE TABLE `log_pelanggan` (
  `id_log` int(11) NOT NULL,
  `aktivitas` varchar(50) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_pelanggan`
--

INSERT INTO `log_pelanggan` (`id_log`, `aktivitas`, `id_pelanggan`, `waktu`) VALUES
(1, 'INSERT PELANGGAN', 13, '2026-11-16 01:48:17');

-- --------------------------------------------------------

--
-- Table structure for table `log_perubahan_pelanggan`
--

DROP TABLE IF EXISTS `log_perubahan_pelanggan`;
CREATE TABLE `log_perubahan_pelanggan` (
  `id_log` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `nama_lama` varchar(100) DEFAULT NULL,
  `nama_baru` varchar(100) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_perubahan_pelanggan`
--

INSERT INTO `log_perubahan_pelanggan` (`id_log`, `id_pelanggan`, `nama_lama`, `nama_baru`, `waktu`) VALUES
(1, 13, 'Rina', 'Rina Wijaya', '2026-11-16 01:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cabang`
--

DROP TABLE IF EXISTS `tbl_cabang`;
CREATE TABLE `tbl_cabang` (
  `id_cabang` int(11) NOT NULL,
  `kota` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cabang`
--

INSERT INTO `tbl_cabang` (`id_cabang`, `kota`) VALUES
(1, 'Ponorogo'),
(2, 'Madiun'),
(3, 'Surabaya'),
(4, 'Malang'),
(5, 'Jakarta');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_detail_transaksi`
--

DROP TABLE IF EXISTS `tbl_detail_transaksi`;
CREATE TABLE `tbl_detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_detail_transaksi`
--

INSERT INTO `tbl_detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `jumlah`, `subtotal`) VALUES
(1, 22, 1, 2, 50000.00),
(2, 23, 3, 4, 88000.00),
(3, 23, 8, 4, 60000.00),
(4, 24, 2, 1, 20000.00),
(5, 25, 8, 2, 30000.00),
(6, 26, 3, 1, 22000.00),
(7, 26, 8, 1, 15000.00),
(8, 27, 2, 1, 20000.00),
(9, 28, 1, 1, 25000.00),
(10, 29, 7, 10, 120000.00),
(11, 30, 1, 1, 25000.00),
(12, 31, 6, 1, 8000.00),
(13, 32, 6, 1, 8000.00),
(14, 33, 6, 1, 8000.00),
(15, 34, 6, 1, 8000.00),
(16, 35, 6, 1, 8000.00),
(17, 35, 5, 1, 7000.00),
(18, 36, 4, 1, 5000.00),
(19, 37, 8, 1, 15000.00),
(20, 37, 5, 2, 14000.00),
(21, 38, 7, 1, 12000.00),
(22, 38, 6, 2, 16000.00),
(23, 39, 6, 1, 8000.00),
(24, 39, 7, 1, 12000.00),
(25, 40, 7, 1, 12000.00),
(26, 40, 6, 1, 8000.00),
(27, 41, 7, 1, 12000.00),
(28, 41, 6, 1, 8000.00),
(29, 42, 6, 1, 8000.00),
(30, 42, 7, 1, 12000.00),
(31, 43, 2, 1, 20000.00),
(32, 44, 6, 1, 8000.00),
(33, 45, 5, 1, 7000.00),
(34, 46, 2, 1, 20000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kategori`
--

DROP TABLE IF EXISTS `tbl_kategori`;
CREATE TABLE `tbl_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'Snack');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelanggan`
--

DROP TABLE IF EXISTS `tbl_pelanggan`;
CREATE TABLE `tbl_pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pelanggan`
--

INSERT INTO `tbl_pelanggan` (`id_pelanggan`, `nama_pelanggan`, `kota`) VALUES
(1, 'Andi', 'Ponorogo'),
(2, 'Budi', 'Madiun'),
(3, 'Citra', 'Surabaya'),
(4, 'Dewi', 'Ponorogo'),
(5, 'Eko', 'Malang'),
(6, 'Fajar', 'Madiun'),
(7, 'Gita', 'Surabaya'),
(8, 'Hana', 'Ponorogo'),
(9, 'Indra', 'Malang'),
(10, 'Joko', 'Madiun'),
(11, 'Kiki', 'Ponorogo'),
(12, 'Kiki', 'Ponorogo'),
(13, 'Rina Wijaya', 'Ponorogo');

--
-- Triggers `tbl_pelanggan`
--
DROP TRIGGER IF EXISTS `after_insert_pelanggan`;
DELIMITER $$
CREATE TRIGGER `after_insert_pelanggan` AFTER INSERT ON `tbl_pelanggan` FOR EACH ROW BEGIN
    INSERT INTO log_pelanggan (aktivitas, id_pelanggan)
    VALUES ('INSERT PELANGGAN', NEW.id_pelanggan);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `after_update_pelanggan`;
DELIMITER $$
CREATE TRIGGER `after_update_pelanggan` AFTER UPDATE ON `tbl_pelanggan` FOR EACH ROW BEGIN
    INSERT INTO log_perubahan_pelanggan (id_pelanggan, nama_lama, nama_baru)
    VALUES (OLD.id_pelanggan, OLD.nama_pelanggan, NEW.nama_pelanggan);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelunasan`
--

DROP TABLE IF EXISTS `tbl_pelunasan`;
CREATE TABLE `tbl_pelunasan` (
  `id_pelunasan` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pelunasan`
--

INSERT INTO `tbl_pelunasan` (`id_pelunasan`, `id_transaksi`, `nominal`, `waktu`) VALUES
(1, 41, 10000.00, '2026-11-18 03:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_produk`
--

DROP TABLE IF EXISTS `tbl_produk`;
CREATE TABLE `tbl_produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_produk`
--

INSERT INTO `tbl_produk` (`id_produk`, `id_kategori`, `nama_produk`, `harga`, `stok`) VALUES
(1, 1, 'Nasi Goreng', 25000.00, 46),
(2, 1, 'Mie Ayam', 20000.00, 46),
(3, 1, 'Ayam Geprek', 22000.00, 45),
(4, 2, 'Es Teh Manis', 5000.00, 99),
(5, 2, 'Es Jeruk', 7000.00, 96),
(6, 2, 'Kopi Hitam', 8000.00, 88),
(7, 3, 'Keripik Kentang', 12000.00, 15),
(8, 3, 'Coklat Batang', 15000.00, 22),
(9, 2, 'Susu beruang', 100000.00, 100),
(10, 1, 'Roti', 5000.00, 50);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_transaksi`
--

DROP TABLE IF EXISTS `tbl_transaksi`;
CREATE TABLE `tbl_transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal` datetime DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `total_belanja` decimal(12,2) DEFAULT NULL,
  `uang_bayar` decimal(12,2) DEFAULT 0.00,
  `kembalian` decimal(12,2) DEFAULT 0.00,
  `metode_pembayaran` varchar(20) DEFAULT 'Tunai',
  `status_bayar` varchar(30) DEFAULT 'Lunas',
  `status_pengiriman` varchar(30) DEFAULT 'Selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_transaksi`
--

INSERT INTO `tbl_transaksi` (`id_transaksi`, `tanggal`, `id_pelanggan`, `total_belanja`, `uang_bayar`, `kembalian`, `metode_pembayaran`, `status_bayar`, `status_pengiriman`) VALUES
(1, '2026-10-01 00:00:00', 1, 250000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(2, '2026-10-02 00:00:00', 2, 350000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(3, '2026-10-03 00:00:00', 1, 150000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(4, '2026-10-04 00:00:00', 3, 500000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(5, '2026-10-05 00:00:00', 4, 275000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(6, '2026-10-06 00:00:00', 2, 425000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(7, '2026-10-07 00:00:00', 5, 180000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(8, '2026-10-08 00:00:00', 6, 620000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(9, '2026-10-09 00:00:00', 7, 310000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(10, '2026-10-10 00:00:00', 8, 145000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(11, '2026-10-11 00:00:00', 9, 480000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(12, '2026-10-12 00:00:00', 10, 390000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(13, '2026-10-13 00:00:00', 3, 220000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(14, '2026-10-14 00:00:00', 1, 175000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(15, '2026-10-15 00:00:00', 5, 260000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(16, '2026-10-16 00:00:00', 7, 540000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(17, '2026-10-17 00:00:00', 2, 310000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(18, '2026-10-18 00:00:00', 9, 125000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(19, '2026-10-19 00:00:00', 4, 450000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(20, '2026-10-20 00:00:00', 6, 280000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(21, '2026-11-02 00:00:00', 1, 200000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(22, '2026-11-16 00:00:00', 1, 50000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(23, '2026-11-16 00:00:00', 1, 148000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(24, '2026-11-16 00:00:00', 6, 20000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(25, '2026-11-16 00:00:00', 13, 30000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(26, '2026-11-16 00:00:00', 1, 37000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(27, '2026-11-16 00:00:00', 4, 20000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(28, '2026-11-16 00:00:00', 6, 25000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(29, '2026-11-16 00:00:00', 11, 120000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(30, '2026-11-16 00:00:00', 11, 25000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(31, '2026-11-16 00:00:00', 4, 8000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(32, '2026-11-16 00:00:00', 3, 8000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(33, '2026-11-16 00:00:00', 4, 8000.00, 0.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(34, '2026-11-16 00:00:00', 2, 8000.00, 10000.00, 2000.00, 'Tunai', 'Lunas', 'Selesai'),
(35, '2026-11-16 00:00:00', 2, 15000.00, 50000.00, 35000.00, 'Tunai', 'Lunas', 'Selesai'),
(36, '2026-11-16 00:00:00', 11, 5000.00, 20000.00, 15000.00, 'Tunai', 'Lunas', 'Selesai'),
(37, '2026-11-16 00:00:00', 10, 29000.00, 50000.00, 21000.00, 'Tunai', 'Lunas', 'Selesai'),
(38, '2026-11-16 00:00:00', 13, 28000.00, 50000.00, 22000.00, 'Tunai', 'Lunas', 'Selesai'),
(39, '2026-11-16 00:00:00', 13, 20000.00, 15000.00, 5000.00, 'Utang', 'Lunas', 'Selesai'),
(40, '2026-11-16 00:00:00', 11, 20000.00, 20000.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(41, '2026-11-18 00:00:00', 3, 20000.00, 10000.00, 0.00, 'Utang (Lunas)', 'Lunas', 'Selesai'),
(42, '2026-11-18 00:00:00', 4, 20000.00, 20000.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(43, '2026-11-18 13:00:36', 1, 20000.00, 20000.00, 0.00, 'Transfer', 'Lunas', 'Selesai'),
(44, '2026-11-18 13:30:09', 11, 8000.00, 8000.00, 0.00, 'Tunai', 'Lunas', 'Selesai'),
(45, '2026-11-18 13:30:46', 7, 7000.00, 10000.00, 3000.00, 'Tunai', 'Lunas', 'Selesai'),
(46, '2026-11-18 13:31:13', 1, 20000.00, 20000.00, 0.00, 'Transfer', 'Lunas', 'Proses Packing');

--
-- Triggers `tbl_transaksi`
--
DROP TRIGGER IF EXISTS `before_insert_transaksi`;
DELIMITER $$
CREATE TRIGGER `before_insert_transaksi` BEFORE INSERT ON `tbl_transaksi` FOR EACH ROW BEGIN
    IF NEW.total_belanja < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Total belanja tidak boleh negatif';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

DROP TABLE IF EXISTS `tbl_user`;
CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'kasir',
  `id_pelanggan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `username`, `password`, `nama_lengkap`, `role`, `id_pelanggan`) VALUES
(1, 'admin', '$2y$12$.oaske0r4rym7T8j3KCpF.asfM4gzuuJ1ec.6sIYrMlAS5O0q62le', 'Admin Toko', 'admin', NULL),
(2, 'admin1', '$2y$10$Fv5EPlRHumw.CXfPIE71P.u8RWFOyfpE7Gx0Z.4ga.DyDe2KmX1yS', 'adminmin', 'kasir', NULL),
(3, 'andi1', '$2y$10$OxcxxqK12qC43XCnXfLgQu1pHQO.7pDDsZEPTIvou3oCfuMPVQ5/u', 'Andi', 'pelanggan', 1),
(4, 'budi2', '$2y$10$1lXk5lxeMaLE7BUx7TRKo.67ADqWfSQGKSQCEnOZ8eRbhsbeTgbMu', 'Budi', 'pelanggan', 2),
(5, 'citra3', '$2y$10$Z85NmIEhfZ8SKi/WDmVrN.m.Hk8sWlcwN48dTGp0uEfdBgBIbQqqy', 'Citra', 'pelanggan', 3),
(6, 'dewi4', '$2y$10$YyQRq3AG/hAn8SZW1HAhCeFw5y8RHFYynELHsP9jMYypwSEzAZw8O', 'Dewi', 'pelanggan', 4),
(7, 'eko5', '$2y$10$ZHB5mvZTnDxUyReDCpyTluG0rpveOlYWxw5rDExaA2PJ2STz9hulK', 'Eko', 'pelanggan', 5),
(8, 'fajar6', '$2y$10$xzGssCKed1W47bN0ihhY4Okz1lzbhbdhRx5pmU7GxPlzTuIF0l3Ky', 'Fajar', 'pelanggan', 6),
(9, 'gita7', '$2y$10$jGnbH65pFiwVgMCAl1DDA.FYSWs.Rk06/fhPh4wsa6rzTofBaoWHK', 'Gita', 'pelanggan', 7),
(10, 'hana8', '$2y$10$uYm6.IJPdOjfFG20PRiK2u8u5SHd9htO3TC7.7qO2P.RtKO45cQKG', 'Hana', 'pelanggan', 8),
(11, 'indra9', '$2y$10$ijVuiTvgX0Fkyiv2FTY4XeHeN8mRb.hOBsAqJcjO9OFRCx3i10ZQu', 'Indra', 'pelanggan', 9),
(12, 'joko10', '$2y$10$dMl/6DaGZOIM8EfxxEd.ZOZ5QNrVyrdJphVeXm5yPXgIvQ7xo5qkO', 'Joko', 'pelanggan', 10),
(13, 'kiki11', '$2y$10$Z6P4suiMYoqua27SzxPm7.JWreekHN3olQ4oA6WwPT5RciN.G0Ize', 'Kiki', 'pelanggan', 11),
(14, 'kiki12', '$2y$10$9WvXwxDAF4ioIzRhNij8/OFaeM1ZpvmGJJH509biRHAMTKn/MH6hC', 'Kiki', 'pelanggan', 12),
(15, 'rinawijaya13', '$2y$10$WFs69D1dqrIBrZRSvvmlp.Eye6Vpfy5KcI3GNpIQFENo.2Bh9HZ9m', 'Rina Wijaya', 'pelanggan', 13);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_detail_penjualan`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_detail_penjualan`;
CREATE TABLE `view_detail_penjualan` (
`id_transaksi` int(11)
,`tanggal` datetime
,`nama_pelanggan` varchar(100)
,`nama_produk` varchar(100)
,`jumlah` int(11)
,`harga` decimal(12,2)
,`subtotal` decimal(12,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_transaksi`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_laporan_transaksi`;
CREATE TABLE `view_laporan_transaksi` (
`id_transaksi` int(11)
,`tanggal` datetime
,`nama_pelanggan` varchar(100)
,`kota` varchar(50)
,`total_belanja` decimal(12,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_total_belanja`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_total_belanja`;
CREATE TABLE `view_total_belanja` (
`id_pelanggan` int(11)
,`nama_pelanggan` varchar(100)
,`total_transaksi` bigint(21)
,`total_belanja` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Structure for view `view_detail_penjualan`
--
DROP TABLE IF EXISTS `view_detail_penjualan`;

DROP VIEW IF EXISTS `view_detail_penjualan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_detail_penjualan`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`tanggal` AS `tanggal`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `pr`.`nama_produk` AS `nama_produk`, `d`.`jumlah` AS `jumlah`, `pr`.`harga` AS `harga`, `d`.`subtotal` AS `subtotal` FROM (((`tbl_transaksi` `t` join `tbl_pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) join `tbl_detail_transaksi` `d` on(`t`.`id_transaksi` = `d`.`id_transaksi`)) join `tbl_produk` `pr` on(`d`.`id_produk` = `pr`.`id_produk`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_transaksi`
--
DROP TABLE IF EXISTS `view_laporan_transaksi`;

DROP VIEW IF EXISTS `view_laporan_transaksi`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_transaksi`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`tanggal` AS `tanggal`, `p`.`nama_pelanggan` AS `nama_pelanggan`, `p`.`kota` AS `kota`, `t`.`total_belanja` AS `total_belanja` FROM (`tbl_transaksi` `t` join `tbl_pelanggan` `p` on(`t`.`id_pelanggan` = `p`.`id_pelanggan`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_total_belanja`
--
DROP TABLE IF EXISTS `view_total_belanja`;

DROP VIEW IF EXISTS `view_total_belanja`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_total_belanja`  AS SELECT `p`.`id_pelanggan` AS `id_pelanggan`, `p`.`nama_pelanggan` AS `nama_pelanggan`, count(`t`.`id_transaksi`) AS `total_transaksi`, coalesce(sum(`t`.`total_belanja`),0) AS `total_belanja` FROM (`tbl_pelanggan` `p` left join `tbl_transaksi` `t` on(`p`.`id_pelanggan` = `t`.`id_pelanggan`)) GROUP BY `p`.`id_pelanggan`, `p`.`nama_pelanggan` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log_pelanggan`
--
ALTER TABLE `log_pelanggan`
  ADD PRIMARY KEY (`id_log`);

--
-- Indexes for table `log_perubahan_pelanggan`
--
ALTER TABLE `log_perubahan_pelanggan`
  ADD PRIMARY KEY (`id_log`);

--
-- Indexes for table `tbl_cabang`
--
ALTER TABLE `tbl_cabang`
  ADD PRIMARY KEY (`id_cabang`);

--
-- Indexes for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `tbl_pelunasan`
--
ALTER TABLE `tbl_pelunasan`
  ADD PRIMARY KEY (`id_pelunasan`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `idx_nama_produk` (`nama_produk`);

--
-- Indexes for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_pelanggan` (`id_pelanggan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log_pelanggan`
--
ALTER TABLE `log_pelanggan`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `log_perubahan_pelanggan`
--
ALTER TABLE `log_perubahan_pelanggan`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_cabang`
--
ALTER TABLE `tbl_cabang`
  MODIFY `id_cabang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_pelunasan`
--
ALTER TABLE `tbl_pelunasan`
  MODIFY `id_pelunasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_detail_transaksi`
--
ALTER TABLE `tbl_detail_transaksi`
  ADD CONSTRAINT `tbl_detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `tbl_produk` (`id_produk`);

--
-- Constraints for table `tbl_pelunasan`
--
ALTER TABLE `tbl_pelunasan`
  ADD CONSTRAINT `tbl_pelunasan_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `tbl_transaksi` (`id_transaksi`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_produk`
--
ALTER TABLE `tbl_produk`
  ADD CONSTRAINT `tbl_produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `tbl_kategori` (`id_kategori`);

--
-- Constraints for table `tbl_transaksi`
--
ALTER TABLE `tbl_transaksi`
  ADD CONSTRAINT `tbl_transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`);

--
-- Constraints for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD CONSTRAINT `fk_user_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
