-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 04:14 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_gomaggot`
--

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pelanggan` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` text NOT NULL,
  `role` ('admin','konsumen') NOT NULL DEFAULT 'konsumen'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pelanggan`, `username`, `email`, `password`, `flag`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$G0jeXpAFjduw8nJfS7ao2ep5QP0f2SmptGIaPJcxoyp1ii1m2pHVS', 'konsumen'),
(2, 'daud', 'daud@gmail.com', '$2y$10$K0Csg2ypr7a3dWUk7DNtK.BGPxD8Q4bH2DvLQLHcm87vkveOrBMpK', 'konsumen');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `idproduk` int(11) AUTO_INCREMENT PRIMARY KEY,
  `namaproduk` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` varchar DEFAULT 0,
  `deskripsi_produk` text ,
  `kategori` varchar(255),
  `masapenyimpanan` varchar(255),
  `berat` varchar(255),
  `pengiriman` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Dumping data for table `produk`
--
INSERT INTO `produk` (`idproduk`, `namaproduk`, `gambar`, `harga`, `stok`, `deskripsi_produk`, `kategori`, 
`masapenyimpanan`, `berat`, `pengiriman`) VALUES (1, `Maggot siap pakai`, `C:\xampp\htdocs\Admingoma\photos`, `Rp 70.000`, `5Kg`, 'Maggot siap pakai untuk keperluan pakan hewan ternak',
 `Maggot BSF`,`2 Minggu`, `70g`, `Bandung`);




INSERT INTO `produk` (`idproduk`, `namaproduk`, `gambar`, `harga`, `qty`, `deskripsi_produk`) VALUES
(2, 'Tes', 'uploads/img_68101692c71150.43519458.png', 100000.00, 50, 'Mantap'),
(3, 'Tes Perbaikan', 'uploads/img_681018ba847466.28459055.png', 50000.00, 10, '100'),
(5, 'dd', 'uploads/img_6810224510efc1.82808259.png', 12000.00, 2, 'qkjd wdqd');

--
-- Indexes for dumped tables
--

--
-- Table structure for table `review produk`
--

CREATE TABLE `review` (
  `idproduk` int(11) AUTO_INCREMENT PRIMARY KEY,
  `namaproduk` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` varchar DEFAULT 0,
  `deskripsi_produk` text DEFAULT NULL,f
  `kategori` varchar(255),
  `masapenyimpanan` varchar(255).
  `berat` varchar(255),
  `pengiriman` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- dumping data for table review
--

CREATE TABLE keranjang(
    
)





--
-- Tabel 



--
-- Table recent_order 
--
CREATE TABLE `recent_order` (
    `id_produk` int(11)
)
















--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`idproduk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `idproduk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
