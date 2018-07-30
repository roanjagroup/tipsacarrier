<?php
/**
 * 2016-2018 TIPSA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <integraciones@tip-sa.com>
 *  @copyright 2016-2018 TIPSA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'tipsa_envios`;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tipsa_envios`(
          `id_envio` int(11) NOT NULL AUTO_INCREMENT,
          `id_envio_order` int(11) NOT NULL,
          `codigo_envio` varchar(50) NOT NULL,
          `url_track` varchar(255) NOT NULL,
          `num_albaran` varchar(100) NOT NULL,
          `codigo_barras` text,
          `fecha` datetime NOT NULL,
          `bultos` int(11) DEFAULT \'0\',
          `observation` varchar(250) DEFAULT NULL,
          PRIMARY KEY (`id_envio`),
          INDEX (`id_envio_order`),
          UNIQUE (`id_envio_order`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'tipsa_servicios`;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tipsa_servicios` (
            `code` varchar(3) NOT NULL,
            `title` varchar(50) NOT NULL,
            `time` varchar(80) NOT NULL,
            `id_reference` int(10) unsigned NOT NULL,
            PRIMARY KEY (`code`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = "INSERT INTO `" . _DB_PREFIX_ . "tipsa_servicios` (`code`, `title`, `time`, `id_reference`) VALUES
          ('06', 'TIPSA AEREA', 'Tipsa carga Aérea', 0),
          ('10', 'TIPSA-10', 'Entrega antes de las 10 am', 0),
          ('14', 'TIPSA-14', 'Entrega antes de las 14 pm', 0),
          ('20', 'DELEGACION', 'Recogida en delegación', 0),
          ('24', 'PREMIUN', 'Entrega en 24 horas', 0),
          ('25', 'FARMA', 'Tipsa Farma', 0),
          ('48', 'ECONOMY','Entrega a domicilio en 48 horas', 0),
          ('96', 'MARITIMA','Tipsa carga maritima', 0),
          ('MV', 'TIPSA-MV', 'Tipsa masivo', 0),
          ('50', 'YUPICK', 'Puntos Yupick', 0);";

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'tipsa_carriers`;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tipsa_carriers` (
            `id` int(11)  NOT NULL AUTO_INCREMENT,
            `code` varchar(5) NOT NULL,
            `title` varchar(50) NOT NULL,
            `id_carrier` int(10) unsigned NOT NULL,
            `id_reference` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`, `code`, `id_carrier`)
          ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'tipsa_configuration`;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tipsa_configuration` (
         `name` varchar( 50 ) NULL ,
         `value` text NULL,
          PRIMARY KEY (`name`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = "INSERT INTO `" . _DB_PREFIX_ . "tipsa_configuration` (`name`, `value`) VALUES
         ('cashondelivery_modules', 'ps_cashondelivery,cashondelivery,megareembolso,codfee,reembolsocargo,cashondeliveryplus,cashondeliveryfee'),
         ('url_dinapaq', 'http://213.236.3.131:8085/dinapaqweb/detalle_envio.php?servicio=@&fecha='),
         ('url_wslg_test', 'http://79.171.110.38:8096/SOAP?service=LoginWSService'),
         ('url_ws_test', 'http://79.171.110.38:8096/SOAP?service=WebServService'),
         ('url_wslg', 'http://webservices.tipsa-dinapaq.com:8099/SOAP?service=LoginWSservice'),
         ('url_ws', 'http://webservices.tipsa-dinapaq.com:8099/SOAP?service=WebServService'),
         ('url_yupick', 'https://courier.yupick.es/servicios.web/puntos/WSPuntos.php'),
         ('url_tracking', 'http://www.tip-sa.com/cliente/datos_prestashop.php?id=@'),
         ('key_yupick', 'iRiFLL51sCKCry2f0KX2VmkcR'),
         ('googlemaps_key', 'AIzaSyA5snVFPcC9n7l8dMiFtJBC5O86TJ_p4ZU');";

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'tipsa_address` (
          `id` int(11)  NOT NULL AUTO_INCREMENT,
          `codageyp` varchar(10) NOT NULL,
          `state` tinyint(1) NOT NULL DEFAULT "0",
          `id_cart` int(10) UNSIGNED NOT NULL DEFAULT "0",
          `id_order` int(10) UNSIGNED NOT NULL,
          `id_carrier` int(10) UNSIGNED DEFAULT NULL,
          `alias` varchar(32) NOT NULL,
          `company` varchar(255) DEFAULT NULL,
          `lastname` varchar(32) NOT NULL,
          `firstname` varchar(32) NOT NULL,
          `address` varchar(128) NOT NULL,
          `postcode` varchar(12) DEFAULT NULL,
          `city` varchar(64) NOT NULL,
          `phone` varchar(32) DEFAULT NULL,
          `phone_mobile` varchar(32) DEFAULT NULL,

          `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY (`id_cart`,`id_carrier`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
