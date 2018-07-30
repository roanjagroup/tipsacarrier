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

class TipsaAdminComun
{
    public static function getCodigoBarras($id_envio)
    {
        $sql = 'SELECT codigo_barras '
        . 'FROM ' . _DB_PREFIX_ . 'tipsa_envios '
        . 'WHERE id_envio = ' . (int) $id_envio . ' AND codigo_barras IS NOT NULL ';

        $trackingCode = (bool) Db::getInstance()->getValue($sql);

        return $trackingCode;
    }

    public static function getServicesTipsa()
    {
        $sql = "SELECT `code`, `title`, `time`, `id_reference` FROM `" . _DB_PREFIX_ . "tipsa_servicios` cc WHERE 1 ";

        $tipsa_servicios = Db::getInstance()->executeS($sql);

        return $tipsa_servicios;
    }

    public static function getTipsaCarriers($where = '')
    {
        $sql = "SELECT `code`, `title`, `id_reference` FROM `" . _DB_PREFIX_ . "tipsa_carriers` cc WHERE 1 ";

        if ($where != '') {
            $sql .= " AND " . $where;
        }

        $tipsa_carrier = Db::getInstance()->executeS($sql);

        if (count($tipsa_carrier) == 0) {
            return false;
        } elseif (count($tipsa_carrier) == 1) {
            return $tipsa_carrier[0];
        } else {
            return $tipsa_carrier;
        }
    }

    /**
     *Selecciona los carriers relacionados con TIPSA que no esten eliminados
     */
    public static function getCarriers()
    {
        $sql = 'SELECT t.id, t.code, t.title, t.id_carrier, t.id_reference, c.name
                FROM ' . _DB_PREFIX_ . 'tipsa_carriers t
                LEFT JOIN ' . _DB_PREFIX_ . 'carrier c ON (t.id_carrier = c.id_carrier)
                WHERE c.deleted != 1';

        $tipsa_carriers = Db::getInstance()->executeS($sql);
        if (count($tipsa_carriers) == 0) {
            return false;
        } else {
            return $tipsa_carriers;
        }
    }

    public static function getCarrierTipsaService()
    {
        $sql = "SELECT `id_carrier`, `active`, `id_reference`
            FROM `" . _DB_PREFIX_ . "carrier`
            WHERE `deleted` = 0 AND `id_reference` IN (SELECT `id_reference` FROM `" . _DB_PREFIX_ . "tipsa_servicios`)";
        $service = Db::getInstance()->executeS($sql);

        return $service;
    }

    public static function getOrder($id_envio)
    {
        $sql = 'SELECT o.id_order, o.module, o.total_paid_real, o.reference,
                  o.total_paid,
                  c.name,c.id_reference,
                  u.email,
                  a.alias,a.firstname,a.lastname,a.address1,a.address2,a.postcode,a.other,a.city,a.phone,a.phone_mobile,
                  z.iso_code,
                  m.message
                FROM ' . _DB_PREFIX_ . 'orders AS o
                LEFT JOIN ' . _DB_PREFIX_ . 'tipsa_envios AS tp ON tp.id_envio_order = o.id_order
                LEFT JOIN ' . _DB_PREFIX_ . 'carrier AS c ON c.id_carrier = o.id_carrier
                LEFT JOIN ' . _DB_PREFIX_ . 'customer AS u ON u.id_customer = o.id_customer
                LEFT JOIN ' . _DB_PREFIX_ . 'address AS a ON a.id_address = o.id_address_delivery
                LEFT JOIN ' . _DB_PREFIX_ . 'country AS z ON a.id_country = z.id_country
                LEFT JOIN ' . _DB_PREFIX_ . 'message AS m ON m.id_order = o.id_order
                WHERE tp.id_envio = ' . (int) $id_envio;

        $shippingDetails = Db::getInstance()->getRow($sql);

        return $shippingDetails;
    }

    public static function sendMail($to, $subject, $template, $templateVars, $bcc = null)
    {
        $id_lang = Language::getIdByIso('es');
        if ($id_lang) {
            return Mail::Send(
                $id_lang,
                $template,
                $subject,
                $templateVars,
                $to,
                null,
                null,
                null,
                null,
                null,
                dirname(__FILE__) . '/../mails/',
                false,
                Context::getContext()->shop->id,
                $bcc
            );
        }
        return false;
    }

    public static function getServiceTipsa()
    {
        $sqlServiciosTipsa = "SELECT * FROM `" . _DB_PREFIX_ . "tipsa_servicios`";
        $serviciosTipsa    = Db::getInstance()->executeS($sqlServiciosTipsa);

        $servicesTisa = array();

        foreach ($serviciosTipsa as $service) {
            $servicesTisa[] = array(
                'code'  => $service['code'],
                'title' => $service['title'],
            );
        }
        return $servicesTisa;
    }

    public static function getCarriersActive()
    {
        $sqlCarriers = "SELECT `id_carrier`, `active`, `id_reference`, `name`
                        FROM `" . _DB_PREFIX_ . "carrier`
                        WHERE `deleted` = 0 AND `active` = 1 AND `external_module_name` IN ('tipsacarrier','')";

        $carriers_activos = Db::getInstance()->executeS($sqlCarriers);

        $carriers = array();

        foreach ($carriers_activos as $carrier) {
            $carriers[] = array(
                'id_carrier' => $carrier['id_carrier'],
                'name'       => $carrier['name'],
            );
        }
        return $carriers;
    }

    public static function getYupickCarrierActive($code)
    {
        $sql = "SELECT pc.`id_carrier`, cc.`code`
         FROM `" . _DB_PREFIX_ . "carrier` pc
         INNER JOIN `" . _DB_PREFIX_ . "tipsa_carriers` cc ON pc.`id_reference` = cc.`id_reference`
         WHERE pc.`active` = 1 AND pc.`deleted` = 0 AND pc.`id_reference` != 0 AND cc.`code` = " . $code;

        $yupick_carriers = Db::getInstance()->getRow($sql);

        if (count($yupick_carriers) == 0) {
            return false;
        } else {
            return $yupick_carriers;
        }
    }

    public static function addTisaConfiguration($dataConfig)
    {
        foreach ($dataConfig as $conf_key => $value) {
            $sql = "INSERT INTO `" . _DB_PREFIX_ . "tipsa_configuration`
                (`value`, `name`)
                VALUES ('" . pSQL($value) . "', '" . pSQL($conf_key) . "')
                ON DUPLICATE KEY UPDATE `value` = '" . pSQL($value) . "'";

            $result = Db::getInstance()->Execute($sql);
        }
        return $result;
    }

    // public static function getTisaConfiguration($dataConfig)
    // {
    //     $config = array();
    //     $sql = 'SELECT * FROM '._DB_PREFIX_.'tipsa_configuration';
    //     if ($results = Db::getInstance()->ExecuteS($sql)) {
    //         foreach ($results as $row) {
    //             $config[$row['name']] = $row['value'];
    //         }
    //     }
    // }

    public static function addTisaCarries($id_carrier, $code)
    {
        $carrier_activo = array();
        $serviceTipsa   = array();

        $sqlCarriers = "SELECT `id_carrier`, `active`, `id_reference`, `name`
                FROM `" . _DB_PREFIX_ . "carrier`
                WHERE `deleted` = 0 AND `active` = 1 AND `id_carrier` = " . $id_carrier;
        $carrier_activo = Db::getInstance()->executeS($sqlCarriers);

        $id_reference = $carrier_activo[0]['id_reference'];

        $sqlTisaServicios = "SELECT `title`
                            FROM `" . _DB_PREFIX_ . "tipsa_servicios`
                            WHERE `code` ='" . $code . "'";

        $serviceTipsa = Db::getInstance()->executeS($sqlTisaServicios);

        $titleService = $serviceTipsa[0]['title'];

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'tipsa_carriers` (`code`, `title`, `id_carrier`, `id_reference`)
            VALUES("' . pSQL($code) . '", "' . pSQL($titleService) . '", ' . (int) $id_carrier . ', ' . (int) $id_reference . ')';

        $res = Db::getInstance()->execute($sql);

        return $res;
    }

    public static function remove($id)
    {
        $result = true;
        $result &= Db::getInstance()->delete('tipsa_carriers', 'id = ' . (int) $id);

        return $result;
    }

    public static function installExternalCarrier($config)
    {

        $tipsacarriers                 = new TipsaCarrier();
        $carrier                       = new Carrier();
        $carrier->name                 = $config['name'];
        $carrier->id_tax               = 1;
        $carrier->id_zone              = 1;
        $carrier->active               = 1;
        $carrier->deleted              = 0;
        $carrier->delay                = $config['time'];
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
            $carrier->delay[(int) $language['id_lang']] = $config['time'][$language['iso_code']];
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

            TipsaAdminComun::addTisaCarries($carrier->id, $config['servicio_code']);

            return true;
        } else {
            return false;
        }
    }
}
