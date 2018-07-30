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

    require_once _PS_MODULE_DIR_ . '/tipsacarrier/classes/tipsaAdminComun.php';
    require_once _PS_MODULE_DIR_ . '/tipsacarrier/classes/tipsaAdmin.php';
    require_once _PS_MODULE_DIR_ . '/tipsacarrier/ws/webservicetipsa.php';

class Tipsacarrier extends CarrierModule
{
    protected $_html = '';
    public $id_carrier;
    private $_postErrors            = array();
    protected $tipsa_codage_test    = '000000';
    protected $tipsa_codcli_test    = '33333';
    protected $tipsa_passw_cli_test = 'PR%20%18%';
    protected $tipsa_modo_trabajo   = 0;
    protected $service_codes_yupick = '50';

    public function __construct()
    {
        $this->name          = 'tipsacarrier';
        $this->tab           = 'shipping_logistics';
        $this->version       = '1.1.0';
        $this->author        = 'Tipsa';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        $this->module_key    = 'abe15358f9eb5ff88608a84d8c80484a';
        parent::__construct();
        $this->displayName            = $this->l('shipping TIPSA');
        $this->description            = $this->l('Module that integrates the shipping system with TIPSA');
        $this->ps_versions_compliancy = array('min' => '1.6.0.4', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (parent::install()
            && $this->registerHook('updateCarrier')
            && $this->registerHook('adminOrder')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('header')
            && $this->registerHook('newOrder')
            && $this->registerHook('updateOrderStatus')
            && $this->registerHook('orderReturn')
            && Configuration::updateValue('TIPSACARRIER_VERSION', $this->version)
            && Configuration::updateValue('YUPICK_TOKEN', Tools::passwdGen(30))
        ) {
            if (version_compare(_PS_VERSION_, '1.7', '>')) {
                $res = $this->registerHook('displayCarrierExtraContent');
            } else {
                $res = $this->registerHook('extraCarrier');
            }

            $shops            = Shop::getContextListShopID();
            $shop_groups_list = array();

            foreach ($shops as $shop_id) {
                $shop_group_id = (int) Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                /* Sets up configuration */
                $res = Configuration::updateValue('TIPSA_CODAGE_TEST', $this->tipsa_codage_test, false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_CODCLI_TEST', $this->tipsa_codcli_test, false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_PASSW_TEST', $this->tipsa_passw_cli_test, false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_MODE', $this->tipsa_modo_trabajo, false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Configuration::get('PS_OS_SHIPPING'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Configuration::get('PS_OS_DELIVERED'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Configuration::get('PS_OS_ERROR'), false, $shop_group_id, $shop_id);
            }
            if (count($shop_groups_list)) {
                foreach ($shop_groups_list as $shop_group_id) {
                    /* Sets up configuration */
                    $res = Configuration::updateValue('TIPSA_CODAGE_TEST', $this->tipsa_codage_test, false, $shop_group_id);
                    $res &= Configuration::updateValue('TIPSA_CODCLI_TEST', $this->tipsa_codcli_test, false, $shop_group_id);
                    $res &= Configuration::updateValue('TIPSA_PASSW_TEST', $this->tipsa_passw_cli_test, false, $shop_group_id);
                    $res &= Configuration::updateValue('TIPSA_MODE', $this->tipsa_modo_trabajo, false, $shop_group_id);
                    $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Configuration::get('PS_OS_SHIPPING'), false, $shop_group_id);
                    $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Configuration::get('PS_OS_DELIVERED'), false, $shop_group_id);
                    $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Configuration::get('PS_OS_ERROR'), false, $shop_group_id);
                }
            }

            $res &= Configuration::updateValue('TIPSA_CODAGE_TEST', $this->tipsa_codage_test);
            $res &= Configuration::updateValue('TIPSA_CODCLI_TEST', $this->tipsa_codcli_test);
            $res &= Configuration::updateValue('TIPSA_PASSW_TEST', $this->tipsa_passw_cli_test);
            $res &= Configuration::updateValue('TIPSA_MODE', $this->tipsa_modo_trabajo);
            $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Configuration::get('PS_OS_SHIPPING'));
            $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Configuration::get('PS_OS_DELIVERED'));
            $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Configuration::get('PS_OS_ERROR'));

            $res &= $this->createTables();
            //$class = 'Admin' . Tools::ucfirst($this->name) . 'Management';
            $class = 'SELL';
            $res &= $this->installModuleTab('Gestor Tipsa', 'management', $class);
            $res &= $this->installModuleTab('Gestor Tipsa', '', 'AdminTipsacarrierManagement');
            $res &= $this->installModuleTab('Tipsa Configuration', 'module', 'AdminTipsacarrierManagement');

            return (bool) $res;
        }

        return false;
    }

    public function createTables()
    {
        // Install SQL
        $sql = array();
        include dirname(__FILE__) . '/sql/sql-install.php';
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return false;
            }
        }
        return true;
    }

    private function installModuleTab($title, $class_sfx = '', $parent = '')
    {
        $class = 'Admin' . Tools::ucfirst($this->name) . Tools::ucfirst($class_sfx);
        @copy(_PS_MODULE_DIR_ . $this->name . '/logo.png', _PS_IMG_DIR_ . 't/' . $class . '.png');
        if ($parent == '') {
            $position = Tab::getCurrentTabId();
        } else {
            $position = Tab::getIdFromClassName($parent);
        }

        $tab             = new Tab();
        $tab->class_name = $class;
        $tab->module     = $this->name;
        $tab->id_parent  = (int) $position;
        $langs           = Language::getLanguages(false);

        foreach ($langs as $l) {
            $tab->name[$l['id_lang']] = $title;
        }

        return $tab->add(true, false);
    }

    public function uninstall()
    {
        /* Deletes Module */
        if (parent::uninstall()) {
            $res = $this->deleteTables();
            $res &= $this->unregisterHook('updateCarrier');
            $res &= $this->unregisterHook('adminOrder');
            $res &= $this->unregisterHook('newOrder');
            $res &= $this->unregisterHook('updateOrderStatus');
            $res &= $this->unregisterHook('orderReturn');
            $res &= $this->unregisterHook('displayBackOfficeHeader');
            $res &= $this->unregisterHook('header');
            $res &= $this->uninstallTab('management');
            $res &= $this->uninstallTab('module');

            if (version_compare(_PS_VERSION_, '1.7', '>')) {
                $res &= $this->unregisterHook('displayCarrierExtraContent');
            } else {
                $res &= $this->unregisterHook('extraCarrier');
            }

            $res &= Configuration::deleteByName('TIPSACARRIER_VERSION');

            return (bool) $res;
        }

        return true;
    }

    protected function deleteTables()
    {
        return Db::getInstance()->execute('
            DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'tipsa_configuration`,
                                 `' . _DB_PREFIX_ . 'tipsa_servicios`,
                                 `' . _DB_PREFIX_ . 'tipsa_carriers`;
                                 ');
    }

    public function uninstallTab($class_sfx = '')
    {
        $tab_class = 'Admin' . Tools::ucfirst($this->name) . Tools::ucfirst($class_sfx);
        $id_tab    = Tab::getIdFromClassName($tab_class);
        if ($id_tab != 0) {
            $tab = new Tab($id_tab);
            $tab->delete();
            return true;
        }
    }

    public function getContent()
    {
        $registered = Configuration::get('TIPSA_REGISTERED');

        if ($registered == 0) {
            if (Tools::isSubmit('registro')) {
                $this->_html .= $this->renderFormRegister();
            } elseif (Tools::isSubmit('regitrar')) {
                if ($this->_postValidation()) {
                    if ($this->_postProcess()) {
                        $this->_html .= $this->renderFormDatos();
                    } else {
                        $this->_html .= $this->renderFormRegister();
                    }
                } else {
                    $this->_html .= $this->renderFormRegister();
                }
            } else {
                $this->_html .= $this->renderIntro();
            }
        } else {
            if (Tools::isSubmit('submitConfig')
                || Tools::isSubmit('submitConfigExtra')
                || Tools::isSubmit('submitService')
                || Tools::isSubmit('deleteserviceCarrier')
                || Tools::isSubmit('form-carriers')
                || Tools::isSubmit('testingConnection')
            ) {
                if ($this->_postValidation()) {
                    $this->_postProcess();
                    $this->_html .= $this->renderTestConection();
                    $this->_html .= $this->renderFormDatos();
                    $this->_html .= $this->renderFormConfigExtra();
                    $this->_html .= $this->renderFormServices();
                    $this->_html .= $this->renderFormAddCarrier();
                    $this->_html .= $this->renderList();
                }
            } else {
                $this->_html .= $this->getWarningMultishopHtml() . $this->getCurrentShopInfoMsg();
                $this->_html .= $this->renderTestConection();
                $this->_html .= $this->renderFormDatos();
                $this->_html .= $this->renderFormConfigExtra();
                $this->_html .= $this->renderFormServices();
                $this->_html .= $this->renderFormAddCarrier();
                if (Shop::getContext() != Shop::CONTEXT_GROUP && Shop::getContext() != Shop::CONTEXT_ALL) {
                    $this->_html .= $this->renderList();
                }
            }
        }
        return $this->_html;
    }

    protected function _postValidation()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop       = Shop::getContextShopID();

        $errors = array();

        if (Tools::isSubmit('registrado')) {
            if (!Configuration::get('TIPSA_REGISTERED')) {
                $errors[] = $this->l('Not yet registered');
            }
        } elseif (Tools::isSubmit('regitrar')) {
            if (Tools::strlen(Tools::getValue('client_company')) > 255) {
                $errors[] = $this->l('The title is too long.');
            }

            if (Tools::strlen(Tools::getValue('client_contact_person')) > 255) {
                $errors[] = $this->l('The contact person is too long.');
            }

            if (Tools::strlen(Tools::getValue('client_state')) > 255) {
                $errors[] = $this->l('The province is too long.');
            }

            if (Tools::strlen(Tools::getValue('client_phone')) > 12) {
                $errors[] = $this->l('The phone is too long.');
            }

            if (Tools::strlen(Tools::getValue('client_email')) > 0 && !Validate::isEmail(Tools::getValue('client_email'))) {
                $errors[] = $this->l('The mail format is not correct.');
            }

            if (Tools::strlen(Tools::getValue('client_comments')) > 4000) {
                $errors[] = $this->l('The comment is too long.');
            }

            if (Tools::strlen(Tools::getValue('client_company')) == 0) {
                $errors[] = $this->l('The company is not set.');
            }

            if (Tools::strlen(Tools::getValue('client_contact_person')) == 0) {
                $errors[] = $this->l('The contact person is not set.');
            }

            if (Tools::strlen(Tools::getValue('client_state')) == 0) {
                $errors[] = $this->l('The province is not set.');
            }

            if (Tools::strlen(Tools::getValue('client_phone')) == 0) {
                $errors[] = $this->l('The telephone is not set.');
            }

            if (Tools::strlen(Tools::getValue('client_email')) == 0) {
                $errors[] = $this->l('The email is not set.');
            }
        } elseif (Tools::isSubmit('submitConfig')) {
            if (Configuration::get('TIPSA_MODE', null, $id_shop_group, $id_shop) == 1) {
                if (Tools::strlen(Tools::getValue('TIPSA_CODAGE')) != 6) {
                    $errors[] = $this->l('The agency code must be 6 characters');
                }
            }
        } elseif (Tools::isSubmit('submitConfigExtra')) {
            return true;
        } elseif (Tools::isSubmit('submitService')) {
            //$id_carrier = Tools::getValue('carrier');
            //$code = Tools::getValue('type_service_tipsa');
        } elseif (Tools::isSubmit('deleteserviceCarrier') && (!Validate::isInt(Tools::getValue('id')))) {
            $errors[] = $this->l('Invalid slide ID');
        } elseif (Tools::isSubmit('form-carriers') && !Tools::getValue('servicio_code')) {
            $errors[] = $this->l('Servicio code empty');
        }
        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
            return false;
        }
        return true;
    }

    protected function _postProcess()
    {
        $errors       = array();
        $shop_context = Shop::getContext();
        $res          = array();

        if (Tools::isSubmit('regitrar')) {
            if (Tools::getValue('client_registered') == 1) {
                $client_registered = $this->l('client Registered');
            } else {
                $client_registered = $this->l('client not Registered');
            }

            $templateVars = array(
                '{client_company}'        => Tools::getValue('client_company'),
                '{client_contact_person}' => Tools::getValue('client_contact_person'),
                '{client_state}'          => Tools::getValue('client_state'),
                '{client_phone}'          => Tools::getValue('client_phone'),
                '{client_email}'          => Tools::getValue('client_email'),
                '{client_comments}'       => Tools::getValue('client_comments'),
                '{client_registered}'     => $client_registered,
                '{version_prestashop}'    => _PS_VERSION_,
            );

            $result_mail = TipsaAdminComun::sendMail(
                "integraciones@tip-sa.com",
                "Solicitud Alta Cliente",
                'email_new_client',
                $templateVars
            );

            if ($result_mail) {
                Configuration::updateValue('TIPSA_REGISTERED', 1);

                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);

                $this->_html .= $this->displayConfirmation(
                    $this->l(
                        "Thank you, mail has been send successfully. " .
                        "Our commercial services will contact with you shortly."
                    )
                );
            } else {
                $this->_html .= $this->displayError($this->l('Error found'));
                return false;
            }
        } elseif (Tools::isSubmit('testingConnection')) {
            $this->testConnectionTipsa();
        } elseif (Tools::isSubmit('submitConfig')) {
            $shop_groups_list = array();
            $shops            = Shop::getContextListShopID();

            foreach ($shops as $shop_id) {
                $shop_group_id = (int) Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                $res = Configuration::updateValue('TIPSA_CODAGE', Tools::getValue('TIPSA_CODAGE'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_CODCLI', Tools::getValue('TIPSA_CODCLI'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_PASSW', Tools::getValue('TIPSA_PASSW'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_CODAGE_TEST', Tools::getValue('TIPSA_CODAGE_TEST'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_CODCLI_TEST', Tools::getValue('TIPSA_CODCLI_TEST'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_PASSW_TEST', Tools::getValue('TIPSA_PASSW_TEST'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_MODE', (int) Tools::getValue('TIPSA_MODE'), false, $shop_group_id, $shop_id);
            }

            switch ($shop_context) {
                case Shop::CONTEXT_ALL:
                    $res = Configuration::updateValue('TIPSA_CODAGE', Tools::getValue('TIPSA_CODAGE'));
                    $res &= Configuration::updateValue('TIPSA_CODCLI', Tools::getValue('TIPSA_CODCLI'));
                    $res &= Configuration::updateValue('TIPSA_PASSW', Tools::getValue('TIPSA_PASSW'));
                    $res &= Configuration::updateValue('TIPSA_CODAGE_TEST', Tools::getValue('TIPSA_CODAGE_TEST'));
                    $res &= Configuration::updateValue('TIPSA_CODCLI_TEST', Tools::getValue('TIPSA_CODCLI_TEST'));
                    $res &= Configuration::updateValue('TIPSA_PASSW_TEST', Tools::getValue('TIPSA_PASSW_TEST'));
                    $res &= Configuration::updateValue('TIPSA_MODE', (int) Tools::getValue('TIPSA_MODE'));
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('TIPSA_CODAGE', Tools::getValue('TIPSA_CODAGE'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_CODCLI', Tools::getValue('TIPSA_CODCLI'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_PASSW', Tools::getValue('TIPSA_PASSW'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_CODAGE_TEST', Tools::getValue('TIPSA_CODAGE_TEST'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_CODCLI_TEST', Tools::getValue('TIPSA_CODCLI_TEST'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_PASSW_TEST', Tools::getValue('TIPSA_PASSW_TEST'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_MODE', (int) Tools::getValue('TIPSA_MODE'), false, $shop_group_id);
                        }
                    }
                    break;
                case Shop::CONTEXT_GROUP:
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('TIPSA_CODAGE', Tools::getValue('TIPSA_CODAGE'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_CODCLI', Tools::getValue('TIPSA_CODCLI'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_PASSW', (int) Tools::getValue('TIPSA_PASSW'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_CODAGE_TETS', Tools::getValue('TIPSA_CODAGE_TETS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_CODCLI_TETS', Tools::getValue('TIPSA_CODCLI_TETS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_PASSW_TETS', (int) Tools::getValue('TIPSA_PASSW_TETS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_MODE', (int) Tools::getValue('TIPSA_MODE'), false, $shop_group_id);
                        }
                    }
                    break;
            }

            $dataConfig = array(
                'url_wslg'       => trim(Tools::getValue('TIPSA_URL_LOGIN')),
                'url_ws'         => trim(Tools::getValue('TIPSA_URL_WS')),
                'url_wslg_test'  => trim(Tools::getValue('TIPSA_URL_LOGIN_TEST')),
                'url_ws_test'    => trim(Tools::getValue('TIPSA_URL_WS_TEST')),
                'url_yupick'     => trim(Tools::getValue('TIPSA_URL_YUPICK')),
                'key_yupick'     => Tools::getValue('TIPSA_KEY_YUPICK'),
                'googlemaps_key' => Tools::getValue('TIPSA_GOOGLEMAPS_KEY'),
            );

            $res &= TipsaAdminComun::addTisaConfiguration($dataConfig);

            if (!$res) {
                $this->_html .= $this->displayError('Error save urls.');
            } else {
                $this->_html .= $this->displayConfirmation(
                    $this->l('The data has been saved successfully.')
                );
            }
        } elseif (Tools::isSubmit('submitConfigExtra')) {
            $shop_groups_list = array();
            $shops            = Shop::getContextListShopID();

            /* Setup each shop */
            foreach ($shops as $shop_id) {
                $shop_group_id = (int) Shop::getGroupFromShop($shop_id, true);

                if (!in_array($shop_group_id, $shop_groups_list)) {
                    $shop_groups_list[] = $shop_group_id;
                }

                $res = Configuration::updateValue('TIPSA_BULTOS', (int) Tools::getValue('TIPSA_BULTOS'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_NUM_FIJO_BULTOS', (int) Tools::getValue('TIPSA_NUM_FIJO_BULTOS'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_NUM_ARTICULOS', (int) Tools::getValue('TIPSA_NUM_ARTICULOS'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Tools::getValue('TIPSA_TRANSITO'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Tools::getValue('TIPSA_ENTREGADO'), false, $shop_group_id, $shop_id);
                $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Tools::getValue('TIPSA_INCIDENCIA'), false, $shop_group_id, $shop_id);
            }
            /* Update global shop context if needed*/
            switch ($shop_context) {
                case Shop::CONTEXT_ALL:
                    $res = Configuration::updateValue('TIPSA_BULTOS', (int) Tools::getValue('TIPSA_BULTOS'));
                    $res &= Configuration::updateValue('TIPSA_NUM_FIJO_BULTOS', (int) Tools::getValue('TIPSA_NUM_FIJO_BULTOS'));
                    $res &= Configuration::updateValue('TIPSA_NUM_ARTICULOS', (int) Tools::getValue('TIPSA_NUM_ARTICULOS'));
                    $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Tools::getValue('TIPSA_TRANSITO'));
                    $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Tools::getValue('TIPSA_ENTREGADO'));
                    $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Tools::getValue('TIPSA_INCIDENCIA'));
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('TIPSA_BULTOS', (int) Tools::getValue('TIPSA_BULTOS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_NUM_FIJO_BULTOS', (int) Tools::getValue('TIPSA_NUM_FIJO_BULTOS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_NUM_ARTICULOS', (int) Tools::getValue('TIPSA_NUM_ARTICULOS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Tools::getValue('TIPSA_TRANSITO'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Tools::getValue('TIPSA_ENTREGADO'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Tools::getValue('TIPSA_INCIDENCIA'), false, $shop_group_id);
                        }
                    }
                    break;
                case Shop::CONTEXT_GROUP:
                    if (count($shop_groups_list)) {
                        foreach ($shop_groups_list as $shop_group_id) {
                            $res = Configuration::updateValue('TIPSA_BULTOS', (int) Tools::getValue('TIPSA_BULTOS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_NUM_FIJO_BULTOS', (int) Tools::getValue('TIPSA_NUM_FIJO_BULTOS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_NUM_ARTICULOS', (int) Tools::getValue('TIPSA_NUM_ARTICULOS'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_TRANSITO', (int) Tools::getValue('TIPSA_TRANSITO'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_ENTREGADO', (int) Tools::getValue('TIPSA_ENTREGADO'), false, $shop_group_id);
                            $res &= Configuration::updateValue('TIPSA_INCIDENCIA', (int) Tools::getValue('TIPSA_INCIDENCIA'), false, $shop_group_id);
                        }
                    }
                    break;
            }

            $dataConfig = array(
                'cashondelivery_modules' => Tools::getValue('TIPSA_CONTRAREEMBOLSO'),
            );

            $res &= TipsaAdminComun::addTisaConfiguration($dataConfig);

            if (!$res) {
                $errors[] = $this->displayError($this->l('Error save configuration extra.'));
            } else {
                $this->_html .= $this->displayConfirmation(
                    $this->l('The data has been saved successfully.')
                );
            }
        } elseif (Tools::isSubmit('submitService')) {
            $id_carrier = Tools::getValue('carrier');
            $code       = Tools::getValue('type_service_tipsa');
            $res        = TipsaAdminComun::addTisaCarries($id_carrier, $code);

            if (!$res) {
                $this->_html .= $this->displayError('Error save service.');
            } else {
                $this->_html .= $this->displayConfirmation(
                    $this->l('The data has been saved successfully.')
                );
            }
        } elseif (Tools::isSubmit('deleteserviceCarrier')) {
            $id  = (int) Tools::getValue('id');
            $res = TipsaAdminComun::remove($id);

            if (!$res) {
                $this->_html .= $this->displayError('Could not delete.');
            } else {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=1&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
            }
        } elseif (Tools::isSubmit('form-carriers')) {
            $servicio_code = Tools::getValue('servicio_code');
            $row           = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'tipsa_servicios` WHERE `code` = ' . pSQL($servicio_code)
            );
            $carrier_config = array(
                'name'          => $row['title'],
                'servicio_code' => $servicio_code,
            );
            $languages = Language::getLanguages(true);
            foreach ($languages as $language) {
                $carrier_config['time'][$language['iso_code']] = $row['time'];
            }
            $res = TipsaAdminComun::installExternalCarrier($carrier_config);

            if (!$res) {
                $this->_html .= $this->displayError('The carrier has been added error.');
            } else {
                return true;
            }
        }

        if (count($errors)) {
            $this->_html .= $this->displayError(implode('<br />', $errors));
        }
    }

    public function renderIntro()
    {
        $this->smarty->assign(
            array(
                'link'    => $this->context->link,
                'version' => Configuration::get('TIPSACARRIER_VERSION'),
                'imgDir'  => $this->_path . 'views/img/',
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/register.tpl');
    }

    public function renderFormRegister()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Client data Tipsa'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Company'),
                        'name'     => 'client_company',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Contact person'),
                        'name'     => 'client_contact_person',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Province'),
                        'name'     => 'client_state',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Telephone contact'),
                        'name'     => 'client_phone',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Email'),
                        'name'     => 'client_email',
                        'required' => true,
                    ),
                    array(
                        'type'  => 'textarea',
                        'label' => $this->l('Comment'),
                        'name'  => 'client_comments',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Already registered by a TIPSA agency?'),
                        'name'    => 'client_registered',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Send'),
                ),
            ),
        );

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form                = array();
        $helper->module                   = $this;
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'regitrar';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value             = $this->getAddServiceFieldsValues();
        return $helper->generateForm(array($fields_form));
    }

    public function renderTestConection()
    {
        $this->smarty->assign(
            array(
                'tipsacarrier_cron' => _PS_BASE_URL_ . _MODULE_DIR_ . 'tipsacarrier/tipsacarrier-cron.php?token=' . Tools::substr(Tools::encrypt('tipsacarrier/cron'), 0, 10),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function renderFormDatos()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop       = Shop::getContextShopID();

        $mode = Configuration::get('TIPSA_MODE', null, $id_shop_group, $id_shop);

        if ($mode == '1') {
            $produccion = '';
            $desarrollo = 'form-group-hidden';
        } else {
            $produccion = 'form-group-hidden';
            $desarrollo = '';
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Client data Tipsa'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Activate production mode'),
                        'name'    => 'TIPSA_MODE',
                        'is_bool' => false,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Agency Code'),
                        'name'  => 'TIPSA_CODAGE',
                        'desc'  => $this->l('TIPSA Agency Code'),
                        'class' => 'mode_produccion ' . $produccion,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Client Code'),
                        'name'  => 'TIPSA_CODCLI',
                        'desc'  => $this->l('TIPSA Customer Code'),
                        'class' => 'mode_produccion ' . $produccion,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Password Client'),
                        'name'  => 'TIPSA_PASSW',
                        'desc'  => $this->l('Password Client Tipsa'),
                        'class' => 'mode_produccion ' . $produccion,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Agency Code Tests'),
                        'name'  => 'TIPSA_CODAGE_TEST',
                        'desc'  => $this->l('TIPSA Agency Code Tests'),
                        'class' => 'mode_desarrollo ' . $desarrollo,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Client Code Tests'),
                        'name'  => 'TIPSA_CODCLI_TEST',
                        'desc'  => $this->l('TIPSA Customer Code Tests'),
                        'class' => 'mode_desarrollo ' . $desarrollo,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Password Client Test'),
                        'name'  => 'TIPSA_PASSW_TEST',
                        'desc'  => $this->l('Password Client Test Tipsa'),
                        'class' => 'mode_desarrollo ' . $desarrollo,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Url login webservice'),
                        'name'  => 'TIPSA_URL_LOGIN',
                        'desc'  => $this->l('the url of login with the Tipsa webservice'),
                        'class' => 'mode_produccion ' . $produccion,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Url webservice'),
                        'name'  => 'TIPSA_URL_WS',
                        'desc'  => $this->l('the url of requests to the webservice Tipsa'),
                        'class' => 'mode_produccion ' . $produccion,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Url login webservice'),
                        'name'  => 'TIPSA_URL_LOGIN_TEST',
                        'desc'  => $this->l('the url of login test with the Tipsa webservice'),
                        'class' => 'mode_desarrollo ' . $desarrollo,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Url webservice'),
                        'name'  => 'TIPSA_URL_WS_TEST',
                        'desc'  => $this->l('the url test of requests to the webservice Tipsa'),
                        'class' => 'mode_desarrollo ' . $desarrollo,
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Url point Yupick!'),
                        'name'  => 'TIPSA_URL_YUPICK',
                        'desc'  => $this->l('the url of requests to the webservice Yupick'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Key Yupick!'),
                        'name'  => 'TIPSA_KEY_YUPICK',
                        'desc'  => $this->l('key Yupick'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Google Maps Key'),
                        'name'  => 'TIPSA_GOOGLEMAPS_KEY',
                        'desc'  => $this->l('Due a Googleimprovement on their security, now we need to create a Google maps API key, you can get it in the following url -> https://developers.google.com/maps/documentation/embed/get-api-key'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        $helper                           = new HelperForm();
        $helper->show_toolbar             = true;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitConfig';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function renderFormConfigExtra()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Tipsa Configuration Extra'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use fixed packages'),
                        'name'    => 'TIPSA_BULTOS',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Number of articles per package fixed'),
                        'name'  => 'TIPSA_NUM_FIJO_BULTOS',
                        'desc'  => $this->l('Indicate the number of fixed packages'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Number of articles per variable packages'),
                        'name'  => 'TIPSA_NUM_ARTICULOS',
                        'desc'  => $this->l('Indicate the number of articles per package'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Name of the cash on delivery module'),
                        'name'  => 'TIPSA_CONTRAREEMBOLSO',
                        'desc'  => $this->l('Name of the cash on delivery module'),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('State in PrestaShop for TRANSITO'),
                        'name'     => 'TIPSA_TRANSITO',
                        'required' => false,
                        'options'  => array(
                            'query' => OrderState::getOrderStates($this->context->language->id),
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('State in PrestaShop for DELIVERED'),
                        'name'     => 'TIPSA_ENTREGADO',
                        'required' => false,
                        'options'  => array(
                            'query' => OrderState::getOrderStates($this->context->language->id),
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('State in PrestaShop for INCIDENCE'),
                        'name'     => 'TIPSA_INCIDENCIA',
                        'required' => false,
                        'options'  => array(
                            'query' => OrderState::getOrderStates($this->context->language->id),
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        $helper                           = new HelperForm();
        $helper->show_toolbar             = true;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitConfigExtra';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function renderFormServices()
    {
        $tipos_servicios = TipsaAdminComun::getServicesTipsa();

        $carriers = TipsaAdminComun::getCarrierTipsaService();

        $logo_prefix = "tipsa_";

        $this->smarty->assign(array(
            'link'            => $this->context->link,
            'path_img'        => $this->_path . 'views/img/',
            'path_module'     => _PS_ROOT_DIR_ . '/modules/' . $this->name . '/',
            'logo_prefix'     => $logo_prefix,
            'carriers'        => $carriers,
            'tipos_servicios' => $tipos_servicios,
        ));

        $this->_html .= $this->display(__FILE__, 'views/templates/admin/carriers.tpl');
    }

    public function renderFormAddCarrier()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add existing configuarado service TIPSA'),
                    'icon'  => 'icon-truck',
                ),
                'input'  => array(
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Carries active'),
                        'desc'     => $this->l('Existing TIPSA transports'),
                        'name'     => 'carrier',
                        'required' => true,
                        'options'  => array(
                            'query' => TipsaAdminComun::getCarriersActive(),
                            'id'    => 'id_carrier',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Service Tipsa:'),
                        'desc'     => $this->l('types of services available in tipsa'),
                        'name'     => 'type_service_tipsa',
                        'required' => true,
                        'options'  => array(
                            'query' => TipsaAdminComun::getServiceTipsa(),
                            'id'    => 'code',
                            'name'  => 'title',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Add'),
                ),
            ),
        );

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form                = array();
        $helper->module                   = $this;
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitService';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value             = $this->getAddServiceFieldsValues();

        return $helper->generateForm(array($fields_form));
    }

    protected function renderList()
    {
        $tipos_servicios = array();
        if (TipsaAdminComun::getCarriers() != 0) {
            $tipos_servicios = TipsaAdminComun::getCarriers();
            $fields_list     = array(
                'id'           => array(
                    'title' => $this->l('ID'),
                    'type'  => 'text',
                ),
                'code'         => array(
                    'title' => $this->l('CODE SERVICE TIPSA'),
                    'type'  => 'text',
                ),
                'title'        => array(
                    'title' => $this->l('NAME SERVICE TIPSA'),
                    'type'  => 'text',
                ),
                'name'         => array(
                    'title' => $this->l('NAME CARRIER'),
                    'type'  => 'text',
                ),
                'id_reference' => array(
                    'title' => $this->l('ID REF'),
                    'type'  => 'text',
                ),
            );

            $helper                = new HelperList();
            $helper->shopLinkType  = '';
            $helper->simple_header = true;
            $helper->identifier    = 'id';
            $helper->table         = 'serviceCarrier';
            $helper->actions       = array('delete');
            $helper->show_toolbar  = true;
            $helper->module        = $this;
            $helper->title         = $this->l('Service list');
            $helper->token         = Tools::getAdminTokenLite('AdminModules');
            $helper->currentIndex  = AdminController::$currentIndex . '&configure=' . $this->name;

            return $helper->generateList($tipos_servicios, $fields_list);
        } else {
            $this->_html .= $this->displayError($this->l('It does not have a transporter configured'));
        }
    }

    public function getConfigFieldsValues()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop       = Shop::getContextShopID();
        $config        = $this->getTipsaConfiguration();
        return array(
            'TIPSA_CODAGE'          => Tools::getValue('TIPSA_CODAGE', Configuration::get('TIPSA_CODAGE', null, $id_shop_group, $id_shop)),
            'TIPSA_CODCLI'          => Tools::getValue('TIPSA_CODCLI', Configuration::get('TIPSA_CODCLI', null, $id_shop_group, $id_shop)),
            'TIPSA_PASSW'           => Tools::getValue('TIPSA_PASSW', Configuration::get('TIPSA_PASSW', null, $id_shop_group, $id_shop)),
            'TIPSA_MODE'            => Tools::getValue('TIPSA_MODE', Configuration::get('TIPSA_MODE', null, $id_shop_group, $id_shop)),
            'TIPSA_CODAGE_TEST'     => Tools::getValue('TIPSA_CODAGE_TEST', Configuration::get('TIPSA_CODAGE_TEST', null, $id_shop_group, $id_shop)),
            'TIPSA_CODCLI_TEST'     => Tools::getValue('TIPSA_CODCLI_TEST', Configuration::get('TIPSA_CODCLI_TEST', null, $id_shop_group, $id_shop)),
            'TIPSA_PASSW_TEST'      => Tools::getValue('TIPSA_PASSW_TEST', Configuration::get('TIPSA_PASSW_TEST', null, $id_shop_group, $id_shop)),
            'TIPSA_BULTOS'          => Tools::getValue('TIPSA_BULTOS', Configuration::get('TIPSA_BULTOS', null, $id_shop_group, $id_shop)),
            'TIPSA_NUM_FIJO_BULTOS' => Tools::getValue('TIPSA_NUM_FIJO_BULTOS', Configuration::get('TIPSA_NUM_FIJO_BULTOS', null, $id_shop_group, $id_shop)),
            'TIPSA_NUM_ARTICULOS'   => Tools::getValue('TIPSA_NUM_ARTICULOS', Configuration::get('TIPSA_NUM_ARTICULOS', null, $id_shop_group, $id_shop)),
            'TIPSA_TRANSITO'        => Tools::getValue('TIPSA_TRANSITO', Configuration::get('TIPSA_TRANSITO', null, $id_shop_group, $id_shop)),
            'TIPSA_ENTREGADO'       => Tools::getValue('TIPSA_ENTREGADO', Configuration::get('TIPSA_ENTREGADO', null, $id_shop_group, $id_shop)),
            'TIPSA_INCIDENCIA'      => Tools::getValue('TIPSA_INCIDENCIA', Configuration::get('TIPSA_INCIDENCIA', null, $id_shop_group, $id_shop)),
            'TIPSA_CONTRAREEMBOLSO' => $config['cashondelivery_modules'],
            'TIPSA_URL_LOGIN'       => $config['url_wslg'],
            'TIPSA_URL_WS'          => $config['url_ws'],
            'TIPSA_URL_LOGIN_TEST'  => $config['url_wslg_test'],
            'TIPSA_URL_WS_TEST'     => $config['url_ws_test'],
            'TIPSA_URL_YUPICK'      => $config['url_yupick'],
            'TIPSA_KEY_YUPICK'      => $config['key_yupick'],
            'TIPSA_GOOGLEMAPS_KEY'  => $config['googlemaps_key'],
        );
    }

    private function getTipsaConfiguration()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop       = Shop::getContextShopID();

        $sql    = 'SELECT * FROM `' . _DB_PREFIX_ . 'tipsa_configuration`';
        $config = array();
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                $config[$row['name']] = $row['value'];
            }
        }

        if (Configuration::get('TIPSA_MODE', null, $id_shop_group, $id_shop) == 1) {
            $config['tipsa_codage'] = Configuration::get('TIPSA_CODAGE', null, $id_shop_group, $id_shop);
            $config['tipsa_codcli'] = Configuration::get('TIPSA_CODCLI', null, $id_shop_group, $id_shop);
            $config['tipsa_passw']  = Configuration::get('TIPSA_PASSW', null, $id_shop_group, $id_shop);
        } else {
            $config['tipsa_codage'] = Configuration::get('TIPSA_CODAGE_TEST', null, $id_shop_group, $id_shop);
            $config['tipsa_codcli'] = Configuration::get('TIPSA_CODCLI_TEST', null, $id_shop_group, $id_shop);
            $config['tipsa_passw']  = Configuration::get('TIPSA_PASSW_TEST', null, $id_shop_group, $id_shop);
        }
        return $config;
    }

    private function getAddServiceFieldsValues()
    {
        //datos indefinidos renderFormAddCarrier
    }

    /**
     * Check if Connection data is ok and return the Curl Object.
     * @param tipo url string(LOGIN or WS or DINA or TRACKING)
     * @return string Curl Object.
     */
    public function getUrlConfiguration($url = null)
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop       = Shop::getContextShopID();

        $urlConfig = $this->getTipsaConfiguration();

        if (Configuration::get('TIPSA_MODE', null, $id_shop_group, $id_shop) == 1) {
            if ($url == 'LOGIN') {
                return $urlConfig['url_wslg'];
            }

            if ($url == 'WS') {
                return $urlConfig['url_ws'];
            }
        } else {
            if ($url == 'LOGIN') {
                return $urlConfig['url_wslg_test'];
            }

            if ($url == 'WS') {
                return $urlConfig['url_ws_test'];
            }
        }

        if ($url == 'TRACKING') {
            return $urlConfig['url_tracking'];
        }

        if ($url == 'DINA') {
            return $urlConfig['url_dinapaq'];
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }

        $this->context->controller->addCSS($this->_path . 'views/css/tipsa_admin.css');
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path . 'views/js/tipsa_admin.js');
    }

    public function getOrderShippingCost($params, $shipping_cost)
    {
        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    public function hookupdateCarrier($params)
    {
        if ($params['carrier']->external_module_name != $this->name) {
            return false;
        }

        $previus_carrier = new Carrier((int) $params['id_carrier']);
        $new_carrier     = new Carrier((int) $params['carrier']->id);

        if ($previus_carrier->active != $new_carrier->active) {
            $sql = 'SELECT GROUP_CONCAT(`name` SEPARATOR ", ") as imploded
                FROM `' . _DB_PREFIX_ . 'carrier`
                WHERE `deleted` = 0 AND `active` = 1 AND `id_reference`
                IN (SELECT `id_reference` FROM `' . _DB_PREFIX_ . 'tipsa_carriers`)';
            Db::getInstance()->getValue($sql);
        }
    }

    /*
     * Busca los puntos Yupick webservice
     */
    public function getYupickPoints($data)
    {
        if (isset($data)) {
            $config = $this->getTipsaConfiguration();

            $postCode  = $data;
            $urlYupick = $config['url_yupick'];
            $yupickKey = $config['key_yupick'];

            $postResult = WebServiceTipsa::wsPuntosYupick($yupickKey, $postCode, $urlYupick);

            $xml = simplexml_load_string($postResult, null, null, "http://http://www.w3.org/2003/05/soap-envelope");
            $xml->registerXPathNamespace("abc", "http://tempuri.org/");

            $result = str_replace("<?xml version='1.0' encoding='ISO-8859-1'?><puntoentrega>", "<?xml version='1.0' encoding='ISO-8859-1'?><puntos><puntoentrega>", $postResult . '</puntos>');

            $result = utf8_decode($result);

            $result = str_replace('</puntos>', '', $result);

            if (strpos($result, 'ERROR')) {
                return "NO RESULTS";
            } else {
                $dataXml = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);

                $arrayResult = array();

                foreach (simplexml_load_string($dataXml->xpath('//ns1:BuscarPuntosResponse[1]/return')[0])->puntoentrega as $yupickSpot) {
                    $puntoentrega = array(
                        "id"             => (string) $yupickSpot->id,
                        "nombre"         => (string) $yupickSpot->nombre[0],
                        "codigopostal"   => (string) $yupickSpot->codigopostal,
                        "direccion"      => (string) $yupickSpot->direccion,
                        "localidad"      => (string) $yupickSpot->localidad,
                        "provincia"      => (string) $yupickSpot->provincia,
                        "comentario"     => (string) $yupickSpot->comentario,
                        "foto"           => (string) $yupickSpot->foto,
                        "poslatitud"     => (string) $yupickSpot->poslatitud,
                        "poslongitud"    => (string) $yupickSpot->poslongitud,
                        "parking"        => (string) $yupickSpot->parking,
                        "wifi"           => (string) $yupickSpot->wifi,
                        "alimentacion"   => (string) $yupickSpot->alimentacion,
                        "prensarevistas" => (string) $yupickSpot->prensarevistas,
                        "tarjetacredito" => (string) $yupickSpot->tarjetacredito,
                        "horario"        => $yupickSpot->horario,
                    );
                    array_push($arrayResult, $puntoentrega);
                }
                return json_encode($arrayResult);
            }
        } else {
            return false;
        }
    }

    /*
     *guarda oficina en tipsa_address temporal
     * @param data office
     */
    public function tipsaAddressYupick($oficina)
    {
        try {
            $cart    = Context::getContext()->cart;
            $id_cart = $cart->id;
            if ($cart->id_carrier) {
                $id_carrier = $cart->id_carrier;
            } else if ($oficina['yp_id_carrier']) {
                $id_carrier = $oficina['yp_id_carrier'];
            }

            //$address = new Address($cart->id_address_delivery);

            $sql = "INSERT INTO `" . _DB_PREFIX_ . "tipsa_address` (`codageyp`,`id_cart`, `id_order`, `id_carrier`, `alias`, `company`, `lastname`, `firstname`, `address`, `postcode`, `city`, `phone`, `phone_mobile`) VALUES (
            '" . pSQL($oficina['oficina_yupick_id']) . "',
            " . (int) $id_cart . ",
            0,
            " . (int) $id_carrier . ",
            '" . pSQL($oficina['nombre_yupick']) . "',
            'Tipsa',
            'Oficina Yupick',
            '" . pSQL($oficina['nombre_yupick']) . "',
            '" . pSQL($oficina['direccion_yupick']) . "',
            '" . pSQL($oficina['cp_yupick']) . "',
            '" . pSQL($oficina['localidad_yupick']) . "',
            '" . pSQL($oficina['yp_client_phone']) . "',
            '" . pSQL($oficina['yupick_type_alert_phone']) . "'
            ) ON DUPLICATE KEY UPDATE codageyp = '" . pSQL($oficina['oficina_yupick_id']) . "', alias = '" . pSQL($oficina['nombre_yupick']) . "', firstname = '" . pSQL($oficina['nombre_yupick']) . "', address = '" . pSQL($oficina['direccion_yupick']) . "', postcode = '" . pSQL($oficina['cp_yupick']) . "', city = '" . pSQL($oficina['localidad_yupick']) . "'";

            return Db::getInstance()->Execute($sql);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /*
     * Final proceso de compra
     * Guarda datos en tabla address si carrier es Yupick
     * tablas tipsa_address -> address
     */
    public function hookNewOrder($params)
    {
        $carrier = new Carrier((int) $params['order']->id_carrier);
        $result  = TipsaAdminComun::getTipsaCarriers("`id_reference` = " . (int) $carrier->id_reference);

        if (!$result) {
            return false;
        }

        $tipo_servicio = $result['code'];

        $office = Db::getInstance()->getRow(
            "SELECT * FROM `" . _DB_PREFIX_ . "tipsa_address`
            WHERE `id_cart` = " . (int) $params['cart']->id . " AND `id_carrier` = " . (int) $params['order']->id_carrier
        );
        try {
            if ($tipo_servicio == $this->service_codes_yupick) {
                if ($office) {
                    // get current customer address
                    $address = new Address($params['order']->id_address_delivery);
                    // Create a new address with the point location
                    $address_point = new Address();
                    //loop through address fields in case the shop has added custom or required fields
                    foreach ($address as $name => $value) {
                        if (!is_array($value) && !in_array($name, array('date_upd', 'date_add', 'id', 'country'))) {
                            switch ($name) {
                                case 'id_customer':
                                    $address_point->id_customer = $params['order']->id_customer;
                                    break;
                                case 'address1':
                                    $address_point->address1 = $office['address'];
                                    break;
                                case 'address2':
                                    $address_point->address2 = $office['firstname'];
                                    break;
                                case 'postcode':
                                    $address_point->postcode = $office['postcode'];
                                    break;
                                case 'city':
                                    $address_point->city = $office['city'];
                                    break;
                                case 'alias':
                                    $address_point->alias = $office['alias'];
                                    break;
                                case 'phone_mobile':
                                    if (!empty($office['phone_mobile'])) {
                                        $address_point->phone_mobile = $office['phone_mobile'];
                                    } else {
                                        $address_point->phone_mobile = "0";
                                    }
                                    break;
                                case 'phone':
                                    $address_point->phone = $office['phone'] != '' ? $office['phone'] : "0";
                                    break;
                                case 'deleted':
                                    $address_point->deleted = true;
                                    break;
                                default:
                                    $address_point->$name = $value;
                            }
                        }
                    }

                    $address_point->save();
                    if ($address_point->id) {
                        $order                      = $params['order'];
                        $order->id_address_delivery = $address_point->id;
                        $order->update();
                    }
                }
            }
            Db::getInstance()->update(
                'tipsa_address',
                array('state' => 1, 'id_order' => (int) $params['order']->id),
                "`state` = 0 AND `id_cart` = " . (int) $params['cart']->id . " AND `id_carrier` = " . (int) $params['order']->id_carrier
            );
        } catch (Exception $e) {
            //In case of error, just do the most important
            return $e->message();
        }

        //Delete old quotes. Need to be in Days because Paypal or card payment validation can last some time
        Db::getInstance()->Execute(
            "DELETE FROM  " . _DB_PREFIX_ . "tipsa_address
            WHERE date < DATE_SUB(NOW(), INTERVAL 7 DAY) AND state = 0"
        );
    }

    public function hookHeader($params)
    {
        if (!($file = basename(Tools::getValue('controller')))) {
            $file = str_replace('.php', '', basename($_SERVER['SCRIPT_NAME']));
        }

        if (in_array($file, array('order-opc', 'order', 'orderopc', 'main'))) {
            $this->context->controller->addCSS($this->_path . '/views/css/style.css');

            if (version_compare(_PS_VERSION_, '1.7', '>')) {
                $this->context->controller->addJS($this->_path . 'views/js/yupick_jq_ps17.js');
            } else {
                $this->context->controller->addJS($this->_path . '/views/js/yupick_jq.js');
            }

            $config                 = $this->getTipsaConfiguration();
            $cashondelivery_modules = explode(',', $config['cashondelivery_modules']);
            $yupick_carriers        = TipsaAdminComun::getYupickCarrierActive($this->service_codes_yupick);

            if ($yupick_carriers) {
                $config_yupick = array(
                    'yp_module_dir'          => _MODULE_DIR_ . $this->name . '/',
                    'yp_carrier_id'          => $yupick_carriers['id_carrier'],
                    'yp_token'               => $config['key_yupick'],
                    'order_opc'              => Configuration::get('PS_ORDER_PROCESS_TYPE'),
                    'type_order_yupick'      => Configuration::get('PS_ORDER_YUPICK'),
                    'gmapsapikey'            => $config['googlemaps_key'],
                    'cashondelivery_modules' => $cashondelivery_modules,
                );

                Media::addJsDef(array("configYupick" => $config_yupick));
                return;
            }
        }
    }

    /*
     * Muestra los transportistas Prestashop 1.6
     */
    public function hookExtraCarrier($params)
    {
        $cart = $params['cart'];

        if (!$cart->id_address_delivery) {
            return false;
        }

        //$carrier = $params['carrier'];
        //var_dump($params);
        $yupick_carriers = TipsaAdminComun::getYupickCarrierActive($this->service_codes_yupick);

        if ($yupick_carriers) {
            $config       = $this->getTipsaConfiguration();
            $id_zone      = Address::getZoneById($cart->id_address_delivery);
            $tmp_address  = new Address((int) $params['cart']->id_address_delivery);
            $checkCarrier = '';

            if (Carrier::checkCarrierZone($yupick_carriers['id_carrier'], $id_zone)) {
                $checkCarrier = Carrier::checkCarrierZone($yupick_carriers['id_carrier'], $id_zone);
            }

            if ($checkCarrier) {
                $address  = $tmp_address;
                $customer = new Customer($cart->id_customer);
                $this->context->smarty->assign(array(
                    'yp_module_dir'     => _MODULE_DIR_ . $this->name . '/',
                    'yp_token'          => $config['key_yupick'],
                    'yp_client_mobile'  => $address->phone_mobile,
                    'yp_client_phone'   => $address->phone,
                    'yp_client_email'   => $customer->email,
                    'yp_client_dir'     => $address->address1 . ' ' . $address->address2,
                    'yp_client_cp'      => $address->postcode,
                    'yp_cart_id'        => $cart->id,
                    'gmapsapikey'       => $config['googlemaps_key'],
                    'yp_google_api_url' => Configuration::get('PS_SSL_ENABLED') ? "https://maps.google.com/maps/api/js?sensor=false" : "http://maps.google.com/maps/api/js?sensor=false",
                ));

                return $this->display(__FILE__, 'extraCarrierYupick.tpl');
            }
        }
    }

    /*
     * Muestra los transportistas Prestashop  1.7
     */
    public function hookdisplayCarrierExtraContent($params)
    {
        $cart = $params['cart'];

        if (!$cart->id_address_delivery) {
            return false;
        }

        $carrier         = $params['carrier'];
        $yupick_carriers = TipsaAdminComun::getTipsaCarriers("`id_reference` = " . (int) $carrier['id_reference']);

        if (!$yupick_carriers) {
            return false;
        }

        if ($yupick_carriers['code'] == $this->service_codes_yupick) {
            $config       = $this->getTipsaConfiguration();
            $id_zone      = Address::getZoneById($cart->id_address_delivery);
            $tmp_address  = new Address((int) $params['cart']->id_address_delivery);
            $checkCarrier = '';
            if (Carrier::checkCarrierZone($carrier['id'], $id_zone)) {
                $checkCarrier = Carrier::checkCarrierZone($carrier['id'], $id_zone);
            }
            if ($checkCarrier) {
                $address  = $tmp_address;
                $customer = new Customer($cart->id_customer);
                $this->smarty->assign(array(
                    'yp_module_dir'     => _MODULE_DIR_ . $this->name . '/',
                    'yp_token'          => $config['key_yupick'],
                    'yp_client_mobile'  => $address->phone_mobile,
                    'yp_client_phone'   => $address->phone,
                    'yp_client_email'   => $customer->email,
                    'yp_client_dir'     => $address->address1 . ' ' . $address->address2,
                    'yp_client_cp'      => $address->postcode,
                    'yp_cart_id'        => $cart->id,
                    'gmapsapikey'       => $config['googlemaps_key'],
                    'yp_google_api_url' => Configuration::get('PS_SSL_ENABLED') ? "https://maps.google.com/maps/api/js?sensor=false" : "http://maps.google.com/maps/api/js?sensor=false",
                ));

                return $this->display(__FILE__, 'extraCarrierContentYupick.tpl');
            }
        }
    }

    /**
     * Initialize the Shipping values for each order if carrier is Tipsa.
     *
     */
    public function initializeShippings()
    {
        // check if there is orders without register of new order
        $sql = 'SELECT o.`id_order` '
            . 'FROM `' . _DB_PREFIX_ . 'orders` o '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c ON c.`id_carrier` = o.`id_carrier` '
            . 'LEFT OUTER JOIN `' . _DB_PREFIX_ . 'tipsa_envios` tp ON tp.`id_envio_order` = o.`id_order` '
            . 'WHERE c.`external_module_name` = "tipsacarrier" AND tp.`id_envio_order` is NULL';
        $shippings = Db::getInstance()->executeS($sql);

        foreach ($shippings as $shipping) {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'tipsa_envios` (`id_envio_order`,`codigo_envio`,`url_track`,`num_albaran`) '
                . 'VALUES ("' . $shipping['id_order'] . '","","","")'
            );
        }
    }

    /*
     * Muestra datos en pedidos con tipsacarrier admin -> Order
     */
    public function hookAdminOrder($params)
    {

        $output  = '';
        $order   = new Order((int) $params['id_order']);
        $carrier = new Carrier((int) $order->id_carrier);

        $this->initializeShippings();

        $config                 = $this->getTipsaConfiguration();
        $cashondelivery_modules = explode(',', $config['cashondelivery_modules']);

        if (in_array($order->module, $cashondelivery_modules)) {
            $contrarreembolso = $order->total_paid_real;
        } else {
            $contrarreembolso = 0;
        }

        if ($carrier->external_module_name == 'tipsacarrier') {
            $codageyp = Db::getInstance()->getValue('SELECT `codageyp` FROM `' . _DB_PREFIX_ . 'tipsa_address` WHERE `id_order` =' . (int) $order->id);
            $sql      = 'SELECT `codigo_barras` '
            . 'FROM `' . _DB_PREFIX_ . 'tipsa_envios`'
            . 'WHERE `id_envio_order` = ' . (int) $order->id . ' AND `codigo_barras` IS NOT NULL ';
            $showLabelButton = (bool) Db::getInstance()->getValue($sql);
            $linkLabel       = Db::getInstance()->getValue($sql);

            if (Tools::isSubmit('generateLabeltipsa_envios') || Tools::isSubmit('updateBultostipsa_envios')) {
                Db::getInstance()->update('tipsa_envios', array('bultos' => (int) Tools::getValue('packages'), 'observation' => Tools::getValue('observation_carrier')), 'id_envio = ' . Tools::getValue('TIPSA_ID_ENVIO'));
                $output .= $this->displayConfirmation($this->l('Status Updated.'));
            }

            if (Tools::isSubmit('generateLabeltipsa_envios') && !$showLabelButton) {
                $this->printLabel((int) Tools::getValue('TIPSA_ID_ENVIO'));
                $showLabelButton = true;
                $output .= $this->displayConfirmation($this->l('Label has been created.'));
            }

            $delivery    = new Address((int) $order->id_address_delivery);
            $messageList = Message::getMessagesByOrderId((int) $order->id);

            $config = $this->getTipsaConfiguration();

            $this->context->smarty->assign(
                array(
                    'TIPSA_CODAGE'                        => $config['tipsa_codage'],
                    'TIPSA_CODCLI'                        => $config['tipsa_codcli'],
                    'base_url'                            => _PS_BASE_URL_ . __PS_BASE_URI__,
                    'module_name'                         => $this->name,
                    'TIPSACARRIER_ID_ENVIO'               => (int) Db::getInstance()->getValue('SELECT `id_envio` FROM `' . _DB_PREFIX_ . 'tipsa_envios` WHERE `id_envio_order` = ' . (int) $order->id),
                    'TIPSACARRIER_PACKAGES'               => (int) Db::getInstance()->getValue('SELECT `bultos` FROM `' . _DB_PREFIX_ . 'tipsa_envios` WHERE `id_envio_order` = ' . (int) $order->id),
                    'ps_version'                          => _PS_VERSION_,
                    'showLabelButton'                     => !$showLabelButton,
                    'linkLabel'                           => $linkLabel,
                    'TIPSACARRIER_REFERENCE'              => $order->reference,
                    'TIPSACARRIER_SHOPNAME'               => Configuration::get('PS_SHOP_NAME'),
                    'TIPSACARRIER_SHOPPHONE'              => Configuration::get('PS_SHOP_PHONE'),
                    'TIPSACARRIER_CP'                     => Configuration::get('PS_SHOP_CODE'),
                    'TIPSACARRIER_CITY'                   => Configuration::get('PS_SHOP_CITY'),
                    'TIPSACARRIER_ADDRESS'                => Configuration::get('PS_SHOP_ADDR1'),
                    'TIPSACARRIER_ORDER_COMMENTS'         => (isset($messageList['0']) && $messageList['0']['id_customer'] > 0) ? $messageList['0']['message'] : '',
                    'TIPSACARRIER_DEST_CONTACT_INFO'      => $delivery->firstname . ' ' . $delivery->lastname,
                    'TIPSACARRIER_DEST_PHONE'             => $delivery->phone . ' - ' . $delivery->phone_mobile,
                    'TIPSACARRIER_DEST_CP'                => $delivery->postcode,
                    'TIPSACARRIER_DEST_CITY'              => $delivery->city,
                    'TIPSACARRIER_DEST_ADDRESS'           => $delivery->address1,
                    'TIPSACARRIER_DEST_COD'               => $contrarreembolso,
                    'TIPSA_CODAGE_YP'                     => $codageyp,
                    'TIPSACARRIER_ORDER_COMMENTS_CARRIER' => Db::getInstance()->getValue('SELECT `observation` FROM `' . _DB_PREFIX_ . 'tipsa_envios` WHERE `id_envio_order` = ' . (int) $order->id),
                )
            );
            $output .= $this->display(__FILE__, 'views/templates/admin/orders_confirmation.tpl');
        }
        return $output;
    }

    public function testConnectionTipsa()
    {
        $urlogin = $this->getUrlConfiguration('LOGIN');

        $datosIp = explode("/", $urlogin);
        $ip      = $datosIp[2];
        $datos   = explode(":", $ip);
        @$ip     = $datos[0];
        @$puerto = $datos[1];

        if (empty($puerto)) {
            $puerto = 80;
        }

        @$fp = fsockopen($ip, $puerto, $errno, $errstr, 10);

        if ($fp) {
            $note = $this->loginTipsaUser();

            foreach ($note as $value) {
                $details       = $value->getElementsByTagName("Result");
                $result_status = $details->item(0)->nodeValue;
            }

            // Saving new configurations
            if ($result_status == 'true') {
                $this->_html .= $this->displayConfirmation($this->l('successful connection tested.'));
            } else {
                $this->_html .= $this->displayError($this->l('Error: Check the values ​​of agency code, client code, client password and Web Service URL.'));
            }
        } else {
            $this->_html .= $this->displayError($this->l('Failed to connect to server' . $ip . ':' . $puerto . ' => (fsockopen error ' . $errno . ')'));
            fclose($fp);
        }
    }

    /**
     * Check if Connection data is ok and return the Curl Object.
     *
     * @return string Curl Object.
     */
    public function loginTipsaUser()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop       = Shop::getContextShopID();

        $config = $this->getTipsaConfiguration();

        $codage = $config['tipsa_codage'];
        $codcli = $config['tipsa_codcli'];
        $passw  = $config['tipsa_passw'];

        if (Configuration::get('TIPSA_MODE', null, $id_shop_group, $id_shop) == 1) {
            $urlogin = $config['url_wslg'];
        } else {
            $urlogin = $config['url_wslg_test'];
        }

        $conexlogin = webservicetipsa::wsLoginCli($codage, $codcli, $passw, $urlogin);

        return $conexlogin;
    }

    /**
     * Create the label for a shipping, recover the information from webservices
     *
     * @param int $id_envio Id Envio inside PrestaShop Tipsa Table.
     *
     * @return string Message of Success.
     */
    public function printLabel($id_envio = 0)
    {
        $datosGrabaEnvio = array();

        if ($id_envio) {
            $trackingCode = TipsaAdminComun::getCodigoBarras($id_envio);
        } else {
            return false;
        }

        if (!$trackingCode) {
            $config = $this->getTipsaConfiguration();

            $datosGrabaEnvio['codage'] = $config['tipsa_codage'];
            $datosGrabaEnvio['codcli'] = $config['tipsa_codcli'];

            $note = $this->loginTipsaUser();

            foreach ($note as $value) {
                $details                     = $value->getElementsByTagName("strSesion");
                $datosGrabaEnvio['idsesion'] = $details->item(0)->nodeValue;
                $details2                    = $value->getElementsByTagName("strURLDetSegEnv");
                $url_seguimiento             = $details2->item(0)->nodeValue;
            }

            $shippingDetails = TipsaAdminComun::getOrder($id_envio);

            //Assign id_order
            $id_order = $shippingDetails['id_order'];
            // Convert type of service to an speciffic value.

            $sql                    = 'SELECT `codageyp` FROM `' . _DB_PREFIX_ . 'tipsa_address` WHERE `id_order` = ' . (int) $id_order;
            $tipsa_address_codageyp = Db::getInstance()->getValue($sql);

            $datosGrabaEnvio['codageyp'] = $tipsa_address_codageyp;

            $sql              = 'SELECT `observation` FROM `' . _DB_PREFIX_ . 'tipsa_envios` WHERE `id_envio_order` = ' . (int) $id_order;
            $tipsa_envios_obs = Db::getInstance()->getValue($sql);


            if ($tipsa_envios_obs != '') {
                $datosGrabaEnvio['observaciones'] = $this->l('Shop: ')
                    . $tipsa_envios_obs;
            } else {
                if ($shippingDetails['message'] != '') {
                    $datosGrabaEnvio['observaciones'] = $this->l('Client: ') . $shippingDetails['message'];
                }if ($shippingDetails['other']) {
                    $datosGrabaEnvio['observaciones'] .= ' / ' . $this->l('other :') . $shippingDetails['other'];
                } else {
                    $datosGrabaEnvio['observaciones'] = '';
                }
            }

            if (isset($shippingDetails['id_reference'])) {
                $sql                              = 'SELECT `code` FROM `' . _DB_PREFIX_ . 'tipsa_carriers` WHERE `id_reference` = ' . $shippingDetails['id_reference'];
                $datosGrabaEnvio['tipo_servicio'] = Db::getInstance()->getValue($sql);
            }

            //get the weihgt and quantity of the product
            $productos = Db::getInstance()->ExecuteS(
                'SELECT `product_quantity`, `product_weight`'
                . 'FROM `' . _DB_PREFIX_ . 'order_detail`'
                . 'WHERE `id_order` = ' . (int) $id_order
            );

            $peso          = 0;
            $num_productos = 0;

            foreach ($productos as $producto) {
                $peso += (float) ($producto['product_quantity'] * $producto['product_weight']);
                $num_productos += $producto['product_quantity'];
            }

            if ($peso < 1) {
                $peso = 1;
            }

            $datosGrabaEnvio['peso_origen'] = $peso;

            // calculated the number of parcels for the number of articles
            $datosGrabaEnvio['numero_paquetes'] = 1;

            $bultos = Configuration::get('TIPSA_BULTOS');
            //regular parcels
            if ($bultos == 0) {
                $num_articulos = Configuration::get('TIPSA_NUM_FIJO_BULTOS');

                if ($num_articulos == '' || $num_articulos == 0) {
                    $num_articulos = 1;
                }

                $datosGrabaEnvio['numero_paquetes'] = (int) ($num_articulos);
            }

            //parcels by articles
            if ($bultos == 1) {
                $num_articulos = Configuration::get('TIPSA_NUM_BULTOS');
                if ($num_articulos == '' || $num_articulos == 0) {
                    $num_articulos = 1;
                }

                $tipsa_numero_paquetes = Tools::ceilf($num_productos / $num_articulos);
                //$num_articulos : num bultos variables en la config
                $datosGrabaEnvio['numero_paquetes'] = (int) $tipsa_numero_paquetes;
            }

            $forcedBultos = (int) Db::getInstance()->getValue('SELECT `bultos` FROM  `' . _DB_PREFIX_ . 'tipsa_envios` WHERE `id_envio_order` = ' . (int) $id_order);

            if ($forcedBultos > 0) {
                $datosGrabaEnvio['numero_paquetes'] = $forcedBultos;
            }

            $datosGrabaEnvio['nombre_destinatario'] = $shippingDetails['firstname'] . '' . $shippingDetails['lastname'];

            if ($datosGrabaEnvio['tipo_servicio'] == $this->service_codes_yupick) {
                $datosGrabaEnvio['nombre_destino'] = $shippingDetails['alias'];
            } else {
                $datosGrabaEnvio['nombre_destino'] = $datosGrabaEnvio['nombre_destinatario'];
            }
            //Order Information
            $datosGrabaEnvio['referencia'] = $shippingDetails['reference'];

            //$tipsa_importe_servicio = $shippingDetails['total_paid_real'];
            $tipsa_importe_servicio = $shippingDetails['total_paid'];
            //Address Information

            $datosGrabaEnvio['dir_destinatario']       = $shippingDetails['address1'] . ' / ' . $shippingDetails['address2'];
            $datosGrabaEnvio['poblacion_destinatario'] = $shippingDetails['city'];
            $datosGrabaEnvio['cp_destinatario']        = $shippingDetails['postcode'];

            if (!empty($shippingDetails['phone_mobile']) || $shippingDetails['phone_mobile'] = '0') {
                $datosGrabaEnvio['telefono'] = $shippingDetails['phone_mobile'];
            } else {
                $datosGrabaEnvio['telefono'] = $shippingDetails['phone'];
            }

            $datosGrabaEnvio['phone_mobile'] = $shippingDetails['phone_mobile'];
            $datosGrabaEnvio['email']        = $shippingDetails['email'];
            $iso_code                        = $shippingDetails['iso_code'];

            //Check for Cash On Delivery methods.

            $config = $this->getTipsaConfiguration();

            $datosGrabaEnvio['reembolso'] = 0;
            if (in_array($shippingDetails['module'], explode(', ', $config['cashondelivery_modules']))) {
                $datosGrabaEnvio['reembolso'] = (float) ($tipsa_importe_servicio);
            }

            //check the country of addresse
            if ($iso_code != 'ES' && $iso_code != 'PT' && $iso_code != 'AD') {
                $datosGrabaEnvio['iso_code'] = $iso_code;
            }

            //Modification if PT CP is used.
            if ($iso_code == 'PT') {
                $tipsa_port                         = Tools::substr($datosGrabaEnvio['cp_destinatario'], 0, 4);
                $datosGrabaEnvio['cp_destinatario'] = '6' . $tipsa_port;
            }

            $vendedor = Configuration::getMultiple(array('PS_SHOP_NAME', 'PS_SHOP_EMAIL', 'PS_SHOP_ADDR1', 'PS_SHOP_ADDR2',
                'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY_ID', 'PS_SHOP_STATE_ID', 'PS_SHOP_PHONE', 'PS_SHOP_FAX'));

            $datosGrabaEnvio['nombre_remitente']    = $vendedor['PS_SHOP_NAME'];
            $datosGrabaEnvio['dir_remitente']       = $vendedor['PS_SHOP_ADDR1'];
            $datosGrabaEnvio['poblacion_remitente'] = $vendedor['PS_SHOP_CITY'];
            $datosGrabaEnvio['telefono_remitente']  = $vendedor['PS_SHOP_PHONE'];
            $datosGrabaEnvio['cp_remitente']        = $vendedor['PS_SHOP_CODE'];

            $urlws = $this->getUrlConfiguration('WS');

            $note = WebServiceTipsa::wsGrabaEnvio($datosGrabaEnvio, $urlws);

            foreach ($note as $value) {
                if (!isset($value->getElementsByTagName("faultstring")->item(0)->nodeValue)) {
                    $details         = $value->getElementsByTagName("strAlbaranOut");
                    $albaran         = $details->item(0)->nodeValue;
                    $numseg          = $value->getElementsByTagName("strGuidOut");
                    $num_seguimiento = $numseg->item(0)->nodeValue;
                } else {
                    $error     = $value->getElementsByTagName("faultstring")->item(0)->nodeValue;
                    $errorcode = $value->getElementsByTagName("faultcode")->item(0)->nodeValue;
                }

                //Error Information
                if (isset($errorcode) && $errorcode == 'EROSessionNotFound') {
                    $albaran = 'Agencia, usuarioopasswordincorrecto';
                }

                if (isset($datosGrabaEnvio['idsesion'])) {
                    if (isset($error)) {
                        $albaran = explode(":", $error);
                        $albaran = $albaran[0];
                        switch ($albaran) {
                            case '9':
                                $albaran = 'CPincorrecto';
                                break;
                            case '6':
                                $albaran = 'Tiposervicionoexisteoestáinactivo';
                                break;
                            case '28':
                                $albaran = 'Servicioincorrectoparaeldestinoseleccionado';
                                break;
                            case '100':
                                $albaran = 'Agencia, usuarioopasswordincorrecto';
                                break;
                        }

                        PrestaShopLogger::addLog($this->l('TIPSAERROR:') . $error . 'Error:' . $albaran, 3);
                    }
                }

                $num_albaran = $albaran;
                // Transform the url detail
                if (isset($num_seguimiento)) {
                    $cod_tracking = Tools::substr($num_seguimiento, 1, 36);
                }

                if (!$cod_tracking) {
                    PrestaShopLogger::addLog($this->l('TIPSAERROR:ThereisnoTrackingcode'), 3);
                }
            }

            $note = WebServiceTipsa::consEtiquetaEnvio($datosGrabaEnvio['idsesion'], $num_albaran, $urlws);

            foreach ($note as $value) {
                $details        = $value->getElementsByTagName("strEtiqueta");
                $tipsa_etiqueta = $details->item(0)->nodeValue;
            }

            if ($ruta = $this->saveTipsaShipping($id_order, $cod_tracking, $url_seguimiento, $num_albaran, $tipsa_etiqueta)) {
                return $this->displayConfirmation($this->l('Labelgeneratedsuccessfully'));
            } else {
                $ruta           = '../modules/tipsacarrier/pdf';
                $permisos       = Tools::substr(sprintf(' % o', fileperms($ruta)), -4);
                $this->errors[] = sprintf(Tools::displayError('Error:PleasecheckloginyourShopLogs . Permissionerrors % s . '), $permisos);
                PrestaShopLogger::addLog('Error:PleasecheckloginyourShopLogs . Permissionerrors' . $permisos, 3);
            }
        }
    }

    /**
     * Create the label for Tipsa Shipping inside the PDF folder.
     *
     * @param int $id_order
     * @param int $codigo_envio
     * @param string $url_track
     * @param string $num_albaran
     * @param string $codigo_barras
     *
     * @return boolean|string Path for PDF file
     */
    public function saveTipsaShipping($id_order, $codigo_envio, $url_track, $num_albaran, $codigo_barras)
    {
        $nombre       = 'etiqueta_' . $id_order . '.pdf';
        $ruta         = '../modules/tipsacarrier/pdf/' . $nombre;
        $descodificar = base64_decode($codigo_barras);
        if (!$fp2 = fopen($ruta, 'wb+')) {
            $this->errors[] = sprintf(Tools::displayError('Impossibletocreatefileon % s . '), $ruta);
            return false;
        }
        if (!fwrite($fp2, trim($descodificar))) {
            $this->errors[] = sprintf(Tools::displayError('Impossibletowritefileon % s . '), $ruta . $nombre);
        }
        fclose($fp2);

        $fecha           = date('d/m/y');
        $cortar          = explode('?', $url_track);
        $url_seguimiento = $cortar[0];
        $enlace          = $url_seguimiento . '?servicio=' . $codigo_envio . '&fecha=' . $fecha;
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'tipsa_envios`'
            . 'SET `codigo_envio`     = "' . $codigo_envio . '", '
            . '`url_track`           = "' . $enlace . '", '
            . '`num_albaran`         = "' . $num_albaran . '", '
            . '`codigo_barras`       = "' . $ruta . '", '
            . '`fecha`               = "' . date('Y-m-d H:i:s') . '"'
            . 'WHERE `id_envio_order` = "' . $id_order . '"');

        //$tipsaCodigoAgencia = Configuration::get('TIPSA_CODIGO_AGENCIA');

        $config = $this->getTipsaConfiguration();

        $codage = $config['tipsa_codage'];
        //$codcli = $config['tipsa_codcli'];

        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'orders`'
            . 'SET `shipping_number` = "' . $codage . $codage . $num_albaran . '"'
            . 'WHERE `id_order`      = ' . $id_order);
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'order_carrier`'
            . 'SET `tracking_number` = "' . $codage . $codage . $num_albaran . '"'
            . 'WHERE `id_order`      = ' . $id_order);

        return $ruta;
    }

    /**
     * Calls the Webservice to recover the actual status from a Shipping
     *
     * @param int $id_envio Id Envio inside PrestaShop Tipsa Table.
     */
    public function updateStatus($id_envio)
    {
        $sql = 'SELECT `id_envio_order`, `num_albaran`'
        . 'FROM `' . _DB_PREFIX_ . 'tipsa_envios`'
        . 'WHERE `id_envio` = ' . (int) $id_envio;
        $envioObj = Db::getInstance()->getRow($sql);

        $albaran  = $envioObj['num_albaran'];
        $id_order = $envioObj['id_envio_order'];

        if (Configuration::get('TIPSA_MODE') == 1) {
            $tipsaCodigoAgencia = Configuration::get('TIPSA_CODAGE');
        } else {
            $tipsaCodigoAgencia = Configuration::get('TIPSA_CODAGE_TEST');
        }

        $urlws = $this->getUrlConfiguration('WS');

        $note = $this->loginTipsaUser();

        foreach ($note as $value) {
            $details  = $value->getElementsByTagName("strSesion");
            $idsesion = $details->item(0)->nodeValue;
        }

        $note = webservicetipsa::wsConsEnvEstado($idsesion, $tipsaCodigoAgencia, $tipsaCodigoAgencia, $albaran, $urlws);

        foreach ($note as $value) {
            $estado = $value->getElementsByTagName("strEnvEstados")->item(0)->nodeValue;
            $error  = (isset($value->getElementsByTagName("faultstring")->item(0)->nodeValue)) ? $value->getElementsByTagName("faultstring")->item(0)->nodeValue : null;
        }

        if (isset($error) && $error != null) {
            PrestaShopLogger::addLog('ErrorObtainingthestatesfromWebservice : ' . $error);
        }

        $estenv   = explode('V_COD_TIPO_EST', $estado);
        $elements = count($estenv);
        $estado   = explode('"', $estenv[$elements - 1]);
        $estado   = (int) $estado[1];

        //assing for the states in  with information of dinapaq
        switch ($estado) {
            // $estado = 1 --> TRANSITO
            case 1:
                $this->updateOrderStatus(Configuration::get('TIPSA_TRANSITO'), $id_order);
                break;
            // $estado = 2 --> REPARTO
            case 2:
                $this->updateOrderStatus(Configuration::get('TIPSA_TRANSITO'), $id_order);
                break;
            // $estado = 3 --> ENTREGADO
            case 3:
                $this->updateOrderStatus(Configuration::get('TIPSA_ENTREGADO'), $id_order);
                break;
            // $estado = 4 --> INCIDENCIA
            case 4:
                $this->updateOrderStatus(Configuration::get('TIPSA_INCIDENCIA'), $id_order);
                break;
            default:
                PrestaShopLogger::addLog('Order State not exists in PrestaShop or not found. ID: ' . $estado);
                break;
        }
    }

    /**
     * Change order Status in PrestaShop.
     *
     * @param int $id_order_state Id of Order status.
     * @param int $id_order Order to change status.
     */
    public function updateOrderStatus($id_order_state, $id_order)
    {
        $order_state = new OrderState($id_order_state);

        if (!Validate::isLoadedObject($order_state)) {
            $this->errors[] = sprintf(Tools::displayError('Order status #%d cannot be loaded'), $id_order_state);
        } else {
            $order = new Order((int) $id_order);
            if (!Validate::isLoadedObject($order)) {
                $this->errors[] = sprintf(Tools::displayError('Order #%d cannot be loaded'), $id_order);
            } else {
                $current_order_state = $order->getCurrentOrderState();
                if ($current_order_state->id == $order_state->id) {
                    $this->errors[] = sprintf(Tools::displayError('Order #%d has already been assigned this status.', $id_order));
                } else {
                    $history              = new OrderHistory();
                    $history->id_order    = $order->id;
                    $history->id_employee = (int) (isset(Context::getContext()->employee->id)) ? Context::getContext()->employee->id : 0;

                    $use_existings_payment = !$order->hasInvoice();
                    $history->changeIdOrderState((int) $order_state->id, $order, $use_existings_payment);

                    $carrier      = new Carrier($order->id_carrier, $order->id_lang);
                    $templateVars = array();
                    if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
                        $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
                    }

                    if ($history->addWithemail(true, $templateVars)) {
                        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                            foreach ($order->getProducts() as $product) {
                                if (StockAvailable::dependsOnStock($product['product_id'])) {
                                    StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
                                }
                            }
                        }
                    } else {
                        $this->errors[] = sprintf(Tools::displayError('Cannot change status for order #%d.'), $id_order);
                    }
                }
            }
        }
    }

    protected function getWarningMultishopHtml()
    {
        $message_warning = '';
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->smarty->assign(
                array(
                    $message_warning => $this->l('You cannot manage slides items from a "AllShops" or a "GroupShop" context, select directly the shop you want to edit'),
                )
            );

            return $this->display(__FILE__, 'views/templates/admin/message.tpl');
        } else {
            return '';
        }
    }

    protected function getCurrentShopInfoMsg()
    {
        $shop_info = null;
        $message_info = '';

        if (Shop::isFeatureActive()) {
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $shop_info = sprintf($this->l('The modifications will be applied to shop: %s'), $this->context->shop->name);
            } else if (Shop::getContext() == Shop::CONTEXT_GROUP) {
                $shop_info = sprintf($this->l('The modifications will be applied to this group: %s'), Shop::getContextShopGroup()->name);
            } else {
                $shop_info = $this->l('The modifications will be applied to all shops and shop groups');
            }

            $this->smarty->assign(
                array(
                    $message_info => $shop_info,
                )
            );

            return $this->display(__FILE__, 'views/templates/admin/message.tpl');
        } else {
            return '';
        }
    }
}
