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
    # module validation
    exit;
}

class AdminTipsacarrierModuleController extends ModuleAdminControllerCore
{

    public function __construct()
    {
        parent::__construct();

        $url = 'index.php?controller=adminmodules&configure=tipsacarrier&tab_module=shipping_logistics&module_name=tipsacarrier&token=' . Tools::getAdminTokenLite('AdminModules');
        Tools::redirectAdmin($url);
    }
}
