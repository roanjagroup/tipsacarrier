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
    <div class="row">
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-heading">
                    <img src="{$base_url|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" width="40" class="img-responsive pull-right" /> 
                    {l s='Tipsa Carrier Origin' mod='tipsacarrier'}
                </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="col-xs-12">
                                <div id="message" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Client Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSA_CODCLI|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Agency Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSA_CODAGE|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Ref.' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_REFERENCE|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Sender' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_SHOPNAME|escape:'htmlall':'UTF-8'}"  disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Phone' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_SHOPPHONE|escape:'htmlall':'UTF-8'}"  disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Postal Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_CP|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='City' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_CITY|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Address' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_ADDRESS|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>    
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-heading">
                    <img src="{$base_url|escape:'htmlall':'UTF-8'}modules/{$module_name|escape:'htmlall':'UTF-8'}/logo.gif" alt="" width="40" class="img-responsive pull-right"/> 
                    {l s='Tipsa Carrier Delivery' mod='tipsacarrier'}
                </div>
                <form method="post" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}#tipsa_show_message">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="col-xs-12">
                                <div id="message" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Contact Info' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_DEST_CONTACT_INFO|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Phone' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_DEST_PHONE|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Postal Code' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_DEST_CP|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='City' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_DEST_CITY|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Code Agenci Yupick!' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSA_CODAGE_YP|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Address' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_DEST_ADDRESS|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='customer observation' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_ORDER_COMMENTS|escape:'htmlall':'UTF-8'}" disabled="disabled">
                                        </div>
                                    </div>
                                    <!-- comentarios para el transportista -->
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='carrier observation' mod='tipsacarrier'}</label>
                                        <div class="col-lg-10">
                                            <input type="text" value="{$TIPSACARRIER_ORDER_COMMENTS_CARRIER|escape:'htmlall':'UTF-8'}" id="observation_carrier" name="observation_carrier">
                                        </div>
                                    </div>                                    
                                    <!-- comentarios para el transportista -->
                                    <div class="form-group">
                                        <label class="control-label col-lg-2">{l s='Packages' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{$TIPSACARRIER_PACKAGES|escape:'htmlall':'UTF-8'}" id="packages" name="packages">
                                        </div>
                                        <label class="control-label col-lg-2">{l s='Cash on delivery' mod='tipsacarrier'}</label>
                                        <div class="col-lg-4">
                                            <input type="text" value="{convertPrice price=$TIPSACARRIER_DEST_COD}" disabled="disabled">
                                        </div>
                                    </div>
                                    <input type="hidden" id="TIPSA_ID_ENVIO" name="TIPSA_ID_ENVIO" value="{$TIPSACARRIER_ID_ENVIO|escape:'htmlall':'UTF-8'}">
                                    <div class="btn-group pull-right" role="group">
                                        {if ($showLabelButton)}
                                            <button type="submit" id="submitGenerateLabeltipsa_envios" class="btn btn-default" name="generateLabeltipsa_envios" onclick="if (!confirm('{l s='Are you sure?' mod='tipsacarrier'}'))
                                                        return false;">
                                                        <i class="icon-barcode"></i>
                                                {l s='Generate Label' mod='tipsacarrier'}
                                            </button>
                                        {else}
                                            <a class="btn btn-default"  target="_blank" href="{$linkLabel|escape:'htmlall':'UTF-8'}">
                                                {l s='Download label' mod='tipsacarrier'}
                                                <i class="icon-external-link"></i>
                                            </a>
                                        {/if}
                                        <button type="submit" id="submitUpdateBultostipsa_envios" class="btn btn-primary " name="updateBultostipsa_envios" onclick="if (!confirm('{l s='Are you sure?' mod='tipsacarrier'}'))
                                                return false;">
                                                <i class="icon-save"></i>
                                            {l s='Update' mod='tipsacarrier'}
                                        </button>
                                    </div>
                                    &nbsp;&nbsp;&nbsp;

                                </div>
                            </div>
                        </div>
                    </div>    
                </form>
            </div>
        </div>
    </div>

