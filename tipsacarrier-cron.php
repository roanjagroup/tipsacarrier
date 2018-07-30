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

include dirname(__FILE__) . '/../../config/config.inc.php';
include dirname(__FILE__) . '/../../init.php';
/* Check to security tocken */

if (Tools::substr(Tools::encrypt('tipsacarrier/cron'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('tipsacarrier')) {
    die('Bad token');
}

$tipsacarrier = Module::getInstanceByName('tipsacarrier');

/* Check if the module is enabled */
if ($tipsacarrier->active) {
    $orders = Db::getInstance()->executeS('SELECT id_envio FROM ' . _DB_PREFIX_ . 'tipsa_envios WHERE `num_albaran`!= "" ORDER BY id_envio DESC');
    foreach ($orders as $order) {
        $tipsacarrier->updateStatus((int) $order['id_envio']);
    }
}
