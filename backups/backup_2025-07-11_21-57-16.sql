-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: AcademiaMusica
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
-- Table structure for table `alumno_clases`
--

DROP TABLE IF EXISTS `alumno_clases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40000 ALTER TABLE `alumno_clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `alumnos` VALUES ('AIEC150601MCLVSRA0','CAROL NATALIA','AVILA ESPIRITU','esto es un ejemplo (borrar despues)','2015-06-01','8713049124','2025-07-10 01:52:39',0.00,1),('AITB140621MCLVTRA8','BARBARA','AVILA TATAY','','2014-06-21','8717102064','2025-07-10 00:20:21',0.00,1),('AITM161119MCLVTRA8','MARIA','AVILA TATAY',NULL,'2016-11-19','8717102064','2025-07-10 00:28:35',0.00,1),('AUVF180315MCLRLTA0','FATIMA','ARGUIJO VILLEGAS','','2018-03-15','8711763110','2025-07-10 20:52:11',850.00,1),('CAAM130925HCLBNTA4','MATEO ABDIEL','CABRERA ANDRADE','','2013-09-25','8713948779','2025-07-10 06:51:40',850.00,1),('CAMS031231HCLHRNA3','SANTIAGO DE JESUS','CHAVEZ MARTINEZ','','2003-12-31','8711357072','2025-07-10 06:53:40',760.00,1),('EISP080622MCLLRMA1','PAMELA','ELIAS SUAREZ',NULL,'2008-06-22','8712664224','2025-07-10 06:48:29',1200.00,0),('JIBA620910MCLMRN00','ANA MARIA','JIMENEZ BERUMEN','','1962-09-10','8717273118','2025-07-10 22:08:43',2000.00,1),('MAAC010901HCLRLHA2','CHRISTIAN EDUARDO','MARTELL ALVARADO','','2001-09-01','8712198061','2025-07-10 20:55:18',0.00,1),('MEML140402HCLNNSA1','LUIS EMILIO','MENA MONTEMAYOR',NULL,'2014-04-02','8713820147','2025-07-10 06:40:22',850.00,1),('MEMT170125HCLNNHA9','THIAGO','MENA MONTEMAYOR','','2017-01-25','8713820147','2025-07-10 06:43:11',850.00,1),('MOCM591215HCLRNR09','MARIO ALBERTO','MORALES CANTU','','1959-12-15','8712112497','2025-07-10 22:07:01',2000.00,1),('MUGA150502MCLXRLA8','ALEJANDRA','MUÑOZ GUERRERO','','2015-05-02','8712307080','2025-07-10 22:10:49',850.00,1),('PEPS110620HCLRRNA3','SANTIAGO','PEREZ GAVILAN PRADO',NULL,'2011-06-20','8713247316','2025-07-10 20:57:35',850.00,1),('PUEJ140618HCLNSNA6','JUAN MANUEL','PUENTES ESTRADA','','2014-06-18','8713422682','2025-07-10 06:46:02',850.00,1);
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos_subsubtema_progreso`
--

DROP TABLE IF EXISTS `alumnos_subsubtema_progreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumnos_subsubtema_progreso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumnos_tema_id` int(11) DEFAULT NULL,
  `subsubtema_id` int(11) DEFAULT NULL,
  `dia1` tinyint(4) DEFAULT 0,
  `dia1_comentario` varchar(255) DEFAULT NULL,
  `dia1_fecha` datetime DEFAULT NULL,
  `dia6` tinyint(4) DEFAULT 0,
  `dia6_comentario` varchar(255) DEFAULT NULL,
  `dia6_fecha` datetime DEFAULT NULL,
  `aprendido` tinyint(4) DEFAULT 0,
  `aprendido_comentario` varchar(255) DEFAULT NULL,
  `aprendido_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_subsubtema_progreso`
--

LOCK TABLES `alumnos_subsubtema_progreso` WRITE;
/*!40000 ALTER TABLE `alumnos_subsubtema_progreso` DISABLE KEYS */;
/*!40000 ALTER TABLE `alumnos_subsubtema_progreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos_subtema_progreso`
--

DROP TABLE IF EXISTS `alumnos_subtema_progreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=757 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_subtema_progreso`
--

LOCK TABLES `alumnos_subtema_progreso` WRITE;
/*!40000 ALTER TABLE `alumnos_subtema_progreso` DISABLE KEYS */;
INSERT INTO `alumnos_subtema_progreso` VALUES (583,26,36,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(584,26,37,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(585,26,38,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(586,26,39,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(587,26,40,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(588,26,41,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(589,26,42,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(590,26,43,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(591,26,44,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(592,26,45,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(593,26,46,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(594,26,47,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(595,26,48,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(596,26,49,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(597,26,50,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(598,26,51,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(599,26,52,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(600,26,53,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(601,26,54,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(602,26,55,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(603,26,56,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(604,26,57,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(605,26,58,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(606,26,59,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(607,26,60,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(608,26,61,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(609,26,62,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(610,26,63,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(611,26,64,0,0,0,0,0,0,0,'','','','','','','',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `alumnos_subtema_progreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos_tema`
--

DROP TABLE IF EXISTS `alumnos_tema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_tema`
--

LOCK TABLES `alumnos_tema` WRITE;
/*!40000 ALTER TABLE `alumnos_tema` DISABLE KEYS */;
INSERT INTO `alumnos_tema` VALUES (4,'AITM161119MCLVTRA8',2,'2025-07-10 00:36:37'),(5,'AIEC150601MCLVSRA0',2,'2025-07-10 01:53:12'),(12,'MEML140402HCLNNSA1',2,'2025-07-10 07:07:45'),(19,'PEPS110620HCLRRNA3',2,'2025-07-10 22:12:41'),(26,'EISP080622MCLLRMA1',2,'2025-07-11 19:43:45'),(33,'AITB140621MCLVTRA8',2,'2025-07-11 19:52:53'),(35,'AUVF180315MCLRLTA0',2,'2025-07-11 19:53:05'),(36,'PUEJ140618HCLNSNA6',2,'2025-07-11 19:53:13'),(37,'MUGA150502MCLXRLA8',2,'2025-07-11 19:53:24'),(38,'MOCM591215HCLRNR09',2,'2025-07-11 19:53:32'),(39,'MEMT170125HCLNNHA9',2,'2025-07-11 19:53:36'),(40,'CAAM130925HCLBNTA4',2,'2025-07-11 19:54:16'),(41,'CAMS031231HCLHRNA3',2,'2025-07-11 19:54:21'),(42,'MAAC010901HCLRLHA2',2,'2025-07-11 19:54:26'),(43,'JIBA620910MCLMRN00',2,'2025-07-11 19:54:29');
/*!40000 ALTER TABLE `alumnos_tema` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_curp` varchar(18) DEFAULT NULL,
  `clase_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` enum('asistio','justifico','falto') DEFAULT NULL,
  `registrado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_asistencia` (`alumno_curp`,`clase_id`,`fecha`),
  KEY `clase_id` (`clase_id`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`alumno_curp`) REFERENCES `alumnos` (`curp`),
  CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (1,'MEMT170125HCLNNHA9',6,'2025-07-11','falto','2025-07-11 19:51:29'),(2,'AUVF180315MCLRLTA0',5,'2025-07-11','falto','2025-07-11 19:51:27'),(3,'AITB140621MCLVTRA8',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(4,'AIEC150601MCLVSRA0',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(5,'MAAC010901HCLRLHA2',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(6,'MEML140402HCLNNSA1',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(7,'AITM161119MCLVTRA8',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(8,'PEPS110620HCLRRNA3',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(9,'MAAC010901HCLRLHA2',3,'2025-07-11','asistio','2025-07-11 17:32:12'),(10,'MOCM591215HCLRNR09',3,'2025-07-11','asistio','2025-07-11 17:32:12'),(11,'MUGA150502MCLXRLA8',1,'2025-07-11','asistio','2025-07-11 17:32:15'),(12,'JIBA620910MCLMRN00',1,'2025-07-11','asistio','2025-07-11 17:32:15'),(13,'AITB140621MCLVTRA8',1,'2025-07-11','asistio','2025-07-11 17:32:15'),(14,'AUVF180315MCLRLTA0',1,'2025-07-11','asistio','2025-07-11 17:32:15'),(15,'PUEJ140618HCLNSNA6',1,'2025-07-11','asistio','2025-07-11 17:32:15'),(16,'CAMS031231HCLHRNA3',1,'2025-07-11','asistio','2025-07-11 17:32:15'),(17,'CAAM130925HCLBNTA4',4,'2025-07-11','asistio','2025-07-11 17:32:18'),(20,'MUGA150502MCLXRLA8',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(21,'JIBA620910MCLMRN00',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(25,'AUVF180315MCLRLTA0',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(26,'PUEJ140618HCLNSNA6',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(29,'MOCM591215HCLRNR09',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(30,'CAAM130925HCLBNTA4',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(32,'CAMS031231HCLHRNA3',2,'2025-07-11','asistio','2025-07-11 19:54:50'),(33,'MEMT170125HCLNNHA9',2,'2025-07-11','asistio','2025-07-11 19:54:50');
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clases`
--

DROP TABLE IF EXISTS `clases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clases`
--

LOCK TABLES `clases` WRITE;
/*!40000 ALTER TABLE `clases` DISABLE KEYS */;
INSERT INTO `clases` VALUES (1,'BATERIA'),(6,'CANTO'),(5,'DIBUJO'),(4,'GUITARRA'),(2,'PIANO'),(3,'VIOLIN');
/*!40000 ALTER TABLE `clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos_mensualidad`
--

DROP TABLE IF EXISTS `pagos_mensualidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_mensualidad`
--

LOCK TABLES `pagos_mensualidad` WRITE;
/*!40000 ALTER TABLE `pagos_mensualidad` DISABLE KEYS */;
INSERT INTO `pagos_mensualidad` VALUES (5,'MUGA150502MCLXRLA8',2025,7,'2025-07-11 06:48:04',150.00),(6,'JIBA620910MCLMRN00',2025,7,'2025-07-11 18:11:33',1500.00);
/*!40000 ALTER TABLE `pagos_mensualidad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subsubtemas`
--

DROP TABLE IF EXISTS `subsubtemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subsubtemas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subtema_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `subtema_id` (`subtema_id`),
  CONSTRAINT `subsubtemas_ibfk_1` FOREIGN KEY (`subtema_id`) REFERENCES `subtemas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subsubtemas`
--

LOCK TABLES `subsubtemas` WRITE;
/*!40000 ALTER TABLE `subsubtemas` DISABLE KEYS */;
INSERT INTO `subsubtemas` VALUES (1,92,'guy',1),(2,92,'guy',2),(18,35,'que',1),(19,35,'insano',2),(20,35,'soy',3),(24,36,'1',1),(25,36,'2',2),(26,36,'3',3),(27,103,'SUB-SUB-TEMA1',1),(28,103,'SUB-SUB-TEMA2',2),(29,103,'SUB-SUB-TEMA3',3);
/*!40000 ALTER TABLE `subsubtemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subtemas`
--

DROP TABLE IF EXISTS `subtemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subtemas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tema_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `tema_id` (`tema_id`),
  CONSTRAINT `subtemas_ibfk_1` FOREIGN KEY (`tema_id`) REFERENCES `temas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtemas`
--

LOCK TABLES `subtemas` WRITE;
/*!40000 ALTER TABLE `subtemas` DISABLE KEYS */;
INSERT INTO `subtemas` VALUES (1,1,'P.A    P.C',1),(2,1,'MODELO 1              C - F - G',2),(3,1,'I LAVA YOU',3),(4,1,'MODELO 2               D - E - A',4),(5,1,'MODELO 3                  B',5),(6,1,'ACORDES               menores',6),(7,1,'ACORDES                      #',7),(8,1,'ACORDES                       #m',8),(9,1,'1.- MARCHA DE LOS GEMELOS',9),(10,1,'2.- FELICES NIÑOS',10),(11,1,'3.- ARRIBA  ABAJO',11),(12,1,'4.- LA SI DO',12),(13,1,'5.- LA ESCALA',13),(14,1,'6.- LOS NIÑOS Y EL POZO',14),(15,1,'7.- MISIFU',15),(16,1,'8.- EL PARTIDO DE PELOTA',16),(17,1,'9.- LA FINCA DE TOMAS',17),(18,1,'10.- JOSE SABE CABALGAR',18),(19,1,'11.- MARCHEMOS',19),(20,1,'12.- CANCION DE EL SOL',20),(21,1,'13.- REMAR',21),(22,1,'14.- CHINITA',22),(23,1,'15.-EL RELOJ',23),(24,1,'16.- MARI TIENE UN GRAN AMOR',24),(25,1,'17.- ESTRELLITA',25),(26,1,'18.- EL VIEJO SAN NICOLAS',26),(27,1,'19.- SANTA',27),(28,1,'20.- EL BANJO',28),(29,1,'21.- SEGUIMOS AL JEFE',29),(30,1,'22.- DIN DAN',30),(31,1,'23.- DUERME NIÑA',31),(32,1,'24.- PRACTICAR',32),(33,1,'25.- EL GORREON',33),(34,1,'26.- EL CARPINTERO',34),(35,1,'27.- DONDE ESTA MI PERRO',35),(36,2,'CALENTAMIENTO',1),(37,2,'ANATOMIA DE LA BATERIA',3),(38,2,'4TOS                  SIMPLE , DOBLE , FLAM',2),(39,2,'4TOS                   CORCHEA EN TOMS',4),(40,2,'4TOS                       C/R 4 TIEMPOS',5),(41,2,'4TOS                       C/R 2 TIEMPOS',6),(42,2,'8VOS                      C/R 4 TIEMPOS',7),(43,2,'8VOS                      C/R   2 TIEMPOS',8),(44,2,'SEMICORCHEA',9),(45,2,'4TOS                   C/R         SEMICORCHEA           4 TIEMPOS',10),(46,2,'4TOS                   C/R         SEMICORCHEA            2 TIEMPOS',11),(47,2,'8VOS                  C/R         SEMICORCHEA            4 TIEMPOS',12),(48,2,'8VOS                  C/R          SEMICORCHEA             2 TIEMPOS',13),(49,2,'4TOS                   C/R         SEMI Y CORCHEA         4 TIEMPOS',14),(50,2,'4TOS                   C/R         SEMI Y CORCHEA         2 TIEMPOS',15),(51,2,'8VOS                   C/R         SEMI Y CORCHEA          4 TIEMPOS',16),(52,2,'8VOS                   C/R         SEMI Y CORCHEA          2 TIEMPOS',17),(53,2,'TRESILLO',18),(54,2,'METRICA COMBINADA',19),(55,2,'VARIACION DE RITMO 1',20),(56,2,'VARIACION DE RITMO 2',21),(57,2,'VARIACION DE RITMO 3',22),(58,2,'VARIACION DE RITMO 4',23),(59,2,'VARIACION DE RITMO 5',24),(60,2,'VARIACION DE RITMO 6',25),(61,2,'VARIACION DE RITMO 7',26),(62,2,'VARIACION DE RITMO 8',27),(63,2,'VARIACION DE RITMO 9',28),(64,2,'VARIACION DE RITMO 10',29),(65,3,'ANATOMIA DE GUITARRA',1),(66,3,'A - E - D',2),(67,3,'G - C',3),(68,3,'Am - Em - Dm',4),(69,3,'D7 - G7',5),(70,3,'CIRCULO DE C',6),(71,3,'CIRCULO DE G',7),(72,3,'RITMOS',8),(73,3,'E - Em - E7',9),(74,3,'A - Am - A7',10),(75,3,'F - B',11),(76,3,'TABLA DE GRADOS ARMONICOS',12),(77,3,'GUITARRA POR NOTA',13),(78,3,'F - F# - G',14),(79,3,'A - A# - B',15),(80,3,'CIFRADO AMERICANO',16),(81,3,'CIRCULO DE A',17),(82,3,'CIRCULO DE E',18),(83,3,'CIRCULO DE D',19),(84,3,'CIRCULO DE F',20),(85,3,'CIRCULO DE B',21),(86,4,'ANATOMIA DE VIOLIN',1),(87,4,'EJERCICIO 1         E - A - D - G     REDONDA',2),(88,4,'EJERCICIO 2         E - A - D - G     BLANCAS',3),(89,4,'EJERCICIO 3         E - A - D - G      NEGRAS',4),(90,4,'EJERCICIO 4          E - A - D - G     CORCHEA',5),(91,4,'EJERCICIO 5            D  NEGRAS Y BLANCAS',6),(92,4,'EJERCICIO 6           D   NEGRAS Y SILENCIOS',7),(93,4,'EJERCICIO 7         E Y D',8),(94,4,'EL GRILLITO TOCA SU VIOLIN',9),(95,4,'A MI MONO LE GUSTA LA LECHUGA',10),(96,4,'MARIA TENIA UN PEQUEÑO CORDERO',11),(97,4,'EN FILA',12),(98,4,'BRINCANDO',13),(99,4,'ESTRELLITA',14),(103,7,'SUB-TEMA1',1),(104,7,'SUB-TEMA2',2);
/*!40000 ALTER TABLE `subtemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temas`
--

DROP TABLE IF EXISTS `temas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temas`
--

LOCK TABLES `temas` WRITE;
/*!40000 ALTER TABLE `temas` DISABLE KEYS */;
INSERT INTO `temas` VALUES (1,'PIANO 1'),(2,'DRUMS 1'),(3,'GUITARRA 1'),(4,'VIOLIN 1'),(5,'CANTO 1'),(6,'DIBUJO'),(7,'BAJO');
/*!40000 ALTER TABLE `temas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-11 13:57:16
