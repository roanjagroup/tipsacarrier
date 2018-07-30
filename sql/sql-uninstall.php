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

$sql = array('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'tipsa_envios');

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
