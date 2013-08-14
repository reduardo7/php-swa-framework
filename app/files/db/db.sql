-- MySQL dump 10.13  Distrib 5.5.32, for debian-linux-gnu (x86_64)
--
-- Host: mobile.dev.eduardocuomo.com.ar    Database: eduardoc_mobile
-- ------------------------------------------------------
-- Server version	5.1.70-cll

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avisos`
--

DROP TABLE IF EXISTS `avisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avisos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_comercio` bigint(20) NOT NULL,
  `id_categoria` bigint(20) NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `descripcion_corta` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL,
  `clicks` int(11) NOT NULL DEFAULT '0',
  `destacado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha` date DEFAULT NULL,
  `hora` decimal(4,2) unsigned zerofill DEFAULT NULL,
  `duracion` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_comercio` (`id_comercio`),
  KEY `activo` (`activo`),
  KEY `orden` (`orden`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `avisos_ibfk_1` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id`),
  CONSTRAINT `avisos_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avisos_busquedas`
--

DROP TABLE IF EXISTS `avisos_busquedas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avisos_busquedas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_aviso` bigint(20) NOT NULL,
  `ip` varchar(14) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_aviso` (`id_aviso`),
  CONSTRAINT `avisos_busquedas_ibfk_1` FOREIGN KEY (`id_aviso`) REFERENCES `avisos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avisos_clicks`
--

DROP TABLE IF EXISTS `avisos_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avisos_clicks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_aviso` bigint(20) NOT NULL,
  `ip` varchar(14) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_aviso` (`id_aviso`),
  CONSTRAINT `avisos_clicks_ibfk_1` FOREIGN KEY (`id_aviso`) REFERENCES `avisos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avisos_formas_pago`
--

DROP TABLE IF EXISTS `avisos_formas_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avisos_formas_pago` (
  `id` bigint(20) DEFAULT NULL,
  `id_aviso` bigint(20) NOT NULL,
  `id_forma_pago` bigint(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_aviso`,`id_forma_pago`),
  KEY `id_forma_pago` (`id_forma_pago`),
  CONSTRAINT `avisos_formas_pago_ibfk_1` FOREIGN KEY (`id_aviso`) REFERENCES `avisos` (`id`),
  CONSTRAINT `avisos_formas_pago_ibfk_2` FOREIGN KEY (`id_forma_pago`) REFERENCES `formas_pago` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulo` (`titulo`),
  KEY `activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Comidas',1),(2,'Servicios',1),(3,'Alquileres',1),(4,'Reparaciones',1),(5,'Arte',1),(14,'Internet',1),(15,'Otra Categoria',1),(16,'Otra categoria 2',1);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comercios`
--

DROP TABLE IF EXISTS `comercios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comercios` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) NOT NULL,
  `id_imagen` bigint(20) NOT NULL,
  `id_imagen_ubicacion` bigint(20) DEFAULT NULL,
  `nombre` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `apellido` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `nombre_comercio` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `calle` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `altura` int(11) NOT NULL,
  `piso` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `departamento` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `localidad` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `provincia` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `pais` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `codigo_postal` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `descripcion` varchar(512) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `cuit` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `url_sitio` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `cantidad_desktops` int(11) NOT NULL DEFAULT '0',
  `cantidad_avisos` int(11) NOT NULL DEFAULT '0',
  `destacado` tinyint(1) NOT NULL DEFAULT '0',
  `ultimo_destacado` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`,`id_imagen`),
  KEY `id_imagen` (`id_imagen`),
  KEY `activo` (`activo`),
  KEY `id_imagen_ubicacion` (`id_imagen_ubicacion`),
  KEY `destacado` (`destacado`),
  CONSTRAINT `comercios_ibfk_1` FOREIGN KEY (`id_imagen`) REFERENCES `imagenes` (`id`),
  CONSTRAINT `comercios_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `comercios_ibfk_4` FOREIGN KEY (`id_imagen_ubicacion`) REFERENCES `imagenes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comercios_redes_sociales`
--

DROP TABLE IF EXISTS `comercios_redes_sociales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comercios_redes_sociales` (
  `id_comercio` bigint(20) NOT NULL,
  `id_red_social` bigint(20) NOT NULL,
  `red_social_url` varchar(255) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `id_comercio_red_social` (`id_comercio`,`id_red_social`),
  KEY `id_red_social` (`id_red_social`),
  CONSTRAINT `comercios_redes_sociales_ibfk_1` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id`),
  CONSTRAINT `comercios_redes_sociales_ibfk_2` FOREIGN KEY (`id_red_social`) REFERENCES `redes_sociales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('DESTACADO_MAX_DURACION','5');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_version`
--

DROP TABLE IF EXISTS `database_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_version` (
  `version` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `executed_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_version`
--

LOCK TABLES `database_version` WRITE;
/*!40000 ALTER TABLE `database_version` DISABLE KEYS */;
INSERT INTO `database_version` VALUES (1,'Quienes Somos agrega ID','2013-05-13 00:35:22'),(2,'Quienes Somos ID 1','2013-05-13 00:35:22'),(3,'Limite Desktops y Avisos','2013-05-13 00:37:52'),(4,'Limite Desktops y Avisos = 5','2013-05-13 00:37:52'),(5,'ID Avisos Forma Pago','2013-05-17 02:51:30'),(6,'Comercio Destacado','2013-05-21 02:12:23'),(7,'Aviso Destacado','2013-05-28 02:58:29'),(8,'Config Table','2013-05-30 01:46:25'),(9,'Config Table','2013-05-30 01:46:25'),(10,'Config Table','2013-05-30 01:46:25'),(11,'Aviso Hora to DECIMAL','2013-05-31 02:04:06'),(12,'Aviso Destacado NULLs','2013-06-02 22:07:27'),(13,'Avisos no Destacados NULLs','2013-06-02 22:07:28'),(14,'Ultimo Aviso Destacado','2013-06-08 21:31:01'),(15,'Contador Reenvios Redes Sociales','2013-07-15 22:00:23'),(16,'Ultimo Aviso Destacado','2013-07-15 22:00:24'),(17,'Registro de Avisos en Busquedas','2013-07-25 23:56:46'),(18,'Registro de Avisos en Busquedas - FK','2013-07-25 23:56:47');
/*!40000 ALTER TABLE `database_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `desktops`
--

DROP TABLE IF EXISTS `desktops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `desktops` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_imagen` bigint(20) NOT NULL,
  `id_comercio` bigint(20) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_imagen` (`id_imagen`),
  KEY `orden` (`orden`),
  KEY `id_comercio` (`id_comercio`),
  CONSTRAINT `desktops_ibfk_1` FOREIGN KEY (`id_imagen`) REFERENCES `imagenes` (`id`),
  CONSTRAINT `desktops_ibfk_2` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formas_pago`
--

DROP TABLE IF EXISTS `formas_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formas_pago` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `id_imagen` bigint(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `titulo` (`titulo`),
  KEY `id_imagen` (`id_imagen`),
  KEY `activo` (`activo`),
  CONSTRAINT `formas_pago_ibfk_2` FOREIGN KEY (`id_imagen`) REFERENCES `imagenes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formas_pago`
--

LOCK TABLES `formas_pago` WRITE;
/*!40000 ALTER TABLE `formas_pago` DISABLE KEYS */;
INSERT INTO `formas_pago` VALUES (1,'Master Card','Master Card',3,1),(2,'Visa','Visa',4,1);
/*!40000 ALTER TABLE `formas_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagenes`
--

DROP TABLE IF EXISTS `imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imagenes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tipo` enum('url','file') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `src` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagenes`
--

LOCK TABLES `imagenes` WRITE;
/*!40000 ALTER TABLE `imagenes` DISABLE KEYS */;
INSERT INTO `imagenes` VALUES (1,'file','file1.jpg'),(2,'file','51bcc548d3dd30.39429796.jpg'),(3,'file','master.png'),(4,'file','visa.png'),(5,'file','falabella.jpg'),(6,'file','rever-pass.jpg'),(7,'file','facebook.png'),(8,'file','twitter.png'),(9,'file','linkedin.png'),(10,'file','51639a0075fa47.82570626.jpg'),(11,'file','logo.jpg'),(12,'file','local-1.jpg'),(13,'file','local-2.jpg'),(14,'file','local-3.jpg'),(15,'file','51c0f0bb9b5f44.05866495.jpg');
/*!40000 ALTER TABLE `imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quienes_somos`
--

DROP TABLE IF EXISTS `quienes_somos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quienes_somos` (
  `id` tinyint(1) NOT NULL,
  `id_imagen` bigint(20) NOT NULL,
  `nombre` varchar(255) COLLATE utf8_bin NOT NULL,
  `descripcion` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nombre` (`nombre`),
  KEY `id_imagen_fk` (`id_imagen`),
  CONSTRAINT `id_imagen_fk` FOREIGN KEY (`id_imagen`) REFERENCES `imagenes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quienes_somos`
--

LOCK TABLES `quienes_somos` WRITE;
/*!40000 ALTER TABLE `quienes_somos` DISABLE KEYS */;
INSERT INTO `quienes_somos` VALUES (1,11,'Nombre del Lugar','<b>Pellentesque</b> habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Mauris dictum scelerisque sodales. Curabitur ligula est, mattis a auctor id, sagittis in orci. In eget diam erat. Morbi commodo ipsum in libero suscipit placerat. Morbi et laoreet magna. Sed lobortis odio ut sapien scelerisque nec tristique nibh bibendum.\r\n\r\nCras sit amet est a massa placerat commodo ut in nibh. Sed pretium porta enim, et bibendum urna aliquet a. Nunc euismod arcu ut arcu vulputate elementum. Suspendisse dignissim viverra iaculis. Suspendisse potenti. Praesent viverra turpis vel augue laoreet rhoncus. Sed auctor pretium urna, vel posuere libero commodo ut.\r\n\r\nSuspendisse adipiscing interdum leo at auctor. Ut tincidunt est at elit blandit cursus. Integer pulvinar sollicitudin blandit. Ut tincidunt sapien ut nisi tincidunt dignissim. Donec porttitor dolor in urna pharetra congue. Vivamus vitae leo nisl, in sollicitudin eros. Nulla facilisi. Nam commodo ligula non magna scelerisque rhoncus. Nam at nunc ut nisi viverra suscipit. Ut sit amet turpis ac ligula iaculis ullamcorper. Duis condimentum ullamcorper vestibulum.\r\n\r\nNulla posuere sodales sem at pretium. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse augue justo, consectetur sed dignissim a, gravida in nisi. Nulla id congue orci. Sed dolor mi, rhoncus et varius a, mollis et enim. Etiam arcu nisi, volutpat vitae posuere fringilla, cursus at odio. Phasellus sodales libero et elit pulvinar tristique. Suspendisse potenti. Integer pulvinar elementum lacus, vehicula auctor felis interdum vitae. Sed non lectus enim. Sed pellentesque cursus elementum. Sed quis elit turpis, volutpat laoreet diam. Nullam dui ipsum, malesuada id vestibulum at, suscipit id nisi.');
/*!40000 ALTER TABLE `quienes_somos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redes_sociales`
--

DROP TABLE IF EXISTS `redes_sociales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `redes_sociales` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `id_imagen` bigint(20) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  UNIQUE KEY `url` (`url`),
  KEY `id_imagen` (`id_imagen`),
  KEY `activo` (`activo`),
  CONSTRAINT `redes_sociales_ibfk_1` FOREIGN KEY (`id_imagen`) REFERENCES `imagenes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `redes_sociales`
--

LOCK TABLES `redes_sociales` WRITE;
/*!40000 ALTER TABLE `redes_sociales` DISABLE KEYS */;
INSERT INTO `redes_sociales` VALUES (5,'Facebook',7,'http://www.facebook.com',1),(6,'Twitter',8,'http://www.twitter.com',1),(7,'Linkedin',9,'http://www.linkedin.com',1);
/*!40000 ALTER TABLE `redes_sociales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redes_sociales_redirect_count`
--

DROP TABLE IF EXISTS `redes_sociales_redirect_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `redes_sociales_redirect_count` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_red_social` bigint(20) NOT NULL,
  `id_comercio` bigint(20) NOT NULL,
  `ip` varchar(14) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_red_social` (`id_red_social`),
  KEY `redes_sociales_redirect_count_ibfk_2` (`id_comercio`),
  CONSTRAINT `redes_sociales_redirect_count_ibfk_1` FOREIGN KEY (`id_red_social`) REFERENCES `redes_sociales` (`id`),
  CONSTRAINT `redes_sociales_redirect_count_ibfk_2` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suscriptores`
--

DROP TABLE IF EXISTS `suscriptores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suscriptores` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `apellido` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `id_comercio` bigint(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_comercio` (`id_comercio`),
  KEY `activo` (`activo`),
  CONSTRAINT `suscriptores_ibfk_1` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `nombre` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tipo` tinyint(1) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tipo` (`tipo`),
  KEY `activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
  (1,'super@admin.com','1593','Super Admin',1,1),
  (2,'admin@admin.com','1593','Admin',2,1)
;
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-08-04 19:34:33
