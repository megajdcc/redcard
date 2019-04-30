/*
Navicat MySQL Data Transfer

Source Server         : mysql
Source Server Version : 100130
Source Host           : localhost:3306
Source Database       : redcard

Target Server Type    : MYSQL
Target Server Version : 100130
File Encoding         : 65001

Date: 2019-04-22 18:06:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for balancefranquiciatario
-- ----------------------------
DROP TABLE IF EXISTS `balancefranquiciatario`;
CREATE TABLE `balancefranquiciatario` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `id_franquiciatario` bigint(20) unsigned NOT NULL,
  `id_venta` bigint(20) DEFAULT '0',
  `comision` decimal(20,2) NOT NULL,
  `id_retiro` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_franquiciatario_balance` (`id_franquiciatario`),
  KEY `fk_retiro_fr` (`id_retiro`),
  CONSTRAINT `fk_franquiciatario_balance` FOREIGN KEY (`id_franquiciatario`) REFERENCES `franquiciatario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_retiro_fr` FOREIGN KEY (`id_retiro`) REFERENCES `retirocomisionfranquiciatario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for balancehotel
-- ----------------------------
DROP TABLE IF EXISTS `balancehotel`;
CREATE TABLE `balancehotel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `id_hotel` bigint(20) NOT NULL,
  `id_venta` bigint(20) DEFAULT '0',
  `comision` decimal(20,2) NOT NULL,
  `id_retiro` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_hotel_balance` (`id_hotel`),
  KEY `fk_retiro_retiro` (`id_retiro`),
  CONSTRAINT `fk_hotel_balance` FOREIGN KEY (`id_hotel`) REFERENCES `hotel` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_retiro_retiro` FOREIGN KEY (`id_retiro`) REFERENCES `retirocomision` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for balancereferidor
-- ----------------------------
DROP TABLE IF EXISTS `balancereferidor`;
CREATE TABLE `balancereferidor` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `id_referidor` bigint(20) unsigned NOT NULL,
  `id_venta` bigint(20) DEFAULT '0',
  `comision` decimal(20,2) NOT NULL,
  `id_retiro` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `balancereferidor_ibfk_1` (`id_referidor`),
  KEY `fk_retiro_ref` (`id_retiro`),
  CONSTRAINT `balancereferidor_ibfk_1` FOREIGN KEY (`id_referidor`) REFERENCES `referidor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_retiro_ref` FOREIGN KEY (`id_retiro`) REFERENCES `retirocomisionreferidor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ciudad
-- ----------------------------
DROP TABLE IF EXISTS `ciudad`;
CREATE TABLE `ciudad` (
  `id_ciudad` mediumint(8) unsigned NOT NULL,
  `ciudad` varchar(255) NOT NULL,
  `id_estado` smallint(5) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ciudad`),
  KEY `fk_ciudad_estado1_idx` (`id_estado`),
  CONSTRAINT `fk_ciudad_estado` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for codigo_administrador
-- ----------------------------
DROP TABLE IF EXISTS `codigo_administrador`;
CREATE TABLE `codigo_administrador` (
  `id_codigo` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `codigo_seguridad` varchar(255) NOT NULL,
  `situacion` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_codigo`),
  KEY `fk_codigo_administrador_usuario1_idx` (`id_usuario`),
  CONSTRAINT `fk_usuario_ca` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for datospagocomision
-- ----------------------------
DROP TABLE IF EXISTS `datospagocomision`;
CREATE TABLE `datospagocomision` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `banco` varchar(255) NOT NULL,
  `cuenta` varchar(255) NOT NULL,
  `clabe` bigint(30) NOT NULL,
  `swift` bigint(30) NOT NULL,
  `banco_tarjeta` varchar(255) NOT NULL,
  `numero_tarjeta` varchar(255) NOT NULL,
  `email_paypal` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for divisa
-- ----------------------------
DROP TABLE IF EXISTS `divisa`;
CREATE TABLE `divisa` (
  `iso` char(3) NOT NULL,
  `divisa` varchar(255) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`iso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for estado
-- ----------------------------
DROP TABLE IF EXISTS `estado`;
CREATE TABLE `estado` (
  `id_estado` smallint(5) unsigned NOT NULL,
  `estado` varchar(255) NOT NULL,
  `id_pais` smallint(5) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estado`),
  KEY `fk_estado_pais_idx` (`id_pais`),
  CONSTRAINT `fk_estado_paid` FOREIGN KEY (`id_pais`) REFERENCES `pais` (`id_pais`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for franquiciatario
-- ----------------------------
DROP TABLE IF EXISTS `franquiciatario`;
CREATE TABLE `franquiciatario` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `telefonofijo` varchar(100) NOT NULL,
  `telefonomovil` varchar(100) NOT NULL,
  `id_datospagocomision` int(20) NOT NULL,
  `comision` int(3) NOT NULL,
  `aprobada` tinyint(3) NOT NULL DEFAULT '0',
  `codigo_hotel` varchar(100) NOT NULL,
  `creado` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_dpc_franquiciatario` (`id_datospagocomision`),
  CONSTRAINT `fk_dpc_franquiciatario` FOREIGN KEY (`id_datospagocomision`) REFERENCES `datospagocomision` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for hotel
-- ----------------------------
DROP TABLE IF EXISTS `hotel`;
CREATE TABLE `hotel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(100) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `direccion` text NOT NULL,
  `latitud` decimal(19,15) NOT NULL,
  `longitud` decimal(19,15) NOT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `id_ciudad` mediumint(8) unsigned NOT NULL,
  `id_responsable_promocion` int(10) NOT NULL,
  `id_datospagocomision` int(20) NOT NULL,
  `codigo_postal` varchar(255) NOT NULL,
  `comision` int(3) NOT NULL DEFAULT '0',
  `aprobada` tinyint(1) NOT NULL DEFAULT '0',
  `id_iata` int(5) unsigned NOT NULL,
  PRIMARY KEY (`id`,`codigo`),
  KEY `fk_ciudad_hotel` (`id_ciudad`),
  KEY `fk_pagocomision_hotel` (`id_datospagocomision`),
  KEY `fk_iata-hotel` (`id_iata`),
  KEY `id` (`id`),
  KEY `fk_responsable_hotel` (`id_responsable_promocion`),
  KEY `codigo` (`codigo`),
  CONSTRAINT `fk_ciudad_hotel` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudad` (`id_ciudad`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_iata-hotel` FOREIGN KEY (`id_iata`) REFERENCES `iata` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pagocomision_hotel` FOREIGN KEY (`id_datospagocomision`) REFERENCES `datospagocomision` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_responsable_hotel` FOREIGN KEY (`id_responsable_promocion`) REFERENCES `responsableareapromocion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for huesped
-- ----------------------------
DROP TABLE IF EXISTS `huesped`;
CREATE TABLE `huesped` (
  `id` bigint(100) NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `telefono_movil` varchar(255) NOT NULL,
  `whatsapp` tinyint(1) NOT NULL DEFAULT '0',
  `hotel` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `fk_usuario_huesped` (`id_usuario`),
  CONSTRAINT `fk_usuario_huesped` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for huespedhotel
-- ----------------------------
DROP TABLE IF EXISTS `huespedhotel`;
CREATE TABLE `huespedhotel` (
  `id_huesped` bigint(100) NOT NULL,
  `id_hotel` bigint(20) NOT NULL,
  KEY `fk_hotel_huespedhotel` (`id_hotel`),
  KEY `fk_huesped_hotel` (`id_huesped`),
  CONSTRAINT `fk_hotel_huespedhotel` FOREIGN KEY (`id_hotel`) REFERENCES `hotel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_huesped_hotel` FOREIGN KEY (`id_huesped`) REFERENCES `huesped` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for iata
-- ----------------------------
DROP TABLE IF EXISTS `iata`;
CREATE TABLE `iata` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) NOT NULL DEFAULT '',
  `aeropuerto` varchar(255) NOT NULL,
  `id_ciudad` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_iata_ciudad` (`id_ciudad`),
  CONSTRAINT `fk_iata_ciudad` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudad` (`id_ciudad`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lista_deseos_certificado
-- ----------------------------
DROP TABLE IF EXISTS `lista_deseos_certificado`;
CREATE TABLE `lista_deseos_certificado` (
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_certificado` bigint(20) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`,`id_certificado`),
  KEY `fk_lista_deseos_certificado_certificado_idx` (`id_certificado`),
  KEY `fk_lista_deseos_certificado_usuario_idx` (`id_usuario`),
  CONSTRAINT `fk_deseo_certificado` FOREIGN KEY (`id_certificado`) REFERENCES `negocio_certificado` (`id_certificado`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_deseo` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for lista_deseos_producto
-- ----------------------------
DROP TABLE IF EXISTS `lista_deseos_producto`;
CREATE TABLE `lista_deseos_producto` (
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_producto` bigint(20) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_usuario_listadeseo` (`id_usuario`),
  KEY `fk_listadeseoproducto_producto` (`id_producto`),
  CONSTRAINT `fk_listadeseoproducto_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_listadeseo` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for movimiento_saldo
-- ----------------------------
DROP TABLE IF EXISTS `movimiento_saldo`;
CREATE TABLE `movimiento_saldo` (
  `id_movimiento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `cantidad` decimal(19,4) unsigned NOT NULL,
  `accion` tinyint(1) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  KEY `fk_usuario_movimiento` (`id_usuario`),
  KEY `fk_movimiento_negocio` (`id_negocio`),
  CONSTRAINT `fk_movimiento_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_movimiento` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio
-- ----------------------------
DROP TABLE IF EXISTS `negocio`;
CREATE TABLE `negocio` (
  `id_negocio` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `breve` varchar(255) NOT NULL,
  `id_categoria` tinyint(3) unsigned NOT NULL,
  `comision` tinyint(3) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `codigo_postal` varchar(255) NOT NULL,
  `id_ciudad` mediumint(8) unsigned NOT NULL,
  `latitud` decimal(19,15) NOT NULL,
  `longitud` decimal(19,15) NOT NULL,
  `vistas` bigint(20) unsigned NOT NULL DEFAULT '0',
  `saldo` decimal(19,4) NOT NULL DEFAULT '0.0000',
  `ultima_recarga` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_solicitud` bigint(20) unsigned NOT NULL,
  `situacion` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_negocio`),
  KEY `id_negocio` (`id_negocio`),
  KEY `fk_negocio_categoria` (`id_categoria`),
  KEY `fk_negocio_ciudad` (`id_ciudad`),
  KEY `fk_solitidud_negocio` (`id_solicitud`),
  CONSTRAINT `fk_negocio_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `negocio_categoria` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_negocio_ciudad` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudad` (`id_ciudad`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_solitidud_negocio` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud_negocio` (`id_solicitud`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_categoria
-- ----------------------------
DROP TABLE IF EXISTS `negocio_categoria`;
CREATE TABLE `negocio_categoria` (
  `id_categoria` tinyint(3) unsigned NOT NULL,
  `categoria` varchar(255) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`),
  KEY `id_categoria` (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_certificado
-- ----------------------------
DROP TABLE IF EXISTS `negocio_certificado`;
CREATE TABLE `negocio_certificado` (
  `id_certificado` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(19,4) unsigned NOT NULL,
  `iso` char(3) NOT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `condiciones` text,
  `restricciones` text,
  `disponibles` smallint(5) unsigned DEFAULT NULL,
  `situacion` tinyint(1) NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_certificado`),
  KEY `fk_certificado_venta` (`iso`),
  KEY `id_certificado` (`id_certificado`),
  KEY `fk_certificado_negocio` (`id_negocio`),
  CONSTRAINT `fk_certificado_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_certificado_venta` FOREIGN KEY (`iso`) REFERENCES `divisa` (`iso`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_email
-- ----------------------------
DROP TABLE IF EXISTS `negocio_email`;
CREATE TABLE `negocio_email` (
  `id_email` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_email`),
  KEY `fk_negocio_email` (`id_negocio`),
  CONSTRAINT `fk_negocio_email` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_empleado
-- ----------------------------
DROP TABLE IF EXISTS `negocio_empleado`;
CREATE TABLE `negocio_empleado` (
  `id_negocio` bigint(20) unsigned NOT NULL,
  `id_empleado` bigint(20) unsigned NOT NULL,
  `id_rol` tinyint(3) unsigned NOT NULL,
  `codigo_seguridad` varchar(255) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_usuario_negocioempleado` (`id_empleado`),
  KEY `fk_negocioempleado_negocio` (`id_negocio`),
  KEY `fk_negocioempleado_roles` (`id_rol`),
  CONSTRAINT `fk_negocioempleado_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_negocioempleado_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_negocioempleado` FOREIGN KEY (`id_empleado`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_evento
-- ----------------------------
DROP TABLE IF EXISTS `negocio_evento`;
CREATE TABLE `negocio_evento` (
  `id_evento` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `imagen` varchar(255) NOT NULL,
  `situacion` tinyint(1) NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `fk_negocio_evento` (`id_negocio`),
  CONSTRAINT `fk_negocio_evento` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_horario
-- ----------------------------
DROP TABLE IF EXISTS `negocio_horario`;
CREATE TABLE `negocio_horario` (
  `id_horario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `dia` tinyint(1) unsigned NOT NULL,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_horario`),
  KEY `fk_negocio_horario` (`id_negocio`),
  CONSTRAINT `fk_negocio_horario` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_imagen
-- ----------------------------
DROP TABLE IF EXISTS `negocio_imagen`;
CREATE TABLE `negocio_imagen` (
  `id_imagen` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `situacion` tinyint(1) NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_imagen`),
  KEY `fk_negocio_imagen` (`id_negocio`),
  CONSTRAINT `fk_negocio_imagen` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_preferencia
-- ----------------------------
DROP TABLE IF EXISTS `negocio_preferencia`;
CREATE TABLE `negocio_preferencia` (
  `id_negocio` bigint(20) unsigned NOT NULL,
  `id_preferencia` tinyint(3) unsigned NOT NULL,
  `preferencia` text NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_np_preferencia` (`id_preferencia`),
  KEY `fk_preferencia_negocio` (`id_negocio`),
  CONSTRAINT `fk_np_preferencia` FOREIGN KEY (`id_preferencia`) REFERENCES `preferencia` (`id_preferencia`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_preferencia_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_publicacion
-- ----------------------------
DROP TABLE IF EXISTS `negocio_publicacion`;
CREATE TABLE `negocio_publicacion` (
  `id_publicacion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `situacion` tinyint(1) NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_publicacion`),
  KEY `fk_publicacion_negocio` (`id_negocio`),
  CONSTRAINT `fk_publicacion_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_telefono
-- ----------------------------
DROP TABLE IF EXISTS `negocio_telefono`;
CREATE TABLE `negocio_telefono` (
  `id_telefono` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_telefono`),
  KEY `fk_telefono_negocio` (`id_negocio`),
  CONSTRAINT `fk_telefono_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for negocio_venta
-- ----------------------------
DROP TABLE IF EXISTS `negocio_venta`;
CREATE TABLE `negocio_venta` (
  `id_venta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `id_empleado` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `iso` char(3) NOT NULL,
  `venta` decimal(19,4) unsigned NOT NULL,
  `comision` tinyint(3) unsigned NOT NULL,
  `bono_esmarties` decimal(19,4) unsigned NOT NULL,
  `bono_referente` decimal(19,4) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venta`),
  KEY `id_venta` (`id_venta`),
  KEY `fk_venta_iso` (`iso`),
  KEY `fk_usuario_venta` (`id_empleado`),
  KEY `fk_venta_empleado` (`id_usuario`),
  KEY `fk_venta_negocio` (`id_negocio`),
  CONSTRAINT `fk_usuario_venta` FOREIGN KEY (`id_empleado`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_venta_empleado` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_venta_iso` FOREIGN KEY (`iso`) REFERENCES `divisa` (`iso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_venta_negocio` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for opinion
-- ----------------------------
DROP TABLE IF EXISTS `opinion`;
CREATE TABLE `opinion` (
  `id_opinion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_venta` bigint(20) unsigned NOT NULL,
  `opinion` text NOT NULL,
  `calificacion_servicio` tinyint(1) unsigned NOT NULL,
  `calificacion_producto` tinyint(1) unsigned NOT NULL,
  `calificacion_ambiente` tinyint(1) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_opinion`),
  KEY `fk_opinion_venta` (`id_venta`),
  CONSTRAINT `fk_opinion_venta` FOREIGN KEY (`id_venta`) REFERENCES `negocio_venta` (`id_venta`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for pais
-- ----------------------------
DROP TABLE IF EXISTS `pais`;
CREATE TABLE `pais` (
  `id_pais` smallint(5) unsigned NOT NULL,
  `codigo` varchar(3) NOT NULL,
  `pais` varchar(255) NOT NULL,
  `lada` int(11) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pais`),
  KEY `id_pais` (`id_pais`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for persona
-- ----------------------------
DROP TABLE IF EXISTS `persona`;
CREATE TABLE `persona` (
  `id` bigint(17) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for preferencia
-- ----------------------------
DROP TABLE IF EXISTS `preferencia`;
CREATE TABLE `preferencia` (
  `id_preferencia` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `llave` varchar(255) NOT NULL,
  `preferencia` varchar(255) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_preferencia`),
  KEY `id_preferencia` (`id_preferencia`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for producto
-- ----------------------------
DROP TABLE IF EXISTS `producto`;
CREATE TABLE `producto` (
  `id_producto` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `id_categoria` tinyint(3) unsigned NOT NULL,
  `precio` decimal(19,4) NOT NULL,
  `disponibles` smallint(5) unsigned NOT NULL,
  `envio` decimal(19,4) DEFAULT NULL,
  `condiciones` text,
  `imagen` varchar(255) NOT NULL,
  `cupon` varchar(255) DEFAULT NULL,
  `situacion` tinyint(1) NOT NULL DEFAULT '1',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  KEY `id_producto` (`id_producto`),
  KEY `fk_producto_categoria` (`id_categoria`),
  CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `producto_categoria` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for producto_categoria
-- ----------------------------
DROP TABLE IF EXISTS `producto_categoria`;
CREATE TABLE `producto_categoria` (
  `id_categoria` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `categoria` varchar(255) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`),
  KEY `id_categoria` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for recomendar_negocio
-- ----------------------------
DROP TABLE IF EXISTS `recomendar_negocio`;
CREATE TABLE `recomendar_negocio` (
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_usuario_recomendar` (`id_usuario`),
  KEY `fk_negocio_recomendar` (`id_negocio`),
  CONSTRAINT `fk_negocio_recomendar` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_recomendar` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for referidor
-- ----------------------------
DROP TABLE IF EXISTS `referidor`;
CREATE TABLE `referidor` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `telefonofijo` varchar(100) NOT NULL,
  `telefonomovil` varchar(100) NOT NULL,
  `id_datospagocomision` int(20) NOT NULL,
  `comision` int(3) NOT NULL,
  `codigo_hotel` varchar(100) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `aprobada` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_dpc_referidor` (`id_datospagocomision`),
  CONSTRAINT `fk_dpc_referidor` FOREIGN KEY (`id_datospagocomision`) REFERENCES `datospagocomision` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for responsableareapromocion
-- ----------------------------
DROP TABLE IF EXISTS `responsableareapromocion`;
CREATE TABLE `responsableareapromocion` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cargo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono_fijo` varchar(255) NOT NULL,
  `telefono_movil` varchar(255) NOT NULL,
  `dni_persona` bigint(17) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_responsable_persona` (`dni_persona`),
  CONSTRAINT `fk_responsable_persona` FOREIGN KEY (`dni_persona`) REFERENCES `persona` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for retiro
-- ----------------------------
DROP TABLE IF EXISTS `retiro`;
CREATE TABLE `retiro` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `creado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `actualizado` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `aprobado` tinyint(1) NOT NULL DEFAULT '0',
  `mensaje` text,
  `id_usuario_solicitud` bigint(20) unsigned NOT NULL,
  `id_usuario_aprobacion` bigint(20) unsigned DEFAULT NULL,
  `recibo` varchar(255) DEFAULT NULL,
  `monto` decimal(50,2) NOT NULL,
  `id_referidor` bigint(20) unsigned DEFAULT NULL,
  `id_franquiciatario` bigint(20) unsigned DEFAULT NULL,
  `id_hotel` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_referidor_solicit` (`id_referidor`) USING BTREE,
  KEY `fk_franquiciatario_retiro` (`id_franquiciatario`) USING BTREE,
  KEY `fk_hotel_retiro` (`id_hotel`) USING BTREE,
  KEY `fk_usuario_solicitu` (`id_usuario_solicitud`),
  KEY `fk_usuario_aprobacion` (`id_usuario_aprobacion`),
  CONSTRAINT `fk_franquiciatario_retiro` FOREIGN KEY (`id_franquiciatario`) REFERENCES `franquiciatario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_hotel_retiro` FOREIGN KEY (`id_hotel`) REFERENCES `hotel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_referidor_solicit` FOREIGN KEY (`id_referidor`) REFERENCES `referidor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_aprobacion` FOREIGN KEY (`id_usuario_aprobacion`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_solicitu` FOREIGN KEY (`id_usuario_solicitud`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for retirocomision
-- ----------------------------
DROP TABLE IF EXISTS `retirocomision`;
CREATE TABLE `retirocomision` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `negocio` varchar(255) NOT NULL DEFAULT '',
  `usuario` varchar(255) NOT NULL DEFAULT '',
  `id_retiro` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_retiro_comisin` (`id_retiro`),
  CONSTRAINT `fk_retiro_comisin` FOREIGN KEY (`id_retiro`) REFERENCES `retiro` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for retirocomisionfranquiciatario
-- ----------------------------
DROP TABLE IF EXISTS `retirocomisionfranquiciatario`;
CREATE TABLE `retirocomisionfranquiciatario` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `negocio` varchar(255) NOT NULL DEFAULT '',
  `usuario` varchar(255) NOT NULL DEFAULT '',
  `id_retiro` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_retiro_comisin` (`id_retiro`),
  CONSTRAINT `fk_retiro_franq` FOREIGN KEY (`id_retiro`) REFERENCES `retiro` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for retirocomisionreferidor
-- ----------------------------
DROP TABLE IF EXISTS `retirocomisionreferidor`;
CREATE TABLE `retirocomisionreferidor` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `negocio` varchar(255) NOT NULL DEFAULT '',
  `usuario` varchar(255) NOT NULL DEFAULT '',
  `id_retiro` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_retiro_comisin` (`id_retiro`),
  CONSTRAINT `fk_reti_ref` FOREIGN KEY (`id_retiro`) REFERENCES `retiro` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `llave` varchar(255) NOT NULL,
  `rol` varchar(255) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rol`),
  KEY `id_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for seguir_negocio
-- ----------------------------
DROP TABLE IF EXISTS `seguir_negocio`;
CREATE TABLE `seguir_negocio` (
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_negocio` bigint(20) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_usuario_seguirnegocio` (`id_usuario`),
  KEY `fk_negocio_seguir` (`id_negocio`),
  CONSTRAINT `fk_negocio_seguir` FOREIGN KEY (`id_negocio`) REFERENCES `negocio` (`id_negocio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_seguirnegocio` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for solicitudfr
-- ----------------------------
DROP TABLE IF EXISTS `solicitudfr`;
CREATE TABLE `solicitudfr` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_franquiciatario` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `condicion` tinyint(1) NOT NULL DEFAULT '0',
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `comentario` text,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_solicitudfr` (`id_usuario`),
  KEY `fk_franquiciatario_solicitud` (`id_franquiciatario`),
  CONSTRAINT `fk_franquiciatario_solicitud` FOREIGN KEY (`id_franquiciatario`) REFERENCES `franquiciatario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_solicitudfr` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for solicitudhotel
-- ----------------------------
DROP TABLE IF EXISTS `solicitudhotel`;
CREATE TABLE `solicitudhotel` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `id_hotel` bigint(20) NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `comentario` text,
  `condicion` tinyint(1) NOT NULL DEFAULT '0',
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_solicitud_hotel` (`id_usuario`),
  KEY `fk_hotel_solicitudhotel` (`id_hotel`),
  CONSTRAINT `fk_hotel_solicitudhotel` FOREIGN KEY (`id_hotel`) REFERENCES `hotel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_solicitud_hotel` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for solicitudreferidor
-- ----------------------------
DROP TABLE IF EXISTS `solicitudreferidor`;
CREATE TABLE `solicitudreferidor` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_referidor` bigint(20) unsigned NOT NULL,
  `condicion` tinyint(1) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `comentario` text,
  PRIMARY KEY (`id`),
  KEY `dk_usuario_sreferidor` (`id_usuario`),
  KEY `fk_referidor_solicitud` (`id_referidor`),
  CONSTRAINT `dk_usuario_sreferidor` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_referidor_solicitud` FOREIGN KEY (`id_referidor`) REFERENCES `referidor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for solicitud_negocio
-- ----------------------------
DROP TABLE IF EXISTS `solicitud_negocio`;
CREATE TABLE `solicitud_negocio` (
  `id_solicitud` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `breve` varchar(255) NOT NULL,
  `id_categoria` tinyint(3) unsigned NOT NULL,
  `comision` tinyint(3) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `codigo_postal` varchar(255) NOT NULL,
  `id_ciudad` mediumint(8) unsigned NOT NULL,
  `latitud` decimal(19,15) NOT NULL,
  `longitud` decimal(19,15) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `situacion` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `comentario` text,
  `mostrar_usuario` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_solicitud`),
  KEY `id_solicitud` (`id_solicitud`),
  KEY `fk_solicitud_categoria` (`id_categoria`),
  KEY `fk_solicitud_ciudad` (`id_ciudad`),
  KEY `fk_usuario_solicitud` (`id_usuario`),
  CONSTRAINT `fk_solicitud_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `negocio_categoria` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_solicitud_ciudad` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudad` (`id_ciudad`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_solicitud` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for usar_certificado
-- ----------------------------
DROP TABLE IF EXISTS `usar_certificado`;
CREATE TABLE `usar_certificado` (
  `id_uso` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_certificado` bigint(20) unsigned NOT NULL,
  `situacion` tinyint(1) NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_uso`),
  KEY `fk_usuario_certificado` (`id_usuario`),
  KEY `fk_certificado_usar` (`id_certificado`),
  CONSTRAINT `fk_certificado_usar` FOREIGN KEY (`id_certificado`) REFERENCES `negocio_certificado` (`id_certificado`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_certificado` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for usuario
-- ----------------------------
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `esmarties` decimal(19,4) unsigned NOT NULL DEFAULT '0.0000',
  `imagen` varchar(255) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `sexo` tinyint(1) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `id_ciudad` mediumint(8) unsigned DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(255) DEFAULT NULL,
  `id_rol` tinyint(3) unsigned NOT NULL DEFAULT '8',
  `verificado` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hash_activacion` varchar(32) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  KEY `id_usuario` (`id_usuario`),
  KEY `fk_usuario_ciudad` (`id_ciudad`),
  KEY `fk_roles` (`id_rol`),
  CONSTRAINT `fk_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_ciudad` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudad` (`id_ciudad`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for usuario_preferencia
-- ----------------------------
DROP TABLE IF EXISTS `usuario_preferencia`;
CREATE TABLE `usuario_preferencia` (
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_preferencia` tinyint(3) unsigned NOT NULL,
  `preferencia` text NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_usuario_preferencia` (`id_usuario`),
  KEY `fk_preferenci_usuario` (`id_preferencia`),
  CONSTRAINT `fk_preferenci_usuario` FOREIGN KEY (`id_preferencia`) REFERENCES `preferencia` (`id_preferencia`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_preferencia` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for usuario_referencia
-- ----------------------------
DROP TABLE IF EXISTS `usuario_referencia`;
CREATE TABLE `usuario_referencia` (
  `id_usuario` bigint(20) unsigned NOT NULL,
  `id_nuevo_usuario` bigint(20) unsigned NOT NULL,
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `fk_usuario_referenca` (`id_usuario`),
  KEY `fk_usuario_newusuarior` (`id_nuevo_usuario`),
  CONSTRAINT `fk_usuario_newusuarior` FOREIGN KEY (`id_nuevo_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_referenca` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for venta_tienda
-- ----------------------------
DROP TABLE IF EXISTS `venta_tienda`;
CREATE TABLE `venta_tienda` (
  `id_venta` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_producto` bigint(20) unsigned NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `precio` decimal(19,4) NOT NULL,
  `entrega` tinyint(1) unsigned NOT NULL,
  `situacion` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `creado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venta`),
  KEY `fk_usuario_ventatienda` (`id_usuario`),
  KEY `fke_producto` (`id_producto`),
  CONSTRAINT `fk_usuario_ventatienda` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fke_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- View structure for listarhoteleforhuesped
-- ----------------------------
DROP VIEW IF EXISTS `listarhoteleforhuesped`;
CREATE VIEW `listarhoteleforhuesped` AS select h.id as id_hotel , h.nombre, h.direccion, CONCAT(c.ciudad,' ',e.estado,' ',p.pais) as ubicacion from hotel as h 
			inner join ciudad as c on h.id_ciudad = c.id_ciudad 
			inner join estado as e on c.id_estado = e.id_estado
			inner join pais as p on e.id_pais = p.id_pais ;

-- ----------------------------
-- View structure for listarhoteles
-- ----------------------------
DROP VIEW IF EXISTS `listarhoteles`;
CREATE VIEW `listarhoteles` AS select h.direccion as direccionhotel, e.estado,p.pais,c.ciudad, h.id, h.nombre as hotel, h.codigo, CONCAT(h.direccion,' ',c.ciudad,' ',e.estado,' ',p.pais) as direccion, h.sitio_web
		from hotel as h join ciudad as c on h.id_ciudad = c.id_ciudad 
						join estado as e on c.id_estado = e.id_estado	
						join pais as p on e.id_pais = p.id_pais
		 order by h.nombre desc ;

-- ----------------------------
-- View structure for listarusuariosperfiles
-- ----------------------------
DROP VIEW IF EXISTS `listarusuariosperfiles`;
CREATE  VIEW `listarusuariosperfiles` AS (select sh.condicion,u.username,u.telefono,u.ultimo_login,u.email,'Hotel' as proviene, sh.id as nrosolicitud,u.imagen, u.nombre, u.apellido,h.comision
 from solicitudhotel as sh join usuario as u on sh.id_usuario = u.id_usuario join hotel as h on sh.id_hotel = h.id where sh.condicion = 1)
	UNION 
(select sfr.condicion,u.username,u.telefono,u.ultimo_login, u.email, 'Franquiciatario' as proviene, sfr.id as nrosolicitud,u.imagen, u.nombre, u.apellido, f.comision
	from solicitudfr as sfr join usuario as u on sfr.id_usuario = u.id_usuario
	join franquiciatario as f on sfr.id_franquiciatario = f.id where sfr.condicion = 1)
	UNION
(select sr.condicion,u.username,u.telefono,u.ultimo_login, u.email,'Referidor' as proviene, sr.id as nrosolicitud,u.imagen, u.nombre, u.apellido,r.comision
 from solicitudreferidor as sr join usuario as u on sr.id_usuario = u.id_usuario join referidor as r on sr.id_referidor = r.id
	where sr.condicion = 1) ;

-- ----------------------------
-- View structure for solicitudretiros
-- ----------------------------
DROP VIEW IF EXISTS `solicitudretiros`;
CREATE VIEW `solicitudretiros` AS (select count(r.id) as retiros from retiro as r join hotel as h on r.id_hotel = h.id
				where r.aprobado = 0)
union 
(SELECT count(r.id) as retiros from retiro as r join franquiciatario as f on r.id_franquiciatario = f.id where r.aprobado =0)
UNION 
(select count(r.id) as retiros from retiro as r join referidor as rf on r.id_referidor = rf.id where r.aprobado = 0) ;
DROP TRIGGER IF EXISTS `registrarcreado_copy`;
DELIMITER ;;
CREATE TRIGGER `registrarcreado_copy` BEFORE INSERT ON `balancefranquiciatario` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `registrarcreado`;
DELIMITER ;;
CREATE TRIGGER `registrarcreado` BEFORE INSERT ON `balancehotel` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `registrarcreado_copy_copy`;
DELIMITER ;;
CREATE TRIGGER `registrarcreado_copy_copy` BEFORE INSERT ON `balancereferidor` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `cargarcreado`;
DELIMITER ;;
CREATE TRIGGER `cargarcreado` BEFORE INSERT ON `franquiciatario` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `ingresar_creado`;
DELIMITER ;;
CREATE TRIGGER `ingresar_creado` BEFORE INSERT ON `referidor` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `ingreso_fecha`;
DELIMITER ;;
CREATE TRIGGER `ingreso_fecha` BEFORE INSERT ON `retiro` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `actualizado_fecha`;
DELIMITER ;;
CREATE TRIGGER `actualizado_fecha` BEFORE UPDATE ON `retiro` FOR EACH ROW set new.actualizado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `agregarfechainsert`;
DELIMITER ;;
CREATE TRIGGER `agregarfechainsert` BEFORE INSERT ON `solicitudfr` FOR EACH ROW begin
			set new.creado = now();
	end
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `agregarfechaupdate`;
DELIMITER ;;
CREATE TRIGGER `agregarfechaupdate` BEFORE UPDATE ON `solicitudfr` FOR EACH ROW begin
			set new.creado = now();
	end
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `insertar_creado`;
DELIMITER ;;
CREATE TRIGGER `insertar_creado` BEFORE INSERT ON `solicitudhotel` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `insertar_actualizado`;
DELIMITER ;;
CREATE TRIGGER `insertar_actualizado` BEFORE UPDATE ON `solicitudhotel` FOR EACH ROW set new.actualizado = now()
;;
DELIMITER ;
DROP TRIGGER IF EXISTS `ing_creado`;
DELIMITER ;;
CREATE TRIGGER `ing_creado` BEFORE INSERT ON `solicitudreferidor` FOR EACH ROW set new.creado = now()
;;
DELIMITER ;
