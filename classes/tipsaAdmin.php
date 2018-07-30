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

if (!defined('_PS_VERSION_')) {
    exit;
}

class TipsaAdmin
{
    public static function getOrders()
    {
        $context = Context::getContext();
        if (Tools::isSubmit('form-search_shipping_action') ||
            Tools::isSubmit('form-search_shipping_filter') ||
            Tools::isSubmit('form-search_shipping_reset') ||
            Tools::getValue('order_rows') ||
            Tools::getValue('order_page')) {
            $context->smarty->assign('CURRENT_FORM', 'search_shipping');
        }

        $where            = "";
        $where_collection = "";
        if (Tools::isSubmit('form-search_shipping_filter')) {
            if (Tools::getValue('orderFilter_id_order')) {
                $where .= " AND o.`id_order` = " . (int) Tools::getValue('orderFilter_id_order');
            }
            if (Tools::getValue('orderFilter_customer')) {
                $where .= " AND c.`firstname` LIKE '%" . pSQL(Tools::getValue('orderFilter_customer')) . "%'";
            }
            if (Tools::getIsset('orderFilter_exported') && Tools::getValue('orderFilter_exported') == '1') {
                $where .= " AND cp.`exported` IS NOT NULL ";
            }
            if (Tools::getIsset('orderFilter_exported') && Tools::getValue('orderFilter_exported') == '0') {
                $where .= " AND cp.`exported` IS NULL ";
            }
            if (Tools::getIsset('orderFilter_printed') && Tools::getValue('orderFilter_printed') == '1') {
                $where .= " AND cp.`label_printed` IS NOT NULL ";
            }
            if (Tools::getIsset('orderFilter_printed') && Tools::getValue('orderFilter_printed') == '0') {
                $where .= " AND cp.`label_printed` IS NULL ";
            }
            if (Tools::getIsset('orderFilter_manifest') && Tools::getValue('orderFilter_manifest') == '1') {
                $where .= " AND cp.`manifest` IS NOT NULL ";
            }
            if (Tools::getIsset('orderFilter_manifest') && Tools::getValue('orderFilter_manifest') == '0') {
                $where .= " AND cp.`manifest` IS NULL ";
            }
            if (Tools::getValue('orderFilter_dateFrom') && Tools::getValue('orderFilter_dateTo')) {
                $where .= " AND (o.`date_add` BETWEEN '" . pSQL(Tools::getValue('orderFilter_dateFrom')) .
                " 00:00:00' AND '" . pSQL(Tools::getValue('orderFilter_dateTo')) . " 23:59:59')";
            }
            if (Tools::getValue('orderFilter_dateFrom') && Tools::getValue('orderFilter_dateTo')) {
                $where .= " AND (o.`date_add` BETWEEN '" . pSQL(Tools::getValue('orderFilter_dateFrom')) .
                " 00:00:00' AND '" . pSQL(Tools::getValue('orderFilter_dateTo')) . " 23:59:59')";
            }
            if (Tools::getIsset('orderFilter_collected') && Tools::getValue('orderFilter_collected') == '1') {
                $where_collection = " WHERE `collection_date` IS NOT NULL ";
            }
            if (Tools::getIsset('orderFilter_collected') && Tools::getValue('orderFilter_collected') == '0') {
                $where_collection = " WHERE `collection_date` IS NULL ";
            }
        }
        $sql = 'SELECT COUNT( * )  FROM  `' . _DB_PREFIX_ . 'orders` o
         INNER JOIN `' . _DB_PREFIX_ . 'correos_preregister` cp ON ( o.`id_order` = cp.`id_order` )
         LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON ( c.`id_customer` = o.`id_customer` )
         WHERE o.`id_carrier` IN (
         SELECT pc.`id_carrier`
         FROM `' . _DB_PREFIX_ . 'carrier` pc
         INNER JOIN `' . _DB_PREFIX_ . 'correos_carrier` cc ON pc.`id_reference` = cc.`id_reference`)' . $where;
        $total_rows = Db::getInstance()->getValue($sql);
        $order_rows = 50;
        $order_page = 1;

        if (Tools::getValue('order_rows')) {
            $order_rows = (int) Tools::getValue('order_rows');
        }
        if (!Tools::getValue('order_page')) {
            $limit_from = 0;
        } else {
            $limit_from = ((int) Tools::getValue('order_page') - 1) * (int) $order_rows;
            $order_page = (int) Tools::getValue('order_page');
        }
        $total_pages = ceil((int) $total_rows / (int) $order_rows);

        $context->smarty->assign(
            'search_shipping_pagination',
            array(
                'total_pages' => (int) $total_pages,
                'total_rows'  => (int) $total_rows,
                'page'        => (int) $order_page,
                'order_rows'  => (int) $order_rows,
            )
        );
        if (Db::getInstance()->executeS("SHOW TABLES LIKE '" . _DB_PREFIX_ . "correos_collection'")) {
            //Is needed te check if table exists, in case user uploaded the module and hasn't update id (PS 1.7)
            $sql = 'SELECT * FROM (SELECT o.`id_order`, c.`firstname`, c.`lastname`, cp.`shipment_code`, cp.`label_printed`, o.`reference`,
            cp.`exported`, cp.`manifest`, o.`date_add`, o.`date_upd`,
            (SELECT `date_requested` FROM `' . _DB_PREFIX_ . 'correos_collection` cc WHERE cc.`id` = cp.`id_collection` LIMIT 1) AS `collection_date`
            FROM `' . _DB_PREFIX_ . 'orders` o
            INNER JOIN `' . _DB_PREFIX_ . 'correos_preregister` cp ON ( o.`id_order` = cp.`id_order` )
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON ( c.`id_customer` = o.`id_customer` )
            WHERE o.`id_carrier`
            IN (
            SELECT pc.`id_carrier`
            FROM `' . _DB_PREFIX_ . 'carrier` pc
            INNER JOIN `' . _DB_PREFIX_ . 'correos_carrier` cc ON pc.`id_reference` = cc.`id_reference`
            ) ' .
            $where
            . ' ORDER BY o.`date_add` DESC LIMIT ' . (int) $limit_from . ',' . (int) $order_rows . ') AS Tab ' . $where_collection;
        } else {
            $sql = 'SELECT o.`id_order`, c.`firstname`, c.`lastname`, cp.`shipment_code`, cp.`label_printed`, o.`reference`,
            cp.`exported`, cp.`manifest`, o.`date_add`, o.`date_upd`, NULL as `collection_date`
            FROM `' . _DB_PREFIX_ . 'orders` o
            INNER JOIN `' . _DB_PREFIX_ . 'correos_preregister` cp ON ( o.`id_order` = cp.`id_order` )
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON ( c.`id_customer` = o.`id_customer` )
            WHERE o.`id_carrier`
            IN (
            SELECT pc.`id_carrier`
            FROM `' . _DB_PREFIX_ . 'carrier` pc
            INNER JOIN `' . _DB_PREFIX_ . 'correos_carrier` cc ON pc.`id_reference` = cc.`id_reference`
            ) ' .
            $where
            . ' ORDER BY o.`date_add` DESC LIMIT ' . (int) $limit_from . ',' . (int) $order_rows;
        }
        return Db::getInstance()->executeS($sql);
    }

    public static function updateCorreosConfig($params)
    {
        foreach ($params as $conf_key => $value) {
            if (strstr(Tools::strtoupper($conf_key), 'TIPSA_')) {
                Configuration::updateValue(Tools::strtoupper($conf_key), $value);
            } else {
                Configuration::updateValue('TIPSA_' . Tools::strtoupper($conf_key), $value);
            }
        }
    }

    public static function installExternalCarrier($config)
    {

        $tipsacarriers                 = new TipsaCarrier();
        $carrier                       = new Carrier();
        $carrier->name                 = $config['name'];
        $carrier->id_tax               = 1;
        $carrier->id_zone              = 1;
        $carrier->active               = 0;
        $carrier->deleted              = 0;
        $carrier->delay                = $config['delay'];
        $carrier->shipping_handling    = false;
        $carrier->range_behavior       = 0;
        $carrier->is_module            = 0;
        $carrier->shipping_external    = 0;
        $carrier->external_module_name = $tipsacarriers->name;
        $carrier->need_range           = true;
        $carrier->max_weight           = $config['servicio_code'] == '48' ? 2 : 30;
        $carrier->url                  = 'http://www.tip-sa.com/cliente/datos_prestashop.php?id=@';
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            $carrier->is_module = 1;
        }
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $carrier->delay[(int) $language['id_lang']] = $config['delay'][$language['iso_code']];
        }

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->Execute(
                    'INSERT INTO `' . _DB_PREFIX_ . 'carrier_group`
                    VALUES (\'' . (int) $carrier->id . '\',\'' . (int) $group['id_group'] . '\')'
                );
            }

            /*Weight range. Default 0-10000*/
            $rangePrice             = new RangePrice();
            $rangePrice->id_carrier = (int) $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '10000';
            $rangePrice->add();

            /*Price range. Default 0-1000*/
            $rangeWeight             = new RangeWeight();
            $rangeWeight->id_carrier = (int) $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = $config['servicio_code'] == '48' ? '2' : '1000';
            $rangeWeight->add();

            $logo_prefix = "tipsa_";

            if (file_exists(
                _PS_MODULE_DIR_ . 'tipsacarrier/views/img/' . $logo_prefix .
                Tools::strtolower($config['servicio_code']) . '.jpg'
            )) {
                $source = _PS_MODULE_DIR_ . 'tipsacarrier/views/img/' . $logo_prefix .
                Tools::strtolower($config['servicio_code']) . '.jpg';
            } else {
                $source = _PS_MODULE_DIR_ . 'tipsacarrier/views/img/' . $logo_prefix . 'default.jpg';
            }

            $destination = _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg';
            copy($source, $destination);

            /*Update table tipsa_servicios*/
            Db::getInstance()->update(
                'tipsa_servicios',
                array('id_reference' => (int) $carrier->id),
                "code = '" . pSQL($config['servicio_code']) . "'"
            );
            return true;
        } else {
            return false;
        }
    }
}
