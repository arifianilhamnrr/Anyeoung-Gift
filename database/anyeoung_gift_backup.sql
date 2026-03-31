-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: anyeoung_gift
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `type` enum('user','store') NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `whatsapp_number` varchar(20) NOT NULL,
  `address_text` text NOT NULL,
  `notes` text,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,1,'user','Arifian Ilham Nur Riandana','085174472744','Karangpucung, Kertanegara, Purbalingga',NULL,0,'2026-03-28 03:27:53','2026-03-31 01:40:49'),(3,NULL,'store','Anyeoung Gift Store','6281234567890','Jalan Raya Karangpucung, Kertanegara, Purbalingga, Jawa Tengah','Ambil pesanan langsung di toko.',1,'2026-03-29 06:17:10','2026-03-29 06:58:09'),(4,1,'user','Testing','0812345678','Karangpucung, Kertanegara, Purbalingga','Saya ambil nanti sore ya',1,'2026-03-31 01:40:49','2026-03-31 01:40:49');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_options`
--

DROP TABLE IF EXISTS `order_item_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_item_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_item_id` int NOT NULL,
  `option_name_snapshot` varchar(100) NOT NULL,
  `option_value_snapshot` varchar(150) NOT NULL,
  `additional_price` int DEFAULT '0',
  `custom_value` text,
  PRIMARY KEY (`id`),
  KEY `order_item_id` (`order_item_id`),
  CONSTRAINT `order_item_options_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_options`
--

LOCK TABLES `order_item_options` WRITE;
/*!40000 ALTER TABLE `order_item_options` DISABLE KEYS */;
INSERT INTO `order_item_options` VALUES (1,7,'Jumlah Tangkai','20 tangkai',0,NULL),(2,7,'Glitter','Ya',0,NULL),(3,7,'Warna','Merah & Pink',0,NULL),(4,7,'Aksesoris','Mahkota Small',0,NULL),(5,7,'Tulisan Pita','-',0,'testing'),(6,7,'Tulisan Custom','-',0,'testing'),(7,8,'Jumlah Tangkai','15 tangkai',0,NULL),(8,8,'Glitter','Ya',0,NULL),(9,8,'Warna','Biru & Pink',0,NULL),(10,8,'Aksesoris','Mahkota Small',0,NULL),(11,9,'Jumlah Tangkai','25 tangkai',0,NULL),(12,9,'Glitter','Ya',0,NULL),(13,9,'Warna','Biru',0,NULL),(14,9,'Aksesoris','Mahkota Large',0,NULL),(15,10,'Jumlah Tangkai','15 tangkai',0,NULL),(16,10,'Glitter','Ya',0,NULL),(17,10,'Warna','Merah & Pink',0,NULL),(18,10,'Aksesoris','Mahkota Large',0,NULL),(19,10,'Tulisan Pita','-',0,'testing 123'),(20,10,'Tulisan Custom','-',0,'testing 123'),(21,11,'Model','Bouquet Uang Biasa',0,NULL),(22,11,'Jumlah Lembar','20 lembar',0,NULL),(23,11,'Nominal Uang','Rp50.000',0,NULL),(24,12,'Jumlah Tangkai','15 tangkai',0,NULL),(25,12,'Glitter','Ya',0,NULL),(26,12,'Warna','Biru & Pink',0,NULL),(27,12,'Aksesoris','Mahkota Small',0,NULL),(28,12,'Tulisan Pita','-',0,'asfa'),(29,12,'Tulisan Custom','-',0,'asfa'),(30,13,'Aksesoris','Coklat',0,NULL),(31,16,'Aksesoris','Lampu',0,NULL),(32,17,'Model','Bouquet Uang Bunga',0,NULL),(33,17,'Jumlah Lembar','20 lembar',0,NULL),(34,17,'Nominal Uang','Rp10.000',0,NULL);
/*!40000 ALTER TABLE `order_item_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name_snapshot` varchar(150) NOT NULL,
  `base_price` int DEFAULT '0',
  `subtotal` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,6,'Hampers Boneka',0,120000),(2,2,6,'Hampers Boneka',0,135000),(3,3,6,'Hampers Boneka',0,120000),(4,4,3,'Bouquet Uang',0,520000),(5,4,2,'Bouquet Satin',0,80000),(6,5,2,'Bouquet Satin',0,77000),(7,6,2,'Bouquet Satin',0,72000),(8,7,2,'Bouquet Satin',0,60000),(9,8,2,'Bouquet Satin',0,95000),(10,9,2,'Bouquet Satin',0,70000),(11,10,3,'Bouquet Uang',0,1010000),(12,11,2,'Bouquet Satin',0,60000),(13,12,6,'Hampers Boneka',0,135000),(14,13,5,'Hampers Hijab B',0,185000),(15,14,5,'Hampers Hijab B',0,185000),(16,15,6,'Hampers Boneka',0,130000),(17,16,3,'Bouquet Uang',0,220000);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `address_snapshot` json NOT NULL,
  `total_price` int NOT NULL,
  `status` enum('pending','waiting_payment','paid','ready_pickup','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',120000,'cancelled','2026-03-28 03:36:38'),(2,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',135000,'cancelled','2026-03-29 04:31:36'),(3,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',120000,'cancelled','2026-03-29 04:37:14'),(4,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',600000,'cancelled','2026-03-29 04:59:33'),(5,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',77000,'cancelled','2026-03-29 05:01:38'),(6,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',72000,'cancelled','2026-03-29 05:27:39'),(7,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',60000,'cancelled','2026-03-31 01:15:12'),(8,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',95000,'cancelled','2026-03-31 01:16:09'),(9,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',70000,'cancelled','2026-03-31 01:19:10'),(10,1,'{\"id\": 1, \"notes\": null, \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Arifian Ilham Nur Riandana\", \"whatsapp_number\": \"085174472744\"}',1010000,'waiting_payment','2026-03-31 01:28:49'),(11,1,'{\"id\": 4, \"notes\": \"Saya ambil nanti sore ya\", \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Testing\", \"whatsapp_number\": \"0812345678\"}',60000,'cancelled','2026-03-31 01:44:29'),(12,1,'{\"id\": 4, \"notes\": \"Saya ambil nanti sore ya\", \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Testing\", \"whatsapp_number\": \"0812345678\"}',135000,'waiting_payment','2026-03-31 02:02:40'),(13,1,'{\"id\": 4, \"notes\": \"Saya ambil nanti sore ya\", \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Testing\", \"whatsapp_number\": \"0812345678\"}',185000,'waiting_payment','2026-03-31 02:08:40'),(14,1,'{\"id\": 4, \"notes\": \"Saya ambil nanti sore ya\", \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Testing\", \"whatsapp_number\": \"0812345678\"}',185000,'waiting_payment','2026-03-31 02:28:21'),(15,1,'{\"id\": 4, \"notes\": \"Saya ambil nanti sore ya\", \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Testing\", \"whatsapp_number\": \"0812345678\"}',130000,'waiting_payment','2026-03-31 04:13:09'),(16,1,'{\"id\": 4, \"notes\": \"Saya ambil nanti sore ya\", \"address_text\": \"Karangpucung, Kertanegara, Purbalingga\", \"recipient_name\": \"Testing\", \"whatsapp_number\": \"0812345678\"}',220000,'paid','2026-03-31 04:27:12');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('transfer','qris','ewallet','onsite') NOT NULL,
  `account_info` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'Seabank','transfer','8994564313246',1,'2026-03-28 03:35:37'),(2,'Bayar di Tempat','onsite',NULL,1,'2026-03-29 04:58:08');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_method_id` int NOT NULL,
  `amount` int NOT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `proof_image` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `payment_method_id` (`payment_method_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,1,120000,'rejected',NULL,NULL,'2026-03-28 03:36:38'),(2,2,1,135000,'rejected','payment_2_1774760108.png','2026-03-29 04:55:08','2026-03-29 04:31:36'),(3,3,1,120000,'rejected',NULL,NULL,'2026-03-29 04:37:14'),(4,4,2,600000,'rejected',NULL,NULL,'2026-03-29 04:59:33'),(5,5,2,77000,'rejected',NULL,NULL,'2026-03-29 05:01:38'),(6,6,2,72000,'rejected',NULL,NULL,'2026-03-29 05:27:39'),(7,7,1,60000,'rejected',NULL,NULL,'2026-03-31 01:15:12'),(8,8,1,95000,'rejected',NULL,NULL,'2026-03-31 01:16:09'),(9,9,1,70000,'rejected',NULL,NULL,'2026-03-31 01:19:10'),(10,10,1,1010000,'pending','payment_10_1774921635.jpg','2026-03-31 01:47:15','2026-03-31 01:28:49'),(11,11,1,60000,'rejected',NULL,NULL,'2026-03-31 01:44:29'),(12,12,1,135000,'pending',NULL,NULL,'2026-03-31 02:02:40'),(13,13,1,185000,'pending','payment_13_1774923211.jpg','2026-03-31 02:13:31','2026-03-31 02:08:40'),(14,14,1,185000,'pending',NULL,NULL,'2026-03-31 02:28:21'),(15,15,1,130000,'pending','payment_15_1774930421.png','2026-03-31 04:13:41','2026-03-31 04:13:09'),(16,16,1,220000,'pending',NULL,NULL,'2026-03-31 04:27:12');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pita_templates`
--

DROP TABLE IF EXISTS `pita_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pita_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `template_text` varchar(255) NOT NULL,
  `max_char` int NOT NULL,
  `additional_price` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `pita_templates_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pita_templates`
--

LOCK TABLES `pita_templates` WRITE;
/*!40000 ALTER TABLE `pita_templates` DISABLE KEYS */;
INSERT INTO `pita_templates` VALUES (5,2,'Happy Birthday {{nama}}',30,5000,1),(6,2,'Untuk {{nama}}',25,5000,1),(7,2,'With Love',20,5000,1);
/*!40000 ALTER TABLE `pita_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `option_value_id` int DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `option_value_id` (`option_value_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`option_value_id`) REFERENCES `product_option_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,2,NULL,'public/uploads/products/bouquet-satin-utama.jpg',1,1,'2026-03-31 00:52:28'),(2,2,NULL,'public/uploads/products/bouquet-satin-detail.jpg',0,2,'2026-03-31 00:52:28'),(3,3,NULL,'public/uploads/products/bouquet-uang-utama.jpg',1,1,'2026-03-31 00:52:28'),(4,4,NULL,'public/uploads/products/hampers-hijab-a-utama.jpg',1,1,'2026-03-31 00:52:28'),(5,5,NULL,'public/uploads/products/hampers-hijab-b-utama.jpg',1,1,'2026-03-31 00:52:28'),(6,6,NULL,'public/uploads/products/hampers-boneka-utama.jpg',1,1,'2026-03-31 00:52:28'),(7,6,NULL,'public/uploads/products/hampers-boneka-detail.jpg',0,2,'2026-03-31 00:52:28'),(8,7,NULL,'public/uploads/products/custom-hampers-utama.jpg',1,1,'2026-03-31 00:52:28');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_option_values`
--

DROP TABLE IF EXISTS `product_option_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_option_values` (
  `id` int NOT NULL AUTO_INCREMENT,
  `option_id` int NOT NULL,
  `value_name` varchar(100) NOT NULL,
  `additional_price` int DEFAULT '0',
  `extra_data` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `product_option_values_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `product_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_values`
--

LOCK TABLES `product_option_values` WRITE;
/*!40000 ALTER TABLE `product_option_values` DISABLE KEYS */;
INSERT INTO `product_option_values` VALUES (19,6,'15 tangkai',45000,NULL,1),(20,6,'20 tangkai',57000,NULL,1),(21,6,'25 tangkai',70000,NULL,1),(22,7,'Ya',5000,NULL,1),(23,7,'Tidak',0,NULL,1),(24,8,'Merah',0,NULL,1),(25,8,'Pink',0,NULL,1),(26,8,'Biru',0,NULL,1),(27,8,'Biru & Pink',0,NULL,1),(28,8,'Merah & Pink',0,NULL,1),(29,9,'Mahkota Small',10000,NULL,1),(30,9,'Mahkota Large',20000,NULL,1),(31,11,'Bouquet Uang Biasa',10000,NULL,1),(32,11,'Bouquet Uang Kipas',15000,NULL,1),(33,11,'Bouquet Uang Bunga',20000,NULL,1),(34,12,'10 lembar',0,'{\"qty\": 10}',1),(35,12,'15 lembar',0,'{\"qty\": 15}',1),(36,12,'20 lembar',0,'{\"qty\": 20}',1),(37,12,'25 lembar',0,'{\"qty\": 25}',1),(38,13,'Rp1.000',0,'{\"nominal\": 1000}',1),(39,13,'Rp2.000',0,'{\"nominal\": 2000}',1),(40,13,'Rp5.000',0,'{\"nominal\": 5000}',1),(41,13,'Rp10.000',0,'{\"nominal\": 10000}',1),(42,13,'Rp20.000',0,'{\"nominal\": 20000}',1),(43,13,'Rp50.000',0,'{\"nominal\": 50000}',1),(44,13,'Rp100.000',0,'{\"nominal\": 100000}',1),(45,14,'Coklat',15000,NULL,1),(46,14,'Lampu',10000,NULL,1);
/*!40000 ALTER TABLE `product_option_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_options`
--

DROP TABLE IF EXISTS `product_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `option_type` enum('single','multiple','custom_input') NOT NULL,
  `is_required` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_options_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_options`
--

LOCK TABLES `product_options` WRITE;
/*!40000 ALTER TABLE `product_options` DISABLE KEYS */;
INSERT INTO `product_options` VALUES (6,2,'Jumlah Tangkai','single',1,'2026-02-04 03:03:43'),(7,2,'Glitter','single',0,'2026-02-04 03:03:43'),(8,2,'Warna','single',1,'2026-02-04 03:03:43'),(9,2,'Aksesoris','multiple',0,'2026-02-04 03:03:43'),(10,2,'Tulisan Pita','custom_input',0,'2026-02-04 03:03:43'),(11,3,'Model','single',1,'2026-02-04 03:06:03'),(12,3,'Jumlah Lembar','single',1,'2026-02-04 03:06:03'),(13,3,'Nominal Uang','single',1,'2026-02-04 03:06:03'),(14,6,'Aksesoris','multiple',0,'2026-02-04 03:17:22');
/*!40000 ALTER TABLE `product_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `product_type` enum('simple','custom_full','custom_money','semi_custom','chat_only') NOT NULL,
  `description` text,
  `base_price` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (2,'Bouquet Satin','bouquet','custom_full','Bouquet bunga satin dengan pilihan warna dan aksesoris',NULL,1,'2026-02-04 03:03:43','2026-02-04 03:03:43'),(3,'Bouquet Uang','bouquet','custom_money','Bouquet dari uang dengan berbagai model dan nominal',NULL,1,'2026-02-04 03:06:03','2026-02-04 03:06:03'),(4,'Hampers Hijab A','hampers','simple','Hampers hijab dengan kemasan eksklusif',150000,1,'2026-02-04 03:10:03','2026-02-04 03:10:03'),(5,'Hampers Hijab B','hampers','simple','Hampers hijab premium cocok untuk hadiah',185000,1,'2026-02-04 03:10:03','2026-02-04 03:10:03'),(6,'Hampers Boneka','hampers','semi_custom','Hampers boneka lucu dengan aksesoris tambahan',120000,1,'2026-02-04 03:17:22','2026-02-04 03:17:22'),(7,'Custom Hampers','hampers','chat_only','Hampers custom sesuai request. Silakan hubungi admin untuk konsultasi dan pemesanan.',NULL,1,'2026-02-04 03:17:55','2026-02-04 03:17:55');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_settings`
--

DROP TABLE IF EXISTS `store_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `store_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_name` varchar(100) NOT NULL,
  `whatsapp_admin` varchar(20) NOT NULL,
  `whatsapp_message_template` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_settings`
--

LOCK TABLES `store_settings` WRITE;
/*!40000 ALTER TABLE `store_settings` DISABLE KEYS */;
INSERT INTO `store_settings` VALUES (1,'Anyeong Gift','6287764023345','Halo Admin Anyoung Gift, saya ingin konsultasi untuk {{product_name}}.','2026-03-29 06:07:06','2026-03-31 04:35:53');
/*!40000 ALTER TABLE `store_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'arifian ilham nur r','arifianilhamnurriandana@gmail.com','$2y$10$H4yzIc.VP08QMz3S8s3l6OkBUM2BpHSO.yILOxE6DKSj0qLanZ79W','user','2026-03-28 02:37:19','2026-03-29 06:03:08'),(2,'testing aja','akhsjdkah@gmail.cp','$2y$10$59wjbgXCbprJ0xJYLtzTR.NIgNz8GSvmwYoPOVtzMsAP89zH9LCDm','user','2026-03-28 02:46:43','2026-03-28 02:46:43'),(3,'Super Admin','super@anyeong.com','bee5688aea66a47460b19c76f8f199c6b9585eb726f8322b1429793863609ca2','admin','2026-03-31 00:27:52','2026-03-31 00:27:52'),(4,'Knalpot DK Speed','testing@test.com','$2y$10$CtsNqqn9VM1DLGHLqNwYgef38gG1ryCfaDHxET6cdKoNXo9zIHdGy','user','2026-03-31 03:28:07','2026-03-31 03:28:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-31 11:55:39
