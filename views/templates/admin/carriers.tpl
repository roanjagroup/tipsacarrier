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
<div id="tab-carriers" class="panel">
    <div class="panel-heading">{l s='Available carriers' mod='tipsacarrier'}</div>
    <div class="panel-body">
        <table class="table table-condensed">
            <tbody>
                {assign var="botton_placed" value=0}
                {foreach from=$tipos_servicios item=servicio}
                    {$botton_placed=0}
                    <tr>                       
                        {if file_exists($path_module|cat:'views/img/'|cat:$logo_prefix|cat:$servicio.code|cat:'.jpg'|lower)}
                            <td>
                                <img class="imgm img-thumbnail" style="vertical-align:middle" src="{$path_img|escape:'htmlall':'UTF-8'}{$logo_prefix|escape:'htmlall':'UTF-8'}{$servicio.code|lower|escape:'htmlall':'UTF-8'}.jpg" alt="" title="" />
                            </td>
                        {/if}
                        <td>{$servicio.title|escape:'htmlall':'UTF-8'}</td>
                        <td>                            
                            {if $servicio.id_reference eq '0'}
                                {$botton_placed=1}
                                <form method="post">
                                    <button class="btn btn-success" name="form-carriers" type="submit">
                                        <i class="icon-plus-sign-alt"></i>
                                        {l s='Install' mod='tipsacarrier'}
                                    </button>
                                    <input type="hidden" name="servicio_code" value="{$servicio.code|escape:'htmlall':'UTF-8'}" />
                                </form> 
                           
                            {else}

                                {foreach from=$carriers item=carrier}
                                    {if $servicio.id_reference eq $carrier.id_reference}
                                        {$botton_placed=1}
                                        <a class="edit btn btn-default" title="{if $carrier.active eq 1}{l s='Configure' mod='tipsacarrier'}{else}{l s='Configure & Active' mod='tipsacarrier'}{/if}" href="{$link->getAdminLink('AdminCarrierWizard')|escape:'html':'UTF-8'}&id_carrier={$carrier.id_carrier|escape:'htmlall':'UTF-8'}">
                                        <i class="icon-pencil"></i>
                                        {if $carrier.active eq 1} 
                                            {l s='Configure' mod='tipsacarrier'} 
                                        {else} 
                                            {l s='Configure & Active' mod='tipsacarrier'} 
                                        {/if}
                                        </a>
                                    {/if}
                                {/foreach}
                                {if $botton_placed==0}
                                    <form method="post">
                                        <button class="btn btn-success" name="form-carriers" type="submit">
                                            <i class="icon-plus-sign-alt"></i>
                                            {l s='Install' mod='tipsacarrier'}
                                        </button>
                                        <input type="hidden" name="servicio_code" value="{$servicio.code|escape:'htmlall':'UTF-8'}" />
                                    </form>                        
                                {/if}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
        <br>
    </div>
</div>