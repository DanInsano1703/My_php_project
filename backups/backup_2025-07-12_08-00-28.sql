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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `mensualidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`curp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos`
--

LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;
INSERT INTO `alumnos` VALUES ('GEMA700512HDFNSR00','ayaka','kamisato','AYATO','2002-10-14','8713968800',1.00,1,'2025-07-11 21:34:57'),('GEMA700512HDFNSR08','daniel','KAMISATO','','2002-12-14','8713968800',1212.00,1,'2025-07-11 21:22:33');
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
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
  `dia1_comentario` text DEFAULT NULL,
  `dia1_fecha` datetime DEFAULT NULL,
  `dia2` tinyint(1) DEFAULT 0,
  `dia2_comentario` text DEFAULT NULL,
  `dia2_fecha` datetime DEFAULT NULL,
  `dia3` tinyint(1) DEFAULT 0,
  `dia3_comentario` text DEFAULT NULL,
  `dia3_fecha` datetime DEFAULT NULL,
  `dia4` tinyint(1) DEFAULT 0,
  `dia4_comentario` text DEFAULT NULL,
  `dia4_fecha` datetime DEFAULT NULL,
  `dia5` tinyint(1) DEFAULT 0,
  `dia5_comentario` text DEFAULT NULL,
  `dia5_fecha` datetime DEFAULT NULL,
  `dia6` tinyint(1) DEFAULT 0,
  `dia6_comentario` text DEFAULT NULL,
  `dia6_fecha` datetime DEFAULT NULL,
  `aprendido` tinyint(1) DEFAULT 0,
  `aprendido_comentario` text DEFAULT NULL,
  `aprendido_fecha` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alumnos_tema_id` (`alumnos_tema_id`),
  KEY `subtema_id` (`subtema_id`),
  CONSTRAINT `alumnos_subtema_progreso_ibfk_1` FOREIGN KEY (`alumnos_tema_id`) REFERENCES `alumnos_tema` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alumnos_subtema_progreso_ibfk_2` FOREIGN KEY (`subtema_id`) REFERENCES `subtemas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_subtema_progreso`
--

LOCK TABLES `alumnos_subtema_progreso` WRITE;
/*!40000 ALTER TABLE `alumnos_subtema_progreso` DISABLE KEYS */;
INSERT INTO `alumnos_subtema_progreso` VALUES (29,13,1,1,'hola','2025-07-12 07:36:58',0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,1,'hola','2025-07-12 07:36:58'),(30,13,2,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL),(31,13,3,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL),(32,14,4,1,'hola','2025-07-12 07:36:08',0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL),(33,14,5,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL),(34,15,1,1,'porfa jala','2025-07-12 07:36:58',0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL),(35,15,2,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL),(36,15,3,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,0,'',NULL,1,'si','2025-07-12 07:36:58');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_tema`
--

LOCK TABLES `alumnos_tema` WRITE;
/*!40000 ALTER TABLE `alumnos_tema` DISABLE KEYS */;
INSERT INTO `alumnos_tema` VALUES (13,'GEMA700512HDFNSR00',1,'2025-07-12 05:27:07'),(14,'GEMA700512HDFNSR00',2,'2025-07-12 05:27:07'),(15,'GEMA700512HDFNSR08',1,'2025-07-12 05:27:10');
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
  `tema_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `estado` enum('asistio','justifico','falto') DEFAULT NULL,
  `registrado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_asistencia` (`alumno_curp`,`tema_id`,`fecha`),
  KEY `tema_id` (`tema_id`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`alumno_curp`) REFERENCES `alumnos` (`curp`),
  CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`tema_id`) REFERENCES `temas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
INSERT INTO `asistencia` VALUES (1,'GEMA700512HDFNSR08',1,'2025-07-11','asistio','2025-07-12 05:35:23'),(2,'GEMA700512HDFNSR08',2,'2025-07-11','justifico','2025-07-11 21:26:11'),(3,'GEMA700512HDFNSR00',1,'2025-07-11','asistio','2025-07-12 05:35:23'),(5,'GEMA700512HDFNSR00',2,'2025-07-11','asistio','2025-07-12 05:34:51');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clases`
--

LOCK TABLES `clases` WRITE;
/*!40000 ALTER TABLE `clases` DISABLE KEYS */;
/*!40000 ALTER TABLE `clases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dias_sin_clase`
--

DROP TABLE IF EXISTS `dias_sin_clase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dias_sin_clase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clase_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `motivo` enum('cancelada','vacaciones') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clase_id` (`clase_id`,`fecha`),
  CONSTRAINT `dias_sin_clase_ibfk_1` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos_mensualidad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_curp` varchar(18) DEFAULT NULL,
  `año` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_curp` (`alumno_curp`,`año`,`mes`),
  CONSTRAINT `pagos_mensualidad_ibfk_1` FOREIGN KEY (`alumno_curp`) REFERENCES `alumnos` (`curp`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_mensualidad`
--

LOCK TABLES `pagos_mensualidad` WRITE;
/*!40000 ALTER TABLE `pagos_mensualidad` DISABLE KEYS */;
INSERT INTO `pagos_mensualidad` VALUES (2,'GEMA700512HDFNSR00',2025,7,'2025-07-11 22:14:01',1.00),(3,'GEMA700512HDFNSR00',2025,6,'2025-07-11 22:51:39',15000.00),(4,'GEMA700512HDFNSR08',2025,6,'2025-07-11 22:51:43',150.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subsubtemas`
--

LOCK TABLES `subsubtemas` WRITE;
/*!40000 ALTER TABLE `subsubtemas` DISABLE KEYS */;
INSERT INTO `subsubtemas` VALUES (1,1,'wdwd',1),(2,1,'wdwd',2);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subtemas`
--

LOCK TABLES `subtemas` WRITE;
/*!40000 ALTER TABLE `subtemas` DISABLE KEYS */;
INSERT INTO `subtemas` VALUES (1,1,'wdwdwd',1),(2,1,'ww',2),(3,1,'w',3),(4,2,'wdwd',1),(5,2,'wd',2);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temas`
--

LOCK TABLES `temas` WRITE;
/*!40000 ALTER TABLE `temas` DISABLE KEYS */;
INSERT INTO `temas` VALUES (1,'porfa jala'),(2,'si jalas 2');
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

-- Dump completed on 2025-07-12  0:00:28
