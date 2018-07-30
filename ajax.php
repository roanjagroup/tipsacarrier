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

require_once realpath(dirname(__FILE__) . '/../../config/config.inc.php');
require_once realpath(dirname(__FILE__) . '/../../init.php');
require_once _PS_MODULE_DIR_ . 'tipsacarrier/tipsacarrier.php';

global $cart;

$yupick = new Tipsacarrier();
$data   = array();
if (Tools::getValue('acc') == 'GetPoint') {
    $data = Tools::getValue('yupick_cp');
    echo $yupick->getYupickPoints($data);
    die();
}
if (Tools::getValue('acc') == 'UpdateRecoger') {
    $data['cart_id']                 = Tools::getValue('cart_id');
    $data['oficina_yupick_id']       = Tools::getValue('oficina_id');
    $data['oficina_yupick_data']     = Tools::getValue('oficina_yupick_data');
    $data['yupick_type_alert']       = Tools::getValue('yupick_type_alert');
    $data['yupick_type_alert_email'] = Tools::getValue('yupick_type_alert_email');
    $data['yupick_type_alert_phone'] = Tools::getValue('yupick_type_alert_phone');
    echo $yupick->UpdateRecoger($data);
    die();
}
if (Tools::getValue('id_carrier')) {
    $cart->id_carrier = (int) Tools::getValue('id_carrier');
    $cart->save();
}
if (Tools::getValue('acc') == 'NewAddresYupick') {
    $data['oficina_yupick_id']       = Tools::getValue('oficina_yupick_id');
    $data['nombre_yupick']           = Tools::getValue('nombre_yupick');
    $data['direccion_yupick']        = Tools::getValue('direccion_yupick');
    $data['cp_yupick']               = Tools::getValue('cp_yupick');
    $data['localidad_yupick']        = Tools::getValue('localidad_yupick');
    $data['provincia_yupick']        = Tools::getValue('provincia_yupick');
    $data['yupick_type_alert_phone'] = Tools::getValue('yupick_type_alert_phone');
    $data['yp_client_phone']         = Tools::getValue('yp_client_phone');
    $data['yp_id_carrier']           = Tools::getValue('yp_id_carrier');

    echo $yupick->tipsaAddressYupick($data);
}
