-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: egi_xii1_paket1_
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alat`
--

DROP TABLE IF EXISTS `alat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alat` (
  `id_alat` int(11) NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(100) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `kondisi` varchar(50) DEFAULT NULL,
  `kategori` varchar(25) NOT NULL,
  PRIMARY KEY (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alat`
--

LOCK TABLES `alat` WRITE;
/*!40000 ALTER TABLE `alat` DISABLE KEYS */;
INSERT INTO `alat` VALUES (1,'jam',9,'baik','elektronik'),(2,'Bor Listrik',3,'Baik','alat'),(3,'kamera',4,'baik','elektronik'),(5,'casan',2,'baik','elektronik'),(6,'palu',7,'baik','alat');
/*!40000 ALTER TABLE `alat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anggota`
--

DROP TABLE IF EXISTS `anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_anggota` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL DEFAULT '',
  `telepon` varchar(30) NOT NULL DEFAULT '',
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_anggota`),
  UNIQUE KEY `nomor_anggota` (`nomor_anggota`),
  UNIQUE KEY `id_user` (`id_user`),
  CONSTRAINT `fk_anggota_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota`
--

LOCK TABLES `anggota` WRITE;
/*!40000 ALTER TABLE `anggota` DISABLE KEYS */;
INSERT INTO `anggota` VALUES (1,'AG1','ahmad','cimahi','080000000001',5),(2,'AG2','rehan','cimahi','080000000002',6),(3,'AG3','pashya','cimahi','080000000003',7),(4,'AG4','alfin','cimahi','080000000004',8),(5,'AG5','Rizky','cimahi','080000000005',9),(7,'AG6','gi','cimahi','',10),(9,'AG7','power','cimahi','',12);
/*!40000 ALTER TABLE `anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (12,'alat'),(13,'elektronik');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_aktivitas`
--

DROP TABLE IF EXISTS `log_aktivitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `aktivitas` varchar(255) DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_aktivitas`
--

LOCK TABLES `log_aktivitas` WRITE;
/*!40000 ALTER TABLE `log_aktivitas` DISABLE KEYS */;
INSERT INTO `log_aktivitas` VALUES (1,0,'Menambah alat','2026-09-16 12:19:47'),(2,0,'Menghapus alat #4','2026-09-16 12:19:54'),(3,0,'Menambah peminjam','2026-09-16 12:25:12'),(4,0,'Menambah alat','2026-09-16 12:42:14'),(5,0,'Menghapus peminjam #5','2026-09-16 12:42:45'),(6,0,'Mengedit anggota #2','2026-09-17 08:13:32'),(7,0,'Mengedit anggota #2','2026-09-17 08:13:42'),(8,0,'Mengedit anggota #5','2026-09-17 08:34:45'),(9,0,'Mengedit anggota #4','2026-09-17 08:34:59'),(10,0,'Mengedit anggota #3','2026-09-17 08:35:13'),(11,0,'Mengedit anggota #2','2026-09-17 08:35:22'),(12,0,'Mengedit anggota #1','2026-09-17 08:35:35'),(13,0,'Menambah alat','2026-09-17 08:41:41'),(14,0,'Mengedit alat','2026-09-17 08:41:57'),(15,0,'Menghapus alat #6','2026-09-17 08:42:12'),(16,0,'Menambah alat','2026-09-17 08:42:32'),(17,0,'Menambah anggota #6','2026-09-17 08:43:05'),(18,0,'Mengedit anggota #6','2026-09-17 08:43:13'),(19,0,'Menghapus anggota #6','2026-09-17 08:43:18'),(20,0,'Mengedit anggota #5','2026-09-17 08:43:32'),(21,0,'Mengedit anggota #4','2026-09-17 08:43:42'),(22,0,'Mengedit anggota #3','2026-09-17 08:43:52'),(23,0,'Mengedit anggota #2','2026-09-17 08:44:01'),(24,0,'Mengedit anggota #1','2026-09-17 08:44:17'),(25,0,'Menambah anggota #7','2026-09-17 08:44:31'),(26,0,'Menambah peminjaman aktif untuk anggota AG1','2026-09-17 08:48:31'),(27,0,'Menambah peminjaman aktif untuk anggota AG2','2026-09-17 08:48:31'),(28,0,'Menambah peminjaman aktif untuk anggota AG3','2026-09-17 08:48:31'),(29,0,'Menambah peminjaman aktif untuk anggota AG4','2026-09-17 08:48:31'),(30,0,'Menambah peminjaman aktif untuk anggota AG5','2026-09-17 08:48:31'),(31,0,'Menambah peminjaman aktif untuk anggota AG6','2026-09-17 08:48:31'),(32,12,'Menambah peminjam','2026-09-17 20:39:02'),(33,12,'Status peminjaman #12: menunggu -> dipinjam','2026-09-17 20:39:31'),(34,2,'Menyetujui peminjaman #12','2026-09-17 20:39:31'),(35,12,'Status peminjaman #12: dipinjam -> menunggu pengembalian','2026-09-17 20:44:49'),(36,1,'Mengajukan pengembalian peminjaman #12','2026-09-17 20:44:49'),(37,10,'Status peminjaman #11: dipinjam -> menunggu pengembalian','2026-09-21 08:32:04'),(38,1,'Mengajukan pengembalian peminjaman #11','2026-09-21 08:32:04'),(39,10,'Status peminjaman #11: menunggu pengembalian -> dikembalikan','2026-09-21 08:32:10'),(40,1,'Memverifikasi pengembalian #6','2026-09-21 08:32:10'),(41,12,'Status peminjaman #12: menunggu pengembalian -> dikembalikan','2026-09-21 08:32:14'),(42,1,'Memverifikasi pengembalian #5','2026-09-21 08:32:14'),(43,9,'Status peminjaman #10: dipinjam -> menunggu pengembalian','2026-09-21 08:32:33'),(44,1,'Mengajukan pengembalian peminjaman #10','2026-09-21 08:32:33'),(45,9,'Status peminjaman #10: menunggu pengembalian -> dikembalikan','2026-09-21 08:32:40'),(46,1,'Memverifikasi pengembalian #7','2026-09-21 08:32:40'),(47,8,'Status peminjaman #9: dipinjam -> menunggu pengembalian','2026-09-21 08:34:32'),(48,1,'Mengajukan pengembalian peminjaman #9','2026-09-21 08:34:32'),(49,8,'Status peminjaman #9: menunggu pengembalian -> dikembalikan','2026-09-21 08:34:48'),(50,1,'Memverifikasi pengembalian #8','2026-09-21 08:34:48'),(51,7,'Status peminjaman #8: dipinjam -> menunggu pengembalian','2026-09-21 08:35:18'),(52,1,'Mengajukan pengembalian peminjaman #8','2026-09-21 08:35:18'),(53,7,'Status peminjaman #8: menunggu pengembalian -> dikembalikan','2026-09-21 08:35:28'),(54,1,'Memverifikasi pengembalian #9','2026-09-21 08:35:28'),(55,6,'Status peminjaman #7: dipinjam -> menunggu pengembalian','2026-09-21 08:35:42'),(56,1,'Mengajukan pengembalian peminjaman #7','2026-09-21 08:35:42'),(57,6,'Status peminjaman #7: menunggu pengembalian -> dikembalikan','2026-09-21 08:35:49'),(58,1,'Memverifikasi pengembalian #10','2026-09-21 08:35:49'),(59,5,'Status peminjaman #6: dipinjam -> menunggu pengembalian','2026-09-21 08:36:00'),(60,1,'Mengajukan pengembalian peminjaman #6','2026-09-21 08:36:00'),(61,5,'Status peminjaman #6: menunggu pengembalian -> dikembalikan','2026-09-21 08:36:05'),(62,1,'Memverifikasi pengembalian #11','2026-09-21 08:36:05'),(63,5,'Menambah peminjam','2026-09-21 08:40:53'),(64,5,'Status peminjaman #6: dikembalikan -> dipinjam','2026-09-21 08:42:04'),(65,1,'Membatalkan pengembalian #11','2026-09-21 08:42:04'),(66,6,'Status peminjaman #7: dikembalikan -> dipinjam','2026-09-21 08:42:14'),(67,1,'Membatalkan pengembalian #10','2026-09-21 08:42:14'),(68,7,'Status peminjaman #8: dikembalikan -> dipinjam','2026-09-21 08:42:17'),(69,1,'Membatalkan pengembalian #9','2026-09-21 08:42:17'),(70,8,'Status peminjaman #9: dikembalikan -> dipinjam','2026-09-21 08:42:21'),(71,1,'Membatalkan pengembalian #8','2026-09-21 08:42:21'),(72,9,'Status peminjaman #10: dikembalikan -> dipinjam','2026-09-21 08:42:25'),(73,1,'Membatalkan pengembalian #7','2026-09-21 08:42:25'),(74,10,'Status peminjaman #11: dikembalikan -> dipinjam','2026-09-21 08:42:28'),(75,1,'Membatalkan pengembalian #6','2026-09-21 08:42:28'),(76,12,'Status peminjaman #12: dikembalikan -> dipinjam','2026-09-21 08:42:33'),(77,1,'Membatalkan pengembalian #5','2026-09-21 08:42:33'),(78,1,'Menghapus peminjam #13','2026-09-21 14:13:23'),(79,5,'Status peminjaman #6: dipinjam -> menunggu pengembalian','2026-09-21 14:29:21'),(80,5,'Mengajukan pengembalian peminjaman #6','2026-09-21 14:29:21'),(81,5,'Menambah peminjam','2026-09-21 14:59:18'),(82,5,'Status peminjaman #14: menunggu -> dipinjam','2026-09-21 22:53:23'),(83,1,'Menyetujui peminjaman #14','2026-09-21 22:53:23'),(84,5,'Menambah peminjam','2026-09-21 23:00:08'),(85,5,'Status peminjaman #15: menunggu -> dipinjam','2026-09-21 23:00:40'),(86,2,'Menyetujui peminjaman #15','2026-09-21 23:00:40'),(87,1,'Mengedit kategori #13','2026-09-22 07:08:28'),(88,1,'Mengedit kategori #13','2026-09-22 07:08:39'),(89,6,'Menambah peminjam','2026-09-22 07:13:07'),(90,6,'Status peminjaman #16: menunggu -> dipinjam','2026-09-22 07:13:30'),(91,2,'Menyetujui peminjaman #16','2026-09-22 07:13:30'),(92,5,'Menambah peminjam','2026-09-23 14:53:33'),(93,5,'Status peminjaman #17: menunggu -> dipinjam','2026-09-23 14:53:55'),(94,2,'Menyetujui peminjaman #17','2026-09-23 14:53:55'),(95,5,'Status peminjaman #17: dipinjam -> menunggu pengembalian','2026-09-23 14:54:20'),(96,5,'Mengajukan pengembalian peminjaman #17','2026-09-23 14:54:20'),(97,5,'Status peminjaman #17: menunggu pengembalian -> dikembalikan','2026-09-23 14:54:40'),(98,2,'Memverifikasi pengembalian #13','2026-09-23 14:54:40');
/*!40000 ALTER TABLE `log_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjam`
--

DROP TABLE IF EXISTS `peminjam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `peminjam` (
  `id_peminjam` int(11) NOT NULL AUTO_INCREMENT,
  `nama_peminjam` varchar(100) DEFAULT NULL,
  `id_alat` int(11) DEFAULT NULL,
  `tanggal_pinjam` date DEFAULT NULL,
  `jumlah_pinjam` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `batas_kembali` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'arsip',
  PRIMARY KEY (`id_peminjam`),
  KEY `idx_pinjam_user` (`id_user`),
  CONSTRAINT `fk_pinjam_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjam`
--

LOCK TABLES `peminjam` WRITE;
/*!40000 ALTER TABLE `peminjam` DISABLE KEYS */;
INSERT INTO `peminjam` VALUES (15,'ahmad',2,'2026-09-21',1,5,'2026-09-28','dipinjam'),(16,'rehan',2,'2026-09-22',1,6,'2026-09-29','dipinjam'),(17,'ahmad',5,'2026-09-23',1,5,'2026-09-30','dikembalikan');
/*!40000 ALTER TABLE `peminjam` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
--
-- Table structure for table `pengembalian`
--

DROP TABLE IF EXISTS `pengembalian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengembalian` (
  `id_pengembalian` int(11) NOT NULL AUTO_INCREMENT,
  `id_peminjam` int(11) DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `kondisi_alat` varchar(50) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `denda` int(11) NOT NULL DEFAULT 0,
  `diverifikasi` tinyint(4) NOT NULL DEFAULT 0,
  `arsip` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_pengembalian`),
  KEY `idx_kembali_pinjam` (`id_peminjam`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengembalian`
--

LOCK TABLES `pengembalian` WRITE;
/*!40000 ALTER TABLE `pengembalian` DISABLE KEYS */;
INSERT INTO `pengembalian` VALUES (13,17,'2026-09-23','baik','dikembalikan',0,1,0);
/*!40000 ALTER TABLE `pengembalian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `level` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_user_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (2,'petugas','$2y$10$4m4l1x2uWRL8wExoyrjDMe75Ma4lXVpAO1FWBFRE.JFySUsUTdMqC','gi','petugas'),(5,'AG1','$2y$10$5aAtap/iQERK3PCcm8hOP.RY5yXz86ljabgKa9vn89Aio6n/IrSnS','ahmad','peminjam'),(6,'AG2','$2y$10$XPCk9faUb5t/5bV9G7t7huco3ReR9K6I1BwTJluKwh8nDybTdYl1a','rehan','peminjam'),(7,'AG3','2','pashya','peminjam'),(8,'AG4','2','alfin','peminjam'),(9,'AG5','2','Rizky','peminjam'),(10,'AG6','2','gi','peminjam'),(12,'AG7','2','power','peminjam');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Fungsi dan prosedur dihilangkan agar kompatibel dengan shared hosting.
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-23 19:28:49
