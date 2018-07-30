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
    <script type="text/javascript">
        var yp_cart_id = {$yp_cart_id|escape:'htmlall':'UTF-8'};
    </script>
    <script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/yupick_jq_hook.js"></script>
    <div id="yupick_content" style="display: none;">
        <div id="yupick_loadingmask" style="display: none;">
            <img alt="" src="{$yp_module_dir|escape:'htmlall':'UTF-8'}views/img/opc-ajax-loader.gif"/>
            {l s='Loading...' mod='tipsacarrier'}
        </div>
        <div class="alert alert-warning" role="alert" id="info-yp" style="display: none;"> <strong>{l s='Info!' mod='tipsacarrier'}</strong> {l s='You must select a point' mod='tipsacarrier'} </div>
        <div id="oficinas_yupick_content" style="display: none;">
            <div class="yupick_carrier_secundario" id="yupick_carrier_secundario"  style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="yupick_calle">{l s='Street' mod='tipsacarrier'}</label>
                            <input class="form-control" id="yupick_calle" name="yupick_calle" placeholder="{l s='Street' mod='tipsacarrier'}" type="text" value="{$yp_client_dir|escape:'htmlall':'UTF-8'}"/>
                        </div>
                    </div>                    
                </div>
            </div>               
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="oficinas_yupick">{l s='Yupick offices! in the zone' mod='tipsacarrier'}</label>
                        <select class="form-control-select" id="oficinas_yupick" name="oficinas_yupick" onchange="yupickInfo();">
                        </select>
                    </div>
                    <input id="oficina_yupick_data" name="oficina_yupick_data" type="hidden" value=""/>
                    <input id="oficina_yupick_id" name="oficina_yupick_id" type="hidden" value=""/>
                    
                </div>
                <div class="col-md-6">
                    <div class="yupick_actions row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="yupick_cp">{l s='Zip Code' mod='tipsacarrier'}</label>
                                <input class="form-control" id="yupick_cp" name="yupick_cp" placeholder="{l s='Zip Code' mod='tipsacarrier'}" type="text" value="{$yp_client_cp|escape:'htmlall':'UTF-8'}"/>
                            </div>                            
                        </div>
                        <div class="col-md-4">
                             <div class="yupick_button_search">
                                <input class="btn btn-primary float-xs-right" onclick="GetYupickPoint(); return false; " type="button" value="{l s='Search' mod='tipsacarrier'}"/>
                            </div> 
                        </div>                      
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div id="yupick_info_map">
                    </div>
                </div>
                <div class="col-md-4">
                    <p id="oficina_yupick_data_text">
                    </p>
                    <hr>
                    <div id="yupick_info_time">
                    </div>                  
                </div>
            </div>         
            
            <div class="row">
                <div class="col-md-12">
                    <p style="padding-top:20px; color:blue;">
                        {l s='How do you want to be informed when your package is ready to be picked up?' mod='tipsacarrier'}
                    </p>
                </div>
                <div class="col-md-6">
                    <span class="checkbox">
                        <label>
                            <input checked="checked" id="yupick_type_alert" name="yupick_type_alert" type="radio" value="Email"/>
                            {l s='Email' mod='tipsacarrier'}
                        </label>
                        <input class="form-control" id="yupick_type_alert_email" name="yupick_type_alert_email" type="text" value="{$yp_client_email|escape:'htmlall':'UTF-8'}"/>
                    </span>
                </div>
                <div class="col-md-6">
                    <span class="checkbox">
                        <label>
                            <input id="yupick_type_alert" name="yupick_type_alert" type="radio" value="SMS"/>
                            {l s='Telephone' mod='tipsacarrier'}
                        </label>
                        <input class="form-control" id="yupick_type_alert_phone" name="yupick_type_alert_phone" type="text" value="{$yp_client_mobile|escape:'htmlall':'UTF-8'}"/>
                        <input id="yp_client_phone" name="yp_client_phone" type="hidden" value="{$yp_client_phone|escape:'htmlall':'UTF-8'}"/>
                    </span>
                </div>
            </div>            
            <br clear="left"/>
        </div>
    </div>
</contact@prestashop.com>