CREATE DATABASE  IF NOT EXISTS `academiamusica` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `academiamusica`;
-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: academiamusica
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alumno_clases`
--

DROP TABLE IF EXISTS `alumno_clases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumno_clases` (
  `alumno_curp` varchar(18) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`alumno_curp`,`clase_id`),
  KEY `clase_id` (`clase_id`),
  CONSTRAINT `alumno_clases_ibfk_1` FOREIGN KEY (`alumno_curp`) REFERENCES `alumnos` (`curp`) ON DELETE CASCADE,
  CONSTRAINT `alumno_clases_ibfk_2` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumno_clases`
--

LOCK TABLES `alumno_clases` WRITE;
/*!40000 ALTER TABLE `alumno_clases` DISABLE KEYS */;
INSERT INTO `alumno_clases` VALUES ('AIEC150601MCLVSRA0',1,'2025-07-10 07:40:11'),('AITB140621MCLVTRA8',1,'2025-07-10 16:05:33'),('AITB140621MCLVTRA8',2,'2025-07-10 16:05:33'),('AITB140621MCLVTRA8',3,'2025-07-10 16:05:33'),('AITM161119MCLVTRA8',2,'2025-07-10 16:05:43'),('AITM161119MCLVTRA8',3,'2025-07-10 16:05:43');
/*!40000 ALTER TABLE `alumno_clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnos` (
  `curp` varchar(18) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `nombre_tutor` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `mensualidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`curp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos`
--

LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;
INSERT INTO `alumnos` VALUES ('AIEC150601MCLVSRA0','pollo insanooooo','Kaedeharaaa','konoha','2002-10-10','8713968800','2025-07-10 01:52:39',15.00,1),('AITB140621MCLVTRA8','barbara','js','1111111111111','2000-02-16','911','2025-07-10 00:20:21',3000.00,1),('AITM161119MCLVTRA8','Kazuha','Kaedeharaaa','konoha','2002-10-10','8713968800','2025-07-10 00:28:35',15.00,1),('RACW050729MMCSHNA2','Kazuha','Kaedeharaaa','konoha','2002-10-10','8713968800','2025-07-10 18:08:19',15.00,1);
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos_subtema_progreso`
--

DROP TABLE IF EXISTS `alumnos_subtema_progreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnos_subtema_progreso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumnos_tema_id` int(11) NOT NULL,
  `subtema_id` int(11) NOT NULL,
  `dia1` tinyint(1) DEFAULT 0,
  `dia2` tinyint(1) DEFAULT 0,
  `dia3` tinyint(1) DEFAULT 0,
  `dia4` tinyint(1) DEFAULT 0,
  `dia5` tinyint(1) DEFAULT 0,
  `dia6` tinyint(1) DEFAULT 0,
  `aprendido` tinyint(1) DEFAULT 0,
  `dia1_comentario` varchar(255) DEFAULT NULL,
  `dia2_comentario` varchar(255) DEFAULT NULL,
  `dia3_comentario` varchar(255) DEFAULT NULL,
  `dia4_comentario` varchar(255) DEFAULT NULL,
  `dia5_comentario` varchar(255) DEFAULT NULL,
  `dia6_comentario` varchar(255) DEFAULT NULL,
  `aprendido_comentario` varchar(255) DEFAULT NULL,
  `aprendido_fecha` datetime DEFAULT NULL,
  `dia1_fecha` datetime DEFAULT NULL,
  `dia2_fecha` datetime DEFAULT NULL,
  `dia3_fecha` datetime DEFAULT NULL,
  `dia4_fecha` datetime DEFAULT NULL,
  `dia5_fecha` datetime DEFAULT NULL,
  `dia6_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alumnos_tema_id` (`alumnos_tema_id`),
  KEY `subtema_id` (`subtema_id`),
  CONSTRAINT `alumnos_subtema_progreso_ibfk_1` FOREIGN KEY (`alumnos_tema_id`) REFERENCES `alumnos_tema` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alumnos_subtema_progreso_ibfk_2` FOREIGN KEY (`subtema_id`) REFERENCES `subtemas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_subtema_progreso`
--

LOCK TABLES `alumnos_subtema_progreso` WRITE;
/*!40000 ALTER TABLE `alumnos_subtema_progreso` DISABLE KEYS */;
INSERT INTO `alumnos_subtema_progreso` VALUES (95,6,36,1,1,1,1,1,1,1,'hola funciona?','d','d','d','d','d','d','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43'),(96,6,37,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(97,6,38,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(98,6,39,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(99,6,40,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(100,6,41,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(101,6,42,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(102,6,43,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(103,6,44,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(104,6,45,1,1,1,1,1,1,1,'a','a','a','a','a','a','a','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43','2025-07-10 09:31:43'),(105,6,46,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(106,6,47,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(107,6,48,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(108,6,49,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(109,6,50,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(110,6,51,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(111,6,52,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(112,6,53,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(113,6,54,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(114,6,55,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(115,6,56,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(116,6,57,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(117,6,58,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(118,6,59,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(119,6,60,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(120,6,61,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(121,6,62,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(122,6,63,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(123,6,64,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(124,7,36,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(125,7,37,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(126,7,38,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(127,7,39,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(128,7,40,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(129,7,41,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(130,7,42,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(131,7,43,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(132,7,44,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(133,7,45,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(134,7,46,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(135,7,47,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(136,7,48,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(137,7,49,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(138,7,50,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(139,7,51,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(140,7,52,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(141,7,53,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(142,7,54,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(143,7,55,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(144,7,56,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(145,7,57,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(146,7,58,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(147,7,59,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(148,7,60,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(149,7,61,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(150,7,62,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(151,7,63,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(152,7,64,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(153,8,36,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(154,8,37,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(155,8,38,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(156,8,39,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(157,8,40,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(158,8,41,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(159,8,42,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(160,8,43,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(161,8,44,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(162,8,45,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(163,8,46,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(164,8,47,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(165,8,48,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(166,8,49,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(167,8,50,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(168,8,51,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(169,8,52,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(170,8,53,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(171,8,54,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(172,8,55,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(173,8,56,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(174,8,57,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(175,8,58,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(176,8,59,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(177,8,60,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(178,8,61,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(179,8,62,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(180,8,63,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(181,8,64,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `alumnos_subtema_progreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos_tema`
--

DROP TABLE IF EXISTS `alumnos_tema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnos_tema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `curp` varchar(18) NOT NULL,
  `tema_id` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `curp` (`curp`),
  KEY `tema_id` (`tema_id`),
  CONSTRAINT `alumnos_tema_ibfk_1` FOREIGN KEY (`curp`) REFERENCES `alumnos` (`curp`) ON DELETE CASCADE,
  CONSTRAINT `alumnos_tema_ibfk_2` FOREIGN KEY (`tema_id`) REFERENCES `temas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_tema`
--

LOCK TABLES `alumnos_tema` WRITE;
/*!40000 ALTER TABLE `alumnos_tema` DISABLE KEYS */;
INSERT INTO `alumnos_tema` VALUES (6,'AIEC150601MCLVSRA0',2,'2025-07-10 06:54:53'),(7,'AITB140621MCLVTRA8',2,'2025-07-10 06:54:53'),(8,'AITM161119MCLVTRA8',2,'2025-07-10 06:54:53');
/*!40000 ALTER TABLE `alumnos_tema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_curp` varchar(18) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('asistio','justifico','falto') NOT NULL,
  `registrado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_curp` (`alumno_curp`,`clase_id`,`fecha`),
  KEY `clase_id` (`clase_id`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`alumno_curp`) REFERENCES `alumnos` (`curp`),
  CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (6,'AIEC150601MCLVSRA0',1,'2025-07-10','falto','2025-07-10 17:05:16'),(8,'AITB140621MCLVTRA8',2,'2025-07-10','justifico','2025-07-10 16:55:41'),(9,'AITM161119MCLVTRA8',2,'2025-07-10','justifico','2025-07-10 16:55:41'),(10,'AITB140621MCLVTRA8',3,'2025-07-10','justifico','2025-07-10 17:04:41'),(11,'AITM161119MCLVTRA8',3,'2025-07-10','justifico','2025-07-10 17:04:41'),(12,'AITB140621MCLVTRA8',1,'2025-07-10','falto','2025-07-10 17:05:16');
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clases`
--

DROP TABLE IF EXISTS `clases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clases`
--

LOCK TABLES `clases` WRITE;
/*!40000 ALTER TABLE `clases` DISABLE KEYS */;
INSERT INTO `clases` VALUES (3,'bateria1'),(1,'guy'),(2,'piano1');
/*!40000 ALTER TABLE `clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dias_sin_clase`
--

DROP TABLE IF EXISTS `dias_sin_clase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dias_sin_clase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clase_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` enum('cancelada','vacaciones') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clase_id` (`clase_id`,`fecha`),
  CONSTRAINT `dias_sin_clase_ibfk_1` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dias_sin_clase`
--

LOCK TABLES `dias_sin_clase` WRITE;
/*!40000 ALTER TABLE `dias_sin_clase` DISABLE KEYS */;
/*!40000 ALTER TABLE `dias_sin_clase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_mensualidad`
--

DROP TABLE IF EXISTS `pagos_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_mensualidad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_curp` varchar(18) DEFAULT NULL,
  `año` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_curp` (`alumno_curp`,`año`,`mes`),
  CONSTRAINT `pagos_mensualidad_ibfk_1` FOREIGN KEY (`alumno_curp`) REFERENCES `alumnos` (`curp`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_mensualidad`
--

LOCK TABLES `pagos_mensualidad` WRITE;
/*!40000 ALTER TABLE `pagos_mensualidad` DISABLE KEYS */;
INSERT INTO `pagos_mensualidad` VALUES (1,'AITB140621MCLVTRA8',2025,7,'2025-07-10 18:05:39',1.00);
/*!40000 ALTER TABLE `pagos_mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subtemas`
--

DROP TABLE IF EXISTS `subtemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subtemas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tema_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `tema_id` (`tema_id`),
  CONSTRAINT `subtemas_ibfk_1` FOREIGN KEY (`tema_id`) REFERENCES `temas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtemas`
--

LOCK TABLES `subtemas` WRITE;
/*!40000 ALTER TABLE `subtemas` DISABLE KEYS */;
INSERT INTO `subtemas` VALUES (1,1,'P.A    P.C',0),(2,1,'MODELO 1              C - F - G',0),(3,1,'I LAVA YOU',0),(4,1,'MODELO 2               D - E - A',0),(5,1,'MODELO 3                  B',0),(6,1,'ACORDES               menores',0),(7,1,'ACORDES                      #',0),(8,1,'ACORDES                       #m',0),(9,1,'1.- MARCHA DE LOS GEMELOS',0),(10,1,'2.- FELICES NIÑOS',0),(11,1,'3.- ARRIBA  ABAJO',0),(12,1,'4.- LA SI DO',0),(13,1,'5.- LA ESCALA',0),(14,1,'6.- LOS NIÑOS Y EL POZO',0),(15,1,'7.- MISIFU',0),(16,1,'8.- EL PARTIDO DE PELOTA',0),(17,1,'9.- LA FINCA DE TOMAS',0),(18,1,'10.- JOSE SABE CABALGAR',0),(19,1,'11.- MARCHEMOS',0),(20,1,'12.- CANCION DE EL SOL',0),(21,1,'13.- REMAR',0),(22,1,'14.- CHINITA',0),(23,1,'15.-EL RELOJ',0),(24,1,'16.- MARI TIENE UN GRAN AMOR',0),(25,1,'17.- ESTRELLITA',0),(26,1,'18.- EL VIEJO SAN NICOLAS',0),(27,1,'19.- SANTA',0),(28,1,'20.- EL BANJO',0),(29,1,'21.- SEGUIMOS AL JEFE',0),(30,1,'22.- DIN DAN',0),(31,1,'23.- DUERME NIÑA',0),(32,1,'24.- PRACTICAR',0),(33,1,'25.- EL GORREON',0),(34,1,'26.- EL CARPINTERO',0),(35,1,'27.- DONDE ESTA MI PERRO',0),(36,2,'CALENTAMIENTO',0),(37,2,'ANATOMIA DE LA BATERIA',0),(38,2,'4TOS                  SIMPLE , DOBLE , FLAM',0),(39,2,'4TOS                   CORCHEA EN TOMS',0),(40,2,'4TOS                       C/R 4 TIEMPOS',0),(41,2,'4TOS                       C/R 2 TIEMPOS',0),(42,2,'8VOS                      C/R 4 TIEMPOS',0),(43,2,'8VOS                      C/R   2 TIEMPOS',0),(44,2,'SEMICORCHEA',0),(45,2,'4TOS                   C/R         SEMICORCHEA           4 TIEMPOS',0),(46,2,'4TOS                   C/R         SEMICORCHEA            2 TIEMPOS',0),(47,2,'8VOS                  C/R         SEMICORCHEA            4 TIEMPOS',0),(48,2,'8VOS                  C/R          SEMICORCHEA             2 TIEMPOS',0),(49,2,'4TOS                   C/R         SEMI Y CORCHEA         4 TIEMPOS',0),(50,2,'4TOS                   C/R         SEMI Y CORCHEA         2 TIEMPOS',0),(51,2,'8VOS                   C/R         SEMI Y CORCHEA          4 TIEMPOS',0),(52,2,'8VOS                   C/R         SEMI Y CORCHEA          2 TIEMPOS',0),(53,2,'TRESILLO',0),(54,2,'METRICA COMBINADA',0),(55,2,'VARIACION DE RITMO 1',0),(56,2,'VARIACION DE RITMO 2',0),(57,2,'VARIACION DE RITMO 3',0),(58,2,'VARIACION DE RITMO 4',0),(59,2,'VARIACION DE RITMO 5',0),(60,2,'VARIACION DE RITMO 6',0),(61,2,'VARIACION DE RITMO 7',0),(62,2,'VARIACION DE RITMO 8',0),(63,2,'VARIACION DE RITMO 9',0),(64,2,'VARIACION DE RITMO 10',0),(65,3,'ANATOMIA DE GUITARRA',0),(66,3,'A - E - D',0),(67,3,'G - C',0),(68,3,'Am - Em - Dm',0),(69,3,'D7 - G7',0),(70,3,'CIRCULO DE C',0),(71,3,'CIRCULO DE G',0),(72,3,'RITMOS',0),(73,3,'E - Em - E7',0),(74,3,'A - Am - A7',0),(75,3,'F - B',0),(76,3,'TABLA DE GRADOS ARMONICOS',0),(77,3,'GUITARRA POR NOTA',0),(78,3,'F - F# - G',0),(79,3,'A - A# - B',0),(80,3,'CIFRADO AMERICANO',0),(81,3,'CIRCULO DE A',0),(82,3,'CIRCULO DE E',0),(83,3,'CIRCULO DE D',0),(84,3,'CIRCULO DE F',0),(85,3,'CIRCULO DE B',0),(86,4,'ANATOMIA DE VIOLIN',1),(87,4,'EJERCICIO 1         E - A - D - G     REDONDA',2),(88,4,'EJERCICIO 2         E - A - D - G     BLANCAS',3),(89,4,'EJERCICIO 3         E - A - D - G      NEGRAS',4),(90,4,'EJERCICIO 4          E - A - D - G     CORCHEA',5),(91,4,'EJERCICIO 5            D  NEGRAS Y BLANCAS',6),(92,4,'EJERCICIO 6           D   NEGRAS Y SILENCIOS',7),(93,4,'EJERCICIO 7         E Y D',8),(94,4,'EL GRILLITO TOCA SU VIOLIN',9),(95,4,'A MI MONO LE GUSTA LA LECHUGA',10),(96,4,'MARIA TENIA UN PEQUEÑO CORDERO',11),(97,4,'EN FILA',12),(98,4,'BRINCANDO',13),(99,4,'ESTRELLITA',14);
/*!40000 ALTER TABLE `subtemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temas`
--

DROP TABLE IF EXISTS `temas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temas`
--

LOCK TABLES `temas` WRITE;
/*!40000 ALTER TABLE `temas` DISABLE KEYS */;
INSERT INTO `temas` VALUES (1,'PIANO 1'),(2,'DRUMS 1'),(3,'GUITARRA 1'),(4,'VIOLIN 1');
/*!40000 ALTER TABLE `temas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'academiamusica'
--

--
-- Dumping routines for database 'academiamusica'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-10 16:06:32
