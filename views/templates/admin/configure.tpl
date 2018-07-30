{*
 * 2016-2018 TIPSA.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author Romell Jaramillo <integraciones@tip-sa.com>
 *  @copyright 2016-2018 TIPSA.COM
 *  @license GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 *}
<div class="panel active" id="test-configuration">
    <h3><i class="icon icon-credit-card"></i> {l s='Tipsa Carrier Shipping Manager' mod='tipsacarrier'}</h3>
    <p>
        <strong>{l s='With this module you can configure in few steps your Carrier Tipsa!' mod='tipsacarrier'}</strong><br />
        {l s='Thanks to this module you can now syncronize your shipping orders and update statuses ans print labels.' mod='tipsacarrier'}<br />
        {l s='Please follow this form to configure it and access to Menu -> Orders -> Tipsa Manager to manage your shippings.' mod='tipsacarrier'}
    </p>
    <br/>
    <p>
        {l s='This module is going to save a lot of time in your day. Thanks by choose Tipsa.' mod='tipsacarrier'}
    </p>
    <p>
        <form method="post">
            <button class="btn btn-info" name="testingConnection" type="submit">
              <i class="icon-link"></i>
              {l s='Test here' mod='tipsacarrier'}
           </button>
        </form>
    </p>
    <p>
        {l s='Cron Url to auto update' mod='tipsacarrier'}: {$tipsacarrier_cron|escape:'html':'UTF-8'}
    </p>
</div>
